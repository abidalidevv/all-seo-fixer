<?php
/**
 * Performance, Database Optimizer & Lazy-Loading Engine
 * (WP Rocket + Smush Pro style optimization)
 *
 * Features:
 *  - Native Lazy Loading enabler for images & iframes
 *  - Database Cleaner (Post revisions, auto-drafts, spam comments, expired transients)
 *  - .htaccess / Nginx Speed Snippet generator (Gzip, Brotli, Browser Cache, WebP)
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Performance {

	public static function init() {
		// Enable native lazy loading filter on post content images & iframes
		add_filter( 'the_content', array( __CLASS__, 'add_lazy_loading' ), 99 );

		// AJAX Endpoints
		add_action( 'wp_ajax_asf_optimize_db', array( __CLASS__, 'handle_db_optimization' ) );
	}

	/**
	 * Ensures loading="lazy" is present on all <img> and <iframe> tags in content
	 */
	public static function add_lazy_loading( $content ) {
		if ( empty( $content ) || is_feed() || is_admin() ) return $content;

		// Add loading="lazy" to <img> tags if missing
		$content = preg_replace_callback( '/<img\s+([^>]+)>/i', function( $matches ) {
			$img_html = $matches[0];
			if ( strpos( $img_html, 'loading=' ) === false ) {
				$img_html = str_replace( '<img ', '<img loading="lazy" ', $img_html );
			}
			return $img_html;
		}, $content );

		// Add loading="lazy" to <iframe> tags if missing
		$content = preg_replace_callback( '/<iframe\s+([^>]+)>/i', function( $matches ) {
			$iframe_html = $matches[0];
			if ( strpos( $iframe_html, 'loading=' ) === false ) {
				$iframe_html = str_replace( '<iframe ', '<iframe loading="lazy" ', $iframe_html );
			}
			return $iframe_html;
		}, $content );

		return $content;
	}

	/**
	 * Database Optimizer AJAX Handler
	 * Cleans post revisions, auto-drafts, trashed posts, spam comments, and expired transients
	 */
	public static function handle_db_optimization() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;

		// 1. Delete post revisions via wp_delete_post (Limit 50 per batch)
		$revision_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' LIMIT 50" );
		$revisions = 0;
		foreach ( $revision_ids as $rev_id ) {
			if ( wp_delete_post( (int) $rev_id, true ) ) {
				$revisions++;
			}
		}

		// 2. Delete auto-drafts via wp_delete_post (Limit 50 per batch)
		$draft_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'auto-draft' LIMIT 50" );
		$drafts = 0;
		foreach ( $draft_ids as $draft_id ) {
			if ( wp_delete_post( (int) $draft_id, true ) ) {
				$drafts++;
			}
		}

		// 3. Delete trashed posts (Limit 50 per batch)
		$trash_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'trash' AND post_type != 'attachment' LIMIT 50" );
		$trashed_posts = 0;
		foreach ( $trash_ids as $t_id ) {
			if ( wp_delete_post( (int) $t_id, true ) ) {
				$trashed_posts++;
			}
		}

		// 4. Delete spam & trashed comments (Limit 50 per batch)
		$comment_ids = $wpdb->get_col( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = 'spam' OR comment_approved = 'trash' LIMIT 50" );
		$spam_comments = 0;
		foreach ( $comment_ids as $c_id ) {
			if ( wp_delete_comment( (int) $c_id, true ) ) {
				$spam_comments++;
			}
		}

		// Check if there are remaining items to process
		$remaining_revisions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );
		$remaining_drafts    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );
		$remaining_trash     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash' AND post_type != 'attachment'" );
		$remaining_comments  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam' OR comment_approved = 'trash'" );
		$has_more            = ( $remaining_revisions + $remaining_drafts + $remaining_trash + $remaining_comments ) > 0;

		// 5. Delete expired transients (Only on last batch)
		$expired_transients = 0;
		if ( ! $has_more ) {
			$time = time();
			$expired_transients = $wpdb->query( $wpdb->prepare(
				"DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
				 WHERE a.option_name LIKE %s
				 AND a.option_name NOT LIKE %s
				 AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
				 AND b.option_value < %d",
				'\_transient\_%',
				'\_transient\_timeout\_%',
				$time
			) );

			// 6. Optimize tables on final batch
			$tables = $wpdb->get_col( 'SHOW TABLES' );
			foreach ( $tables as $table ) {
				$wpdb->query( "OPTIMIZE TABLE `{$table}`" );
			}
		} else {
			$tables = array();
		}

		wp_send_json( array(
			'success' => true,
			'message' => $has_more ? 'Processing database batch...' : '🚀 Database Optimized Successfully!',
			'data'    => array(
				'revisions'          => (int) $revisions,
				'drafts'             => (int) $drafts,
				'trashed_posts'      => (int) $trashed_posts,
				'spam_comments'      => (int) $spam_comments,
				'expired_transients' => (int) $expired_transients,
				'tables_optimized'   => count( $tables ),
				'has_more'           => $has_more,
				'remaining_total'    => ( $remaining_revisions + $remaining_drafts + $remaining_trash + $remaining_comments ),
			),
		) );
	}
}
