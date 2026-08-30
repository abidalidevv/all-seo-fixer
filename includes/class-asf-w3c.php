<?php
/**
 * W3C HTML Markup Validator API Handler
 * (100% Free — No API key needed)
 *
 * Queries the official W3C Nu HTML Checker API (https://validator.w3.org/nu/)
 * to audit HTML syntax errors, unclosed tags, duplicate IDs, and invalid attributes.
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_W3C {

	const W3C_ENDPOINT = 'https://validator.w3.org/nu/';

	public static function init() {
		add_action( 'wp_ajax_asf_w3c_validate', array( __CLASS__, 'handle_validate' ) );
	}

	public static function handle_validate() {
		asf_check_nonce();
		asf_cap_check();

		$url = esc_url_raw( $_GET['url'] ?? '' );
		if ( ! $url ) {
			wp_send_json( array( 'success' => false, 'message' => 'No URL provided.' ) );
		}

		$api_url = add_query_arg( array(
			'doc' => $url,
			'out' => 'json',
		), self::W3C_ENDPOINT );

		$response = wp_remote_get( $api_url, array(
			'timeout'    => 20,
			'user-agent' => 'All-in-One-SEO-Fixer/' . ASF_VERSION,
		) );

		if ( is_wp_error( $response ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Failed to reach W3C Validator: ' . $response->get_error_message() ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['messages'] ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid response from W3C Validator service.' ) );
		}

		$errors   = array();
		$warnings = array();

		foreach ( $body['messages'] as $msg ) {
			$item = array(
				'type'     => $msg['type'] ?? 'info',
				'message'  => $msg['message'] ?? '',
				'line'     => $msg['lastLine'] ?? 'N/A',
				'column'   => $msg['lastColumn'] ?? 'N/A',
				'extract'  => $msg['extract'] ?? '',
			);

			if ( ( $msg['type'] ?? '' ) === 'error' ) {
				$errors[] = $item;
			} else {
				$warnings[] = $item;
			}
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'url'           => $url,
				'error_count'   => count( $errors ),
				'warning_count' => count( $warnings ),
				'errors'        => array_slice( $errors, 0, 15 ),
				'warnings'      => array_slice( $warnings, 0, 10 ),
			),
		) );
	}
}
