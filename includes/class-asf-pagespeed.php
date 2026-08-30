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
		add_action( 'wp_ajax_asf_pagespeed_test', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		$url      = esc_url_raw( $_GET['url'] ?? '' );
		$strategy = strtoupper( $_GET['strategy'] ?? 'MOBILE' );
		$strategy = in_array( $strategy, array( 'MOBILE', 'DESKTOP' ), true ) ? $strategy : 'MOBILE';
		$api_key  = get_option( ASF_OPT_PSI_KEY, '' );

		if ( ! $url )     wp_send_json( array( 'success' => false, 'message' => 'No URL provided.' ) );
		if ( ! $api_key ) wp_send_json( array( 'success' => false, 'message' => 'PageSpeed Insights API key not configured. Go to Settings and add your free key.' ) );

		$endpoint = add_query_arg( array(
			'url'      => rawurlencode( $url ),
			'strategy' => $strategy,
			'key'      => $api_key,
		), self::API_ENDPOINT );

		$response = wp_remote_get( $endpoint, array( 'timeout' => 60 ) );

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
}
