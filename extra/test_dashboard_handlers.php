<?php
/**
 * Test Dashboard Handlers Logic
 */
define( 'ABSPATH', __DIR__ . '/../' );
define( 'ASF_PLUGIN_DIR', __DIR__ . '/../' );
define( 'ASF_OPT_REDIRECTS', 'asf_redirects' );
define( 'ASF_OPT_PSI_KEY', 'asf_pagespeed_api_key' );
define( 'ASF_VERSION', '3.5.0' );

// Mock WordPress functions
function get_bloginfo( $param ) {
    if ( $param === 'name' ) return 'Efix Repair UAE';
    if ( $param === 'description' ) return 'Premier Device Repair Services Dubai';
    if ( $param === 'admin_email' ) return 'admin@efix.ae';
    if ( $param === 'language' ) return 'en-US';
    return '';
}
function home_url( $path = '' ) { return 'https://efix.ae' . $path; }
function wp_count_posts( $type = 'post' ) {
    $o = new stdClass();
    $o->publish = 127;
    $o->inherit = 169;
    return $o;
}
function get_post_types( $args = array() ) { return array( 'post' => 'post', 'page' => 'page' ); }
function get_option( $k, $d = false ) {
    if ( $k === 'asf_schema_enable' ) return '1';
    if ( $k === 'asf_schema_org_name' ) return 'Efix Repair UAE';
    return $d;
}
function update_option( $k, $v ) { return true; }
function sanitize_title( $t ) { return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $t ) ); }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function strip_shortcodes( $s ) { return $s; }
function wp_trim_words( $text, $num_words = 55, $more = null ) {
    $words = explode( ' ', $text );
    if ( count( $words ) > $num_words ) {
        $words = array_slice( $words, 0, $num_words );
        return implode( ' ', $words ) . ( $more ?: '...' );
    }
    return $text;
}
function is_ssl() { return true; }
function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) { return true; }
function asf_check_nonce() {}
function asf_cap_check() {}

// Require class-asf-handlers.php after checking syntax
require_once __DIR__ . '/../includes/class-asf-handlers.php';

echo "Testing ASF_AutoFixer::generate_smart_meta_desc...\n";
// Create a dummy post
$test_post = new stdClass();
$test_post->ID = 999;
$test_post->post_title = 'iPhone 15 Screen Repair in Dubai';
$test_post->post_content = 'Get your broken iPhone 15 OLED screen replaced in under 30 minutes with original Apple parts, certified technicians, and 1-year warranty.';

function get_post( $id ) {
    global $test_post;
    return $test_post;
}
function get_the_title( $id ) {
    global $test_post;
    return $test_post->post_title;
}
function get_post_meta( $id, $key, $single = true ) {
    return '';
}
function get_permalink( $id ) { return 'https://efix.ae/iphone-15-screen-repair'; }

$desc = ASF_AutoFixer::generate_smart_meta_desc( 999 );
echo "Generated Meta Description: {$desc}\n";
echo "Character Length: " . mb_strlen( $desc ) . "\n";

if ( mb_strlen( $desc ) >= 110 && mb_strlen( $desc ) <= 160 && strpos( $desc, 'iPhone 15 Screen Repair' ) !== false ) {
    echo "PASS: High quality content-aware description generated!\n";
} else {
    echo "FAIL: Generated description does not meet criteria!\n";
    exit(1);
}

echo "\nAll Handlers Verification Passed!\n";
