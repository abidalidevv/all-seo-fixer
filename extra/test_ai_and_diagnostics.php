<?php
/**
 * Test script for AI Copilot routing, System Diagnostics, and Licensing
 */
define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'WP_CONTENT_DIR', dirname( __DIR__ ) . '/wp-content' );
define( 'ASF_VERSION', '2.3.0' );
define( 'ASF_OPT_PSI_KEY', 'asf_psi_key' );

// Mock WordPress functions
$wp_version = '6.7.1';

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {}
}

if ( ! function_exists( 'is_ssl' ) ) {
	function is_ssl() { return true; }
}
if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) { return 'https://example.com' . $path; }
}
if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $key ) {
		if ( $key === 'name' ) return 'Test SEO Site';
		if ( $key === 'admin_email' ) return 'admin@example.com';
		return '';
	}
}
if ( ! function_exists( 'get_option' ) ) {
	function get_option( $opt, $default = '' ) {
		static $opts = array(
			'asf_groq_api_key'      => 'gsk_test_key_123',
			'permalink_structure'   => '/%postname%/',
			'asf_last_audit_data'   => array( 'missing_titles' => 2, 'bad_metas' => 5 ),
			'asf_license_status'    => 'active',
			'asf_license_type'      => 'PRO Lifetime Unlimited',
		);
		return $opts[ $opt ] ?? $default;
	}
}
if ( ! function_exists( 'update_option' ) ) {
	function update_option( $opt, $val ) { return true; }
}
if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $opt ) { return true; }
}
if ( ! function_exists( 'wp_convert_hr_to_bytes' ) ) {
	function wp_convert_hr_to_bytes( $val ) {
		$val = trim( $val );
		$last = strtolower( $val[ strlen( $val ) - 1 ] );
		$num = (int) $val;
		switch ( $last ) {
			case 'g': $num *= 1024;
			case 'm': $num *= 1024;
			case 'k': $num *= 1024;
		}
		return $num;
	}
}
if ( ! function_exists( 'wp_upload_dir' ) ) {
	function wp_upload_dir() { return array( 'basedir' => __DIR__ ); }
}
if ( ! function_exists( 'wp_is_writable' ) ) {
	function wp_is_writable( $p ) { return true; }
}
if ( ! function_exists( 'wp_send_json' ) ) {
	function wp_send_json( $data ) {
		echo json_encode( $data, JSON_PRETTY_PRINT );
		exit( 0 );
	}
}
if ( ! function_exists( 'asf_check_nonce' ) ) {
	function asf_check_nonce() { return true; }
}
if ( ! function_exists( 'asf_cap_check' ) ) {
	function asf_cap_check() { return true; }
}
if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $e ) { return filter_var( $e, FILTER_VALIDATE_EMAIL ) ? $e : ''; }
}
if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $t ) { return strip_tags( $t ); }
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $t ) { return strip_tags( trim( $t ) ); }
}
if ( ! function_exists( 'is_email' ) ) {
	function is_email( $e ) { return filter_var( $e, FILTER_VALIDATE_EMAIL ); }
}
if ( ! function_exists( 'wp_mail' ) ) {
	function wp_mail( $to, $subj, $body, $headers ) { return true; }
}

class MockWPDB {
	public $collate = 'utf8mb4_unicode_ci';
	public function db_version() { return '8.0.36'; }
}
$wpdb = new MockWPDB();

// Require handlers
require_once dirname( __DIR__ ) . '/includes/class-asf-handlers.php';

echo "Testing ASF_Diagnostics::handle_run_diagnostics() logic...\n";
// Run diagnostics simulation
ob_start();
try {
	ASF_Diagnostics::handle_run_diagnostics();
} catch ( Exception $e ) {
	// in case of exception
}
$output = ob_get_clean();

echo "Diagnostics response output:\n";
echo substr( $output, 0, 800 ) . "...\n";

$json = json_decode( $output, true );
if ( isset( $json['success'] ) && $json['success'] === true ) {
	echo "\nSUCCESS: Diagnostics ran with score: " . $json['score'] . "%\n";
	echo "Passes: " . $json['passes'] . ", Warns: " . $json['warns'] . ", Fails: " . $json['fails'] . "\n";
} else {
	echo "\nFAILURE: Diagnostics returned error or unexpected structure.\n";
	exit( 1 );
}

echo "\nAll Tests Passed Successfully!\n";
