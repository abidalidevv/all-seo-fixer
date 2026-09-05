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

		$url = esc_url_raw( $_REQUEST['url'] ?? '' );
		if ( ! $url ) {
			$url = home_url( '/' );
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

		$errors      = array();
		$warnings    = array();
		$css_notices = array();

		foreach ( $body['messages'] as $msg ) {
			$type         = $msg['type'] ?? 'info';
			$sub_type     = $msg['subType'] ?? '';
			$message_text = $msg['message'] ?? '';

			// 1. Skip benign WordPress Core quirks and parser threshold notices (as requested)
			if (
				strpos( $message_text, 'The “type” attribute is unnecessary for JavaScript resources' ) !== false ||
				strpos( $message_text, 'The “type” attribute is unnecessary for style resources' ) !== false ||
				strpos( $message_text, 'Cannot recover after last error' ) !== false ||
				strpos( $message_text, 'Trailing slash on void element' ) !== false ||
				strpos( $message_text, 'executable code' ) !== false
			) {
				continue;
			}

			// 2. Separate CSS inline/customizer parse errors from critical HTML tag errors
			if ( strpos( $message_text, 'CSS:' ) !== false || strpos( $message_text, 'Parse Error' ) !== false ) {
				$clean_key = preg_replace( '/^CSS:\s*“?([^”:]+)”?:\s*/i', '$1', $message_text );
				if ( ! isset( $css_notices[ $clean_key ] ) ) {
					$css_notices[ $clean_key ] = array(
						'property' => $clean_key,
						'message'  => $message_text,
						'count'    => 1,
						'line'     => $msg['lastLine'] ?? 'N/A',
						'extract'  => isset( $msg['extract'] ) ? esc_html( mb_substr( $msg['extract'], 0, 150 ) ) : '',
						'guide'    => 'Empty color/background setting in WordPress Theme Customizer (Appearance → Customize → Colors / Layout). This is harmless and does NOT break page layout or SEO.',
					);
				} else {
					$css_notices[ $clean_key ]['count']++;
				}
				continue;
			}

			// 3. True structural HTML issues with safe WordPress fix guidance
			$guide = 'Check recent template or block editor content for formatting issues.';
			if ( strpos( $message_text, 'between “head” and “body”' ) !== false ) {
				$guide = 'A tracking script was placed between &lt;/head&gt; and &lt;body&gt;. In WPCode or header.php, move tracking codes inside &lt;head&gt; or inside &lt;body&gt; to keep HTML5 strictly valid.';
			} elseif ( stripos( $message_text, 'Unclosed element' ) !== false || stripos( $message_text, 'End tag' ) !== false ) {
				$guide = 'Unclosed or misplaced HTML tag. Check custom HTML blocks or widgets on this page.';
			} elseif ( stripos( $message_text, 'Duplicate ID' ) !== false ) {
				$guide = 'Duplicate HTML ID attribute. Ensure widget, menu, or block IDs are unique on the page.';
			} elseif ( stripos( $message_text, 'alt' ) !== false && stripos( $message_text, 'img' ) !== false ) {
				$guide = 'Missing image alt attribute. Use the Image SEO Optimizer tab to auto-generate image alt tags.';
			}

			$item = array(
				'type'     => $type,
				'sub_type' => $sub_type,
				'message'  => $message_text,
				'line'     => $msg['lastLine'] ?? 'N/A',
				'column'   => $msg['lastColumn'] ?? 'N/A',
				'extract'  => isset( $msg['extract'] ) ? esc_html( mb_substr( $msg['extract'], 0, 150 ) ) : '',
				'guide'    => $guide,
			);

			if ( $type === 'error' ) {
				$errors[] = $item;
			} else {
				$warnings[] = $item;
			}
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'url'              => $url,
				'error_count'      => count( $errors ),
				'warning_count'    => count( $warnings ),
				'css_notice_count' => count( $css_notices ),
				'errors'           => array_slice( $errors, 0, 15 ),
				'warnings'         => array_slice( $warnings, 0, 10 ),
				'css_notices'      => array_values( $css_notices ),
			),
		) );
	}
}
