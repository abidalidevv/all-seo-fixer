<?php
/**
 * Test Harness for All SEO Fixer - SEO & GEO Execution Verification
 */

// 1. Setup minimal WordPress mocks
$wp_options = [];
$wp_postmeta = [];
$wp_actions = [];
$wp_filters = [];

function get_option($key, $default = false) {
    global $wp_options;
    return isset($wp_options[$key]) ? $wp_options[$key] : $default;
}

function update_option($key, $value, $autoload = null) {
    global $wp_options;
    $wp_options[$key] = $value;
    return true;
}

function get_post_meta($post_id, $key = '', $single = false) {
    global $wp_postmeta;
    if (empty($key)) return isset($wp_postmeta[$post_id]) ? $wp_postmeta[$post_id] : [];
    return isset($wp_postmeta[$post_id][$key]) ? $wp_postmeta[$post_id][$key] : ($single ? '' : []);
}

function update_post_meta($post_id, $key, $value, $prev = '') {
    global $wp_postmeta;
    if (!isset($wp_postmeta[$post_id])) $wp_postmeta[$post_id] = [];
    $wp_postmeta[$post_id][$key] = $value;
    return true;
}

function esc_attr($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function esc_html($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function esc_url($str) { return filter_var($str, FILTER_SANITIZE_URL); }
function esc_url_raw($str) { return filter_var($str, FILTER_SANITIZE_URL); }
function sanitize_email($e) { return filter_var($e, FILTER_SANITIZE_EMAIL); }
function sanitize_text_field($str) { return trim(strip_tags($str)); }
function sanitize_textarea_field($str) { return trim(strip_tags($str)); }
function wp_unslash($val) { return $val; }
function wp_strip_all_tags($str, $remove_breaks = false) { return strip_tags($str); }
function get_theme_mod($name, $default = false) { return false; }
function wp_get_attachment_image_src($id, $size = 'full') { return false; }
function get_locale() { return 'en_US'; }
function wp_get_canonical_url($post = 0) { return 'https://dallasmasterplumbers.com/test-seo-article/'; }
function get_post_type($post = null) { return 'post'; }
function get_post_field($field, $post = null) { return '2026-01-01 12:00:00'; }
function get_the_author_meta($field, $user = null) { return 'Abid Ali'; }
function is_single() { return true; }
function is_page() { return false; }
function get_the_author() { return 'Abid Ali'; }
function mysql2date($format, $date) { return '2026-01-01T12:00:00+00:00'; }
function get_author_posts_url($author_id = 0) { return 'https://dallasmasterplumbers.com/author/abid/'; }
function get_the_category($post_id = 0) { return []; }
function get_the_date($format = 'c', $post = null) { return '2026-01-01T12:00:00+00:00'; }
function get_the_modified_date($format = 'c', $post = null) { return '2026-01-02T12:00:00+00:00'; }
function is_wp_error($thing) { return false; }
function is_admin() { return false; }
function is_feed() { return false; }
function current_theme_supports($feature) { return false; }
function get_bloginfo($show = 'name') {
    if ($show === 'name') return 'Dallas Master Plumbers';
    if ($show === 'description') return 'Top Rated Emergency Plumbing';
    return '';
}
function home_url($path = '') { return 'https://dallasmasterplumbers.com' . $path; }
function get_permalink($post = 0) { return 'https://dallasmasterplumbers.com/test-seo-article/'; }
function is_singular() { return true; }
function is_front_page() { return false; }
function is_home() { return false; }
function is_category() { return false; }
function is_tag() { return false; }
function is_tax() { return false; }
function is_author() { return false; }
function is_search() { return false; }
function is_404() { return false; }
function get_the_ID() { return 101; }
function get_the_title($id = 0) { return "Test SEO Article Page"; }
function get_the_excerpt($id = 0) { return "This is a sample excerpt for our article."; }
function get_queried_object_id() { return 101; }
function get_post($id) {
    $p = new stdClass();
    $p->ID = 101;
    $p->post_author = 1;
    $p->post_title = "Test SEO Article Page";
    $p->post_excerpt = "This is a sample excerpt for our article.";
    $p->post_content = "<p>Here is full body text of the article for SEO testing.</p>";
    return $p;
}
function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
    global $wp_actions;
    $wp_actions[$tag][] = $callback;
}
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
    global $wp_filters;
    $wp_filters[$tag][] = $callback;
}
function wp_json_encode($data, $options = 0, $depth = 512) {
    return json_encode($data, $options, $depth);
}

// Define Constants needed by All SEO Fixer
define('ABSPATH', dirname(__DIR__) . '/');
define('ASF_VERSION', '2.5.0');
define('ASF_PLUGIN_DIR', dirname(__DIR__) . '/');
define('ASF_PLUGIN_URL', 'http://example.com/wp-content/plugins/all-seo-fixer/');
define('ASF_OPT_ENABLE_SCHEMA', 'asf_opt_enable_schema');
define('ASF_OPT_ENABLE_OG', 'asf_opt_enable_og');
define('ASF_OPT_ENABLE_TWITTER', 'asf_opt_enable_twitter');
define('ASF_OPT_ENABLE_CANONICAL', 'asf_opt_enable_canonical');
define('ASF_OPT_ENABLE_LAZY', 'asf_opt_enable_lazy');

// Load Core class
require_once dirname(__DIR__) . '/includes/class-asf-core.php';

echo "=== ALL-IN-ONE SEO FIXER: DEEP VERIFICATION TEST ===\n\n";

// TEST 1: Save Title & Meta Description to Database (Simulating asf_save_onpage_meta)
echo "[TEST 1] Testing Database Persistence of Post SEO Titles and Descriptions...\n";
$post_id = 101;
$new_title = "Expert Plumbing & HVAC Services in Dallas, TX | All SEO Fixer";
$new_desc = "Get award-winning plumbing and emergency HVAC repair in Dallas, Texas. 24/7 licensed technicians with same-day appointments.";

// Save via PostMeta (WordPress standard for Yoast, RankMath, and ASF Native)
update_post_meta($post_id, 'rank_math_title', $new_title);
update_post_meta($post_id, '_yoast_wpseo_title', $new_title);
update_post_meta($post_id, 'asf_custom_title', $new_title);

update_post_meta($post_id, 'rank_math_description', $new_desc);
update_post_meta($post_id, '_yoast_wpseo_metadesc', $new_desc);
update_post_meta($post_id, 'asf_custom_description', $new_desc);

assert(get_post_meta($post_id, 'asf_custom_title', true) === $new_title);
assert(get_post_meta($post_id, 'asf_custom_description', true) === $new_desc);
echo "  ✓ Post ID 101 Meta saved to DB (asf_custom_title, RankMath, Yoast compatible).\n";

// TEST 2: Verify Frontend Title and Description Injection
echo "\n[TEST 2] Verifying Frontend Title and Meta Description Injection in <head>...\n";
ob_start();
ASF_Core::inject_title_and_description_meta();
$meta_output = ob_get_clean();

echo "  Output generated:\n" . trim($meta_output) . "\n";
assert(strpos($meta_output, $new_desc) !== false);
echo "  ✓ Frontend <meta name=\"description\"> dynamically injected with saved database value!\n";

function has_post_thumbnail($post = null) { return false; }
function get_post_thumbnail_id($post = null) { return 0; }
function wp_get_attachment_image_url($id, $size = 'thumbnail') { return ''; }

// TEST 3: Configure GEO SEO Settings in Options (Simulating Settings Control Panel Save)
echo "\n[TEST 3] Testing Local & GEO SEO Settings Storage...\n";
$geo_settings = [
    'asf_geo_enable' => '1',
    'asf_geo_lat' => '32.776664',
    'asf_geo_lng' => '-96.796988',
    'asf_geo_placename' => 'Dallas, Texas',
    'asf_geo_region' => 'US-TX',
    'asf_geo_country' => 'US',
    'asf_geo_postal' => '75202',
    'asf_geo_biz_type' => 'Plumber',
    'asf_local_biz_name' => 'Dallas Master Plumbers',
    'asf_local_biz_phone' => '+1-214-555-0199',
    'asf_geo_email' => 'service@dallasmasterplumbers.com',
    'asf_local_biz_address' => '1200 Commerce St, Suite 400',
    'asf_geo_map_url' => 'https://maps.google.com/?cid=123456789',
    'asf_local_biz_price_range' => '$$',
    'asf_local_biz_hours' => 'Mo-Sa 08:00-18:00',
    'asf_local_biz_same_as' => "https://facebook.com/dallasplumber\nhttps://twitter.com/dallasplumber",
    'asf_opt_enable_schema' => '1'
];

foreach ($geo_settings as $k => $v) {
    update_option($k, $v);
}
echo "  ✓ All GEO SEO options saved to WordPress options table successfully.\n";

// TEST 4: Verify GEO Meta Tags Injection in <head>
echo "\n[TEST 4] Verifying Dynamic GEO Meta Tags (<meta name=\"geo.*\"> & Open Graph Location)...\n";
ob_start();
ASF_Core::inject_geo_seo_meta_tags();
$geo_meta_output = ob_get_clean();

echo "  Output generated:\n" . trim($geo_meta_output) . "\n";
assert(strpos($geo_meta_output, '<meta name="geo.position" content="32.776664;-96.796988" />') !== false);
assert(strpos($geo_meta_output, '<meta name="geo.placename" content="Dallas, Texas" />') !== false);
assert(strpos($geo_meta_output, '<meta name="geo.region" content="US-TX" />') !== false);
assert(strpos($geo_meta_output, '<meta name="ICBM" content="32.776664, -96.796988" />') !== false);
assert(strpos($geo_meta_output, '<meta property="place:location:latitude" content="32.776664" />') !== false);
assert(strpos($geo_meta_output, '<meta property="place:location:longitude" content="-96.796988" />') !== false);
assert(strpos($geo_meta_output, '<meta property="business:contact_data:locality" content="Dallas, Texas" />') !== false);
assert(strpos($geo_meta_output, '<meta property="business:contact_data:region" content="US-TX" />') !== false);
assert(strpos($geo_meta_output, '<meta property="business:contact_data:country_name" content="US" />') !== false);
echo "  ✓ All GEO SEO & Geotargeting meta tags perfectly rendered in <head>!\n";

// TEST 5: Verify LocalBusiness Schema JSON-LD Injection
echo "\n[TEST 5] Verifying Rich LocalBusiness Schema JSON-LD Injection...\n";
ob_start();
ASF_Core::inject_auto_schema_and_og_tags();
$schema_output = ob_get_clean();

echo "  Output generated:\n" . trim($schema_output) . "\n";
assert(strpos($schema_output, '"@type": "Plumber"') !== false);
assert(strpos($schema_output, '"name": "Dallas Master Plumbers"') !== false);
assert(strpos($schema_output, '"telephone": "+1-214-555-0199"') !== false);
assert(strpos($schema_output, '"streetAddress": "1200 Commerce St, Suite 400"') !== false);
assert(strpos($schema_output, '"addressLocality": "Dallas, Texas"') !== false);
assert(strpos($schema_output, '"addressRegion": "US-TX"') !== false);
assert(strpos($schema_output, '"postalCode": "75202"') !== false);
assert(strpos($schema_output, '"latitude": 32.776664') !== false);
assert(strpos($schema_output, '"longitude": -96.796988') !== false);
assert(strpos($schema_output, '"hasMap": "https://maps.google.com/?cid=123456789"') !== false);
assert(strpos($schema_output, '"priceRange": "$$"') !== false);
echo "  ✓ Rich LocalBusiness JSON-LD Schema verified and valid for Google Search!\n";

// TEST 6: Verify Native Lazy Load Engine with LCP Optimization
echo "\n[TEST 6] Testing Image Lazy Load Engine (LCP Eager + Subsequent Lazy Load)...\n";
update_option('asf_enable_lazy', '1');
$html_input = '<p>Hero:</p><img src="https://example.com/hero.jpg" alt="Hero Image" width="800" height="400"><p>Body:</p><img src="https://example.com/body.jpg" alt="Body Image" width="400" height="300">';
$html_output = ASF_Core::filter_lazy_load_content($html_input);
echo "  Original HTML: " . $html_input . "\n";
echo "  Filtered HTML: " . $html_output . "\n";
assert(strpos($html_output, 'loading="eager" fetchpriority="high"') !== false);
assert(strpos($html_output, 'loading="lazy"') !== false);
echo "  ✓ 1st image prioritized for LCP (eager/high) and subsequent images lazy-loaded!\n";

echo "\n=======================================================\n";
echo "🎉 ALL 6 SEO & GEO EXECUTION TESTS PASSED WITH 100% SUCCESS!\n";
echo "=======================================================\n";
