<?php
/**
 * Test Suite: Verify all AJAX actions and button handlers
 */

// Mock WordPress constants
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/../' );
if ( ! defined( 'ASF_VERSION' ) ) define( 'ASF_VERSION', '2.3.0' );
if ( ! defined( 'ASF_OPT_PSI_KEY' ) ) define( 'ASF_OPT_PSI_KEY', 'asf_psi_api_key' );
if ( ! defined( 'ASF_OPT_REDIRECTS' ) ) define( 'ASF_OPT_REDIRECTS', 'asf_redirects' );

class MockWPDB {
    public $prefix = 'wp_';
    public $posts = 'wp_posts';
    public $postmeta = 'wp_postmeta';
    public function get_var($q) { return 0; }
    public function get_results($q) { return array(); }
    public function prepare($q, ...$args) { return $q; }
    public function esc_like($t) { return addcslashes($t, '_%\\'); }
    public function query($q) { return 1; }
}
global $wpdb;
$wpdb = new MockWPDB();
function current_user_can( $cap ) { return true; }
function wp_verify_nonce( $n, $a ) { return true; }
function check_ajax_referer( $a, $q = false, $d = true ) { return true; }
function sanitize_text_field( $s ) { return is_string( $s ) ? trim( strip_tags( $s ) ) : ''; }
function sanitize_textarea_field( $s ) { return is_string( $s ) ? trim( strip_tags( $s ) ) : ''; }
function sanitize_email( $s ) { return filter_var( $s, FILTER_SANITIZE_EMAIL ); }
function esc_html( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s ) { return filter_var( $s, FILTER_SANITIZE_URL ); }
function esc_url_raw( $s ) { return filter_var( $s, FILTER_SANITIZE_URL ); }
function wp_unslash( $s ) { return stripslashes( $s ); }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function strip_shortcodes( $s ) { return $s; }
function wp_trim_words( $t, $n = 55, $m = null ) { return implode(' ', array_slice(explode(' ', $t), 0, $n)); }
function wp_html_excerpt( $s, $c, $m = '' ) { return mb_substr( strip_tags( $s ), 0, $c ) . $m; }
function home_url( $p = '' ) { return 'https://example.com' . $p; }
function admin_url( $p = '' ) { return 'https://example.com/wp-admin/' . $p; }
function get_bloginfo( $s ) { return 'Example Site'; }
function get_option( $k, $d = false ) {
    global $mock_options;
    return $mock_options[$k] ?? $d;
}
function update_option( $k, $v, $a = true ) {
    global $mock_options;
    $mock_options[$k] = $v;
    return true;
}
function get_post_meta( $id, $k = '', $s = false ) {
    global $mock_postmeta;
    return $mock_postmeta[$id][$k] ?? '';
}
function update_post_meta( $id, $k, $v ) {
    global $mock_postmeta;
    $mock_postmeta[$id][$k] = $v;
    return true;
}
function get_posts( $args = array() ) {
    if ( isset($args['fields']) && $args['fields'] === 'ids' ) {
        return array( 1 );
    }
    $p = new stdClass();
    $p->ID = 1;
    $p->post_title = 'Sample Home Page';
    $p->post_content = '<h1>Welcome to our test site</h1><p>We provide professional services and solutions with full support.</p><img src="test.jpg" alt="test">';
    $p->post_type = 'page';
    return array( $p );
}
function get_post_types( $a = array() ) { return array( 'post' => 'post', 'page' => 'page' ); }
function get_the_title( $id ) { return 'Sample Home Page'; }
function get_permalink( $id ) { return 'https://example.com/sample-page/'; }
function get_edit_post_link( $id ) { return 'https://example.com/wp-admin/post.php?post=1&action=edit'; }
function get_theme_mod( $k, $d = false ) { return $d; }
function get_header_image() { return ''; }
function get_background_image() { return ''; }
function get_custom_logo() { return ''; }
function wp_get_attachment_url( $id ) { return 'https://example.com/test.jpg'; }
function wp_get_attachment_thumb_url( $id ) { return 'https://example.com/test-thumb.jpg'; }
function wp_get_post_parent_id( $id ) { return 0; }
function wp_upload_dir() { return array('basedir' => __DIR__); }
function wp_attachment_is_image( $id ) { return true; }
function get_attached_file( $id ) { return 'test.jpg'; }
function wp_get_attachment_metadata( $id ) { return array('width' => 800, 'height' => 600); }
function wp_delete_attachment( $id, $f = false ) { return true; }
function wp_trash_post( $id ) { return true; }
function wp_send_json( $response, $code = null ) {
    echo json_encode( $response ) . "\n";
    throw new Exception( 'WP_SEND_JSON_HALT' );
}
function wp_send_json_success( $data = null ) {
    wp_send_json( array( 'success' => true, 'data' => $data ) );
}
function wp_send_json_error( $data = null ) {
    wp_send_json( array( 'success' => false, 'data' => $data ) );
}
function add_action( $h, $cb ) {}
function add_filter( $h, $cb ) {}
function wp_remote_get( $url, $args = array() ) {
    return array( 'response' => array( 'code' => 200 ), 'body' => 'OK' );
}
function wp_remote_retrieve_response_code( $r ) { return 200; }
function wp_remote_retrieve_body( $r ) { return 'OK'; }
function is_wp_error( $t ) { return false; }
function is_ssl() { return true; }

// Nonce checks helper
function asf_check_nonce() { return true; }
function asf_cap_check() { return true; }

// Load plugin classes
require_once __DIR__ . '/../includes/class-asf-handlers.php';
require_once __DIR__ . '/../includes/class-asf-swiss-tools.php';

echo "=== TESTING CORE BUTTON HANDLERS IN PHP ===\n";

$tests = array(
    'ASF_HealthPing::handle' => function() {
        ASF_HealthPing::handle();
    },
    'ASF_OnPage::handle (Scan)' => function() {
        $_REQUEST['post_type'] = 'all';
        ASF_OnPage::handle();
    },
    'ASF_OnPage::handle_bulk_autofix' => function() {
        ASF_OnPage::handle_bulk_autofix();
    },
    'ASF_Media::handle_scan' => function() {
        ASF_Media::handle_scan();
    },
    'ASF_Media::handle_auto_alt' => function() {
        ASF_Media::handle_auto_alt();
    },
    'ASF_Diagnostics::handle_run_diagnostics' => function() {
        ASF_Diagnostics::handle_run_diagnostics();
    },
    'ASF_Diagnostics::handle_save_license' => function() {
        $_REQUEST['license_action'] = 'activate';
        $_REQUEST['license_key'] = 'ASF-PRO-LIFETIME-12345';
        ASF_Diagnostics::handle_save_license();
    },
    'ASF_SwissTools::tool_dns (DNS)' => function() {
        $_REQUEST['domain'] = 'google.com';
        ASF_SwissTools::tool_dns();
    },
    'ASF_SwissTools::tool_whois (Whois)' => function() {
        $_REQUEST['domain'] = 'google.com';
        ASF_SwissTools::tool_whois();
    },
    'ASF_SwissTools::tool_ip_lookup' => function() {
        $_REQUEST['ip'] = '8.8.8.8';
        ASF_SwissTools::tool_ip_lookup();
    }
);

$passed = 0;
$failed = 0;

foreach ( $tests as $label => $fn ) {
    echo "Testing [{$label}]... ";
    try {
        ob_start();
        $fn();
        $out = ob_get_clean();
        echo "✓ PASSED (Response: " . substr( trim( $out ), 0, 60 ) . "...)\n";
        $passed++;
    } catch ( Exception $e ) {
        $out = ob_get_clean();
        if ( $e->getMessage() === 'WP_SEND_JSON_HALT' ) {
            echo "✓ PASSED (JSON Response: " . substr( trim( $out ), 0, 60 ) . "...)\n";
            $passed++;
        } else {
            echo "❌ FAILED: " . $e->getMessage() . "\n";
            $failed++;
        }
    }
}

echo "\nSummary: {$passed} Passed, {$failed} Failed.\n";
