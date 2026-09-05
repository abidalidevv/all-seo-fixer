<?php
/**
 * Plugin Name: All-in-One SEO Fixer & Auditor
 * Plugin URI:  https://github.com/abidalidevv/all-seo-fixer
 * Description: Free, open-source 360° SEO Diagnostic & Auto-Fixer for ANY WordPress site. On-page SEO checker, broken link cleaner, PageSpeed Insights, Schema detector, Social Meta checker, Robots.txt & Sitemap validator, 301 Redirect Manager, and IndexNow auto-pinger. No subscription, no upsells — 100% free forever.
 * Version:     3.0.0
 * Author:      Abid Ali Dev
 * Author URI:  https://abidalidev.com
 * Text Domain: all-seo-fixer
 * Domain Path: /languages
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

/**
 * All-in-One SEO Fixer & Auditor
 *
 * @package   All_SEO_Fixer
 * @author    Abid Ali Dev <https://abidalidev.com>
 * @link      https://github.com/abidalidevv/all-seo-fixer
 * @copyright 2024 Abid Ali Dev
 * @license   GPL-2.0-or-later
 *
 * Plugin Structure:
 * ├── all-seo-fixer.php           ← You are here (bootstrap)
 * ├── readme.txt                  ← WordPress.org readme
 * ├── README.md                   ← GitHub readme
 * ├── assets/
 * │   ├── css/admin.css           ← Premium admin stylesheet
 * │   └── js/admin.js             ← All admin JavaScript + AJAX
 * ├── includes/
 * │   ├── class-asf-core.php      ← Always-on protection hooks
 * │   ├── class-asf-audit.php     ← 360° SEO audit AJAX handler
 * │   ├── class-asf-pagespeed.php ← Google PageSpeed Insights API
 * │   └── class-asf-handlers.php  ← OnPage, LinkCleaner, Media, Pinger
 * └── admin/
 *     ├── class-asf-admin.php     ← Menu registration + asset enqueue
 *     └── views/
 *         ├── dashboard.php       ← 360° Audit Dashboard
 *         ├── pagespeed.php       ← PageSpeed Insights
 *         ├── onpage.php          ← On-Page SEO Checker
 *         ├── link-cleaner.php    ← Broken Link Cleaner
 *         ├── media-scanner.php   ← Orphan Media Scanner
 *         ├── redirects.php       ← 301 Redirect Manager
 *         └── settings.php        ← Settings & API Keys
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── PHP Version Guard ──────────────────────────────────────
 * Prevents fatal errors / white-screen on PHP < 7.4.
 * Arrow functions (fn) require PHP 7.4+.
 * Auto-deactivates the plugin and shows a clear admin notice.
 * ─────────────────────────────────────────────────────────── */
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>'
			. '<strong>All-in-One SEO Fixer:</strong> Requires <strong>PHP 7.4+</strong>. '
			. 'Your server runs PHP ' . esc_html( PHP_VERSION ) . '. '
			. 'Ask your host to upgrade PHP. Plugin deactivated to protect your site.'
			. '</p></div>';
	} );
	add_action( 'admin_init', function () {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );
	return; // Stop loading — no fatal, no white screen
}

/* ─── Version & Path Constants ─────────────────────────────── */
define( 'ASF_VERSION',     '3.0.0' );
define( 'ASF_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'ASF_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'ASF_PLUGIN_FILE', __FILE__ );

/* ─── Option Keys ───────────────────────────────────────────── */
define( 'ASF_OPT_REDIRECTS', 'asf_custom_redirects' );
define( 'ASF_OPT_PSI_KEY',   'asf_psi_api_key' );

/* ─── Activation / Deactivation ────────────────────────────── */
register_activation_hook( __FILE__, 'asf_activate' );
function asf_activate() {
	if ( get_option( ASF_OPT_REDIRECTS ) === false ) {
		add_option( ASF_OPT_REDIRECTS, array() );
	}

	// Create custom DB table for 404 URL Hit Logger with index
	global $wpdb;
	$table_name = $wpdb->prefix . 'asf_404_logs';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		url varchar(255) NOT NULL,
		hits bigint(20) unsigned NOT NULL DEFAULT 1,
		last_seen int(11) NOT NULL,
		created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY url_idx (url(191))
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

register_deactivation_hook( __FILE__, 'asf_deactivate' );
function asf_deactivate() {
	// Flush rewrite rules on deactivation — no data deleted.
	flush_rewrite_rules();
}

/* ─── Security Helpers (used by all AJAX handlers) ─────────── */

/**
 * Verify nonce. Sends JSON error and dies on failure.
 */
function asf_check_nonce() {
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'asf_nonce' ) ) {
		wp_send_json( array( 'success' => false, 'message' => 'Security check failed. Please refresh and try again.' ) );
	}
}

/**
 * Check manage_options capability. Sends JSON error and dies on failure.
 */
function asf_cap_check() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( array( 'success' => false, 'message' => 'You do not have permission to perform this action.' ) );
	}
}

/* ─── Autoload All Classes ──────────────────────────────────── */
$asf_includes = array(
	ASF_PLUGIN_DIR . 'includes/class-asf-core.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-audit.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-pagespeed.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-security.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-performance.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-authority.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-builder.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-gsc.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-w3c.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-error-doctor.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-handlers.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-swiss-tools.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-schema.php',
	ASF_PLUGIN_DIR . 'includes/class-asf-geo.php',
	ASF_PLUGIN_DIR . 'admin/class-asf-admin.php',
);

foreach ( $asf_includes as $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

/* ─── Bootstrap All Classes ─────────────────────────────────── */
add_action( 'plugins_loaded', function () {
	ASF_Core::init();
	ASF_Audit::init();
	ASF_PageSpeed::init();
	ASF_Security::init();
	ASF_Performance::init();
	ASF_Authority::init();
	ASF_Builder::init();
	ASF_GSC::init();
	ASF_W3C::init();
	ASF_ErrorDoctor::init();
	ASF_AIChatbot::init();
	ASF_KeywordDensity::init();
	ASF_LazyLoad::init();
	ASF_OnPage::init();
	ASF_LinkCleaner::init();
	ASF_Media::init();
	ASF_Pinger::init();
	ASF_AutoFixer::init();
	ASF_HealthPing::init();
	ASF_SwissTools::init();
	ASF_Schema::init();
	ASF_GEO::init();
	ASF_Diagnostics::init();

	if ( is_admin() ) {
		ASF_Admin::init();
	}
} );
