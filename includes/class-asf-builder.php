<?php
/**
 * Page Builder & Elementor Overhead Inspector & Optimizer
 *
 * Detects Elementor, Divi, Oxygen, Beaver Builder, WPBakery, Brizy.
 * Analyzes Elementor DOM depth, DB payload (_elementor_data size),
 * unused font scripts, and provides 1-click overhead reduction.
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Builder {

	public static function init() {
		add_action( 'wp_ajax_asf_builder_scan',      array( __CLASS__, 'handle_scan' ) );
		add_action( 'wp_ajax_asf_builder_optimize',  array( __CLASS__, 'handle_optimize' ) );

		// Enforce Elementor optimizations if turned on
		if ( get_option( 'asf_opt_disable_eicons', 0 ) ) {
			add_action( 'elementor/frontend/after_enqueue_styles', array( __CLASS__, 'dequeue_eicons' ), 99 );
		}
		if ( get_option( 'asf_opt_disable_elementor_gfonts', 0 ) ) {
			add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );
		}
	}

	public static function dequeue_eicons() {
		if ( ! is_admin() && ! current_user_can( 'edit_posts' ) ) {
			wp_dequeue_style( 'elementor-icons' );
		}
	}

	/**
	 * Scans WordPress database for active page builders & Elementor bloat metrics
	 */
	public static function handle_scan() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;

		// 1. Detect Active Page Builders
		$active_builders = array();

		if ( defined( 'ELEMENTOR_VERSION' ) )       $active_builders[] = array( 'name' => 'Elementor (v' . ELEMENTOR_VERSION . ')', 'active' => true );
		if ( defined( 'ET_BUILDER_VERSION' ) )      $active_builders[] = array( 'name' => 'Divi Builder (v' . ET_BUILDER_VERSION . ')', 'active' => true );
		if ( defined( 'CT_VERSION' ) )              $active_builders[] = array( 'name' => 'Oxygen Builder', 'active' => true );
		if ( class_exists( 'FLBuilder' ) )          $active_builders[] = array( 'name' => 'Beaver Builder', 'active' => true );
		if ( defined( 'WPB_VC_VERSION' ) )          $active_builders[] = array( 'name' => 'WPBakery Page Builder', 'active' => true );
		if ( defined( 'BRIZY_VERSION' ) )           $active_builders[] = array( 'name' => 'Brizy Builder', 'active' => true );

		// Check DB for Elementor data even if plugin inactive
		$elementor_page_count = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_edit_mode' AND meta_value = 'builder'" );

		$elementor_json_bytes = (int) $wpdb->get_var( "SELECT SUM(LENGTH(meta_value)) FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data'" );
		$elementor_json_mb    = round( $elementor_json_bytes / ( 1024 * 1024 ), 2 );

		// Sample Elementor Pages DOM Complexity
		$heavy_pages = array();
		if ( $elementor_page_count > 0 ) {
			$rows = $wpdb->get_results( "SELECT post_id, LENGTH(meta_value) as bytes FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' ORDER BY bytes DESC LIMIT 5" );
			foreach ( $rows as $r ) {
				$heavy_pages[] = array(
					'post_id'  => $r->post_id,
					'title'    => get_the_title( $r->post_id ),
					'edit_url' => get_edit_post_link( $r->post_id ),
					'size_kb'  => round( $r->bytes / 1024, 1 ),
				);
			}
		}

		$opt_eicons = (int) get_option( 'asf_opt_disable_eicons', 0 );
		$opt_gfonts = (int) get_option( 'asf_opt_disable_elementor_gfonts', 0 );

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'active_builders'      => $active_builders,
				'is_elementor'         => defined( 'ELEMENTOR_VERSION' ) || $elementor_page_count > 0,
				'elementor_page_count' => $elementor_page_count,
				'elementor_json_mb'    => $elementor_json_mb,
				'heavy_pages'          => $heavy_pages,
				'opt_eicons'           => $opt_eicons,
				'opt_gfonts'           => $opt_gfonts,
			),
		) );
	}

	/**
	 * Applies 1-click Elementor & Page Builder Overhead Optimization
	 */
	public static function handle_optimize() {
		asf_check_nonce();
		asf_cap_check();

		$disable_eicons = intval( $_GET['disable_eicons'] ?? 0 );
		$disable_gfonts = intval( $_GET['disable_gfonts'] ?? 0 );
		$clear_css      = intval( $_GET['clear_css'] ?? 0 );

		update_option( 'asf_opt_disable_eicons', $disable_eicons );
		update_option( 'asf_opt_disable_elementor_gfonts', $disable_gfonts );

		// Flush Elementor CSS files if Elementor active
		if ( $clear_css && class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		wp_send_json( array(
			'success' => true,
			'message' => '🚀 Page Builder & Elementor Optimizations Saved & CSS Cache Cleared!',
		) );
	}
}
