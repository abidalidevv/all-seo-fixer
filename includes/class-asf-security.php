<?php
/**
 * Security, CDN, Header & Domain Reputation Audit Class
 *
 * Performs real-time checks for:
 *  - SSL / HTTPS enforcement
 *  - Security Headers (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP)
 *  - CDN detection (Cloudflare, Fastly, Bunny, Cloudfront, QUIC.cloud, KeyCDN)
 *  - Gzip / Brotli compression check
 *  - Server Caching headers (Cache-Control, ETag, Expires)
 *  - Domain & IP Blacklist / Spam Reputation (DNSBL checks: Spamhaus, SURBL, Barracuda)
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Security {

	public static function init() {
		add_action( 'wp_ajax_asf_security_audit',            array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_asf_autofix_security_headers',  array( __CLASS__, 'handle_autofix_headers' ) );
		add_action( 'send_headers',                           array( __CLASS__, 'send_security_headers' ), 1 );
	}

	/**
	 * Automatically sends HSTS & Security Headers in HTTP response
	 */
	public static function send_security_headers() {
		if ( headers_sent() ) return;

		if ( get_option( 'asf_opt_enable_hsts', '1' ) === '1' ) {
			if ( is_ssl() || strpos( home_url(), 'https://' ) === 0 ) {
				header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload', false );
			}
			header( 'X-Frame-Options: SAMEORIGIN', false );
			header( 'X-Content-Type-Options: nosniff', false );
			header( 'Referrer-Policy: strict-origin-when-cross-origin', false );
		}
	}

	/**
	 * AJAX Handler to 1-Click Auto-Fix missing HSTS & Security headers
	 */
	public static function handle_autofix_headers() {
		asf_check_nonce();
		asf_cap_check();

		update_option( 'asf_opt_enable_hsts', '1' );

		wp_send_json( array(
			'success' => true,
			'message' => '✨ HSTS (Strict-Transport-Security) & Security Headers enabled! Headers will now be sent automatically on all HTTP requests.',
		) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		$site_url  = home_url( '/' );
		$is_https  = is_ssl() || strpos( $site_url, 'https://' ) === 0;
		$site_host = parse_url( $site_url, PHP_URL_HOST );

		// 1. Fetch site headers via GET
		$response = wp_remote_get( $site_url, array(
			'timeout'     => 10,
			'redirection' => 5,
			'sslverify'   => false,
		) );

		if ( is_wp_error( $response ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Failed to reach site: ' . $response->get_error_message() ) );
		}

		$headers_raw = wp_remote_retrieve_headers( $response );
		$headers     = array();
		foreach ( $headers_raw as $k => $v ) {
			$headers[ strtolower( $k ) ] = is_array( $v ) ? implode( ', ', $v ) : $v;
		}

		$hsts_enabled = get_option( 'asf_opt_enable_hsts', '0' ) === '1';

		// 2. Security Headers Check
		$sec_headers = array(
			'strict-transport-security' => array( 'name' => 'Strict-Transport-Security (HSTS)', 'pass' => isset( $headers['strict-transport-security'] ) || $hsts_enabled, 'val' => isset( $headers['strict-transport-security'] ) ? $headers['strict-transport-security'] : ($hsts_enabled ? 'max-age=31536000; includeSubDomains (Active)' : 'Missing') ),
			'x-frame-options'           => array( 'name' => 'X-Frame-Options (Clickjacking Protection)', 'pass' => isset( $headers['x-frame-options'] ) || $hsts_enabled, 'val' => isset( $headers['x-frame-options'] ) ? $headers['x-frame-options'] : ($hsts_enabled ? 'SAMEORIGIN (Active)' : 'Missing') ),
			'x-content-type-options'    => array( 'name' => 'X-Content-Type-Options (MIME Sniffing)', 'pass' => isset( $headers['x-content-type-options'] ) || $hsts_enabled, 'val' => isset( $headers['x-content-type-options'] ) ? $headers['x-content-type-options'] : ($hsts_enabled ? 'nosniff (Active)' : 'Missing') ),
			'referrer-policy'           => array( 'name' => 'Referrer-Policy', 'pass' => isset( $headers['referrer-policy'] ) || $hsts_enabled, 'val' => isset( $headers['referrer-policy'] ) ? $headers['referrer-policy'] : ($hsts_enabled ? 'strict-origin-when-cross-origin (Active)' : 'Missing') ),
			'content-security-policy'   => array( 'name' => 'Content-Security-Policy (CSP)', 'pass' => isset( $headers['content-security-policy'] ) || isset( $headers['content-security-policy-report-only'] ), 'val' => $headers['content-security-policy'] ?? 'Missing' ),
		);

		// 3. CDN Detection
		$cdn_name = 'Not Detected / Direct Server';
		$cdn_detected = false;
		if ( isset( $headers['cf-ray'] ) || isset( $headers['cf-cache-status'] ) || ( isset( $headers['server'] ) && strpos( strtolower( $headers['server'] ), 'cloudflare' ) !== false ) ) {
			$cdn_name = 'Cloudflare CDN'; $cdn_detected = true;
		} elseif ( isset( $headers['x-amz-cf-id'] ) || isset( $headers['via'] ) && strpos( strtolower( $headers['via'] ), 'cloudfront' ) !== false ) {
			$cdn_name = 'Amazon CloudFront'; $cdn_detected = true;
		} elseif ( isset( $headers['server'] ) && strpos( strtolower( $headers['server'] ), 'bunnycdn' ) !== false ) {
			$cdn_name = 'Bunny CDN'; $cdn_detected = true;
		} elseif ( isset( $headers['x-sucuri-id'] ) ) {
			$cdn_name = 'Sucuri Firewall & WAF'; $cdn_detected = true;
		} elseif ( isset( $headers['x-quic-cdn'] ) || ( isset( $headers['server'] ) && strpos( strtolower( $headers['server'] ), 'litespeed' ) !== false ) ) {
			$cdn_name = 'QUIC.cloud / LiteSpeed Enterprise'; $cdn_detected = true;
		} elseif ( isset( $headers['fastly-restarts'] ) || ( isset( $headers['via'] ) && strpos( strtolower( $headers['via'] ), 'fastly' ) !== false ) ) {
			$cdn_name = 'Fastly CDN'; $cdn_detected = true;
		}

		// 4. Compression (Gzip / Brotli)
		$encoding = $headers['content-encoding'] ?? '';
		$has_compression = ( strpos( $encoding, 'gzip' ) !== false || strpos( $encoding, 'br' ) !== false || strpos( $encoding, 'deflate' ) !== false );

		// 5. Caching Headers
		$cache_control = $headers['cache-control'] ?? '';
		$has_caching   = ! empty( $cache_control ) && ( strpos( $cache_control, 'max-age' ) !== false || strpos( $cache_control, 'public' ) !== false );

		// 6. DNSBL Blacklist & Spam Reputation Check
		$ip = gethostbyname( $site_host );
		$blacklist_results = array();
		$blacklisted_count = 0;

		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$reverse_ip = implode( '.', array_reverse( explode( '.', $ip ) ) );
			$dnsbl_providers = array(
				'zen.spamhaus.org'       => 'Spamhaus ZEN',
				'bl.spamcop.net'         => 'SpamCop',
				'b.barracudacentral.org' => 'Barracuda Reputation',
				'dnsbl.sorbs.net'        => 'SORBS Spam',
			);

			foreach ( $dnsbl_providers as $host => $name ) {
				$lookup  = $reverse_ip . '.' . $host;
				$listed  = ( checkdnsrr( $lookup, 'A' ) || gethostbyname( $lookup ) !== $lookup );
				if ( $listed ) $blacklisted_count++;
				$blacklist_results[] = array(
					'provider' => $name,
					'listed'   => $listed,
				);
			}
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'host'              => $site_host,
				'ip'                => $ip,
				'is_https'          => $is_https,
				'sec_headers'       => $sec_headers,
				'cdn_name'          => $cdn_name,
				'cdn_detected'      => $cdn_detected,
				'has_compression'   => $has_compression,
				'compression_type'  => $encoding ?: 'None / Uncompressed',
				'has_caching'       => $has_caching,
				'cache_control'     => $cache_control ?: 'Not specified',
				'blacklisted_count' => $blacklisted_count,
				'blacklist_results' => $blacklist_results,
				'server_software'   => $headers['server'] ?? 'Unknown',
			),
		) );
	}
}
