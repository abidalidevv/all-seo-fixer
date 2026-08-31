<?php
/**
 * Admin Menu Registration + Asset Enqueueing
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Admin {

	/** Slug prefix for all plugin pages */
	const SLUG = 'asf-panel';

	public static function init() {
		add_action( 'admin_menu',            array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 1 );
		add_action( 'admin_head',            array( __CLASS__, 'print_inline_asf_data' ), 1 );
	}

	/** Prints window.asfData globally in <head> so JS can never miss it */
	public static function print_inline_asf_data() {
		$page = sanitize_text_field( $_GET['page'] ?? '' );
		if ( strpos( $page, 'asf' ) === false ) return;

		$data = array(
			'ajax'      => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'asf_nonce' ),
			'adminUrl'  => admin_url(),
			'siteUrl'   => home_url(),
			'hasPsiKey' => get_option( ASF_OPT_PSI_KEY, '' ) ? '1' : '0',
			'version'   => ASF_VERSION,
		);
		echo '<script type="text/javascript">window.asfData = ' . wp_json_encode( $data ) . ';</script>' . "\n";
	}

	/** Register all admin menu pages */
	public static function register_menu() {
		add_menu_page(
			__( 'All-in-One SEO Fixer', 'all-seo-fixer' ),
			__( 'All SEO Fixer', 'all-seo-fixer' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'dispatch' ),
			'dashicons-chart-bar',
			58
		);

		$pages = array(
			array( 'asf-panel',            '360° SEO Audit' ),
			array( 'asf-onpage',           'On-Page Checker' ),
			array( 'asf-pagespeed',        'PageSpeed Insights' ),
			array( 'asf-lazy-load',        'Lazy Load Images' ),
			array( 'asf-media',            'Media Scanner' ),
			array( 'asf-performance',      'Speed & DB Optimizer' ),
			array( 'asf-builder-analyzer', 'Page Builder Optimizer' ),
			array( 'asf-gsc-inspector',    'Google Search Console' ),
			array( 'asf-w3c-validator',    'W3C HTML Validator' ),
			array( 'asf-keyword-analyzer', 'Keyword Density' ),
			array( 'asf-authority',        'On-Page SEO Health' ),
			array( 'asf-serp-preview',     'SERP Simulator' ),
			array( 'asf-security',         'Security & Headers' ),
			array( 'asf-link-cleaner',     'Broken Link Cleaner' ),
			array( 'asf-redirects',        '301 Redirects' ),
			array( 'asf-settings',         'Settings' ),
		);

		foreach ( $pages as $page ) {
			add_submenu_page( self::SLUG, $page[1], $page[1], 'manage_options', $page[0], array( __CLASS__, 'dispatch' ) );
		}
	}

	/** Route current page slug to the correct view file */
	public static function dispatch() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$page = $_GET['page'] ?? self::SLUG;
		$map  = array(
			'asf-panel'            => 'dashboard',
			'asf-pagespeed'        => 'pagespeed',
			'asf-performance'      => 'performance',
			'asf-builder-analyzer' => 'builder-analyzer',
			'asf-onpage'           => 'onpage',
			'asf-gsc-inspector'    => 'gsc-inspector',
			'asf-w3c-validator'    => 'w3c-validator',
			'asf-keyword-analyzer' => 'keyword-analyzer',
			'asf-authority'        => 'authority',
			'asf-serp-preview'     => 'serp-preview',
			'asf-security'         => 'domain-security',
			'asf-link-cleaner'     => 'link-cleaner',
			'asf-media'            => 'media-scanner',
			'asf-lazy-load'        => 'lazy-load',
			'asf-redirects'        => 'redirects',
			'asf-settings'         => 'settings',
		);

		$view = $map[ $page ] ?? 'dashboard';
		$file = ASF_PLUGIN_DIR . 'admin/views/' . $view . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		} else {
			echo '<div class="wrap"><div class="asf-notice asf-notice-error">View file not found: ' . esc_html( $file ) . '</div></div>';
		}
	}

	/** Enqueue CSS + JS only on our plugin pages */
	public static function enqueue_assets( $hook ) {

		// Robust check: load assets if hook contains asf- OR $_GET['page'] starts with asf-
		$page = sanitize_text_field( $_GET['page'] ?? '' );
		$is_our_page = ( strpos( $hook, 'asf-' ) !== false || strpos( $page, 'asf-' ) !== false );

		if ( ! $is_our_page ) return;

		$ver = ASF_VERSION;

		// Admin CSS
		wp_enqueue_style(
			'asf-admin',
			ASF_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$ver
		);

		// Chart.js from jsDelivr CDN
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Admin JS
		wp_enqueue_script(
			'asf-admin',
			ASF_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			$ver,
			true
		);

		$last_audit = get_option( 'asf_last_audit_data', false );

		// Localize all dynamic data for JS
		wp_localize_script( 'asf-admin', 'asfData', array(
			'ajax'      => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'asf_nonce' ),
			'adminUrl'  => admin_url(),
			'siteUrl'   => home_url(),
			'hasPsiKey' => get_option( ASF_OPT_PSI_KEY, '' ) ? '1' : '0',
			'version'   => ASF_VERSION,
			'lastAudit' => $last_audit ? $last_audit : null,
		) );
	}
}
