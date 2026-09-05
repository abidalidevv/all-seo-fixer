<?php
/**
 * Test Dashboard Fixes and Enhancements
 */

// Define stubs if WP not loaded
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/../' );
    define( 'ASF_PLUGIN_DIR', __DIR__ . '/../' );
    define( 'ASF_OPT_REDIRECTS', 'asf_redirects' );
    define( 'ASF_OPT_PSI_KEY', 'asf_pagespeed_api_key' );
    define( 'ASF_VERSION', '3.5.0' );

    function esc_html( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
    function esc_url( $s ) { return filter_var( $s, FILTER_SANITIZE_URL ); }
    function esc_textarea( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
    function admin_url( $path = '' ) { return 'https://example.com/wp-admin/' . $path; }
    function home_url( $path = '' ) { return 'https://example.com' . $path; }
    function get_option( $k, $d = false ) { return $d; }
    function update_option( $k, $v ) { return true; }
    function wp_count_posts( $type = 'post' ) {
        $obj = new stdClass();
        $obj->publish = 127;
        $obj->inherit = 169;
        return $obj;
    }
    function current_user_can( $cap ) { return true; }
}

echo "1. Testing dashboard.php render without stdClass warnings...\n";
ob_start();
require_once __DIR__ . '/../admin/views/dashboard.php';
$dash_html = ob_get_clean();

// Check for warning in output
if ( stripos( $dash_html, 'Warning: Object of class' ) !== false ) {
    echo "FAIL: stdClass warning found in dashboard.php output!\n";
    exit(1);
} else {
    echo "PASS: No PHP warnings during dashboard.php evaluation.\n";
}

// Check for missing cards in dashboard
$expected_cards = array(
    'asf-schema' => 'Schema JSON-LD Studio',
    'asf-geo' => 'GEO &amp; AI Search Hub',
    'asf-error-doctor' => 'Console &amp; Error Doctor',
    'asf-swiss-tools' => 'Swiss-Knife Multi-Tools',
);

foreach ( $expected_cards as $slug => $label ) {
    if ( strpos( $dash_html, $slug ) !== false ) {
        echo "PASS: Tool card '{$slug}' found in SEO Tools Command Center.\n";
    } else {
        echo "FAIL: Tool card '{$slug}' MISSING from dashboard.php!\n";
        exit(1);
    }
}

// Check Health Banner IDs
if ( strpos( $dash_html, 'id="asf-score-num"' ) !== false && strpos( $dash_html, 'id="asf-health-grade"' ) !== false ) {
    echo "PASS: Health Banner elements correctly formatted in dashboard.php.\n";
} else {
    echo "FAIL: Health Banner element IDs missing!\n";
    exit(1);
}

echo "\nAll Dashboard Verification Checks Passed Successfully!\n";
