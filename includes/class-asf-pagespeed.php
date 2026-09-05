<?php
/**
 * Google PageSpeed Insights v5 API Handler
 *
 * Fetches Performance, Accessibility, Best Practices, and SEO scores
 * along with Core Web Vitals (LCP, TBT, CLS, FCP, Speed Index)
 * and up to 5 actionable improvement opportunities.
 *
 * Requires free API key: https://developers.google.com/speed/docs/insights/v5/get-started
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_PageSpeed {

	const API_ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

	public static function init() {
		add_action( 'wp_ajax_asf_pagespeed_test',      array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_asf_pagespeed_benchmark', array( __CLASS__, 'handle_local_benchmark' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		$url      = esc_url_raw( $_REQUEST['url'] ?? '' );
		$strategy = strtoupper( $_REQUEST['strategy'] ?? 'MOBILE' );
		$strategy = in_array( $strategy, array( 'MOBILE', 'DESKTOP' ), true ) ? $strategy : 'MOBILE';
		$api_key  = trim( get_option( ASF_OPT_PSI_KEY, '' ) );

		if ( ! $url ) {
			$url = home_url( '/' );
		}

		$params = array(
			'url'      => $url,
			'strategy' => strtolower( $strategy ),
		);

		if ( ! empty( $api_key ) ) {
			$params['key'] = $api_key;
		}

		$endpoint = add_query_arg( $params, self::API_ENDPOINT );
		$endpoint .= '&category=performance&category=accessibility&category=best-practices&category=seo';

		$response = wp_remote_get( $endpoint, array( 'timeout' => 45 ) );

		if ( is_wp_error( $response ) ) {
			wp_send_json( array( 'success' => false, 'message' => $response->get_error_message() ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['lighthouseResult'] ) ) {
			$err_msg = $body['error']['message'] ?? 'Unexpected API response. Check your API key and URL.';
			wp_send_json( array( 'success' => false, 'message' => $err_msg ) );
		}

		$lr     = $body['lighthouseResult'];
		$cats   = $lr['categories'] ?? array();
		$audits = $lr['audits']     ?? array();

		// Helper to get rounded 0–100 category score
		$score = function ( $cat ) use ( $cats ) {
			return isset( $cats[ $cat ]['score'] ) ? (int) round( $cats[ $cat ]['score'] * 100 ) : 0;
		};

		// Helper to get display value from audit
		$display = function ( $key ) use ( $audits ) {
			return $audits[ $key ]['displayValue'] ?? '—';
		};

		// Collect improvement opportunities
		$opportunities = array();
		foreach ( $audits as $audit ) {
			if ( isset( $audit['details']['type'] )
				&& $audit['details']['type'] === 'opportunity'
				&& ( $audit['score'] ?? 1 ) < 0.9
			) {
				$opportunities[] = array(
					'title'       => $audit['title']       ?? '',
					'description' => $audit['description'] ?? '',
					'saving'      => $audit['displayValue'] ?? null,
				);
			}
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'url'           => $url,
				'strategy'      => $strategy,
				'performance'   => $score( 'performance' ),
				'accessibility' => $score( 'accessibility' ),
				'best_practices'=> $score( 'best-practices' ),
				'seo'           => $score( 'seo' ),
				'lcp'           => $display( 'largest-contentful-paint' ),
				'tbt'           => $display( 'total-blocking-time' ),
				'cls'           => $display( 'cumulative-layout-shift' ),
				'fcp'           => $display( 'first-contentful-paint' ),
				'speed_index'   => $display( 'speed-index' ),
				'opportunities' => array_slice( $opportunities, 0, 5 ),
			),
		) );
	}

	public static function handle_local_benchmark() {
		asf_check_nonce();
		asf_cap_check();

		$url = esc_url_raw( $_REQUEST['url'] ?? '' );
		if ( ! $url ) {
			$url = home_url( '/' );
		}

		// Microtime benchmark
		$start = microtime( true );

		$response = wp_remote_get( $url, array(
			'timeout'     => 25,
			'redirection' => 5,
			'sslverify'   => false,
			'headers'     => array(
				'Accept-Encoding' => 'gzip, deflate, br',
				'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 AllSEOFixerBenchmark/1.0',
			),
		) );

		$total_time = round( ( microtime( true ) - $start ) * 1000 ); // ms

		if ( is_wp_error( $response ) ) {
			wp_send_json( array(
				'success' => false,
				'message' => 'Connection failed: ' . $response->get_error_message(),
			) );
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		$headers     = wp_remote_retrieve_headers( $response );
		$body        = wp_remote_retrieve_body( $response );

		$raw_size_bytes = strlen( $body );
		$size_kb        = round( $raw_size_bytes / 1024, 2 );

		// Compression
		$content_encoding = strtolower( $headers['content-encoding'] ?? '' );
		$is_compressed    = ! empty( $content_encoding );
		$compression_type = $content_encoding ? strtoupper( $content_encoding ) : 'None (Uncompressed)';

		// Server & Caching
		$server_header  = $headers['server'] ?? 'Unknown Server';
		$cache_control  = $headers['cache-control'] ?? '';
		$cf_cache       = $headers['cf-cache-status'] ?? '';
		$x_cache        = $headers['x-cache'] ?? '';
		$is_cached      = ( stripos( $cf_cache, 'HIT' ) !== false || stripos( $x_cache, 'HIT' ) !== false || stripos( $cache_control, 'max-age' ) !== false );

		// DOM complexity
		$img_count    = preg_match_all( '/<img\b[^>]*>/i', $body, $m_img );
		$script_count = preg_match_all( '/<script\b[^>]*>/i', $body, $m_scr );
		$style_count  = preg_match_all( '/<link\b[^>]*rel=[\'"]stylesheet[\'"]/i', $body, $m_css );

		// TTFB estimate
		$ttfb_est = max( 20, round( $total_time * 0.65 ) );

		// Performance Scoring (0 - 100)
		$score = 100;

		// 1. TTFB Penalty
		if ( $total_time > 1500 ) {
			$score -= 35;
		} elseif ( $total_time > 800 ) {
			$score -= 20;
		} elseif ( $total_time > 400 ) {
			$score -= 10;
		}

		// 2. Compression Penalty
		if ( ! $is_compressed ) {
			$score -= 20;
		}

		// 3. Document Payload Size Penalty
		if ( $size_kb > 250 ) {
			$score -= 20;
		} elseif ( $size_kb > 100 ) {
			$score -= 10;
		}

		// 4. Resource Count Penalty
		if ( $script_count > 25 ) {
			$score -= 10;
		}
		if ( $style_count > 15 ) {
			$score -= 10;
		}

		$score = max( 15, min( 100, $score ) );

		if ( $score >= 90 ) {
			$grade = 'A+';
			$grade_color = '#10b981';
		} elseif ( $score >= 80 ) {
			$grade = 'A';
			$grade_color = '#10b981';
		} elseif ( $score >= 65 ) {
			$grade = 'B';
			$grade_color = '#3b82f6';
		} elseif ( $score >= 50 ) {
			$grade = 'C';
			$grade_color = '#f59e0b';
		} else {
			$grade = 'D';
			$grade_color = '#ef4444';
		}

		// Actionable advice
		$advice = array();
		if ( $total_time < 300 ) {
			$advice[] = array( 'type' => 'good', 'text' => '⚡ Lightning-fast server response (' . $total_time . 'ms). Your hosting and PHP engine are blazing fast!' );
		} else {
			$advice[] = array( 'type' => 'warn', 'text' => 'Server response time was ' . $total_time . 'ms. Consider using Redis object cache or a page caching plugin (e.g. WP Super Cache, LiteSpeed) to drop TTFB below 200ms.' );
		}

		if ( $is_compressed ) {
			$advice[] = array( 'type' => 'good', 'text' => 'HTTP Compression is active (' . $compression_type . '). HTML documents are delivered compressed.' );
		} else {
			$advice[] = array( 'type' => 'bad', 'text' => 'Gzip/Brotli compression is not active on this response! Enabling Gzip reduces HTML file transfer sizes by 60–80%.' );
		}

		if ( $size_kb > 120 ) {
			$advice[] = array( 'type' => 'warn', 'text' => 'HTML document is large (' . $size_kb . ' KB). Large HTML payloads delay mobile parsing and increase TTFB.' );
		} else {
			$advice[] = array( 'type' => 'good', 'text' => 'HTML document weight is lean and efficient (' . $size_kb . ' KB).' );
		}

		if ( $script_count > 20 ) {
			$advice[] = array( 'type' => 'warn', 'text' => $script_count . ' JavaScript tags detected in HTML. Combine or defer unused scripts to reduce render-blocking execution.' );
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'url'              => $url,
				'status_code'      => $status_code,
				'total_time_ms'    => $total_time,
				'ttfb_est_ms'      => $ttfb_est,
				'size_kb'          => $size_kb,
				'size_bytes'       => $raw_size_bytes,
				'is_compressed'    => $is_compressed,
				'compression_type' => $compression_type,
				'server'           => $server_header,
				'is_cached'        => $is_cached,
				'cache_header'     => $cache_control ?: ( $cf_cache ? 'Cloudflare: ' . $cf_cache : 'None' ),
				'script_count'     => $script_count,
				'style_count'      => $style_count,
				'img_count'        => $img_count,
				'score'            => $score,
				'grade'            => $grade,
				'grade_color'      => $grade_color,
				'advice'           => $advice,
			),
		) );
	}
}
