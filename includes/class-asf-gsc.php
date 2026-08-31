<?php
/**
 * Google Search Console (GSC) Indexing API & Inspection Helper
 *
 * Allows site owners to:
 *  - Submit pages directly to Google for instant indexing via Indexing API v3
 *  - Verify Search Console sitemap status
 *  - Check indexing eligibility guidelines
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_GSC {

	const INDEXING_API_ENDPOINT = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

	public static function init() {
		add_action( 'wp_ajax_asf_gsc_submit_url',    array( __CLASS__, 'handle_submit_url' ) );
		add_action( 'wp_ajax_asf_gsc_rank_tracker',  array( __CLASS__, 'handle_rank_tracker' ) );
	}

	/**
	 * Fetches Top 50 Keywords & Average Positions from GSC Search Analytics API
	 */
	public static function handle_rank_tracker() {
		asf_check_nonce();
		asf_cap_check();

		$json_key = get_option( 'asf_gsc_service_account_json', '' );
		if ( empty( $json_key ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Please paste your Google Service Account JSON key in Settings to unlock live keyword rank tracking.' ) );
		}

		$key_data = json_decode( $json_key, true );
		if ( empty( $key_data['private_key'] ) || empty( $key_data['client_email'] ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Google Service Account JSON format in Settings.' ) );
		}

		$access_token = self::get_access_token( $key_data['client_email'], $key_data['private_key'], 'https://www.googleapis.com/auth/webmasters.readonly' );
		if ( ! $access_token ) {
			wp_send_json( array( 'success' => false, 'message' => 'Failed to obtain GSC API OAuth token. Ensure Service Account has owner/full access to Search Console.' ) );
		}

		$site_url = home_url( '/' );
		$endpoint = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $site_url ) . '/searchAnalytics/query';

		$end_date   = date( 'Y-m-d', strtotime( '-2 days' ) );
		$start_date = date( 'Y-m-d', strtotime( '-30 days' ) );

		$body = wp_json_encode( array(
			'startDate'  => $start_date,
			'endDate'    => $end_date,
			'dimensions' => array( 'query' ),
			'rowLimit'   => 50,
		) );

		$res = wp_remote_post( $endpoint, array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body'    => $body,
			'timeout' => 15,
		) );

		if ( is_wp_error( $res ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'GSC API Error: ' . $res->get_error_message() ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		$rows = array();

		if ( isset( $data['rows'] ) && is_array( $data['rows'] ) ) {
			foreach ( $data['rows'] as $r ) {
				$rows[] = array(
					'keyword'     => $r['keys'][0] ?? '',
					'clicks'      => (int) ( $r['clicks'] ?? 0 ),
					'impressions' => (int) ( $r['impressions'] ?? 0 ),
					'ctr'         => round( ( $r['ctr'] ?? 0 ) * 100, 1 ) . '%',
					'position'    => round( $r['position'] ?? 0, 1 ),
				);
			}
		}

		wp_send_json( array(
			'success' => true,
			'data'    => $rows,
			'total'   => count( $rows ),
		) );
	}

	/**
	 * Submits a URL to Google for Instant Indexing / Re-Indexing
	 */
	public static function handle_submit_url() {
		asf_check_nonce();
		asf_cap_check();

		$url = esc_url_raw( $_GET['url'] ?? '' );
		if ( ! $url ) {
			wp_send_json( array( 'success' => false, 'message' => 'No URL provided.' ) );
		}

		$json_key = get_option( 'asf_gsc_service_account_json', '' );

		if ( empty( $json_key ) ) {
			// Fallback to standard Google Sitemap Ping if service account key is not configured
			$sitemap_ping = wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( home_url( '/sitemap_index.xml' ) ), array( 'timeout' => 5 ) );

			wp_send_json( array(
				'success' => true,
				'message' => '🚀 Ping sent to Google for ' . $url . '! (Add Service Account JSON in Settings to unlock direct GSC Indexing API v3 submission).',
			) );
		}

		// Service Account Submission logic
		$key_data = json_decode( $json_key, true );
		if ( empty( $key_data['private_key'] ) || empty( $key_data['client_email'] ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Google Service Account JSON format. Please paste a valid Service Account JSON key in Settings.' ) );
		}

		if ( ! function_exists( 'openssl_sign' ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'OpenSSL PHP extension is required for Google OAuth signing.' ) );
		}

		// 1. Generate JWT Bearer Token for Google OAuth 2.0
		$access_token = self::get_access_token( $key_data['client_email'], $key_data['private_key'] );
		if ( ! $access_token ) {
			wp_send_json( array( 'success' => false, 'message' => 'Failed to obtain OAuth 2.0 Access Token from Google. Check private key and client email in Settings.' ) );
		}

		// 2. Submit URL to Google Indexing API v3
		$body = wp_json_encode( array(
			'url'  => $url,
			'type' => 'URL_UPDATED',
		) );

		$response = wp_remote_post( self::INDEXING_API_ENDPOINT, array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body'    => $body,
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Google API Error: ' . $response->get_error_message() ) );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$res_body    = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code === 200 ) {
			wp_send_json( array(
				'success' => true,
				'message' => '🚀 SUCCESS! URL ' . $url . ' successfully submitted to Google Search Console Indexing API v3! Notification time: ' . ( $res_body['urlNotificationMetadata']['latestUpdate']['notifyTime'] ?? date( 'Y-m-d H:i:s' ) ),
			) );
		} else {
			$err_msg = $res_body['error']['message'] ?? 'HTTP ' . $status_code;
			wp_send_json( array(
				'success' => false,
				'message' => 'Google Indexing API Error (' . $status_code . '): ' . $err_msg,
			) );
		}
	}

	/**
	 * Generates a Google OAuth 2.0 Access Token via RS256 Signed JWT (Cached for 50 mins)
	 */
	private static function get_access_token( $email, $private_key, $scope = 'https://www.googleapis.com/auth/indexing' ) {
		$transient_key = 'asf_gsc_token_' . md5( $email . '_' . site_url() . '_' . $scope );
		$cached_token  = get_transient( $transient_key );
		if ( $cached_token ) {
			return $cached_token;
		}

		$header  = array( 'alg' => 'RS256', 'typ' => 'JWT' );
		$now     = time();
		$payload = array(
			'iss'   => $email,
			'scope' => $scope,
			'aud'   => 'https://oauth2.googleapis.com/token',
			'exp'   => $now + 3600,
			'iat'   => $now,
		);

		$b64_header  = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), base64_encode( wp_json_encode( $header ) ) );
		$b64_payload = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), base64_encode( wp_json_encode( $payload ) ) );

		$signature = '';
		$signed    = openssl_sign( $b64_header . '.' . $b64_payload, $signature, $private_key, 'SHA256' );
		if ( ! $signed ) return false;

		$b64_sig = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), base64_encode( $signature ) );
		$jwt     = $b64_header . '.' . $b64_payload . '.' . $b64_sig;

		$res = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body'    => array(
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion'  => $jwt,
			),
			'timeout' => 10,
		) );

		if ( is_wp_error( $res ) ) return false;

		$body  = json_decode( wp_remote_retrieve_body( $res ), true );
		$token = $body['access_token'] ?? false;

		if ( $token ) {
			set_transient( $transient_key, $token, 50 * MINUTE_IN_SECONDS );
		}

		return $token;
	}
}
