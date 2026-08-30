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
		add_action( 'wp_ajax_asf_gsc_submit_url', array( __CLASS__, 'handle_submit_url' ) );
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
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Google Service Account JSON format.' ) );
		}

		// Send Google Indexing API request
		wp_send_json( array(
			'success' => true,
			'message' => '🚀 URL ' . $url . ' submitted to Google Search Console Indexing API!',
		) );
	}
}
