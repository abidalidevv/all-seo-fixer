<?php
/**
 * Google Search Console (GSC) Indexing API, OAuth 2.0 & Rank Tracker
 *
 * Allows site owners to:
 *  - Connect with Google via 1-Click OAuth 2.0 (using logged-in Gmail account)
 *  - Or connect via Google Cloud Service Account JSON key (headless/cron)
 *  - Submit pages directly to Google for instant indexing via Indexing API v3
 *  - Fetch live 30-day top search keyword rankings, impressions, clicks, CTR & positions
 *  - Verify Search Console sitemap and indexation health
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_GSC {

	const INDEXING_API_ENDPOINT = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
	const OAUTH_AUTH_ENDPOINT   = 'https://accounts.google.com/o/oauth2/v2/auth';
	const OAUTH_TOKEN_ENDPOINT  = 'https://oauth2.googleapis.com/token';

	public static function init() {
		add_action( 'wp_ajax_asf_gsc_submit_url',       array( __CLASS__, 'handle_submit_url' ) );
		add_action( 'wp_ajax_asf_gsc_rank_tracker',     array( __CLASS__, 'handle_rank_tracker' ) );
		add_action( 'admin_init',                       array( __CLASS__, 'handle_oauth_callback' ) );
		add_action( 'admin_post_asf_gsc_disconnect',    array( __CLASS__, 'handle_disconnect_oauth' ) );
	}

	/**
	 * Returns true if GSC is connected via either OAuth 2.0 or Service Account JSON
	 */
	public static function is_connected() {
		$oauth = get_option( 'asf_gsc_oauth_tokens', array() );
		if ( ! empty( $oauth['access_token'] ) || ! empty( $oauth['refresh_token'] ) ) {
			return 'oauth';
		}
		$json_key = get_option( 'asf_gsc_service_account_json', '' );
		if ( ! empty( $json_key ) ) {
			return 'service_account';
		}
		return false;
	}

	/**
	 * Generates the 1-Click Google OAuth Authorization URL
	 */
	public static function get_google_auth_url() {
		$client_id = get_option( 'asf_gsc_client_id', '' );
		if ( empty( $client_id ) ) {
			return '';
		}

		$redirect_uri = admin_url( 'admin.php?page=asf-gsc' );
		$scopes = array(
			'https://www.googleapis.com/auth/webmasters.readonly',
			'https://www.googleapis.com/auth/indexing',
		);

		return self::OAUTH_AUTH_ENDPOINT . '?' . http_build_query( array(
			'client_id'              => $client_id,
			'redirect_uri'           => $redirect_uri,
			'response_type'          => 'code',
			'scope'                  => implode( ' ', $scopes ),
			'access_type'            => 'offline',
			'prompt'                 => 'consent',
			'include_granted_scopes' => 'true',
			'state'                  => wp_create_nonce( 'asf_gsc_oauth_state' ),
		) );
	}

	/**
	 * Catches Google OAuth redirect (?code=...) on admin.php?page=asf-gsc
	 */
	public static function handle_oauth_callback() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ( $_GET['page'] ?? '' ) !== 'asf-gsc' ) {
			return;
		}

		// Handle OAuth code exchange
		if ( ! empty( $_GET['code'] ) ) {
			$code          = sanitize_text_field( $_GET['code'] );
			$client_id     = get_option( 'asf_gsc_client_id', '' );
			$client_secret = get_option( 'asf_gsc_client_secret', '' );
			$redirect_uri  = admin_url( 'admin.php?page=asf-gsc' );

			if ( empty( $client_id ) || empty( $client_secret ) ) {
				return;
			}

			$res = wp_remote_post( self::OAUTH_TOKEN_ENDPOINT, array(
				'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
				'body'    => array(
					'code'          => $code,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'redirect_uri'  => $redirect_uri,
					'grant_type'    => 'authorization_code',
				),
				'timeout' => 15,
			) );

			if ( ! is_wp_error( $res ) ) {
				$body = json_decode( wp_remote_retrieve_body( $res ), true );
				if ( ! empty( $body['access_token'] ) ) {
					$existing = get_option( 'asf_gsc_oauth_tokens', array() );
					$tokens   = array(
						'access_token'  => $body['access_token'],
						'refresh_token' => $body['refresh_token'] ?? ( $existing['refresh_token'] ?? '' ),
						'expires_at'    => time() + intval( $body['expires_in'] ?? 3600 ),
						'connected_at'  => time(),
					);
					update_option( 'asf_gsc_oauth_tokens', $tokens );
					wp_safe_redirect( admin_url( 'admin.php?page=asf-gsc&gsc_connected=1' ) );
					exit;
				}
			}
		}
	}

	/**
	 * Disconnects Google OAuth
	 */
	public static function handle_disconnect_oauth() {
		check_admin_referer( 'asf_disconnect_gsc' );
		if ( current_user_can( 'manage_options' ) ) {
			delete_option( 'asf_gsc_oauth_tokens' );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=asf-gsc&gsc_disconnected=1' ) );
		exit;
	}

	/**
	 * Refreshes an expired Google OAuth access token using refresh_token
	 */
	private static function refresh_oauth_token( $refresh_token ) {
		$client_id     = get_option( 'asf_gsc_client_id', '' );
		$client_secret = get_option( 'asf_gsc_client_secret', '' );

		if ( empty( $client_id ) || empty( $client_secret ) || empty( $refresh_token ) ) {
			return false;
		}

		$res = wp_remote_post( self::OAUTH_TOKEN_ENDPOINT, array(
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body'    => array(
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'refresh_token' => $refresh_token,
				'grant_type'    => 'refresh_token',
			),
			'timeout' => 15,
		) );

		if ( is_wp_error( $res ) ) return false;

		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! empty( $body['access_token'] ) ) {
			$tokens = get_option( 'asf_gsc_oauth_tokens', array() );
			$tokens['access_token'] = $body['access_token'];
			$tokens['expires_at']   = time() + intval( $body['expires_in'] ?? 3600 );
			update_option( 'asf_gsc_oauth_tokens', $tokens );
			return $body['access_token'];
		}

		return false;
	}

	/**
	 * Universal Access Token Getter (Tries OAuth 2.0 first, then Service Account)
	 */
	private static function get_valid_access_token( $scope = 'https://www.googleapis.com/auth/webmasters.readonly' ) {
		// 1. Try Google OAuth 2.0 User Tokens
		$oauth = get_option( 'asf_gsc_oauth_tokens', array() );
		if ( ! empty( $oauth['access_token'] ) ) {
			if ( ! empty( $oauth['expires_at'] ) && $oauth['expires_at'] > time() + 180 ) {
				return $oauth['access_token'];
			}
			if ( ! empty( $oauth['refresh_token'] ) ) {
				$refreshed = self::refresh_oauth_token( $oauth['refresh_token'] );
				if ( $refreshed ) {
					return $refreshed;
				}
			}
		}

		// 2. Try Service Account JSON
		$json_key = get_option( 'asf_gsc_service_account_json', '' );
		if ( ! empty( $json_key ) ) {
			$key_data = json_decode( $json_key, true );
			if ( ! empty( $key_data['client_email'] ) && ! empty( $key_data['private_key'] ) ) {
				return self::get_service_account_token( $key_data['client_email'], $key_data['private_key'], $scope );
			}
		}

		return false;
	}

	/**
	 * Fetches Top 50 Keywords & Average Positions from GSC Search Analytics API
	 */
	public static function handle_rank_tracker() {
		asf_check_nonce();
		asf_cap_check();

		$access_token = self::get_valid_access_token( 'https://www.googleapis.com/auth/webmasters.readonly' );
		if ( ! $access_token ) {
			wp_send_json( array(
				'success' => false,
				'message' => 'Google Search Console is not connected. Please connect with your Google Account via 1-click Google Sign-In or add your Service Account JSON in Settings.',
			) );
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

		$url = esc_url_raw( $_REQUEST['url'] ?? '' );
		if ( ! $url ) {
			$url = home_url( '/' );
		}

		$access_token = self::get_valid_access_token( 'https://www.googleapis.com/auth/indexing' );

		if ( ! $access_token ) {
			// Fallback to standard Google Sitemap Ping if not authenticated
			$sitemap_ping = wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( home_url( '/sitemap_index.xml' ) ), array( 'timeout' => 5 ) );

			wp_send_json( array(
				'success' => true,
				'message' => '🚀 Ping sent to Google for ' . $url . '! (Connect Google Account or Service Account JSON to unlock direct GSC Indexing API v3 submission).',
			) );
		}

		// Submit URL to Google Indexing API v3
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
	 * Generates a Google OAuth 2.0 Access Token via RS256 Signed JWT for Service Accounts
	 */
	private static function get_service_account_token( $email, $private_key, $scope = 'https://www.googleapis.com/auth/indexing' ) {
		if ( ! function_exists( 'openssl_sign' ) ) {
			return false;
		}

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
			'aud'   => self::OAUTH_TOKEN_ENDPOINT,
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

		$res = wp_remote_post( self::OAUTH_TOKEN_ENDPOINT, array(
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
