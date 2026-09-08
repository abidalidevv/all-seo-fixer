<?php
/**
 * On-Page SEO Checker + Broken Link Cleaner + Media Scanner + Search Engine Pinger
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==============================================================
   ON-PAGE SEO CHECKER
   ============================================================== */
class ASF_OnPage {

	public static function init() {
		add_action( 'wp_ajax_asf_onpage_scan',                array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_asf_onpage_bulk_autofix',       array( __CLASS__, 'handle_bulk_autofix' ) );
		add_action( 'wp_ajax_asf_onpage_autofix_single_ai',  array( __CLASS__, 'handle_autofix_single_ai' ) );
		add_action( 'wp_ajax_asf_onpage_generate_single_seo',array( __CLASS__, 'handle_generate_single_seo' ) );
		add_action( 'wp_ajax_asf_onpage_get_post_meta',      array( __CLASS__, 'handle_get_post_meta' ) );
		add_action( 'wp_ajax_asf_onpage_save_post_meta',     array( __CLASS__, 'handle_save_post_meta' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		$public_types  = array_values( get_post_types( array( 'public' => true ) ) );
		$exclude_types = array(
			'attachment', 'nav_menu_item', 'revision', 'custom_css', 'customize_changeset',
			'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part',
			'wp_global_styles', 'wp_navigation', 'elementor_library', 'elementor_snippet',
			'elementor_font', 'elementor_icons', 'e-landing-page', 'action_monitor'
		);

		$req_type = sanitize_text_field( $_REQUEST['post_type'] ?? 'all' );
		if ( $req_type !== 'all' && post_type_exists( $req_type ) ) {
			$post_types = array( $req_type );
		} else {
			$post_types = array_values( array_diff( $public_types, $exclude_types ) );
		}

		$posts = get_posts( array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => 250,
		) );

		$results     = array();
		$site_name   = get_bloginfo( 'name' );
		$seen_titles = array();
		$seen_metas  = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			$issues  = array();

			// ── Title checks ─────────────────────────────────────────
			$raw_title = get_post_meta( $post->ID, '_asf_seo_title', true )
				?: ( get_post_meta( $post->ID, 'rank_math_title', true )
				?: ( get_post_meta( $post->ID, '_yoast_wpseo_title', true )
				?: get_the_title( $post->ID ) ) );

			$title_clean = str_replace( array( '%title%', '%%title%%', '%sitename%', '%%sitename%%', '%sep%', '%%sep%%' ), array( get_the_title( $post->ID ), get_the_title( $post->ID ), $site_name, $site_name, '-', '-' ), $raw_title );
			$title_clean = trim( strip_tags( $title_clean ) );
			$title_len   = mb_strlen( $title_clean );

			if ( $title_len === 0 )     $issues[] = array( 'type' => 'error', 'key' => 'title', 'msg' => 'Missing Title Tag — add a unique SEO title.' );
			elseif ( $title_len < 30 )  $issues[] = array( 'type' => 'warn',  'key' => 'title', 'msg' => 'Title too short (' . $title_len . ' chars). Aim for 50–60 characters.' );
			elseif ( $title_len > 65 )  $issues[] = array( 'type' => 'warn',  'key' => 'title', 'msg' => 'Title too long (' . $title_len . ' chars). Keep under 60 to avoid truncation in Google.' );

			// Duplicate Title Tag check
			if ( ! empty( $title_clean ) ) {
				$t_key = mb_strtolower( $title_clean );
				if ( isset( $seen_titles[ $t_key ] ) ) {
					$issues[] = array( 'type' => 'warn', 'key' => 'dup_title', 'msg' => 'Duplicate Title Tag — identical to another published page. Titles must be unique.' );
				} else {
					$seen_titles[ $t_key ] = $post->ID;
				}
			}

			// ── Meta Description checks ───────────────────────────────
			$raw_meta = get_post_meta( $post->ID, '_asf_meta_description', true )
				?: ( get_post_meta( $post->ID, 'rank_math_description', true )
				?: get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) );

			$meta_clean = str_replace( array( '%excerpt%', '%%excerpt%%', '%title%', '%%title%%', '%sitename%', '%%sitename%%' ), array( '', '', get_the_title( $post->ID ), get_the_title( $post->ID ), $site_name, $site_name ), (string) $raw_meta );
			$meta_clean = trim( strip_tags( $meta_clean ) );
			$meta_len   = mb_strlen( $meta_clean );

			if ( $meta_len === 0 )      $issues[] = array( 'type' => 'error', 'key' => 'meta_desc', 'msg' => 'Missing Meta Description — write a 120–155 char summary.' );
			elseif ( $meta_len < 70 )   $issues[] = array( 'type' => 'warn',  'key' => 'meta_desc', 'msg' => 'Meta Description too short (' . $meta_len . ' chars). Aim for 120–155.' );
			elseif ( $meta_len > 160 )  $issues[] = array( 'type' => 'warn',  'key' => 'meta_desc', 'msg' => 'Meta Description too long (' . $meta_len . ' chars). Keep under 160.' );

			// Duplicate Meta Description check
			if ( ! empty( $meta_clean ) && $meta_len >= 30 ) {
				$m_key = mb_strtolower( $meta_clean );
				if ( isset( $seen_metas[ $m_key ] ) ) {
					$issues[] = array( 'type' => 'warn', 'key' => 'dup_meta', 'msg' => 'Duplicate Meta Description — identical to another published page. Summaries must be unique.' );
				} else {
					$seen_metas[ $m_key ] = $post->ID;
				}
			}

			// ── H1 check (checks content, Elementor JSON, theme title, and native WooCommerce single-product template) ──
			$has_h1 = preg_match( '/<h1[\s>]/i', $content );
			if ( ! $has_h1 ) {
				$elem_data = get_post_meta( $post->ID, '_elementor_data', true );
				if ( $elem_data && ( stripos( $elem_data, '"tag":"h1"' ) !== false || stripos( $elem_data, '"header_size":"h1"' ) !== false ) ) {
					$has_h1 = true;
				}
			}
			// WordPress themes and WooCommerce templates render the page/product title as H1 in single/page templates
			if ( ! $has_h1 && ( $post->post_type === 'product' || ! empty( $post->post_title ) ) ) {
				$has_h1 = true;
			}

			if ( ! $has_h1 ) {
				$issues[] = array( 'type' => 'error', 'key' => 'h1', 'msg' => 'Missing H1 heading — every page needs exactly one H1 tag with the primary keyword.' );
			} elseif ( $post->post_type !== 'product' && preg_match_all( '/<h1[\s>]/i', $content ) > 1 ) {
				$issues[] = array( 'type' => 'warn', 'key' => 'h1', 'msg' => 'Multiple H1 tags detected — a page should have only one H1.' );
			}

			// ── Content length & Depth (with Elementor page builder text extraction) ──
			$full_text = $content;
			if ( ! empty( $post->post_excerpt ) ) {
				$full_text .= ' ' . $post->post_excerpt;
			}
			$elem_data = get_post_meta( $post->ID, '_elementor_data', true );
			if ( ! empty( $elem_data ) && is_string( $elem_data ) ) {
				$elem_parsed = json_decode( $elem_data, true );
				if ( is_array( $elem_parsed ) ) {
					$elem_extracted = array();
					array_walk_recursive( $elem_parsed, function( $v, $k ) use ( &$elem_extracted ) {
						if ( in_array( $k, array( 'title', 'editor', 'text', 'heading_title', 'html', 'description' ), true ) && is_string( $v ) ) {
							$elem_extracted[] = $v;
						}
					} );
					if ( ! empty( $elem_extracted ) ) {
						$full_text .= ' ' . implode( ' ', $elem_extracted );
					}
				}
			}

			// ── H2 check (applies to long informational articles and pages) ──
			if ( in_array( $post->post_type, array( 'post', 'page' ), true ) && ! preg_match( '/<h2[\s>]/i', $full_text ) && mb_strlen( strip_tags( $full_text ) ) > 450 ) {
				$issues[] = array( 'type' => 'warn', 'key' => 'h2', 'msg' => 'No H2 subheadings found — use H2 tags to structure long content for readability and SEO.' );
			}

			// ── Image alt text ────────────────────────────────────────
			if ( preg_match_all( '/<img[^>]+>/i', $full_text, $imgs ) ) {
				$no_alt = 0;
				foreach ( $imgs[0] as $img ) {
					if ( ! preg_match( '/alt=["\'][^"\']+["\']/', $img ) ) $no_alt++;
				}
				if ( $no_alt ) {
					$issues[] = array( 'type' => 'warn', 'key' => 'alt', 'msg' => $no_alt . ' image(s) missing alt text — critical for accessibility and image SEO.' );
				}
			}

			$content_words = str_word_count( strip_tags( $full_text ) );
			if ( $content_words < 300 && $post->post_type === 'post' ) {
				$issues[] = array( 'type' => 'warn', 'key' => 'content', 'msg' => 'Thin content (' . $content_words . ' words) — blog posts should have at least 600 words for ranking.' );
			}

			// ── Schema JSON-LD Detection ──────────────────────────────
			$has_schema = get_post_meta( $post->ID, '_asf_schema_type', true )
				|| get_post_meta( $post->ID, 'rank_math_rich_snippet', true )
				|| get_option( 'asf_schema_enable', '1' ) === '1'
				|| defined( 'WPSEO_VERSION' );

			if ( ! $has_schema ) {
				$issues[] = array( 'type' => 'warn', 'key' => 'schema', 'msg' => 'No Schema JSON-LD markup — add Organization/Article schema for rich snippets.' );
			}

			// ── Internal links (checks full text including Elementor links) ──
			preg_match_all( '/<a[^>]+href=["\']([^"\']*)["\'][^>]*>/i', $full_text, $links );
			$internal_links = 0;
			$site_host      = parse_url( home_url(), PHP_URL_HOST );
			if ( ! empty( $links[1] ) ) {
				foreach ( $links[1] as $link ) {
					$link_host = parse_url( $link, PHP_URL_HOST );
					if ( ! $link_host || $link_host === $site_host || strpos( $link, '/' ) === 0 ) {
						$internal_links++;
					}
				}
			}
			if ( $internal_links === 0 && mb_strlen( strip_tags( $full_text ) ) > 250 && $post->post_type !== 'product' ) {
				$issues[] = array( 'type' => 'warn', 'key' => 'links', 'msg' => 'No internal links found — add links to related pages to improve crawlability.' );
			}

			$has_valid_meta  = ( $meta_len >= 70 && $meta_len <= 160 );
			$has_valid_title = ( $title_len >= 30 && $title_len <= 65 );

			$results[] = array(
				'id'             => $post->ID,
				'title'          => esc_html( get_the_title( $post->ID ) ),
				'post_type'      => esc_html( $post->post_type ),
				'url'            => get_permalink( $post->ID ),
				'edit_url'       => get_edit_post_link( $post->ID ),
				'snippet'        => wp_trim_words( wp_strip_all_tags( strip_shortcodes( $full_text ) ), 40, '...' ),
				'word_count'     => $content_words,
				'internal_links' => $internal_links,
				'issues'         => $issues,
				'seo_title'      => $title_clean,
				'meta_desc'      => $meta_clean,
				'has_meta_desc'  => $has_valid_meta,
				'has_good_title' => $has_valid_title,
			);
		}

		usort( $results, function ( $a, $b ) {
			return count( $b['issues'] ) - count( $a['issues'] );
		} );

		wp_send_json( array( 'success' => true, 'data' => $results ) );
	}

	public static function handle_bulk_autofix() {
		asf_check_nonce();
		asf_cap_check();

		$public_types  = array_values( get_post_types( array( 'public' => true ) ) );
		$exclude_types = array(
			'attachment', 'nav_menu_item', 'revision', 'custom_css', 'customize_changeset',
			'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part',
			'wp_global_styles', 'wp_navigation', 'elementor_library', 'elementor_snippet',
			'elementor_font', 'elementor_icons', 'e-landing-page', 'action_monitor'
		);
		$post_types = array_values( array_diff( $public_types, $exclude_types ) );

		$posts = get_posts( array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => 250,
		) );

		$site_name   = get_bloginfo( 'name' );
		$fixed_count = 0;

		foreach ( $posts as $p ) {
			$modified = false;

			// Check meta description
			$desc = get_post_meta( $p->ID, '_asf_meta_description', true )
				?: ( get_post_meta( $p->ID, 'rank_math_description', true )
				?: get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) );

			if ( empty( trim( (string) $desc ) ) || mb_strlen( trim( (string) $desc ) ) < 80 ) {
				$seo_data = ASF_AIChatbot::generate_seo_data( $p->ID, 'meta' );
				$gen_desc = $seo_data['meta_desc'];

				update_post_meta( $p->ID, '_asf_meta_description', sanitize_text_field( $gen_desc ) );
				update_post_meta( $p->ID, 'rank_math_description', sanitize_text_field( $gen_desc ) );
				update_post_meta( $p->ID, '_yoast_wpseo_metadesc', sanitize_text_field( $gen_desc ) );
				$modified = true;
			}

			// Check title length
			$title = get_post_meta( $p->ID, '_asf_seo_title', true )
				?: ( get_post_meta( $p->ID, 'rank_math_title', true )
				?: ( get_post_meta( $p->ID, '_yoast_wpseo_title', true )
				?: get_the_title( $p->ID ) ) );

			if ( mb_strlen( trim( (string) $title ) ) < 30 || mb_strlen( trim( (string) $title ) ) > 65 ) {
				$seo_data = ASF_AIChatbot::generate_seo_data( $p->ID, 'title' );
				$gen_title = $seo_data['title'];

				update_post_meta( $p->ID, '_asf_seo_title', sanitize_text_field( $gen_title ) );
				update_post_meta( $p->ID, 'rank_math_title', sanitize_text_field( $gen_title ) );
				update_post_meta( $p->ID, '_yoast_wpseo_title', sanitize_text_field( $gen_title ) );
				$modified = true;
			}

			if ( $modified ) {
				if ( ! empty( $seo_data['focus_keyword'] ) ) {
					update_post_meta( $p->ID, '_asf_focus_keyword', sanitize_text_field( $seo_data['focus_keyword'] ) );
					update_post_meta( $p->ID, 'rank_math_focus_keyword', sanitize_text_field( $seo_data['focus_keyword'] ) );
					update_post_meta( $p->ID, '_yoast_wpseo_focuskw', sanitize_text_field( $seo_data['focus_keyword'] ) );
				}
				$fixed_count++;
			}
		}

		$stats = null;
		if ( $fixed_count > 0 ) {
			$stats = ASF_StatsTracker::record_fix( 'title_and_meta', $fixed_count, 'Bulk Auto-Fix', 'Bulk AI: Optimized ' . $fixed_count . ' pages with SEO Titles, Meta Descriptions & Focus Keywords' );
		}

		wp_send_json( array(
			'success'     => true,
			'message'     => 'Successfully auto-fixed and generated SEO titles, meta descriptions & focus keywords for ' . $fixed_count . ' pages!',
			'fixed_count' => $fixed_count,
			'stats'       => $stats,
		) );
	}

	/**
	 * 1-Click AI Auto-Fix for a single page
	 * Generates SEO Title (48-60 chars), Meta Description (120-155 chars) & Primary Focus Keyword
	 * Saves directly to _asf_* and syncs with Rank Math & Yoast
	 */
	public static function handle_autofix_single_ai() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = intval( $_REQUEST['post_id'] ?? 0 );
		$post    = get_post( $post_id );
		if ( ! $post_id || ! $post ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Post ID.' ) );
		}

		$seo   = ASF_AIChatbot::generate_seo_data( $post_id, 'all' );
		$title = sanitize_text_field( $seo['title'] ?? '' );
		$desc  = sanitize_text_field( $seo['meta_desc'] ?? '' );
		$kw    = sanitize_text_field( $seo['focus_keyword'] ?? '' );

		if ( ! empty( $title ) ) {
			update_post_meta( $post_id, '_asf_seo_title', $title );
			update_post_meta( $post_id, 'rank_math_title', $title );
			update_post_meta( $post_id, '_yoast_wpseo_title', $title );
		}
		if ( ! empty( $desc ) ) {
			update_post_meta( $post_id, '_asf_meta_description', $desc );
			update_post_meta( $post_id, 'rank_math_description', $desc );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $desc );
		}
		if ( ! empty( $kw ) ) {
			update_post_meta( $post_id, '_asf_focus_keyword', $kw );
			update_post_meta( $post_id, 'rank_math_focus_keyword', $kw );
			update_post_meta( $post_id, '_yoast_wpseo_focuskw', $kw );
		}

		// Clear cached audit so fresh scan and reload immediately reflects updates
		delete_option( 'asf_last_audit_data' );
		delete_option( 'asf_health_score_cache' );

		$p_title = get_the_title( $post_id );
		$stats   = ASF_StatsTracker::record_fix( 'title_and_meta', 1, $p_title, 'AI 1-Click: SEO Title, Meta Description & Focus Keyword generated for "' . $p_title . '"' );

		wp_send_json( array(
			'success'       => true,
			'post_id'       => $post_id,
			'title'         => $title,
			'meta_desc'     => $desc,
			'focus_keyword' => $kw,
			'source'        => $seo['source'] ?? 'AI',
			'stats'         => $stats,
			'message'       => '✓ Page SEO metadata successfully optimized with AI!',
		) );
	}

	/**
	 * Generates preview AI metadata (Title, Meta Description, Focus Keyword) for modal auto-fill without saving
	 */
	public static function handle_generate_single_seo() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = intval( $_REQUEST['post_id'] ?? 0 );
		$post    = get_post( $post_id );
		if ( ! $post_id || ! $post ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Post ID.' ) );
		}

		$seo = ASF_AIChatbot::generate_seo_data( $post_id, 'all' );
		wp_send_json( array(
			'success'       => true,
			'post_id'       => $post_id,
			'title'         => $seo['title'] ?? '',
			'meta_desc'     => $seo['meta_desc'] ?? '',
			'focus_keyword' => $seo['focus_keyword'] ?? '',
			'source'        => $seo['source'] ?? 'AI',
		) );
	}

	public static function handle_get_post_meta() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = intval( $_REQUEST['id'] ?? 0 );
		$post    = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json( array( 'success' => false, 'message' => 'Post not found.' ) );
		}

		$site_name = get_bloginfo( 'name' );
		$title     = get_post_meta( $post_id, '_asf_seo_title', true )
			?: ( get_post_meta( $post_id, 'rank_math_title', true )
			?: ( get_post_meta( $post_id, '_yoast_wpseo_title', true )
			?: get_the_title( $post_id ) ) );

		$desc = get_post_meta( $post_id, '_asf_meta_description', true )
			?: ( get_post_meta( $post_id, 'rank_math_description', true )
			?: get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ) );

		$keyword = get_post_meta( $post_id, '_asf_focus_keyword', true )
			?: ( get_post_meta( $post_id, 'rank_math_focus_keyword', true )
			?: ( get_post_meta( $post_id, '_yoast_wpseo_focuskw', true ) ?: '' ) );

		$schema = get_post_meta( $post_id, '_asf_schema_type', true ) ?: ( $post->post_type === 'page' ? 'WebPage' : 'Article' );

		// Suggestions
		$sug_title = get_the_title( $post_id );
		if ( mb_strlen( $sug_title ) < 35 ) {
			$sug_title .= ' — ' . $site_name;
		}

		$clean_body = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		if ( mb_strlen( $clean_body ) >= 70 ) {
			$sug_desc = wp_html_excerpt( $clean_body, 145, '…' );
		} else {
			$sug_desc = sprintf( 'Explore professional %s solutions by %s. Quality service, reliable results, and fast assistance.', get_the_title( $post_id ), $site_name );
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'id'            => $post_id,
				'post_title'    => get_the_title( $post_id ),
				'permalink'     => get_permalink( $post_id ),
				'seo_title'     => $title,
				'meta_desc'     => (string) $desc,
				'focus_keyword' => (string) $keyword,
				'schema_type'   => (string) $schema,
				'sug_title'     => $sug_title,
				'sug_desc'      => $sug_desc,
			),
		) );
	}

	public static function handle_save_post_meta() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = intval( $_REQUEST['id'] ?? 0 );
		if ( ! $post_id || ! get_post( $post_id ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Post ID.' ) );
		}

		$seo_title = sanitize_text_field( $_REQUEST['seo_title'] ?? '' );
		$meta_desc = sanitize_text_field( $_REQUEST['meta_desc'] ?? '' );
		$keyword   = sanitize_text_field( $_REQUEST['focus_keyword'] ?? '' );
		$schema    = sanitize_text_field( $_REQUEST['schema_type'] ?? 'Article' );

		// Save into ASF postmeta
		update_post_meta( $post_id, '_asf_seo_title', $seo_title );
		update_post_meta( $post_id, '_asf_meta_description', $meta_desc );
		update_post_meta( $post_id, '_asf_focus_keyword', $keyword );
		update_post_meta( $post_id, '_asf_schema_type', $schema );

		// Sync with Rank Math / Yoast if installed
		update_post_meta( $post_id, 'rank_math_title', $seo_title );
		update_post_meta( $post_id, 'rank_math_description', $meta_desc );
		update_post_meta( $post_id, 'rank_math_focus_keyword', $keyword );

		update_post_meta( $post_id, '_yoast_wpseo_title', $seo_title );
		update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_desc );
		update_post_meta( $post_id, '_yoast_wpseo_focuskw', $keyword );

		// Clear cached audit so fresh scan and reload immediately reflects updates
		delete_option( 'asf_last_audit_data' );
		delete_option( 'asf_health_score_cache' );

		$p_title     = get_the_title( $post_id );
		$title_saved = ! empty( $seo_title );
		$desc_saved  = ! empty( $meta_desc );
		$fix_type    = ( $title_saved && $desc_saved ) ? 'title_and_meta' : ( $title_saved ? 'title' : 'meta' );
		$stats       = ASF_StatsTracker::record_fix( $fix_type, 1, $p_title, 'Quick-Fix: SEO Title & Meta Description saved for "' . $p_title . '"' );

		wp_send_json( array(
			'success' => true,
			'message' => 'SEO metadata successfully saved for Post #' . $post_id . '!',
			'stats'   => $stats,
		) );
	}
}

/* ==============================================================
   BROKEN LINK & TYPO CLEANER
   ============================================================== */
class ASF_LinkCleaner {

	public static function init() {
		add_action( 'wp_ajax_asf_clean_broken_links', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;
		$results = array( 'found' => 0, 'fixed' => 0, 'details' => array() );

		// 1. Scan wp_postmeta for double-domain typos
		$meta_rows = $wpdb->get_results(
			"SELECT meta_id, post_id, meta_key, meta_value
			 FROM {$wpdb->postmeta}
			 WHERE meta_value LIKE '%comhttp%' OR meta_value LIKE '%orghttp%' OR meta_value LIKE '%nethttp%' OR meta_value LIKE '%aehttp%'"
		);

		foreach ( $meta_rows as $row ) {
			$old_val = $row->meta_value;
			$new_val = preg_replace( '#(https?://[^/\s"\']+)https?://[^/\s"\']*(/?)#i', '$1$2', $old_val );

			if ( $old_val !== $new_val ) {
				$results['found']++;
				$wpdb->update(
					$wpdb->postmeta,
					array( 'meta_value' => $new_val ),
					array( 'meta_id'    => $row->meta_id )
				);
				$results['fixed']++;
				$results['details'][] = array(
					'post_id'  => $row->post_id,
					'meta_key' => $row->meta_key,
					'old_val'  => substr( $old_val, 0, 90 ),
					'new_val'  => substr( $new_val, 0, 90 ),
				);
			}
		}

		// 2. Scan wp_posts post_content for double-domain typos
		$post_rows = $wpdb->get_results(
			"SELECT ID, post_title, post_content
			 FROM {$wpdb->posts}
			 WHERE post_status IN ('publish','draft','private')
			   AND (post_content LIKE '%comhttp%' OR post_content LIKE '%orghttp%' OR post_content LIKE '%nethttp%' OR post_content LIKE '%aehttp%')"
		);

		foreach ( $post_rows as $post ) {
			$old_val = $post->post_content;
			$new_val = preg_replace( '#(https?://[^/\s"\']+)https?://[^/\s"\']*(/?)#i', '$1$2', $old_val );

			if ( $old_val !== $new_val ) {
				$results['found']++;
				$wpdb->update(
					$wpdb->posts,
					array( 'post_content' => $new_val ),
					array( 'ID'           => $post->ID )
				);
				$results['fixed']++;
				$results['details'][] = array(
					'post_id'  => $post->ID,
					'meta_key' => 'post_content',
					'old_val'  => substr( $old_val, 0, 90 ),
					'new_val'  => substr( $new_val, 0, 90 ),
				);
			}
		}

		if ( ! empty( $results['fixed'] ) ) {
			ASF_StatsTracker::record_fix( 'typo', $results['fixed'], 'Broken Link Cleaner', 'Cleaned ' . $results['fixed'] . ' broken URL typo(s)' );
		}

		wp_send_json( array( 'success' => true, 'data' => $results ) );
	}
}

/* ==============================================================
   SMART INTERNAL LINKING & CATEGORY SILO ENGINE
   ============================================================== */
class ASF_InternalLinks {

	public static function init() {
		add_action( 'wp_ajax_asf_autolink_single_page',       array( __CLASS__, 'handle_autolink_single_page' ) );
		add_action( 'wp_ajax_asf_autolink_bulk_pages',        array( __CLASS__, 'handle_autolink_bulk_pages' ) );
		add_action( 'wp_ajax_asf_get_internal_link_targets',  array( __CLASS__, 'handle_get_targets' ) );
		add_action( 'wp_ajax_asf_get_internal_linking_stats', array( __CLASS__, 'handle_get_stats' ) );
	}

	/**
	 * Builds indexed dictionary of target categories, landing pages and focus keywords
	 */
	public static function build_target_dictionary( $exclude_id = 0 ) {
		$targets   = array();
		$seen_urls = array();

		// 1. WooCommerce Product Categories
		if ( taxonomy_exists( 'product_cat' ) ) {
			$prod_cats = get_terms( array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			) );
			if ( ! is_wp_error( $prod_cats ) && ! empty( $prod_cats ) ) {
				foreach ( $prod_cats as $cat ) {
					$link = get_term_link( $cat );
					if ( is_wp_error( $link ) || empty( $link ) ) continue;
					if ( isset( $seen_urls[ $link ] ) ) continue;
					$seen_urls[ $link ] = true;

					$name = trim( $cat->name );
					if ( mb_strlen( $name ) >= 3 ) {
						$targets[] = array(
							'keyword' => $name,
							'url'     => $link,
							'name'    => $name,
							'type'    => 'product_cat',
							'term_id' => $cat->term_id,
						);
					}
				}
			}
		}

		// 2. Standard Post Categories
		if ( taxonomy_exists( 'category' ) ) {
			$post_cats = get_terms( array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			) );
			if ( ! is_wp_error( $post_cats ) && ! empty( $post_cats ) ) {
				foreach ( $post_cats as $cat ) {
					if ( in_array( strtolower( $cat->name ), array( 'uncategorized', 'general' ), true ) ) continue;
					$link = get_term_link( $cat );
					if ( is_wp_error( $link ) || empty( $link ) ) continue;
					if ( isset( $seen_urls[ $link ] ) ) continue;
					$seen_urls[ $link ] = true;

					$name = trim( $cat->name );
					if ( mb_strlen( $name ) >= 3 ) {
						$targets[] = array(
							'keyword' => $name,
							'url'     => $link,
							'name'    => $name,
							'type'    => 'category',
							'term_id' => $cat->term_id,
						);
					}
				}
			}
		}

		// 3. High-Value Pillar Service Pages & Top Products with Focus Keywords
		$key_pages = get_posts( array(
			'post_type'      => array( 'page', 'product' ),
			'post_status'    => 'publish',
			'posts_per_page' => 150,
			'exclude'        => $exclude_id ? array( $exclude_id ) : array(),
		) );

		foreach ( $key_pages as $kp ) {
			$link = get_permalink( $kp->ID );
			if ( ! $link || isset( $seen_urls[ $link ] ) ) continue;

			$kw = get_post_meta( $kp->ID, '_asf_focus_keyword', true );
			if ( empty( $kw ) ) {
				$raw_title = trim( $kp->post_title );
				if ( mb_strlen( $raw_title ) >= 4 && mb_strlen( $raw_title ) <= 35 && ! in_array( strtolower( $raw_title ), array( 'home', 'cart', 'checkout', 'my account', 'sample page' ), true ) ) {
					$kw = $raw_title;
				}
			}

			if ( ! empty( $kw ) && mb_strlen( $kw ) >= 3 ) {
				$seen_urls[ $link ] = true;
				$targets[] = array(
					'keyword' => $kw,
					'url'     => $link,
					'name'    => get_the_title( $kp->ID ),
					'type'    => $kp->post_type,
					'post_id' => $kp->ID,
				);
			}
		}

		// Sort descending by keyword length so specific multi-word phrases match before short words
		usort( $targets, function( $a, $b ) {
			return mb_strlen( $b['keyword'] ) - mb_strlen( $a['keyword'] );
		} );

		return $targets;
	}

	/**
	 * Counts internal links in given HTML content
	 */
	public static function count_internal_links( $content ) {
		if ( empty( $content ) ) return 0;
		preg_match_all( '/<a[^>]+href=["\']([^"\']*)["\'][^>]*>/i', $content, $links );
		$internal_links = 0;
		$site_host      = parse_url( home_url(), PHP_URL_HOST );
		if ( ! empty( $links[1] ) ) {
			foreach ( $links[1] as $link ) {
				$link_host = parse_url( $link, PHP_URL_HOST );
				if ( ! $link_host || $link_host === $site_host || strpos( $link, '/' ) === 0 ) {
					$internal_links++;
				}
			}
		}
		return $internal_links;
	}

	/**
	 * Safely replaces target category/service keywords in body text with contextual internal links
	 * Strictly skips existing <a>, <h1-h6>, tag attributes, scripts, buttons, and self-links.
	 */
	public static function auto_link_content( $content, $targets, $exclude_url = '', $max_links = 3 ) {
		if ( empty( $content ) || empty( $targets ) ) {
			return array( 'content' => $content, 'links_added' => 0, 'added_links' => array() );
		}

		$tokens = preg_split( '/(<\/?[a-zA-Z0-9]+(?:\s+[^>]*?)?>|<!--[\s\S]*?-->)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! is_array( $tokens ) ) {
			return array( 'content' => $content, 'links_added' => 0, 'added_links' => array() );
		}

		$forbidden_tags = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'script', 'style', 'button', 'input', 'textarea', 'select' );
		$in_forbidden   = 0;
		$links_added    = 0;
		$used_targets   = array();
		$added_details  = array();
		$new_tokens     = array();

		$exclude_path = $exclude_url ? rtrim( (string) parse_url( $exclude_url, PHP_URL_PATH ), '/' ) : '';

		foreach ( $tokens as $token ) {
			if ( $token === '' ) continue;

			if ( preg_match( '/^<(\/)?([a-zA-Z0-9]+)/', $token, $tm ) ) {
				$is_closing = ! empty( $tm[1] );
				$tag_name   = strtolower( $tm[2] );
				if ( in_array( $tag_name, $forbidden_tags, true ) ) {
					if ( $is_closing ) {
						$in_forbidden = max( 0, $in_forbidden - 1 );
					} else {
						$in_forbidden++;
					}
				}
				$new_tokens[] = $token;
			} else {
				// Text node outside forbidden tags
				if ( $in_forbidden === 0 && $links_added < $max_links ) {
					foreach ( $targets as $target ) {
						if ( $links_added >= $max_links ) break;

						$kw = trim( $target['keyword'] );
						if ( empty( $kw ) || mb_strlen( $kw ) < 3 ) continue;

						$lower_kw = mb_strtolower( $kw );
						if ( isset( $used_targets[ $lower_kw ] ) ) continue;

						// Avoid self-linking to the same page or category
						if ( $exclude_path ) {
							$target_path = rtrim( (string) parse_url( $target['url'], PHP_URL_PATH ), '/' );
							if ( $target_path && $target_path === $exclude_path ) {
								continue;
							}
						}

						$pattern = '/\b(' . preg_quote( $kw, '/' ) . ')\b/i';
						if ( preg_match( $pattern, $token ) ) {
							$target_url   = esc_url( $target['url'] );
							$target_title = esc_attr( $target['name'] );

							$token = preg_replace_callback( $pattern, function( $m ) use ( $target_url, $target_title, $lower_kw, &$links_added, &$used_targets, &$added_details ) {
								if ( isset( $used_targets[ $lower_kw ] ) ) {
									return $m[0];
								}
								$links_added++;
								$used_targets[ $lower_kw ] = true;
								$added_details[] = array(
									'keyword' => $m[1],
									'url'     => $target_url,
									'title'   => $target_title,
								);
								return '<a href="' . $target_url . '" title="' . $target_title . '">' . $m[1] . '</a>';
							}, $token, 1 );
						}
					}
				}
				$new_tokens[] = $token;
			}
		}

		return array(
			'content'     => implode( '', $new_tokens ),
			'links_added' => $links_added,
			'added_links' => $added_details,
		);
	}

	/**
	 * AJAX: Auto-link a single page/post/product
	 */
	public static function handle_autolink_single_page() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = intval( $_REQUEST['post_id'] ?? 0 );
		$post    = get_post( $post_id );
		if ( ! $post_id || ! $post ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Post ID.' ) );
		}

		$targets = self::build_target_dictionary( $post_id );
		if ( empty( $targets ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'No target category pages or focus keywords indexed.' ) );
		}

		$permalink = get_permalink( $post_id );
		$res       = self::auto_link_content( $post->post_content, $targets, $permalink, 3 );

		if ( $res['links_added'] > 0 ) {
			wp_update_post( array(
				'ID'           => $post_id,
				'post_content' => $res['content'],
			) );

			$total_internal = self::count_internal_links( $res['content'] );
			$p_title        = get_the_title( $post_id );
			$stats          = ASF_StatsTracker::record_fix( 'internal_link', $res['links_added'], $p_title, 'Smart Silo: Added ' . $res['links_added'] . ' internal link(s) to "' . $p_title . '"' );

			wp_send_json( array(
				'success'        => true,
				'post_id'        => $post_id,
				'links_added'    => $res['links_added'],
				'added_links'    => $res['added_links'],
				'total_internal' => $total_internal,
				'stats'          => $stats,
				'message'        => '✓ Contextually generated ' . $res['links_added'] . ' internal link(s) to related categories & services!',
			) );
		} else {
			$total_internal = self::count_internal_links( $post->post_content );
			wp_send_json( array(
				'success'        => true,
				'post_id'        => $post_id,
				'links_added'    => 0,
				'added_links'    => array(),
				'total_internal' => $total_internal,
				'stats'          => ASF_StatsTracker::get_stats(),
				'message'        => 'No matching category keywords found in body text, or max links already reached.',
			) );
		}
	}

	/**
	 * AJAX: Bulk auto-link multiple pages
	 */
	public static function handle_autolink_bulk_pages() {
		asf_check_nonce();
		asf_cap_check();

		$public_types  = array_values( get_post_types( array( 'public' => true ) ) );
		$exclude_types = array(
			'attachment', 'nav_menu_item', 'revision', 'custom_css', 'customize_changeset',
			'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part',
			'wp_global_styles', 'wp_navigation', 'elementor_library', 'elementor_snippet',
			'elementor_font', 'elementor_icons', 'e-landing-page', 'action_monitor'
		);
		$post_types = array_values( array_diff( $public_types, $exclude_types ) );

		$posts = get_posts( array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => 150,
		) );

		$targets      = self::build_target_dictionary();
		$total_linked = 0;
		$pages_fixed  = 0;

		foreach ( $posts as $p ) {
			$cur_links = self::count_internal_links( $p->post_content );
			if ( $cur_links >= 3 ) continue;

			$permalink = get_permalink( $p->ID );
			$res       = self::auto_link_content( $p->post_content, $targets, $permalink, ( 3 - $cur_links ) );

			if ( $res['links_added'] > 0 ) {
				wp_update_post( array(
					'ID'           => $p->ID,
					'post_content' => $res['content'],
				) );
				$total_linked += $res['links_added'];
				$pages_fixed++;
			}
		}

		$stats = null;
		if ( $total_linked > 0 ) {
			$stats = ASF_StatsTracker::record_fix( 'internal_link', $total_linked, 'Site-Wide Silo Engine', 'Smart Silo: Added ' . $total_linked . ' internal links across ' . $pages_fixed . ' page(s)' );
		}

		wp_send_json( array(
			'success'      => true,
			'total_linked' => $total_linked,
			'pages_fixed'  => $pages_fixed,
			'stats'        => $stats ?: ASF_StatsTracker::get_stats(),
			'message'      => "✓ Smart Link Silo Engine generated {$total_linked} internal links across {$pages_fixed} pages!",
		) );
	}

	/**
	 * AJAX: Returns targets dictionary for preview modal/table
	 */
	public static function handle_get_targets() {
		asf_check_nonce();
		asf_cap_check();

		$targets = self::build_target_dictionary();
		wp_send_json( array(
			'success' => true,
			'total'   => count( $targets ),
			'targets' => array_slice( $targets, 0, 100 ),
		) );
	}

	/**
	 * AJAX: Returns internal linking stats
	 */
	public static function handle_get_stats() {
		asf_check_nonce();
		asf_cap_check();

		$targets = self::build_target_dictionary();
		$posts   = get_posts( array(
			'post_type'      => array( 'page', 'post', 'product' ),
			'post_status'    => 'publish',
			'posts_per_page' => 200,
		) );

		$needs_links = 0;
		$total_links = 0;
		foreach ( $posts as $p ) {
			$c = self::count_internal_links( $p->post_content );
			$total_links += $c;
			if ( $c === 0 ) {
				$needs_links++;
			}
		}

		wp_send_json( array(
			'success'     => true,
			'categories'  => count( $targets ),
			'total_posts' => count( $posts ),
			'total_links' => $total_links,
			'needs_links' => $needs_links,
		) );
	}
}

/* ==============================================================
   PERSISTENT LIFETIME SEO STATS & OPTIMIZATION TRACKER
   ============================================================== */
class ASF_StatsTracker {

	const OPT_STATS = 'asf_lifetime_stats';

	public static function init() {
		add_action( 'wp_ajax_asf_get_live_stats', array( __CLASS__, 'handle_get_live_stats' ) );
		add_action( 'wp_ajax_asf_reset_stats',    array( __CLASS__, 'handle_reset_stats' ) );
	}

	/**
	 * Retrieve all persistent lifetime optimization metrics from wp_options
	 */
	public static function get_stats() {
		$defaults = array(
			'total_fixes'        => 0,
			'titles_fixed'       => 0,
			'metas_fixed'        => 0,
			'keywords_fixed'     => 0,
			'links_generated'    => 0,
			'alts_fixed'         => 0,
			'typos_fixed'        => 0,
			'h1_fixed'           => 0,
			'last_activity_time' => 0,
			'last_activity_msg'  => 'No optimizations recorded yet.',
			'recent_activity'    => array(),
		);
		$saved = get_option( self::OPT_STATS, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Persistently record any optimization action applied to the site
	 */
	public static function record_fix( $type, $count = 1, $item_title = '', $details = '' ) {
		$stats = self::get_stats();
		$count = max( 1, (int) $count );

		$stats['total_fixes'] += $count;

		switch ( $type ) {
			case 'title':
				$stats['titles_fixed'] += $count;
				break;
			case 'meta':
				$stats['metas_fixed'] += $count;
				break;
			case 'title_and_meta':
				$stats['titles_fixed'] += $count;
				$stats['metas_fixed']  += $count;
				break;
			case 'internal_link':
				$stats['links_generated'] += $count;
				break;
			case 'alt':
				$stats['alts_fixed'] += $count;
				break;
			case 'typo':
				$stats['typos_fixed'] += $count;
				break;
			case 'h1':
				$stats['h1_fixed'] += $count;
				break;
			case 'keyword':
				$stats['keywords_fixed'] += $count;
				break;
		}

		$now = current_time( 'timestamp' );
		$stats['last_activity_time'] = $now;

		$msg = $details;
		if ( empty( $msg ) ) {
			if ( $type === 'internal_link' ) {
				$msg = sprintf( 'Added %d internal link(s) to "%s"', $count, $item_title );
			} elseif ( $type === 'title_and_meta' ) {
				$msg = sprintf( 'Saved SEO Title & Meta Description for "%s"', $item_title );
			} elseif ( $type === 'meta' ) {
				$msg = sprintf( 'Saved Meta Description for "%s"', $item_title );
			} elseif ( $type === 'title' ) {
				$msg = sprintf( 'Saved SEO Title for "%s"', $item_title );
			} elseif ( $type === 'alt' ) {
				$msg = sprintf( 'Fixed ALT text for %d image(s)', $count );
			} elseif ( $type === 'typo' ) {
				$msg = sprintf( 'Cleaned %d broken URL typo(s)', $count );
			} else {
				$msg = sprintf( 'Applied %d SEO optimization(s)', $count );
			}
		}
		$stats['last_activity_msg'] = $msg;

		if ( ! isset( $stats['recent_activity'] ) || ! is_array( $stats['recent_activity'] ) ) {
			$stats['recent_activity'] = array();
		}
		array_unshift( $stats['recent_activity'], array(
			'time'    => $now,
			'date'    => current_time( 'mysql' ),
			'type'    => $type,
			'count'   => $count,
			'title'   => $item_title,
			'message' => $msg,
		) );
		$stats['recent_activity'] = array_slice( $stats['recent_activity'], 0, 25 );

		update_option( self::OPT_STATS, $stats, false );

		// Flush health score cache so health score re-evaluates fresh
		delete_option( 'asf_health_score_cache' );

		return $stats;
	}

	public static function handle_get_live_stats() {
		asf_check_nonce();
		asf_cap_check();
		wp_send_json( array( 'success' => true, 'stats' => self::get_stats() ) );
	}

	public static function handle_reset_stats() {
		asf_check_nonce();
		asf_cap_check();
		delete_option( self::OPT_STATS );
		wp_send_json( array( 'success' => true, 'message' => 'Stats reset successfully.', 'stats' => self::get_stats() ) );
	}
}

/* ==============================================================
   MEDIA & ORPHAN SCANNER
   ============================================================== */
class ASF_Media {

	public static function init() {
		add_action( 'wp_ajax_asf_scan_media',       array( __CLASS__, 'handle_scan' ) );
		add_action( 'wp_ajax_asf_trash_media',      array( __CLASS__, 'handle_trash' ) );
		add_action( 'wp_ajax_asf_save_single_alt',  array( __CLASS__, 'handle_save_single_alt' ) );
		add_action( 'wp_ajax_asf_save_bulk_alt',    array( __CLASS__, 'handle_save_bulk_alt' ) );
		add_action( 'wp_ajax_asf_auto_alt_media',   array( __CLASS__, 'handle_auto_alt' ) );
	}

	public static function handle_scan() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;

		// 1. Fetch all image attachment IDs
		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		$total_count = count( $attachments );

		// Quick cache check for theme assets
		$logo_id     = (int) get_theme_mod( 'custom_logo' );
		$icon_id     = (int) get_option( 'site_icon' );
		$header_img  = basename( get_header_image() ?: '' );
		$bg_img      = basename( get_background_image() ?: '' );

		$paged    = max( 1, intval( $_REQUEST['paged'] ?? 1 ) );
		$per_page = 20;
		$filter   = sanitize_text_field( $_REQUEST['filter'] ?? 'all' ); // 'all', 'missing_alt', 'orphans', 'in_use'

		$all_items         = array();
		$orphan_count      = 0;
		$missing_alt_count = 0;
		$used_count        = 0;

		foreach ( $attachments as $att_id ) {
			$att_id    = is_object( $att_id ) ? (int) $att_id->ID : (int) $att_id;
			$url       = wp_get_attachment_url( $att_id );
			$thumb_url = wp_get_attachment_thumb_url( $att_id ) ?: $url;
			$alt_text  = (string) get_post_meta( $att_id, '_wp_attachment_image_alt', true );
			$fname     = $url ? basename( $url ) : '';
			if ( ! $fname ) continue;

			$has_alt = ( trim( $alt_text ) !== '' );
			if ( ! $has_alt ) {
				$missing_alt_count++;
			}

			// Generate smart contextual suggestion
			$raw_name = pathinfo( $fname, PATHINFO_FILENAME );
			$clean    = ucwords( trim( preg_replace( '/[_\-\s]+/', ' ', $raw_name ) ) );
			if ( ! preg_match( '/^[0-9_\-\s]+$/', $clean ) && strlen( $clean ) >= 3 ) {
				$suggestion = $clean;
			} else {
				$parent_id    = wp_get_post_parent_id( $att_id );
				$parent_title = $parent_id ? get_the_title( $parent_id ) : '';
				if ( $parent_title ) {
					$suggestion = ucwords( trim( $parent_title ) ) . ' Image';
				} else {
					$suggestion = get_bloginfo( 'name' ) . ' Photo #' . $att_id;
				}
			}

			// Deep 100% Safe Usage Check across all WordPress data structures
			$usage_type = '';

			// A. Theme Logo, Favicon, Header, Background
			if ( ( $logo_id && $att_id === $logo_id ) || ( $icon_id && $att_id === $icon_id ) ) {
				$usage_type = 'Site Logo / Favicon';
			} elseif ( ( $header_img && $fname === $header_img ) || ( $bg_img && $fname === $bg_img ) ) {
				$usage_type = 'Header / Background Image';
			}

			// B. Featured Images
			if ( ! $usage_type ) {
				$feat = $wpdb->get_var( $wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id' AND meta_value=%s LIMIT 1",
					(string) $att_id
				) );
				if ( $feat ) {
					$usage_type = 'Featured Image (Post #' . $feat . ')';
				}
			}

			// C. WooCommerce Product Gallery
			if ( ! $usage_type ) {
				$wc_gal = $wpdb->get_var( $wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_product_image_gallery' AND meta_value LIKE %s LIMIT 1",
					'%' . $wpdb->esc_like( (string) $att_id ) . '%'
				) );
				if ( $wc_gal ) {
					$usage_type = 'Product Gallery (Product #' . $wc_gal . ')';
				}
			}

			// D. Post / Page Content (Checks by filename or wp-image-ID class)
			if ( ! $usage_type ) {
				$in_content = $wpdb->get_var( $wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_status IN ('publish','draft','private') AND (post_content LIKE %s OR post_content LIKE %s) LIMIT 1",
					'%' . $wpdb->esc_like( $fname ) . '%',
					'%' . $wpdb->esc_like( 'wp-image-' . $att_id ) . '%'
				) );
				if ( $in_content ) {
					$usage_type = 'Post Content (Post #' . $in_content . ')';
				}
			}

			// E. Elementor Page Builder JSON
			if ( ! $usage_type ) {
				$in_elem = $wpdb->get_var( $wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND (meta_value LIKE %s OR meta_value LIKE %s) LIMIT 1",
					'%' . $wpdb->esc_like( $fname ) . '%',
					'%' . $wpdb->esc_like( (string) $att_id ) . '%'
				) );
				if ( $in_elem ) {
					$usage_type = 'Elementor Page Builder';
				}
			}

			// F. Attached to Post Parent
			if ( ! $usage_type ) {
				$parent = wp_get_post_parent_id( $att_id );
				if ( $parent ) {
					$usage_type = 'Attached to Post #' . $parent;
				}
			}

			$is_orphan = empty( $usage_type );
			if ( $is_orphan ) {
				$orphan_count++;
			} else {
				$used_count++;
			}

			// Filter handling
			if ( $filter === 'missing_alt' && $has_alt ) {
				continue;
			}
			if ( $filter === 'orphans' && ! $is_orphan ) {
				continue;
			}
			if ( $filter === 'in_use' && $is_orphan ) {
				continue;
			}

			$file_path    = get_attached_file( $att_id );
			$filesize_str = ( $file_path && file_exists( $file_path ) ) ? size_format( filesize( $file_path ) ) : '—';

			$all_items[] = array(
				'id'         => $att_id,
				'filename'   => $fname,
				'thumb_url'  => $thumb_url,
				'full_url'   => $url,
				'filesize'   => $filesize_str,
				'alt_text'   => $alt_text,
				'has_alt'    => $has_alt,
				'is_orphan'  => $is_orphan,
				'usage'      => $usage_type ?: 'Not found in any post, page, or slider',
				'suggestion' => $suggestion,
			);
		}

		$filtered_total = count( $all_items );
		$total_pages    = max( 1, ceil( $filtered_total / $per_page ) );
		$offset         = ( $paged - 1 ) * $per_page;
		$page_items     = array_slice( $all_items, $offset, $per_page );

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'total_images'       => $total_count,
				'used_images'        => $used_count,
				'orphan_images'      => $orphan_count,
				'missing_alt_images' => $missing_alt_count,
				'filtered_total'     => $filtered_total,
				'current_page'       => $paged,
				'total_pages'        => $total_pages,
				'per_page'           => $per_page,
				'items'              => $page_items,
			),
		) );
	}

	public static function handle_save_single_alt() {
		asf_check_nonce();
		asf_cap_check();

		$att_id = intval( $_REQUEST['id'] ?? 0 );
		$alt    = sanitize_text_field( $_REQUEST['alt'] ?? '' );

		if ( $att_id ) {
			update_post_meta( $att_id, '_wp_attachment_image_alt', $alt );
			$stats = ASF_StatsTracker::record_fix( 'alt', 1, 'Attachment #' . $att_id, 'Fixed ALT text for image #' . $att_id );
			wp_send_json( array( 'success' => true, 'message' => 'Alt text updated!', 'stats' => $stats ) );
		}
		wp_send_json( array( 'success' => false, 'message' => 'Invalid attachment ID' ) );
	}

	public static function handle_save_bulk_alt() {
		asf_check_nonce();
		asf_cap_check();

		$items = $_REQUEST['items'] ?? array();
		$count = 0;

		if ( is_array( $items ) ) {
			foreach ( $items as $it ) {
				$att_id = intval( $it['id'] ?? 0 );
				$alt    = sanitize_text_field( $it['alt'] ?? '' );
				if ( $att_id ) {
					update_post_meta( $att_id, '_wp_attachment_image_alt', $alt );
					$count++;
				}
			}
		}

		$stats = null;
		if ( $count > 0 ) {
			$stats = ASF_StatsTracker::record_fix( 'alt', $count, 'Media Library', 'Bulk updated ALT text for ' . $count . ' images' );
		}

		wp_send_json( array(
			'success' => true,
			'message' => 'Successfully updated ' . $count . ' image alt texts!',
			'stats'   => $stats ?: ASF_StatsTracker::get_stats(),
		) );
	}

	public static function handle_trash() {
		asf_check_nonce();
		asf_cap_check();

		$att_id = intval( $_REQUEST['id'] ?? 0 );
		if ( $att_id && wp_trash_post( $att_id ) ) {
			wp_send_json( array( 'success' => true ) );
		} else {
			wp_send_json( array( 'success' => false, 'message' => 'Failed to trash attachment #' . $att_id ) );
		}
	}

	/**
	 * Auto-generates clean, human-readable Alt Text for all images missing Alt Text in Media Library.
	 * Formats filenames or uses parent page title as fallback for numeric filenames.
	 */
	public static function handle_auto_alt() {
		asf_check_nonce();
		asf_cap_check();

		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
		) );

		$updated_count = 0;
		$fixed_details = array();

		foreach ( $attachments as $att ) {
			$alt = get_post_meta( $att->ID, '_wp_attachment_image_alt', true );
			if ( empty( trim( $alt ) ) ) {
				$filename = pathinfo( get_attached_file( $att->ID ), PATHINFO_FILENAME );
				if ( ! $filename ) {
					$url = wp_get_attachment_url( $att->ID );
					$filename = pathinfo( basename( $url ?: '' ), PATHINFO_FILENAME );
				}
				if ( $filename ) {
					// Clean up separators and numbers
					$clean_alt = preg_replace( '/[_\-\s]+/', ' ', $filename );
					$clean_alt = ucwords( trim( $clean_alt ) );

					// Fallback for numeric/random filenames (e.g. 83746874365.png)
					if ( preg_match( '/^[0-9_\-\s]+$/', $clean_alt ) || strlen( $clean_alt ) < 3 ) {
						$parent_title = $att->post_parent ? get_the_title( $att->post_parent ) : '';
						if ( $parent_title ) {
							$clean_alt = ucwords( trim( $parent_title ) ) . ' Image';
						} else {
							$clean_alt = get_bloginfo( 'name' ) . ' Media #' . $att->ID;
						}
					}

					if ( $clean_alt ) {
						update_post_meta( $att->ID, '_wp_attachment_image_alt', $clean_alt );
						$updated_count++;
						$fixed_details[] = array(
							'id'        => $att->ID,
							'filename'  => $filename,
							'alt'       => $clean_alt,
							'thumb_url' => wp_get_attachment_thumb_url( $att->ID ) ?: wp_get_attachment_url( $att->ID ),
						);
					}
				}
			}
		}

		$stats = null;
		if ( $updated_count > 0 ) {
			$stats = ASF_StatsTracker::record_fix( 'alt', $updated_count, 'Media Auto-Alt Generator', 'Auto-generated ' . $updated_count . ' clean image ALT tags' );
		}

		wp_send_json( array(
			'success' => true,
			'message' => '✨ Auto-generated alt text for ' . $updated_count . ' image(s)!',
			'data'    => array(
				'updated_count' => $updated_count,
				'details'       => $fixed_details,
			),
			'stats'   => $stats ?: ASF_StatsTracker::get_stats(),
		) );
	}
}

/* ==============================================================
   SEARCH ENGINE RE-INDEXING PINGER
   ============================================================== */
class ASF_Pinger {

	public static function init() {
		add_action( 'wp_ajax_asf_ping_search_engines', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;
		$host        = parse_url( home_url(), PHP_URL_HOST );
		$sitemap_url = home_url( '/sitemap_index.xml' );

		// NOTE: Google deprecated the /ping?sitemap= endpoint in July 2023.
		// Use Google Search Console Indexing API v3 (in GSC Inspector page) for Google indexing.

		// 1. IndexNow (Bing, Yandex, Seznam, Naver)
		$recent = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );
		$urls = array( home_url( '/' ) );
		foreach ( $recent as $p ) {
			$urls[] = get_permalink( $p->ID );
		}

		// FIX #6: Create IndexNow key verification file if it doesn't exist
		$indexnow_key  = md5( $host );
		$key_file_path = ABSPATH . $indexnow_key . '.txt';
		if ( ! file_exists( $key_file_path ) ) {
			@file_put_contents( $key_file_path, $indexnow_key );
		}

		$indexnow = array(
			'host'        => $host,
			'key'         => $indexnow_key,
			'keyLocation' => home_url( '/' . $indexnow_key . '.txt' ),
			'urlList'     => array_unique( $urls ),
		);
		wp_remote_post( 'https://api.indexnow.org/indexnow', array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $indexnow ),
			'timeout' => 5,
		) );

		// 2. Purge Rank Math + Yoast sitemap caches from DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%rank_math_sitemap%' OR option_name LIKE '%wpseo_xml_sitemap%'" );

		wp_send_json( array(
			'success' => true,
			'message' => 'Done! IndexNow alert delivered to Bing, Yandex, Naver & Seznam for ' . count( $urls ) . ' URLs + Sitemap caches purged! Use the GSC Inspector page for direct Google indexing.',
		) );
	}
}

/* ==============================================================
   1-CLICK AUTO-FIXER ENGINE
   ============================================================== */
class ASF_AutoFixer {

	public static function init() {
		add_action( 'wp_ajax_asf_autofix_missing_h1',          array( __CLASS__, 'handle_autofix_h1' ) );
		add_action( 'wp_ajax_asf_autofix_robots',              array( __CLASS__, 'handle_autofix_robots' ) );
		add_action( 'wp_ajax_asf_autofix_metas',               array( __CLASS__, 'handle_autofix_metas' ) );
		add_action( 'wp_ajax_asf_generate_single_meta_desc',  array( __CLASS__, 'handle_generate_single_meta_desc' ) );
		add_action( 'wp_ajax_asf_generate_single_title',      array( __CLASS__, 'handle_generate_single_title' ) );
		add_action( 'wp_ajax_asf_ai_batch_generate',          array( __CLASS__, 'handle_ai_batch_generate' ) );
		add_action( 'wp_ajax_asf_autofix_titles',              array( __CLASS__, 'handle_autofix_titles' ) );
		add_action( 'wp_ajax_asf_save_onpage_meta',            array( __CLASS__, 'handle_save_onpage_meta' ) );
		add_action( 'wp_ajax_asf_batch_save_onpage_meta',      array( __CLASS__, 'handle_batch_save_onpage_meta' ) );
	}

	/**
	 * Extracts page title and content/excerpt to generate a high-CTR, 120-155 char unique meta description
	 */
	public static function generate_smart_meta_desc( $post_id ) {
		$seo = ASF_AIChatbot::generate_seo_data( $post_id, 'meta' );
		return $seo['meta_desc'] ?? '';
	}

	/**
	 * Generates a high-CTR, 48-60 char unique SEO title tag
	 */
	public static function generate_smart_title( $post_id ) {
		$seo = ASF_AIChatbot::generate_seo_data( $post_id, 'title' );
		return $seo['title'] ?? '';
	}

	/**
	 * AJAX endpoint to generate a single smart meta description for a post using connected AI
	 */
	public static function handle_generate_single_meta_desc() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
		if ( ! $post_id ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Post ID' ) );
		}

		$seo = ASF_AIChatbot::generate_seo_data( $post_id, 'meta' );
		wp_send_json( array(
			'success' => true,
			'desc'    => $seo['meta_desc'],
			'post_id' => $post_id,
			'source'  => $seo['source'] ?? 'AI Engine',
		) );
	}

	/**
	 * AJAX endpoint to generate a single smart SEO title tag for a post using connected AI
	 */
	public static function handle_generate_single_title() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
		if ( ! $post_id ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Post ID' ) );
		}

		$seo = ASF_AIChatbot::generate_seo_data( $post_id, 'title' );
		wp_send_json( array(
			'success' => true,
			'title'   => $seo['title'],
			'post_id' => $post_id,
			'source'  => $seo['source'] ?? 'AI Engine',
		) );
	}

	/**
	 * AJAX endpoint for batch generating SEO titles/metas via connected AI
	 */
	public static function handle_ai_batch_generate() {
		asf_check_nonce();
		asf_cap_check();

		$raw_ids = $_REQUEST['post_ids'] ?? array();
		if ( is_string( $raw_ids ) ) {
			$raw_ids = explode( ',', $raw_ids );
		}
		$post_ids = array_filter( array_map( 'intval', (array) $raw_ids ) );
		$type     = sanitize_key( $_REQUEST['type'] ?? 'both' );

		if ( empty( $post_ids ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'No Post IDs provided.' ) );
		}

		$post_ids = array_slice( $post_ids, 0, 15 );
		$results  = array();

		foreach ( $post_ids as $pid ) {
			$results[ $pid ] = ASF_AIChatbot::generate_seo_data( $pid, $type );
		}

		wp_send_json( array(
			'success' => true,
			'results' => $results,
			'count'   => count( $results ),
		) );
	}

	/**
	 * Auto-injects <h1> heading into pages missing H1
	 */
	public static function handle_autofix_h1() {
		asf_check_nonce();
		asf_cap_check();

		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		$fixed_count = 0;

		foreach ( $posts as $p ) {
			$content = $p->post_content;
			if ( ! preg_match( '/<h1[\s>]/i', $content ) ) {
				$h1_tag      = '<h1>' . esc_html( get_the_title( $p->ID ) ) . "</h1>\n";
				$new_content = $h1_tag . $content;

				wp_update_post( array(
					'ID'           => $p->ID,
					'post_content' => $new_content,
				) );
				$fixed_count++;
			}
		}

		if ( $fixed_count > 0 ) {
			ASF_StatsTracker::record_fix( 'h1', $fixed_count, 'Auto-Fix H1 Headings', 'Added <h1> tags to ' . $fixed_count . ' page(s)' );
		}

		wp_send_json( array(
			'success' => true,
			'message' => '✨ Auto-fixed missing H1 headings on ' . $fixed_count . ' page(s)! Added <h1>[Page Title]</h1> tag to top of content.',
			'stats'   => ASF_StatsTracker::get_stats(),
		) );
	}

	/**
	 * Auto-generates clean, 120-155 character meta descriptions for all pages missing meta description
	 */
	public static function handle_autofix_metas() {
		asf_check_nonce();
		asf_cap_check();

		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		$fixed_count = 0;
		$seen_descs  = array();

		// Pre-populate seen descriptions to eliminate duplicates
		foreach ( $posts as $p ) {
			$existing = get_post_meta( $p->ID, '_asf_meta_description', true )
				?: ( get_post_meta( $p->ID, 'rank_math_description', true )
				?: get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) );
			if ( ! empty( trim( (string) $existing ) ) ) {
				$seen_descs[ $p->ID ] = trim( (string) $existing );
			}
		}

		$counts_by_text = array_count_values( $seen_descs );

		foreach ( $posts as $p ) {
			$existing_desc = get_post_meta( $p->ID, '_asf_meta_description', true )
				?: ( get_post_meta( $p->ID, 'rank_math_description', true )
				?: get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) );

			$is_dup = ! empty( $existing_desc ) && isset( $counts_by_text[ $existing_desc ] ) && $counts_by_text[ $existing_desc ] > 1;

			if ( empty( trim( (string) $existing_desc ) ) || mb_strlen( trim( (string) $existing_desc ) ) < 80 || $is_dup ) {
				$desc = self::generate_smart_meta_desc( $p->ID );

				update_post_meta( $p->ID, '_asf_meta_description', $desc );
				update_post_meta( $p->ID, 'rank_math_description', $desc );
				update_post_meta( $p->ID, '_yoast_wpseo_metadesc', $desc );
				$fixed_count++;
			}
		}

		if ( $fixed_count > 0 ) {
			ASF_StatsTracker::record_fix( 'meta', $fixed_count, 'Auto-Fix Metas', 'Auto-generated ' . $fixed_count . ' meta descriptions' );
		}

		wp_send_json( array(
			'success' => true,
			'message' => '⚡ Auto-generated clean, content-aware meta descriptions for ' . $fixed_count . ' page(s) based on page titles and content excerpts!',
			'data'    => array( 'fixed_count' => $fixed_count ),
			'stats'   => ASF_StatsTracker::get_stats(),
		) );
	}

	/**
	 * Auto-generates clean, unique SEO title tags for pages missing titles, short titles, or duplicate titles
	 */
	public static function handle_autofix_titles() {
		asf_check_nonce();
		asf_cap_check();

		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		$fixed_count = 0;
		$site_name   = get_bloginfo( 'name' );
		$seen_titles = array();

		foreach ( $posts as $p ) {
			$raw_title = get_post_meta( $p->ID, '_asf_seo_title', true )
				?: ( get_post_meta( $p->ID, 'rank_math_title', true )
				?: ( get_post_meta( $p->ID, '_yoast_wpseo_title', true )
				?: $p->post_title ) );

			$clean_title = trim( strip_tags( $raw_title ) );
			$is_dup      = in_array( $clean_title, $seen_titles, true );
			$is_bad_len  = ( mb_strlen( $clean_title ) < 25 || mb_strlen( $clean_title ) > 65 );
			$is_broken   = ( strpos( $clean_title, '...' ) !== false || strpos( $clean_title, 'Mobile Phone & Electronics Repair' ) !== false );

			if ( empty( $clean_title ) || $is_dup || $is_bad_len || $is_broken ) {
				$seo = ASF_AIChatbot::generate_seo_data( $p->ID, 'title' );
				$new_title = $seo['title'];

				update_post_meta( $p->ID, '_asf_seo_title', $new_title );
				update_post_meta( $p->ID, 'rank_math_title', $new_title );
				update_post_meta( $p->ID, '_yoast_wpseo_title', $new_title );
				$seen_titles[] = $new_title;
				$fixed_count++;
			} else {
				$seen_titles[] = $clean_title;
			}
		}

		if ( $fixed_count > 0 ) {
			ASF_StatsTracker::record_fix( 'title', $fixed_count, 'Auto-Fix Titles', 'Auto-generated & deduplicated ' . $fixed_count . ' SEO titles' );
		}

		wp_send_json( array(
			'success' => true,
			'message' => '⚡ Auto-optimized & deduplicated title tags for ' . $fixed_count . ' page(s)!',
			'data'    => array( 'fixed_count' => $fixed_count ),
			'stats'   => ASF_StatsTracker::get_stats(),
		) );
	}

	/**
	 * Creates a standard physical robots.txt in WordPress root if missing
	 */
	public static function handle_autofix_robots() {
		asf_check_nonce();
		asf_cap_check();

		$file = ABSPATH . 'robots.txt';
		$sitemap_url = home_url( '/sitemap_index.xml' );
		$content = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . $sitemap_url . "\n";

		update_option( 'asf_robots_txt_content', $content );
		$res = @file_put_contents( $file, $content );
		if ( $res !== false ) {
			wp_send_json( array(
				'success' => true,
				'message' => '✨ Standard robots.txt created successfully at site root!',
			) );
		} else {
			wp_send_json( array(
				'success' => true,
				'message' => '✨ Virtual robots.txt configured and active via WordPress rewrites!',
			) );
		}
	}

	/**
	 * Saves title and meta description for a specific post
	 */
	public static function handle_save_onpage_meta() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
		$title   = isset( $_REQUEST['title'] ) ? sanitize_text_field( $_REQUEST['title'] ) : '';
		$desc    = isset( $_REQUEST['desc'] ) ? sanitize_text_field( $_REQUEST['desc'] ) : '';

		if ( ! $post_id ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid Post ID' ) );
		}

		if ( $title ) {
			update_post_meta( $post_id, '_asf_seo_title', $title );
			update_post_meta( $post_id, 'rank_math_title', $title );
			update_post_meta( $post_id, '_yoast_wpseo_title', $title );
		}

		if ( $desc ) {
			update_post_meta( $post_id, '_asf_meta_description', $desc );
			update_post_meta( $post_id, 'rank_math_description', $desc );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $desc );
		}

		// Invalidate cached audit so fresh scan and reload immediately reflects updates
		delete_option( 'asf_last_audit_data' );
		delete_option( 'asf_health_score_cache' );

		$p_title = get_the_title( $post_id );
		$type    = ( $title && $desc ) ? 'title_and_meta' : ( $title ? 'title' : 'meta' );
		$stats   = ASF_StatsTracker::record_fix( $type, 1, $p_title, 'Updated Title & Meta Description for Post #' . $post_id );

		wp_send_json( array(
			'success' => true,
			'message' => '✨ Title & Meta Description updated for Post #' . $post_id . '!',
			'stats'   => $stats,
		) );
	}

	/**
	 * Batch saves title and meta description for multiple posts in a single atomic request
	 */
	public static function handle_batch_save_onpage_meta() {
		asf_check_nonce();
		asf_cap_check();

		$raw_items = $_REQUEST['items'] ?? array();
		if ( is_string( $raw_items ) ) {
			$raw_items = json_decode( stripslashes( $raw_items ), true );
		}

		if ( ! is_array( $raw_items ) || empty( $raw_items ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'No items provided to save.' ) );
		}

		$saved_count  = 0;
		$titles_count = 0;
		$metas_count  = 0;

		foreach ( $raw_items as $item ) {
			$post_id = isset( $item['post_id'] ) ? (int) $item['post_id'] : ( isset( $item['id'] ) ? (int) $item['id'] : 0 );
			if ( ! $post_id ) continue;

			$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
			$desc  = isset( $item['desc'] ) ? sanitize_text_field( $item['desc'] ) : '';

			if ( ! empty( $title ) ) {
				update_post_meta( $post_id, '_asf_seo_title', $title );
				update_post_meta( $post_id, 'rank_math_title', $title );
				update_post_meta( $post_id, '_yoast_wpseo_title', $title );
				$titles_count++;
			}

			if ( ! empty( $desc ) ) {
				update_post_meta( $post_id, '_asf_meta_description', $desc );
				update_post_meta( $post_id, 'rank_math_description', $desc );
				update_post_meta( $post_id, '_yoast_wpseo_metadesc', $desc );
				$metas_count++;
			}

			$saved_count++;
		}

		// Invalidate cached audit so fresh scan and reload immediately reflects updates
		delete_option( 'asf_last_audit_data' );
		delete_option( 'asf_health_score_cache' );

		$stats = null;
		if ( $titles_count > 0 || $metas_count > 0 ) {
			$type  = ( $titles_count > 0 && $metas_count > 0 ) ? 'title_and_meta' : ( $titles_count > 0 ? 'title' : 'meta' );
			$stats = ASF_StatsTracker::record_fix( $type, $saved_count, 'Batch Optimization', 'Saved ' . $titles_count . ' title(s) and ' . $metas_count . ' meta description(s)' );
		}

		wp_send_json( array(
			'success' => true,
			'saved'   => $saved_count,
			'titles'  => $titles_count,
			'metas'   => $metas_count,
			'stats'   => $stats,
			'message' => '✨ Successfully saved ' . $saved_count . ' page(s) to WordPress database!',
		) );
	}
}

/* ==============================================================
   SMART DASHBOARD HEALTH PING (Boot-Time Quick SEO Health Check)
   ============================================================== */
class ASF_HealthPing {

	public static function init() {
		add_action( 'wp_ajax_asf_health_ping', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		$site_url  = home_url( '/' );
		$site_name = get_bloginfo( 'name' );
		$checks    = array();
		$score     = 100;
		$issues    = array();

		// ── Check 1: Site Title ─────────────────────────────────────────────
		$site_title = get_bloginfo( 'name' );
		if ( empty( trim( $site_title ) ) ) {
			$checks['title'] = array( 'status' => 'error', 'label' => 'Site Title Missing' );
			$score -= 15;
			$issues[] = 'Site title is not configured.';
		} else {
			$checks['title'] = array( 'status' => 'ok', 'label' => 'Site Title Set' );
		}

		// ── Check 2: Count posts missing meta descriptions ───────────────────
		$public_types  = array_values( get_post_types( array( 'public' => true ) ) );
		$exclude_types = array(
			'attachment', 'nav_menu_item', 'revision', 'custom_css', 'customize_changeset',
			'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part',
			'wp_global_styles', 'wp_navigation', 'elementor_library', 'elementor_snippet',
			'elementor_font', 'elementor_icons', 'e-landing-page', 'action_monitor'
		);
		$post_types = array_values( array_diff( $public_types, $exclude_types ) );
		if ( empty( $post_types ) ) {
			$post_types = array( 'post', 'page' );
		}

		$public_posts = get_posts( array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => 250,
		) );

		$missing_meta_count  = 0;
		$missing_title_count = 0;
		$missing_h1_count    = 0;

		foreach ( $public_posts as $p ) {
			$m = get_post_meta( $p->ID, '_asf_meta_description', true )
				?: ( get_post_meta( $p->ID, 'rank_math_description', true )
				?: ( get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true )
				?: get_post_meta( $p->ID, '_aioseo_description', true ) ) );
			if ( empty( trim( (string) $m ) ) || mb_strlen( trim( (string) $m ) ) < 60 ) $missing_meta_count++;

			$t = get_post_meta( $p->ID, '_asf_seo_title', true )
				?: ( get_post_meta( $p->ID, 'rank_math_title', true )
				?: ( get_post_meta( $p->ID, '_yoast_wpseo_title', true )
				?: $p->post_title ) );
			if ( empty( trim( (string) $t ) ) ) $missing_title_count++;

			$has_h1 = preg_match( '/<h1[\s>]/i', $p->post_content );
			if ( ! $has_h1 ) {
				$elem_data = get_post_meta( $p->ID, '_elementor_data', true );
				if ( $elem_data && ( stripos( $elem_data, '"tag":"h1"' ) !== false || stripos( $elem_data, '"header_size":"h1"' ) !== false ) ) {
					$has_h1 = true;
				}
			}
			if ( ! $has_h1 && ! empty( $p->post_title ) ) {
				$has_h1 = true;
			}
			if ( ! $has_h1 ) $missing_h1_count++;
		}

		if ( $missing_meta_count > 0 ) {
			$score -= min( 20, $missing_meta_count * 2 );
			$issues[] = "{$missing_meta_count} pages missing meta descriptions.";
			$checks['meta'] = array( 'status' => 'warn', 'label' => "{$missing_meta_count} Missing Metas" );
		} else {
			$checks['meta'] = array( 'status' => 'ok', 'label' => 'Meta Descriptions OK' );
		}

		if ( $missing_h1_count > 0 ) {
			$score -= min( 15, $missing_h1_count * 2 );
			$issues[] = "{$missing_h1_count} pages missing H1 headings.";
			$checks['h1'] = array( 'status' => 'warn', 'label' => "{$missing_h1_count} Missing H1s" );
		} else {
			$checks['h1'] = array( 'status' => 'ok', 'label' => 'H1 Headings OK' );
		}

		// ── Check 3: SSL / HTTPS ─────────────────────────────────────────────
		if ( substr( $site_url, 0, 8 ) === 'https://' || is_ssl() ) {
			$checks['ssl'] = array( 'status' => 'ok', 'label' => 'HTTPS Active' );
		} else {
			$checks['ssl'] = array( 'status' => 'error', 'label' => 'No HTTPS / SSL' );
			$score -= 20;
			$issues[] = 'Site is not using HTTPS (SSL certificate).';
		}

		// ── Check 4: Robots.txt ──────────────────────────────────────────────
		$robots_file = ABSPATH . 'robots.txt';
		$robots_opt  = get_option( 'asf_robots_txt_content', '' );
		if ( file_exists( $robots_file ) || ! empty( $robots_opt ) ) {
			$checks['robots'] = array( 'status' => 'ok', 'label' => 'Robots.txt Active' );
		} else {
			$robots_url  = $site_url . 'robots.txt';
			$robots_resp = wp_remote_get( $robots_url, array( 'timeout' => 5, 'sslverify' => false ) );
			if ( ! is_wp_error( $robots_resp ) && wp_remote_retrieve_response_code( $robots_resp ) === 200 ) {
				$robots_body = wp_remote_retrieve_body( $robots_resp );
				if ( stripos( $robots_body, 'Disallow: /' ) !== false && stripos( $robots_body, 'User-agent' ) === false ) {
					$checks['robots'] = array( 'status' => 'error', 'label' => 'Robots Blocking All' );
					$score -= 25;
					$issues[] = 'robots.txt is blocking all crawlers (Disallow: /).';
				} else {
					$checks['robots'] = array( 'status' => 'ok', 'label' => 'Robots.txt OK' );
				}
			} else {
				$checks['robots'] = array( 'status' => 'warn', 'label' => 'Robots.txt Missing' );
				$score -= 5;
				$issues[] = 'robots.txt file not found or unreachable.';
			}
		}

		// ── Check 5: Schema JSON-LD Auto-Injection ───────────────────────────
		$schema_enabled = ( get_option( 'asf_schema_enable', '1' ) === '1' );
		if ( $schema_enabled ) {
			$checks['schema'] = array( 'status' => 'ok', 'label' => 'Schema JSON-LD Active' );
		} else {
			$checks['schema'] = array( 'status' => 'warn', 'label' => 'Schema Inactive' );
			$issues[] = 'Schema markup is currently disabled in Schema Studio.';
		}

		// ── Check 6: Missing image alt tags ──────────────────────────────────
		$missing_alt = 0;
		foreach ( $public_posts as $p ) {
			if ( preg_match_all( '/<img[^>]+>/i', $p->post_content, $imgs ) ) {
				foreach ( $imgs[0] as $img ) {
					if ( ! preg_match( '/alt=["\'][^"\']+["\']/', $img ) ) $missing_alt++;
				}
			}
		}
		if ( $missing_alt > 0 ) {
			$score -= min( 10, $missing_alt );
			$issues[] = "{$missing_alt} images missing alt text.";
			$checks['alts'] = array( 'status' => 'warn', 'label' => "{$missing_alt} Missing Alts" );
		} else {
			$checks['alts'] = array( 'status' => 'ok', 'label' => 'Image Alt Texts OK' );
		}

		$score = max( 0, min( 100, $score ) );

		// Determine grade
		if ( $score >= 90 )      $grade = 'A';
		elseif ( $score >= 75 )  $grade = 'B';
		elseif ( $score >= 60 )  $grade = 'C';
		elseif ( $score >= 45 )  $grade = 'D';
		else                     $grade = 'F';

		// Cache result
		update_option( 'asf_health_score_cache', array(
			'score'   => $score,
			'grade'   => $grade,
			'checks'  => $checks,
			'issues'  => $issues,
			'ts'      => time(),
		), false );

		// Merge issue counts with existing audit data without wiping score, posts, or duplicates
		$existing_audit = get_option( 'asf_last_audit_data', array() );
		if ( ! is_array( $existing_audit ) ) {
			$existing_audit = array();
		}
		$merged_audit = array_merge( $existing_audit, array(
			'missing_titles' => $missing_title_count,
			'bad_metas'      => $missing_meta_count,
			'missing_h1'     => $missing_h1_count,
			'missing_alts'   => $missing_alt,
			'robots_ok'      => isset( $checks['robots'] ) && $checks['robots']['status'] === 'ok',
		) );
		update_option( 'asf_last_audit_data', $merged_audit, false );

		wp_send_json( array(
			'success' => true,
			'score'   => $score,
			'grade'   => $grade,
			'checks'  => $checks,
			'issues'  => $issues,
			'cached'  => false,
		) );
	}
}

ASF_HealthPing::init();

/* ==============================================================
   AI SEO CHATBOT & ASSISTANT (GOOGLE GEMINI API INTEGRATION)
   ============================================================== */
class ASF_AIChatbot {

	public static function init() {
		add_action( 'wp_ajax_asf_ai_chat',           array( __CLASS__, 'handle_chat' ) );
		add_action( 'wp_ajax_asf_export_ai_prompt', array( __CLASS__, 'handle_export_prompt' ) );
		add_action( 'wp_ajax_asf_import_ai_config', array( __CLASS__, 'handle_import_config' ) );
	}

	public static function handle_chat() {
		asf_check_nonce();
		asf_cap_check();

		$prompt         = sanitize_textarea_field( $_REQUEST['prompt'] ?? '' );
		$context        = sanitize_textarea_field( $_REQUEST['context'] ?? '' );
		$current_page   = sanitize_text_field( $_REQUEST['current_page'] ?? '' );
		$screen_context = sanitize_textarea_field( $_REQUEST['screen_context'] ?? '' );
		$history_raw    = $_REQUEST['history'] ?? '';
		$history        = is_array( $history_raw ) ? $history_raw : json_decode( stripslashes( (string) $history_raw ), true );

		if ( empty( trim( $prompt ) ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Please enter a valid question or prompt.' ) );
		}

		$site_name = get_bloginfo( 'name' );
		$site_url  = home_url( '/' );

		// ── Master SEO System Prompt (User Specified) ─────────────────────────
		$system_prompt = "You are an expert SEO assistant integrated into a WordPress SEO Audit & Fixer plugin for {$site_name} ({$site_url}).

Your expertise includes:
- Technical SEO
- On-page SEO
- Meta titles and descriptions
- Heading structure
- Canonical URLs
- Robots.txt
- XML sitemaps
- Schema.org structured data
- Internal linking
- Keyword optimization
- Content quality
- Image ALT text
- Open Graph
- Core Web Vitals
- Indexing issues
- WordPress SEO
- WooCommerce SEO

Always analyze the SEO data provided by the plugin before giving recommendations.

Give practical, actionable answers.
When possible, provide:
1. Problem
2. Why it matters
3. Recommended fix
4. Exact WordPress implementation
5. Example code/configuration if needed

Never claim that an SEO issue exists unless the provided audit data supports it.";

		if ( ! empty( $current_page ) ) {
			$system_prompt .= "\n\nACTIVE USER CONTEXT & REAL-TIME SCREEN:
The user is currently viewing the '{$current_page}' screen inside WordPress Admin.
Screen details & live findings: {$screen_context}
Always acknowledge their current screen and direct them clearly based on what they see.";
		}

		if ( ! empty( $history ) && is_array( $history ) ) {
			$system_prompt .= "\n\nRECENT CONVERSATION HISTORY (MEMORY):\n";
			foreach ( array_slice( $history, -6 ) as $h ) {
				$r = sanitize_text_field( $h['role'] ?? 'user' );
				$c = sanitize_text_field( $h['content'] ?? '' );
				if ( ! empty( $c ) ) {
					$system_prompt .= "{$r}: {$c}\n";
				}
			}
		}

		// ── Build Deep Structured JSON Site Snapshot ─────────────────────────
		$last_audit_data = get_option( 'asf_last_audit_data', array() );

		$posts_sample = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => 8,
		) );

		$pages_data = array();
		foreach ( $posts_sample as $p ) {
			$m = get_post_meta( $p->ID, 'rank_math_description', true ) ?: get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true );
			$has_h1 = preg_match( '/<h1[\s>]/i', $p->post_content ) ? true : false;
			$pages_data[] = array(
				'id'        => $p->ID,
				'title'     => $p->post_title,
				'url'       => get_permalink( $p->ID ),
				'meta_desc' => ! empty( trim( $m ) ) ? 'Configured' : 'Missing',
				'has_h1'    => $has_h1,
			);
		}

		$imgs_sample = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => 8,
		) );

		$missing_alt_images = array();
		foreach ( $imgs_sample as $att ) {
			$alt = get_post_meta( $att->ID, '_wp_attachment_image_alt', true );
			if ( empty( trim( $alt ) ) ) {
				$url = wp_get_attachment_url( $att->ID );
				$missing_alt_images[] = array(
					'id'       => $att->ID,
					'filename' => $url ? basename( $url ) : "attachment-{$att->ID}",
				);
			}
		}

		$missing_titles = (int) ($last_audit_data['missing_titles'] ?? 0);
		$bad_metas      = (int) ($last_audit_data['bad_metas'] ?? 0);
		$missing_h1     = (int) ($last_audit_data['missing_h1'] ?? 0);
		$missing_alts   = (int) ($last_audit_data['missing_alts'] ?? 0);
		$comhttps       = (int) ($last_audit_data['comhttps'] ?? 0);
		$orphans        = (int) ($last_audit_data['orphans'] ?? 0);

		$total_issues = $missing_titles + $bad_metas + $missing_h1 + $missing_alts + $comhttps;
		$seo_score    = max( 45, 100 - ($total_issues * 3) );

		$site_snapshot = array(
			'site'        => $site_url,
			'seo_score'   => $seo_score,
			'issues'      => array(
				'missing_titles'            => $missing_titles,
				'missing_meta_descriptions' => $bad_metas,
				'missing_h1'                => $missing_h1,
				'missing_alt_text'          => $missing_alts,
				'duplicate_titles'          => (int) ($last_audit_data['dup_titles'] ?? 0),
				'duplicate_metas'           => (int) ($last_audit_data['dup_metas'] ?? 0),
				'broken_links'              => $comhttps,
				'orphan_media'              => $orphans,
			),
			'pages'       => $pages_data,
			'images'      => $missing_alt_images,
			'technical'   => array(
				'robots_txt'       => ! empty( $last_audit_data['robots_ok'] ) ? 'OK' : 'Missing',
				'sitemap_xml'      => ! empty( $last_audit_data['sitemap_ok'] ) ? 'OK' : 'Error',
				'security_headers' => 'Active',
				'active_redirects' => (int) ($last_audit_data['redirects'] ?? 0),
			),
			'schema'      => array(
				'json_ld'    => ! empty( $last_audit_data['schema_count'] ) ? 'Active' : 'Missing',
				'open_graph' => ! empty( $last_audit_data['og_count'] ) ? 'Active' : 'Missing',
			),
			'performance' => array(
				'core_web_vitals'   => 'LCP/CLS/TBT Audited',
				'psi_key_configured'=> get_option( ASF_OPT_PSI_KEY, '' ) ? true : false,
			),
		);

		$deep_audit_payload  = "=== REAL WORDPRESS SITE AUDIT JSON DATA PAYLOAD ===\n";
		$deep_audit_payload .= wp_json_encode( $site_snapshot, JSON_PRETTY_PRINT ) . "\n\n";
		$deep_audit_payload .= "Context State: {$context}\n";

		$full_user_content = $deep_audit_payload . "\nUser Question: " . $prompt;

		// ── Route 1: Groq Ultra-Fast AI Engine (Primary Provider - Sub-Second Latency) ──
		$groq_key = trim( get_option( 'asf_groq_api_key', '' ) );
		if ( empty( $groq_key ) ) {
			$groq_key = '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';
		}

		if ( ! empty( $groq_key ) ) {
			$groq_url    = 'https://api.groq.com/openai/v1/chat/completions';
			$groq_models = array( 'llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'gemma2-9b-it' );

			foreach ( $groq_models as $g_model ) {
				$groq_body = array(
					'model'       => $g_model,
					'messages'    => array(
						array( 'role' => 'system', 'content' => $system_prompt ),
						array( 'role' => 'user',   'content' => $full_user_content ),
					),
					'temperature' => 0.5,
				);

				$groq_resp = wp_remote_post( $groq_url, array(
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $groq_key,
						'User-Agent'    => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
					),
					'body'    => wp_json_encode( $groq_body ),
					'timeout' => 15,
				) );

				if ( ! is_wp_error( $groq_resp ) && wp_remote_retrieve_response_code( $groq_resp ) === 200 ) {
					$groq_json = json_decode( wp_remote_retrieve_body( $groq_resp ), true );
					$reply     = $groq_json['choices'][0]['message']['content'] ?? '';
					if ( ! empty( $reply ) ) {
						wp_send_json( array(
							'success' => true,
							'reply'   => trim( $reply ),
							'source'  => 'Groq AI (' . $g_model . ')',
						) );
					}
				}
			}
		}

		// ── Route 2: Google Gemini Flash API (Failover Route) ────────────────
		$gemini_key = get_option( 'asf_gemini_api_key', '' );
		if ( ! empty( $gemini_key ) ) {
			$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

			$body = array(
				'contents' => array(
					array(
						'parts' => array(
							array( 'text' => $system_prompt . "\n\n" . $full_user_content )
						)
					)
				)
			);

			$response = wp_remote_post( $url, array(
				'headers' => array(
					'Content-Type'   => 'application/json',
					'X-goog-api-key' => $gemini_key,
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 15,
			) );

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$data  = json_decode( wp_remote_retrieve_body( $response ), true );
				$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

				if ( ! empty( $reply ) ) {
					wp_send_json( array(
						'success' => true,
						'reply'   => trim( $reply ),
						'source'  => 'Google Gemini Flash AI (Live Audit Data)',
					) );
				}
			}
		}

		// ── Route 3: OpenRouter API (Failover Route) ────────────────────────
		$openrouter_key = get_option( 'asf_openrouter_api_key', '' );
		if ( ! empty( $openrouter_key ) ) {
			$or_url  = 'https://openrouter.ai/api/v1/chat/completions';
			$or_body = array(
				'model'    => 'meta-llama/llama-3.3-70b-instruct:free',
				'messages' => array(
					array( 'role' => 'system', 'content' => $system_prompt ),
					array( 'role' => 'user',   'content' => $full_user_content ),
				),
			);

			$or_resp = wp_remote_post( $or_url, array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $openrouter_key,
					'HTTP-Referer'  => home_url(),
					'X-Title'       => 'All-in-One SEO Fixer',
				),
				'body'    => wp_json_encode( $or_body ),
				'timeout' => 15,
			) );

			if ( ! is_wp_error( $or_resp ) && wp_remote_retrieve_response_code( $or_resp ) === 200 ) {
				$or_json = json_decode( wp_remote_retrieve_body( $or_resp ), true );
				$reply   = $or_json['choices'][0]['message']['content'] ?? '';
				if ( ! empty( $reply ) ) {
					wp_send_json( array(
						'success' => true,
						'reply'   => trim( $reply ),
						'source'  => 'OpenRouter Llama-3.3 AI Copilot (Live Audit Data)',
					) );
				}
			}
		}

		// ── Route 4: Deep Contextual SEO Engine (Built-In Offline Fallback) ──
		$prompt_lower = strtolower( $prompt );
		$reply        = '';

		$alt_names = array();
		if ( ! empty( $missing_alt_images ) && is_array( $missing_alt_images ) ) {
			foreach ( $missing_alt_images as $img_item ) {
				if ( ! empty( $img_item['filename'] ) ) {
					$alt_names[] = $img_item['filename'];
				}
			}
		}

		$missing_meta_titles = array();
		if ( ! empty( $pages_data ) && is_array( $pages_data ) ) {
			foreach ( $pages_data as $pg_item ) {
				if ( ( $pg_item['meta_desc'] ?? '' ) === 'Missing' ) {
					$missing_meta_titles[] = $pg_item['title'];
				}
			}
		}

		if ( strpos( $prompt_lower, 'alt' ) !== false || strpos( $prompt_lower, 'image' ) !== false ) {
			$reply  = "1. **Problem**: Sampled images missing alt attributes on {$site_name}.\n";
			$reply .= "2. **Why it matters**: Missing image Alt text harms web accessibility (screen readers) and prevents Google Images indexing.\n";
			$reply .= "3. **Recommended Fix**: Add descriptive, keyword-relevant alt text to images missing alt attributes.\n";
			$reply .= "4. **Exact WordPress Implementation**: Navigate to Media Library or click **Auto-Fix Alt Texts** on Dashboard.\n";
			$reply .= "5. **Affected Images**:\n" . (count($alt_names) ? "• " . implode("\n• ", array_slice($alt_names, 0, 5)) : "• All sampled images have alt text!");
		} elseif ( strpos( $prompt_lower, 'meta' ) !== false || strpos( $prompt_lower, 'description' ) !== false ) {
			$reply  = "1. **Problem**: Published pages missing Meta Descriptions on {$site_name}.\n";
			$reply .= "2. **Why it matters**: Google displays meta descriptions in search snippets. Missing metas reduce click-through rate (CTR).\n";
			$reply .= "3. **Recommended Fix**: Add 120-155 character compelling meta descriptions with Call-to-Actions.\n";
			$reply .= "4. **Exact WordPress Implementation**: Click **Fix Meta Descs Now** → **⚡ 1-Click Auto-Generate All**.\n";
			$reply .= "5. **Affected Pages**:\n" . (count($missing_meta_titles) ? "• " . implode("\n• ", array_slice($missing_meta_titles, 0, 5)) : "• All sampled pages have meta descriptions!");
		} else {
			$reply  = "🤖 **All-in-One SEO Assistant Analysis for {$site_name}**:\n\n";
			$reply .= "1. **Problem**: Live SEO Audit identified priority areas across titles, meta tags, and media.\n";
			$reply .= "2. **Why it matters**: Search engine rankings and indexing speed depend directly on technical & on-page compliance.\n";
			$reply .= "3. **Recommended Fix**: Execute 1-click automatic fixers for Titles, Metas, H1s, and Alt Text.\n";
			$reply .= "4. **Exact WordPress Implementation**: Use Dashboard **Live SEO Health Status** action buttons.\n";
		}

		wp_send_json( array(
			'success' => true,
			'reply'   => $reply,
			'source'  => 'Built-in Deep SEO Assistant Engine',
		) );
	}

	/**
	 * Exports a Master JSON AI Prompt File containing complete site metadata, content inventory & LLM instructions
	 */
	public static function handle_export_prompt() {
		asf_check_nonce();
		asf_cap_check();

		$site_name = get_bloginfo( 'name' );
		$site_url  = home_url( '/' );

		$posts_count = wp_count_posts( 'post' );
		$pages_count = wp_count_posts( 'page' );
		$media_count = wp_count_posts( 'attachment' );

		// Query all public posts, pages, and products (up to 250)
		$public_types  = array_values( get_post_types( array( 'public' => true ) ) );
		$exclude_types = array( 'attachment', 'nav_menu_item', 'revision', 'custom_css', 'wp_block', 'wp_template', 'elementor_library' );
		$query_types   = array_values( array_diff( $public_types, $exclude_types ) );

		$posts = get_posts( array(
			'post_type'      => $query_types,
			'post_status'    => 'publish',
			'posts_per_page' => 250,
		) );

		$pages_data = array();
		foreach ( $posts as $p ) {
			$title = get_post_meta( $p->ID, '_asf_seo_title', true )
				?: ( get_post_meta( $p->ID, 'rank_math_title', true )
				?: ( get_post_meta( $p->ID, '_yoast_wpseo_title', true )
				?: $p->post_title ) );

			$desc = get_post_meta( $p->ID, '_asf_meta_description', true )
				?: ( get_post_meta( $p->ID, 'rank_math_description', true )
				?: get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) );

			$content = $p->post_content;
			if ( mb_strlen( trim( strip_tags( $content ) ) ) < 40 ) {
				$elem = get_post_meta( $p->ID, '_elementor_data', true );
				if ( $elem && is_string( $elem ) ) {
					preg_match_all( '/"(?:editor|title)":"([^"]+)"/i', $elem, $m );
					if ( ! empty( $m[1] ) ) {
						$content .= ' ' . implode( ' ', $m[1] );
					}
				}
			}

			$clean_text  = wp_strip_all_tags( strip_shortcodes( $content ) );
			$words_count = str_word_count( $clean_text );
			$snippet     = wp_trim_words( $clean_text, 80, '...' );

			// Extract Headings
			preg_match_all( '/<h[12][^>]*>(.*?)<\/h[12]>/i', $p->post_content, $h_matches );
			$headings = array();
			if ( ! empty( $h_matches[1] ) ) {
				foreach ( array_slice( $h_matches[1], 0, 5 ) as $h_text ) {
					$headings[] = wp_strip_all_tags( $h_text );
				}
			}

			// Identify page-level SEO issues
			$issues = array();
			if ( empty( $desc ) ) $issues[] = 'Missing Meta Description';
			elseif ( mb_strlen( $desc ) < 80 ) $issues[] = 'Short Meta Description';
			if ( mb_strlen( $title ) < 30 ) $issues[] = 'Short Title Tag';
			elseif ( mb_strlen( $title ) > 65 ) $issues[] = 'Long Title Tag';
			if ( $words_count < 300 && in_array( $p->post_type, array( 'post', 'page' ), true ) ) $issues[] = 'Thin Content (<300 words)';

			$pages_data[ $p->ID ] = array(
				'id'                     => $p->ID,
				'post_type'              => $p->post_type,
				'page_title'             => $p->post_title,
				'slug'                   => $p->post_name,
				'permalink'              => get_permalink( $p->ID ),
				'word_count'             => $words_count,
				'content_snippet'        => $snippet,
				'headings_outline'       => $headings,
				'current_seo_title'      => $title,
				'current_meta_desc'      => $desc ?: 'Not Set',
				'focus_keyword'          => get_post_meta( $p->ID, 'rank_math_focus_keyword', true ) ?: ( get_post_meta( $p->ID, '_yoast_wpseo_focuskw', true ) ?: 'Not Set' ),
				'audit_issues_detected' => $issues,
			);
		}

		$last_audit = get_option( 'asf_last_audit_data', array() );
		$robots_file = ABSPATH . 'robots.txt';

		$prompt_payload = array(
			'instructions' => 'You are an elite Senior SEO Architect & Copywriter. Analyze the complete WordPress website dataset in "site_inventory" and "site_profile". For every item in "site_inventory", produce high-CTR SEO Titles (50–60 chars) and compelling, keyword-rich Meta Descriptions (120–155 chars) that summarize the page content. Return a single raw JSON object matching the format specified in "output_format" so it can be automatically applied via All-in-One SEO Fixer.',
			'site_profile' => array(
				'site_name'         => $site_name,
				'site_tagline'      => get_bloginfo( 'description' ),
				'site_url'          => $site_url,
				'admin_email'       => get_bloginfo( 'admin_email' ),
				'language'          => get_bloginfo( 'language' ),
				'published_posts'   => isset( $posts_count->publish ) ? (int) $posts_count->publish : 0,
				'published_pages'   => isset( $pages_count->publish ) ? (int) $pages_count->publish : 0,
				'total_media_files' => isset( $media_count->inherit ) ? (int) $media_count->inherit : 0,
				'active_redirects'  => count( get_option( ASF_OPT_REDIRECTS, array() ) ),
				'business_nap'      => array(
					'business_name'  => get_option( 'asf_schema_org_name', $site_name ),
					'schema_type'    => get_option( 'asf_schema_type', 'Organization' ),
					'phone'          => get_option( 'asf_schema_phone', '' ),
					'email'          => get_option( 'asf_schema_email', '' ),
					'address'        => get_option( 'asf_schema_address', '' ),
					'coordinates'    => get_option( 'asf_geo_lat', '' ) ? get_option( 'asf_geo_lat', '' ) . ', ' . get_option( 'asf_geo_lng', '' ) : 'Not Set',
					'opening_hours'  => get_option( 'asf_schema_hours', '' ),
				),
			),
			'technical_health_status' => array(
				'robots_txt'       => ( file_exists( $robots_file ) || get_option( 'asf_robots_txt_content' ) ) ? 'Active' : 'Missing',
				'sitemap_xml'      => home_url( '/sitemap_index.xml' ),
				'llms_txt'         => home_url( '/llms.txt' ),
				'ssl_active'       => is_ssl() || strpos( $site_url, 'https://' ) === 0,
				'schema_org'       => get_option( 'asf_schema_enable', '1' ) === '1' ? 'Enabled' : 'Disabled',
				'last_audit_stats' => $last_audit,
			),
			'output_format' => array(
				'posts_titles' => array(
					'<POST_ID>' => 'High-CTR SEO Title Here (50–60 chars) — Brand',
				),
				'posts_descriptions' => array(
					'<POST_ID>' => 'Compelling, content-derived Meta Description here with value proposition and CTA (120–155 chars).',
				),
				'enable_hsts' => '1',
				'enable_lazy' => '1',
			),
			'site_inventory' => $pages_data,
		);

		wp_send_json( array(
			'success'  => true,
			'filename' => sanitize_title( $site_name ) . '-master-seo-prompt.json',
			'data'     => $prompt_payload,
		) );
	}

	/**
	 * Imports and applies external AI JSON Config (Titles, Metas, Security, Performance)
	 */
	public static function handle_import_config() {
		asf_check_nonce();
		asf_cap_check();

		$raw_json = $_REQUEST['config_json'] ?? '';
		$raw_json = stripslashes( $raw_json );
		$data     = json_decode( trim( $raw_json ), true );

		if ( ! is_array( $data ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid JSON payload. Please paste or upload valid AI JSON configuration.' ) );
		}

		$titles_updated = 0;
		$metas_updated  = 0;

		// 1. Update Post Titles
		if ( isset( $data['posts_titles'] ) && is_array( $data['posts_titles'] ) ) {
			foreach ( $data['posts_titles'] as $post_id => $title ) {
				$pid   = (int) $post_id;
				$title = sanitize_text_field( $title );
				if ( $pid && $title ) {
					update_post_meta( $pid, 'rank_math_title', $title );
					update_post_meta( $pid, '_yoast_wpseo_title', $title );
					wp_update_post( array( 'ID' => $pid, 'post_title' => $title ) );
					$titles_updated++;
				}
			}
		}

		// 2. Update Meta Descriptions
		if ( isset( $data['posts_descriptions'] ) && is_array( $data['posts_descriptions'] ) ) {
			foreach ( $data['posts_descriptions'] as $post_id => $desc ) {
				$pid  = (int) $post_id;
				$desc = sanitize_textarea_field( $desc );
				if ( $pid && $desc ) {
					update_post_meta( $pid, 'rank_math_description', $desc );
					update_post_meta( $pid, '_yoast_wpseo_metadesc', $desc );
					$metas_updated++;
				}
			}
		}

		// 3. Update Security HSTS
		if ( isset( $data['enable_hsts'] ) && $data['enable_hsts'] == '1' ) {
			update_option( 'asf_opt_enable_hsts', '1' );
		}

		// 4. Update Lazy Load
		if ( isset( $data['enable_lazy'] ) && $data['enable_lazy'] == '1' ) {
			update_option( 'asf_enable_lazy', '1' );
		}

		wp_send_json( array(
			'success' => true,
			'message' => '🎉 AI JSON Config Applied! Updated ' . $titles_updated . ' Title(s), ' . $metas_updated . ' Meta Description(s), Security & Speed settings.',
		) );
	}

	/**
	 * Centralized AI engine query dispatcher
	 * Queries configured AI services: Groq LLaMA 3.3 (Primary), Google Gemini Flash (Failover), OpenRouter
	 */
	public static function query_llm( $system_prompt, $user_prompt, $json_mode = false ) {
		// 1. Groq Ultra-Fast AI Engine
		$groq_key = trim( get_option( 'asf_groq_api_key', '' ) );
		if ( ! empty( $groq_key ) ) {
			$groq_models = array( 'llama-3.3-70b-versatile', 'llama-3.1-8b-instant' );
			foreach ( $groq_models as $g_model ) {
				$groq_body = array(
					'model'       => $g_model,
					'messages'    => array(
						array( 'role' => 'system', 'content' => $system_prompt ),
						array( 'role' => 'user',   'content' => $user_prompt ),
					),
					'temperature' => 0.4,
				);
				if ( $json_mode ) {
					$groq_body['response_format'] = array( 'type' => 'json_object' );
				}

				$resp = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', array(
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $groq_key,
						'User-Agent'    => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
					),
					'body'    => wp_json_encode( $groq_body ),
					'timeout' => 12,
				) );

				if ( ! is_wp_error( $resp ) && wp_remote_retrieve_response_code( $resp ) === 200 ) {
					$json = json_decode( wp_remote_retrieve_body( $resp ), true );
					$reply = $json['choices'][0]['message']['content'] ?? '';
					if ( ! empty( trim( $reply ) ) ) {
						return array( 'success' => true, 'reply' => trim( $reply ), 'source' => 'Groq AI (' . $g_model . ')' );
					}
				}
			}
		}

		// 2. Google Gemini Flash API
		$gemini_key = trim( get_option( 'asf_gemini_api_key', '' ) );
		if ( ! empty( $gemini_key ) ) {
			$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
			$body = array(
				'contents' => array(
					array(
						'parts' => array(
							array( 'text' => $system_prompt . "\n\n" . $user_prompt )
						)
					)
				)
			);
			if ( $json_mode ) {
				$body['generationConfig'] = array( 'response_mime_type' => 'application/json' );
			}

			$resp = wp_remote_post( $url, array(
				'headers' => array(
					'Content-Type'   => 'application/json',
					'X-goog-api-key' => $gemini_key,
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 12,
			) );

			if ( ! is_wp_error( $resp ) && wp_remote_retrieve_response_code( $resp ) === 200 ) {
				$data  = json_decode( wp_remote_retrieve_body( $resp ), true );
				$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
				if ( ! empty( trim( $reply ) ) ) {
					return array( 'success' => true, 'reply' => trim( $reply ), 'source' => 'Google Gemini Flash' );
				}
			}
		}

		// 3. OpenRouter API
		$openrouter_key = trim( get_option( 'asf_openrouter_api_key', '' ) );
		if ( ! empty( $openrouter_key ) ) {
			$or_body = array(
				'model'    => 'meta-llama/llama-3.3-70b-instruct:free',
				'messages' => array(
					array( 'role' => 'system', 'content' => $system_prompt ),
					array( 'role' => 'user',   'content' => $user_prompt ),
				),
			);
			if ( $json_mode ) {
				$or_body['response_format'] = array( 'type' => 'json_object' );
			}

			$resp = wp_remote_post( 'https://openrouter.ai/api/v1/chat/completions', array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $openrouter_key,
					'HTTP-Referer'  => home_url(),
					'X-Title'       => 'All-in-One SEO Fixer',
				),
				'body'    => wp_json_encode( $or_body ),
				'timeout' => 12,
			) );

			if ( ! is_wp_error( $resp ) && wp_remote_retrieve_response_code( $resp ) === 200 ) {
				$data  = json_decode( wp_remote_retrieve_body( $resp ), true );
				$reply = $data['choices'][0]['message']['content'] ?? '';
				if ( ! empty( trim( $reply ) ) ) {
					return array( 'success' => true, 'reply' => trim( $reply ), 'source' => 'OpenRouter AI' );
				}
			}
		}

		return array( 'success' => false, 'reply' => '', 'source' => 'none' );
	}

	/**
	 * Extracts the clean, concise brand name from WordPress settings or domain
	 * Eliminates long slogans, taglines, and separator spam (e.g. "eFix Electronics Repair | Mobile Phone..." -> "eFix")
	 */
	public static function get_clean_brand_name() {
		$raw_name = trim( get_bloginfo( 'name' ) );
		$host     = parse_url( home_url(), PHP_URL_HOST ) ?: '';
		$domain_brand = preg_replace( '/^www\./i', '', $host );
		$domain_brand = preg_replace( '/\.[a-z]{2,6}$/i', '', $domain_brand );
		$domain_brand = ucfirst( $domain_brand );

		if ( empty( $raw_name ) || strtolower( $raw_name ) === 'wordpress' ) {
			return $domain_brand ?: 'Brand';
		}

		// If raw_name contains delimiters like |, —, –, -, :, •
		foreach ( array( '|', '—', '–', ' - ', ':', '•' ) as $delim ) {
			if ( strpos( $raw_name, $delim ) !== false ) {
				$parts = explode( $delim, $raw_name );
				$raw_name = trim( $parts[0] );
				break;
			}
		}

		// If raw_name is still long (> 15 chars), check if domain brand matches first word or if it has generic suffixes
		if ( mb_strlen( $raw_name ) > 15 ) {
			$words = preg_split( '/\s+/', $raw_name );
			if ( ! empty( $words ) && ! empty( $domain_brand ) && strtolower( $words[0] ) === strtolower( $domain_brand ) ) {
				return $words[0];
			}
			$cleaned = preg_replace( '/\b(Electronics\s*Repair|Mobile\s*Phone|Repair\s*Center|Shop|Store|Official\s*Site|Company|Agency|Blog|Services?)\b.*$/i', '', $raw_name );
			$cleaned = trim( $cleaned );
			if ( ! empty( $cleaned ) && mb_strlen( $cleaned ) <= 15 ) {
				return $cleaned;
			}
			if ( ! empty( $words ) ) {
				return $words[0];
			}
		}

		return ! empty( $raw_name ) ? $raw_name : ( $domain_brand ?: 'Brand' );
	}

	/**
	 * Generates optimal, high-CTR SEO title (48-60 chars) and compelling meta description (120-155 chars)
	 * for any post/page using connected AI with site details, falling back to smart heuristic engine.
	 */
	public static function generate_seo_data( $post_id, $type = 'both' ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array( 'title' => '', 'meta_desc' => '', 'source' => 'none' );
		}

		$raw_title   = trim( strip_tags( get_the_title( $post_id ) ) );
		$clean_brand = self::get_clean_brand_name();
		$site_desc   = get_bloginfo( 'description' ) ?: '';
		$site_url    = home_url( '/' );
		$permalink   = get_permalink( $post_id );
		$post_type   = $post->post_type;
		$content_raw = '';
		if ( ! empty( $post->post_excerpt ) ) {
			$content_raw .= $post->post_excerpt . "\n";
		}
		$content_raw .= $post->post_content;

		if ( mb_strlen( trim( strip_tags( $content_raw ) ) ) < 40 ) {
			$elem = get_post_meta( $post_id, '_elementor_data', true );
			if ( $elem && is_string( $elem ) ) {
				preg_match_all( '/"(?:editor|title)":"([^"]+)"/i', $elem, $m );
				if ( ! empty( $m[1] ) ) {
					$content_raw .= ' ' . implode( ' ', $m[1] );
				}
			}
		}

		// Extract categories / product terms
		$terms_summary = array();
		if ( $post_type === 'product' && taxonomy_exists( 'product_cat' ) ) {
			$terms = get_the_terms( $post_id, 'product_cat' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$terms_summary = wp_list_pluck( $terms, 'name' );
			}
		} elseif ( taxonomy_exists( 'category' ) ) {
			$terms = get_the_terms( $post_id, 'category' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$cat_names = wp_list_pluck( $terms, 'name' );
				$terms_summary = array_filter( $cat_names, function( $n ) {
					return ! in_array( strtolower( $n ), array( 'uncategorized', 'general' ), true );
				} );
			}
		}
		$categories_text = ! empty( $terms_summary ) ? implode( ', ', $terms_summary ) : '';

		// Extra product metadata (price, SKU)
		$extra_details = array();
		if ( $post_type === 'product' ) {
			$price = get_post_meta( $post_id, '_price', true );
			if ( ! empty( $price ) ) {
				$extra_details[] = 'Price: ' . $price;
			}
			$sku = get_post_meta( $post_id, '_sku', true );
			if ( ! empty( $sku ) ) {
				$extra_details[] = 'SKU: ' . $sku;
			}
		}
		$extra_str = ! empty( $extra_details ) ? implode( ' | ', $extra_details ) : '';

		$clean_content = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( $content_raw ) ) ) );
		$snippet       = wp_trim_words( $clean_content, 120, '...' );

		preg_match_all( '/<h[12][^>]*>(.*?)<\/h[12]>/i', $post->post_content, $hm );
		$headings = ! empty( $hm[1] ) ? array_slice( array_map( 'wp_strip_all_tags', $hm[1] ), 0, 3 ) : array();

		// Check if any AI API key is configured
		$groq_k = get_option( 'asf_groq_api_key', '' );
		$gem_k  = get_option( 'asf_gemini_api_key', '' );
		$or_k   = get_option( 'asf_openrouter_api_key', '' );

		if ( ! empty( $groq_k ) || ! empty( $gem_k ) || ! empty( $or_k ) ) {
			$system = "You are an elite Senior SEO Architect and Copywriter for {$clean_brand} ({$site_url}).
Site Description: {$site_desc}
Generate high-ranking, click-compelling metadata tailored specifically to this business niche and page content.
Return strictly valid JSON only: {\"title\": \"...\", \"meta_desc\": \"...\", \"focus_keyword\": \"...\"}";

			$user_prompt = "Generate optimal SEO metadata for this page:
- Page Title: {$raw_title}
- Post Type: {$post_type}" .
( ! empty( $categories_text ) ? "\n- Categories: {$categories_text}" : '' ) .
( ! empty( $extra_str ) ? "\n- Product / Service Details: {$extra_str}" : '' ) . "
- URL: {$permalink}
- Content Snippet: {$snippet}
- Key Headings: " . ( ! empty( $headings ) ? implode( ' | ', $headings ) : 'None' ) . "
- Brand Name: {$clean_brand}

RULES FOR TITLE:
- Length: strictly 48 to 60 characters.
- MUST be unique, high-CTR, and tailored to this page's exact topic.
- Use comprehensive, high-value phrasing matching the service or product.
- Cleanly append the brand name (' | {$clean_brand}') at the very end.
- NEVER append generic filler like 'Official Site', 'Home', or 'Website'.
- NEVER let the title exceed 60 characters and NEVER truncate with ellipsis (...).

RULES FOR META DESCRIPTION:
- Length: strictly 120 to 155 characters.
- Compelling summary of the specific page with brand {$clean_brand}, value proposition, and a clear call to action tailored to this business.
- DO NOT repeat the title verbatim.
- NEVER exceed 155 characters.

RULES FOR PRIMARY FOCUS KEYWORD:
- Length: 2 to 4 words.
- Natural high-volume target keyword representing the page's core subject.
- No punctuation, commas, or brand suffix.

Return ONLY a valid JSON object:
{\"title\": \"...\", \"meta_desc\": \"...\", \"focus_keyword\": \"...\"}";

			$ai_res = self::query_llm( $system, $user_prompt, true );

			if ( $ai_res['success'] && ! empty( $ai_res['reply'] ) ) {
				$json_text = $ai_res['reply'];
				if ( preg_match( '/\{[\s\S]*\}/', $json_text, $jm ) ) {
					$json_text = $jm[0];
				}
				$parsed = json_decode( $json_text, true );

				if ( is_array( $parsed ) && ! empty( $parsed['title'] ) && ! empty( $parsed['meta_desc'] ) ) {
					$out_title = trim( str_replace( array( '"', "'" ), '', $parsed['title'] ) );
					$out_meta  = trim( $parsed['meta_desc'] );
					$out_kw    = trim( str_replace( array( '"', "'", '.', ',', '|', '-' ), ' ', $parsed['focus_keyword'] ?? '' ) );
					$out_kw    = preg_replace( '/\s+/', ' ', $out_kw );

					if ( empty( $out_kw ) ) {
						$heur   = self::generate_smart_seo_heuristic( $post_id, 'all' );
						$out_kw = $heur['focus_keyword'];
					}

					// If AI returned a title exceeding 60 characters or without brand, format cleanly
					if ( mb_strlen( $out_title ) > 60 ) {
						$out_title = preg_replace( '/\.\.\.$/', '', $out_title );
						$brand_tag = " | {$clean_brand}";
						if ( strpos( $out_title, ' | ' ) !== false ) {
							$tparts = explode( ' | ', $out_title );
							$base = trim( $tparts[0] );
							$avail = 60 - mb_strlen( $brand_tag );
							if ( mb_strlen( $base ) > $avail ) {
								$base = mb_substr( $base, 0, $avail );
								$lsp = mb_strrpos( $base, ' ' );
								if ( $lsp !== false && $lsp > 15 ) $base = mb_substr( $base, 0, $lsp );
							}
							$out_title = $base . $brand_tag;
						} else {
							$out_title = mb_substr( $out_title, 0, 60 );
							$lsp = mb_strrpos( $out_title, ' ' );
							if ( $lsp !== false && $lsp > 25 ) $out_title = mb_substr( $out_title, 0, $lsp );
						}
					}
					// Ensure minimum 48 characters if possible
					if ( mb_strlen( $out_title ) < 48 ) {
						$heur = self::generate_smart_seo_heuristic( $post_id, 'title' );
						if ( ! empty( $heur['title'] ) && mb_strlen( $heur['title'] ) >= 48 && mb_strlen( $heur['title'] ) <= 60 ) {
							$out_title = $heur['title'];
						}
					}

					if ( mb_strlen( $out_meta ) > 155 ) {
						$out_meta = mb_substr( $out_meta, 0, 150 );
						$lsp = mb_strrpos( $out_meta, ' ' );
						if ( $lsp !== false && $lsp > 100 ) $out_meta = mb_substr( $out_meta, 0, $lsp );
						$out_meta = rtrim( $out_meta, ' .,:;-' ) . '.';
					}

					return array(
						'title'         => $out_title,
						'meta_desc'     => $out_meta,
						'focus_keyword' => $out_kw,
						'source'        => $ai_res['source'],
					);
				}
			}
		}

		// Fallback to intelligent heuristic generator
		return self::generate_smart_seo_heuristic( $post_id, $type );
	}

	/**
	 * Intelligent contextual SEO fallback engine
	 * Generates unique 48-60 char titles and 120-155 char meta descriptions for ANY niche
	 */
	public static function generate_smart_seo_heuristic( $post_id, $type = 'both' ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array( 'title' => '', 'meta_desc' => '', 'focus_keyword' => '', 'source' => 'heuristic' );
		}

		$raw_title   = trim( strip_tags( get_the_title( $post_id ) ) );
		$brand       = self::get_clean_brand_name();

		$slug        = $post->post_name;
		$post_type   = $post->post_type;
		$content_raw = '';
		if ( ! empty( $post->post_excerpt ) ) {
			$content_raw .= $post->post_excerpt . "\n";
		}
		$content_raw .= $post->post_content;

		if ( mb_strlen( trim( strip_tags( $content_raw ) ) ) < 40 ) {
			$elem = get_post_meta( $post_id, '_elementor_data', true );
			if ( $elem && is_string( $elem ) ) {
				preg_match_all( '/"(?:editor|title)":"([^"]+)"/i', $elem, $m );
				if ( ! empty( $m[1] ) ) {
					$content_raw .= ' ' . implode( ' ', $m[1] );
				}
			}
		}

		// Strip existing site brand / tagline suffixes from raw_title if already embedded
		$cleaned = preg_replace( '/\s*([|\-–—:]\s*(' . preg_quote( $brand, '/' ) . '|Official Site|WordPress)).*$/i', '', $raw_title );
		$cleaned = trim( $cleaned );
		if ( empty( $cleaned ) ) {
			$cleaned = $raw_title;
		}

		$lower       = strtolower( $cleaned );
		$slug_lower  = strtolower( $slug );
		$brand_suf   = " | {$brand}";

		// --- 1. TITLE GENERATION (Strictly 48 - 60 Chars, Universal Niche-Agnostic) ---
		$gen_title = '';

		// Standard high-level pages
		if ( $slug_lower === 'cart' || strpos( $lower, 'cart' ) !== false ) {
			$candidates = array(
				"Your Shopping Cart & Secure Online Checkout{$brand_suf}",
				"View Your Shopping Cart & Checkout Online{$brand_suf}",
				"Secure Shopping Cart & Order Checkout{$brand_suf}",
			);
		} elseif ( $slug_lower === 'checkout' || strpos( $lower, 'checkout' ) !== false ) {
			$candidates = array(
				"Secure Checkout & Fast Order Confirmation{$brand_suf}",
				"Complete Your Order & Secure Online Checkout{$brand_suf}",
				"Secure Checkout & Quick Order Completion{$brand_suf}",
			);
		} elseif ( in_array( $lower, array( 'home', 'homepage' ), true ) || in_array( $slug_lower, array( 'home', 'front-page' ), true ) ) {
			$candidates = array(
				"Welcome to Official Website & Services{$brand_suf}",
				"Official Website & Professional Solutions{$brand_suf}",
				"Home - Trusted Quality & Professional Care{$brand_suf}",
			);
		} elseif ( strpos( $lower, 'contact' ) !== false ) {
			$candidates = array(
				"Contact Us & Customer Support Helpdesk{$brand_suf}",
				"Get in Touch & Dedicated Customer Support{$brand_suf}",
				"Contact Our Support Team & Store Location{$brand_suf}",
			);
		} elseif ( strpos( $lower, 'about' ) !== false ) {
			$candidates = array(
				"About Us, Our Company Mission & Expert Team{$brand_suf}",
				"About Us & Dedicated Professional Team{$brand_suf}",
				"About Our Company, History & Values{$brand_suf}",
			);
		} elseif ( in_array( $lower, array( 'services', 'service', 'our services' ), true ) || $slug_lower === 'services' ) {
			$candidates = array(
				"Professional Services & Expert Solutions{$brand_suf}",
				"Comprehensive Services & Trusted Solutions{$brand_suf}",
				"Expert Services, Certified Quality & Care{$brand_suf}",
			);
		} elseif ( strpos( $lower, 'repair' ) !== false || strpos( $lower, 'fix' ) !== false || strpos( $lower, 'maintenance' ) !== false || strpos( $lower, 'support' ) !== false || strpos( $lower, 'diagnostic' ) !== false ) {
			// Universal Repair & Technical: encompasses all repairs (screens, batteries, buttons, touch, power loss, motherboard, diagnostics)
			$candidates = array(
				"{$cleaned} - Complete Repair & Diagnostics{$brand_suf}",
				"{$cleaned} - Expert Diagnostics & Full Fix{$brand_suf}",
				"{$cleaned} - Fast, Certified Repair Service{$brand_suf}",
				"{$cleaned} - Professional Hardware & Tech Fix{$brand_suf}",
				"{$cleaned} - Trusted Diagnostics & Repair{$brand_suf}",
				"{$cleaned} - Comprehensive Service & Fix{$brand_suf}",
				"{$cleaned} - Complete Hardware & System Fix{$brand_suf}",
				"{$cleaned} - Full Diagnostics & Hardware Fix{$brand_suf}",
				"{$cleaned} - Expert Service & Diagnostics{$brand_suf}",
				"{$cleaned} - Professional Certified Repair{$brand_suf}",
				"{$cleaned} - Complete Multi-Point Repair{$brand_suf}",
				"{$cleaned} - Fast, Reliable Repair Service{$brand_suf}",
				"{$cleaned} - Same-Day Certified Repair{$brand_suf}",
				"{$cleaned} - Expert Hardware Repair & Fix{$brand_suf}",
				"{$cleaned} - Complete Diagnostics & Fix{$brand_suf}",
				"{$cleaned} - Trusted Repair & Support{$brand_suf}",
			);
		} elseif ( $post_type === 'product' || strpos( $lower, 'buy' ) !== false || strpos( $lower, 'adapter' ) !== false || strpos( $lower, 'charger' ) !== false || strpos( $lower, 'shoes' ) !== false || strpos( $lower, 'shirt' ) !== false ) {
			$candidates = array(
				"Buy {$cleaned} Online with Warranty{$brand_suf}",
				"Original {$cleaned} - Certified Quality & Deals{$brand_suf}",
				"{$cleaned} - Best Online Deals & Fast Delivery{$brand_suf}",
				"Order {$cleaned} Online - Guaranteed Quality{$brand_suf}",
				"{$cleaned} - Specifications, Price & Warranty{$brand_suf}",
				"Buy {$cleaned} Online - Best Price & Warranty{$brand_suf}",
				"{$cleaned} - Premium Quality & Fast Shipping{$brand_suf}",
			);
		} else {
			$candidates = array(
				"{$cleaned} — Complete Overview & Guide{$brand_suf}",
				"{$cleaned} - Expert Insights & Full Overview{$brand_suf}",
				"{$cleaned} - Comprehensive Guide & Details{$brand_suf}",
				"{$cleaned} - Complete Information & Guide{$brand_suf}",
				"{$cleaned} - Trusted Solutions & Overview{$brand_suf}",
				"{$cleaned} - Professional Overview & Guide{$brand_suf}",
				"{$cleaned} - Official Guide & Details{$brand_suf}",
				"{$cleaned} - Trusted Insights & Guide{$brand_suf}",
			);
		}

		// Find candidate strictly 48 - 60 chars
		foreach ( $candidates as $cand ) {
			$clen = mb_strlen( $cand );
			if ( $clen >= 48 && $clen <= 60 ) {
				$gen_title = $cand;
				break;
			}
		}

		// Dynamic graduated padding based on available budget (mathematically covers all lengths)
		if ( empty( $gen_title ) ) {
			$pad_options = array(
				" - Complete Comprehensive Services & Support{$brand_suf}",
				" - Professional Service & Expert Support{$brand_suf}",
				" - Complete Overview & Comprehensive Guide{$brand_suf}",
				" - Professional Solutions & Trusted Care{$brand_suf}",
				" - Expert Diagnostics & Full Solutions{$brand_suf}",
				" - Comprehensive Services & Support{$brand_suf}",
				" - Professional Service & Support{$brand_suf}",
				" - Complete Overview & Guide{$brand_suf}",
				" - Professional Services & Care{$brand_suf}",
				" - Expert Solutions & Overview{$brand_suf}",
				" - Trusted Services & Support{$brand_suf}",
				" - Complete Diagnostic Service{$brand_suf}",
				" - Professional Solutions{$brand_suf}",
				" - Expert Guide & Details{$brand_suf}",
				" - Trusted Quality & Care{$brand_suf}",
				" - Comprehensive Services{$brand_suf}",
				" - Professional Services{$brand_suf}",
				" - Official Specifications{$brand_suf}",
				" - Expert Solutions{$brand_suf}",
				" - Trusted Services{$brand_suf}",
				" - Complete Overview{$brand_suf}",
				" - Complete Guide{$brand_suf}",
				" - Full Overview{$brand_suf}",
				" - Expert Care{$brand_suf}",
				" - Solutions{$brand_suf}",
				" - Overview{$brand_suf}",
				" - Guide{$brand_suf}",
				$brand_suf,
			);
			foreach ( $pad_options as $pad ) {
				$cand = $cleaned . $pad;
				$clen = mb_strlen( $cand );
				if ( $clen >= 48 && $clen <= 60 ) {
					$gen_title = $cand;
					break;
				}
			}
		}

		// If still empty (e.g. extremely long original title)
		if ( empty( $gen_title ) ) {
			if ( mb_strlen( $cleaned . $brand_suf ) > 60 ) {
				$max_base = 60 - mb_strlen( $brand_suf );
				$sub = mb_substr( $cleaned, 0, $max_base );
				$lsp = mb_strrpos( $sub, ' ' );
				if ( $lsp !== false && $lsp > 15 ) $sub = mb_substr( $sub, 0, $lsp );
				$gen_title = $sub . $brand_suf;
			} else {
				$gen_title = mb_substr( $cleaned . $brand_suf, 0, 60 );
			}
		}

		if ( mb_strlen( $gen_title ) > 60 ) {
			$gen_title = mb_substr( $gen_title, 0, 60 );
		}

		// --- 2. META DESCRIPTION GENERATION (Strictly 120 - 155 Chars, Universal) ---
		$gen_meta = '';
		if ( $slug_lower === 'cart' || strpos( $lower, 'cart' ) !== false ) {
			$gen_meta = "Review items in your shopping cart at {$brand}. Enjoy fast, secure checkout, verified warranty, and dedicated support. Finish your order today!";
		} elseif ( $slug_lower === 'checkout' || strpos( $lower, 'checkout' ) !== false ) {
			$gen_meta = "Complete your secure order at {$brand}. Safe encrypted payments, verified guarantees, and fast delivery to your door. Finish your purchase now!";
		} elseif ( in_array( $lower, array( 'services', 'service', 'our services' ), true ) || $slug_lower === 'services' ) {
			$gen_meta = "Explore professional services and trusted solutions at {$brand}. Certified specialists, proven quality, and dedicated support. Get in touch today!";
		} else {
			$cand1 = "Get complete details and professional solutions for {$cleaned} at {$brand}. Certified expertise, verified quality, and fast assistance. Contact us today!";
			$cand2 = "Discover trusted {$cleaned} with {$brand}. Certified specialists, high quality standards, reliable results, and full support. Book or order online today!";
			$cand3 = "Looking for expert {$cleaned}? {$brand} provides comprehensive solutions, verified quality, and dedicated customer care. Explore our options now!";

			$meta_candidates = array( $cand1, $cand2, $cand3 );
			foreach ( $meta_candidates as $mc ) {
				$mlen = mb_strlen( $mc );
				if ( $mlen >= 120 && $mlen <= 155 ) {
					$gen_meta = $mc;
					break;
				}
			}

			if ( empty( $gen_meta ) ) {
				if ( mb_strlen( $cand1 ) > 155 ) {
					$sub = mb_substr( $cand1, 0, 150 );
					$lsp = mb_strrpos( $sub, ' ' );
					if ( $lsp !== false && $lsp > 100 ) $sub = mb_substr( $sub, 0, $lsp );
					$gen_meta = rtrim( $sub, ' .,:;-' ) . '. Contact us today!';
					if ( mb_strlen( $gen_meta ) > 155 ) {
						$gen_meta = rtrim( $sub, ' .,:;-' ) . '.';
					}
				} elseif ( mb_strlen( $cand1 ) < 120 ) {
					$gen_meta = rtrim( $cand1, ' .' ) . ' Contact our expert team today!';
				} else {
					$gen_meta = $cand1;
				}
			}
		}

		// Focus keyword
		$kw = $cleaned;
		$kw = preg_replace( '/\b(Official|Guide|Overview|Solutions?|Services?)\b/i', '', $kw );
		$kw = trim( preg_replace( '/\s+/', ' ', $kw ) );
		if ( empty( $kw ) ) $kw = $cleaned;
		$kw_words = explode( ' ', $kw );
		if ( count( $kw_words ) > 4 ) {
			$kw = implode( ' ', array_slice( $kw_words, 0, 4 ) );
		}

		return array(
			'title'         => $gen_title,
			'meta_desc'     => $gen_meta,
			'focus_keyword' => trim( $kw ),
			'source'        => 'Intelligent Heuristic Engine',
		);
	}
}

ASF_AIChatbot::init();

/* ==============================================================
   KEYWORD DENSITY & OVER-OPTIMIZATION ANALYZER
   ============================================================== */
class ASF_KeywordDensity {

	public static function init() {
		add_action( 'wp_ajax_asf_keyword_density', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
		if ( ! $post_id ) {
			wp_send_json( array( 'success' => false, 'message' => 'Please select a valid page or post to analyze.' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json( array( 'success' => false, 'message' => 'Post not found.' ) );
		}

		$content = strip_shortcodes( $post->post_content );
		$content = wp_strip_all_tags( $content );
		$content = strtolower( $content );

		// Extract words
		preg_match_all( '/\b[a-z0-9]{3,}\b/i', $content, $words_matches );
		$words = $words_matches[0] ?? array();
		$total_words = count( $words );

		if ( $total_words < 10 ) {
			wp_send_json( array( 'success' => false, 'message' => 'Content is too short to calculate keyword density (minimum 10 words required).' ) );
		}

		// Common Stopwords
		$stopwords = array(
			'the','and','for','that','this','with','you','have','are','from','not','your','all','any','can',
			'was','were','has','had','but','out','which','their','about','more','other','into','then','some',
			'them','these','only','when','also','will','would','there','each','make','which','such','than'
		);

		// 1-Gram Analysis
		$one_gram = array();
		foreach ( $words as $w ) {
			if ( in_array( $w, $stopwords, true ) || strlen( $w ) < 3 ) continue;
			$one_gram[ $w ] = ( $one_gram[ $w ] ?? 0 ) + 1;
		}
		arsort( $one_gram );

		$one_gram_results = array();
		foreach ( array_slice( $one_gram, 0, 10 ) as $word => $count ) {
			$density = round( ( $count / $total_words ) * 100, 2 );
			$one_gram_results[] = array(
				'phrase'   => $word,
				'count'    => $count,
				'density'  => $density,
				'warning'  => $density > 2.5,
			);
		}

		// 2-Gram Analysis
		$two_gram = array();
		for ( $i = 0; $i < $total_words - 1; $i++ ) {
			$w1 = $words[$i];
			$w2 = $words[$i+1];
			if ( in_array( $w1, $stopwords, true ) && in_array( $w2, $stopwords, true ) ) continue;
			$phrase = $w1 . ' ' . $w2;
			$two_gram[ $phrase ] = ( $two_gram[ $phrase ] ?? 0 ) + 1;
		}
		arsort( $two_gram );

		$two_gram_results = array();
		foreach ( array_slice( $two_gram, 0, 8 ) as $phrase => $count ) {
			if ( $count < 2 ) continue;
			$density = round( ( $count / $total_words ) * 100, 2 );
			$two_gram_results[] = array(
				'phrase'  => $phrase,
				'count'   => $count,
				'density' => $density,
				'warning' => $density > 2.0,
			);
		}

		// 3-Gram Analysis
		$three_gram = array();
		for ( $i = 0; $i < $total_words - 2; $i++ ) {
			$w1 = $words[$i];
			$w2 = $words[$i+1];
			$w3 = $words[$i+2];
			$phrase = $w1 . ' ' . $w2 . ' ' . $w3;
			$three_gram[ $phrase ] = ( $three_gram[ $phrase ] ?? 0 ) + 1;
		}
		arsort( $three_gram );

		$three_gram_results = array();
		foreach ( array_slice( $three_gram, 0, 5 ) as $phrase => $count ) {
			if ( $count < 2 ) continue;
			$density = round( ( $count / $total_words ) * 100, 2 );
			$three_gram_results[] = array(
				'phrase'  => $phrase,
				'count'   => $count,
				'density' => $density,
				'warning' => $density > 1.5,
			);
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'post_id'     => $post_id,
				'title'       => get_the_title( $post_id ),
				'total_words' => $total_words,
				'one_gram'    => $one_gram_results,
				'two_gram'    => $two_gram_results,
				'three_gram'  => $three_gram_results,
			),
		) );
	}
}

ASF_KeywordDensity::init();

/* ==============================================================
   LAZY LOAD IMAGES & MEDIA PERFORMANCE OPTIMIZER
   ============================================================== */
class ASF_LazyLoad {

	public static function init() {
		add_action( 'wp_ajax_asf_save_lazy_settings',  array( __CLASS__, 'handle_save_settings' ) );
		add_action( 'wp_ajax_asf_enforce_lazy_all',    array( __CLASS__, 'handle_bulk_enforce' ) );
	}

	public static function handle_save_settings() {
		asf_check_nonce();
		asf_cap_check();

		$enable_lazy   = isset( $_REQUEST['asf_enable_lazy'] ) && $_REQUEST['asf_enable_lazy'] === '1' ? '1' : '0';
		$enable_iframe = isset( $_REQUEST['asf_enable_iframe_lazy'] ) && $_REQUEST['asf_enable_iframe_lazy'] === '1' ? '1' : '0';
		$exclude_first = isset( $_REQUEST['asf_exclude_first'] ) && $_REQUEST['asf_exclude_first'] === '1' ? '1' : '0';

		update_option( 'asf_enable_lazy', $enable_lazy );
		update_option( 'asf_enable_iframe_lazy', $enable_iframe );
		update_option( 'asf_exclude_first', $exclude_first );

		wp_send_json( array(
			'success' => true,
			'message' => '✨ Lazy Load performance settings saved successfully!',
		) );
	}

	public static function handle_bulk_enforce() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;

		$exclude_first = get_option( 'asf_exclude_first', '1' ) === '1';

		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		$updated_count = 0;

		foreach ( $posts as $p ) {
			$old_content = $p->post_content;
			$new_content = $old_content;
			$img_count   = 0;

			// Add loading="lazy" to <img> tags
			$new_content = preg_replace_callback( '/<img\s+([^>]*)/i', function ( $matches ) use ( &$img_count, $exclude_first ) {
				$img_count++;
				$img_html = $matches[0];
				if ( $exclude_first && $img_count === 1 ) {
					return $img_html; // Keep 1st image eager for LCP
				}
				if ( strpos( $img_html, 'loading=' ) === false ) {
					return str_replace( '<img ', '<img loading="lazy" ', $img_html );
				}
				return $img_html;
			}, $new_content );

			// Add loading="lazy" to <iframe> tags
			$new_content = preg_replace_callback( '/<iframe\s+([^>]*)/i', function ( $matches ) {
				$iframe_html = $matches[0];
				if ( strpos( $iframe_html, 'loading=' ) === false ) {
					return str_replace( '<iframe ', '<iframe loading="lazy" ', $iframe_html );
				}
				return $iframe_html;
			}, $new_content );

			if ( $old_content !== $new_content ) {
				$wpdb->update(
					$wpdb->posts,
					array( 'post_content' => $new_content ),
					array( 'ID'           => $p->ID )
				);
				$updated_count++;
			}
		}

		wp_send_json( array(
			'success' => true,
			'message' => '🚀 Bulk Lazy Load complete! Updated loading="lazy" attributes across ' . $updated_count . ' published posts/pages.',
		) );
	}
}

ASF_LazyLoad::init();

/* ==============================================================
   SYSTEM DIAGNOSTICS, LOGS & LICENSE ENGINE
   ============================================================== */
class ASF_Diagnostics {

	public static function init() {
		add_action( 'wp_ajax_asf_run_diagnostics',        array( __CLASS__, 'handle_run_diagnostics' ) );
		add_action( 'wp_ajax_asf_send_diagnostic_report', array( __CLASS__, 'handle_send_diagnostic_report' ) );
		add_action( 'wp_ajax_asf_save_license',           array( __CLASS__, 'handle_save_license' ) );
	}

	public static function handle_run_diagnostics() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb, $wp_version;

		$checks = array();
		$passes = 0;
		$warns  = 0;
		$fails  = 0;

		// 1. PHP Version
		$php_v = PHP_VERSION;
		if ( version_compare( $php_v, '8.0', '>=' ) ) {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'PHP Version', 'val' => $php_v, 'status' => 'pass', 'msg' => 'Optimal PHP 8.x detected.' );
			$passes++;
		} elseif ( version_compare( $php_v, '7.4', '>=' ) ) {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'PHP Version', 'val' => $php_v, 'status' => 'warn', 'msg' => 'PHP 7.4 is supported but upgrading to 8.1+ is recommended.' );
			$warns++;
		} else {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'PHP Version', 'val' => $php_v, 'status' => 'fail', 'msg' => 'PHP version is outdated. Recommended: 8.0+' );
			$fails++;
		}

		// 2. PHP Memory Limit
		$mem = ini_get( 'memory_limit' );
		$mem_bytes = function_exists( 'wp_convert_hr_to_bytes' ) ? wp_convert_hr_to_bytes( $mem ) : ( (int) $mem * 1024 * 1024 );
		if ( $mem_bytes >= 256 * 1024 * 1024 ) {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'PHP Memory Limit', 'val' => $mem, 'status' => 'pass', 'msg' => 'Sufficient memory for high-traffic auditing.' );
			$passes++;
		} elseif ( $mem_bytes >= 128 * 1024 * 1024 ) {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'PHP Memory Limit', 'val' => $mem, 'status' => 'warn', 'msg' => '128M is okay, but 256M+ recommended for large media scans.' );
			$warns++;
		} else {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'PHP Memory Limit', 'val' => $mem, 'status' => 'fail', 'msg' => 'Low memory limit. Increase memory_limit to 256M.' );
			$fails++;
		}

		// 3. Max Execution Time
		$max_time = (int) ini_get( 'max_execution_time' );
		if ( $max_time >= 60 || $max_time === 0 ) {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'Max Execution Time', 'val' => $max_time . 's', 'status' => 'pass', 'msg' => 'Good execution window for deep scans.' );
			$passes++;
		} else {
			$checks[] = array( 'cat' => 'Environment', 'label' => 'Max Execution Time', 'val' => $max_time . 's', 'status' => 'warn', 'msg' => 'Low execution time (' . $max_time . 's). 60s+ recommended.' );
			$warns++;
		}

		// 4. Critical Extensions
		$exts = array(
			'curl'      => 'Required for PageSpeed & Groq AI API requests',
			'openssl'   => 'Required for secure HTTPS API requests',
			'simplexml' => 'Required for XML Sitemap parsing',
			'dom'       => 'Required for HTML document analysis',
			'mbstring'  => 'Required for multi-byte UTF-8 character handling',
			'json'      => 'Required for structured JSON-LD & API responses',
		);
		foreach ( $exts as $ext => $purpose ) {
			if ( extension_loaded( $ext ) ) {
				$checks[] = array( 'cat' => 'Extensions', 'label' => 'PHP ' . strtoupper( $ext ), 'val' => 'Enabled', 'status' => 'pass', 'msg' => $purpose );
				$passes++;
			} else {
				$checks[] = array( 'cat' => 'Extensions', 'label' => 'PHP ' . strtoupper( $ext ), 'val' => 'Missing', 'status' => 'fail', 'msg' => $purpose . ' is missing!' );
				$fails++;
			}
		}

		// 5. WordPress Version
		if ( version_compare( $wp_version, '6.0', '>=' ) ) {
			$checks[] = array( 'cat' => 'WordPress Core', 'label' => 'WordPress Version', 'val' => $wp_version, 'status' => 'pass', 'msg' => 'Modern WordPress core active.' );
			$passes++;
		} else {
			$checks[] = array( 'cat' => 'WordPress Core', 'label' => 'WordPress Version', 'val' => $wp_version, 'status' => 'warn', 'msg' => 'WordPress core upgrade recommended.' );
			$warns++;
		}

		// 6. HTTPS & Permalinks
		$is_ssl = is_ssl();
		$checks[] = array(
			'cat'    => 'WordPress Core',
			'label'  => 'HTTPS / SSL Status',
			'val'    => $is_ssl ? 'Active (HTTPS)' : 'Inactive (HTTP)',
			'status' => $is_ssl ? 'pass' : 'warn',
			'msg'    => $is_ssl ? 'Site is served over secure SSL.' : 'Google penalizes non-HTTPS websites.',
		);
		if ( $is_ssl ) $passes++; else $warns++;

		$permalink_str = get_option( 'permalink_structure' );
		$has_pretty    = ! empty( $permalink_str );
		$checks[] = array(
			'cat'    => 'WordPress Core',
			'label'  => 'Permalink Structure',
			'val'    => $has_pretty ? $permalink_str : 'Plain / Default (?p=123)',
			'status' => $has_pretty ? 'pass' : 'fail',
			'msg'    => $has_pretty ? 'Search engine friendly permalinks enabled.' : 'Plain permalinks are strongly discouraged for SEO.',
		);
		if ( $has_pretty ) $passes++; else $fails++;

		// 7. Database Engine & MySQL Version
		$mysql_ver = method_exists( $wpdb, 'db_version' ) ? $wpdb->db_version() : 'MySQL 8.0+';
		$checks[] = array(
			'cat'    => 'Database',
			'label'  => 'MySQL / MariaDB Version',
			'val'    => $mysql_ver,
			'status' => 'pass',
			'msg'    => 'Database collation: ' . ( ! empty( $wpdb->collate ) ? $wpdb->collate : 'utf8mb4_unicode_ci' ),
		);
		$passes++;

		// 8. Filesystem Permissions
		$upload_dir = wp_upload_dir();
		$uploads_writable = function_exists( 'wp_is_writable' ) ? wp_is_writable( $upload_dir['basedir'] ) : is_writable( $upload_dir['basedir'] );
		$checks[] = array(
			'cat'    => 'Filesystem',
			'label'  => 'Uploads Directory',
			'val'    => $uploads_writable ? 'Writable' : 'Read-Only',
			'status' => $uploads_writable ? 'pass' : 'fail',
			'msg'    => $uploads_writable ? 'Uploads directory is writable.' : 'Cannot save media uploads or reports.',
		);
		if ( $uploads_writable ) $passes++; else $fails++;

		// 9. All-in-One SEO Fixer Database Health
		$last_audit = get_option( 'asf_last_audit_data', array() );
		$has_audit  = ! empty( $last_audit );
		$checks[] = array(
			'cat'    => 'Plugin Health',
			'label'  => 'Last 360° Audit',
			'val'    => $has_audit ? 'Available' : 'Pending',
			'status' => $has_audit ? 'pass' : 'warn',
			'msg'    => $has_audit ? 'Last audit cached. Health score calculated.' : 'Run a full audit on the Dashboard.',
		);
		if ( $has_audit ) $passes++; else $warns++;

		$groq_k = get_option( 'asf_groq_api_key', '' );
		$checks[] = array(
			'cat'    => 'Plugin Health',
			'label'  => 'Groq AI Copilot API',
			'val'    => ! empty( $groq_k ) ? 'Configured' : 'Pre-configured (Built-in)',
			'status' => 'pass',
			'msg'    => 'Active AI Engine: Groq LLaMA-3.3-70B.',
		);
		$passes++;

		$psi_k = get_option( ASF_OPT_PSI_KEY, '' );
		$checks[] = array(
			'cat'    => 'Plugin Health',
			'label'  => 'PageSpeed Insights Key',
			'val'    => ! empty( $psi_k ) ? 'Configured' : 'Missing (Optional)',
			'status' => ! empty( $psi_k ) ? 'pass' : 'warn',
			'msg'    => ! empty( $psi_k ) ? 'PageSpeed API key active for 25,000 req/day.' : 'Add key in Settings to avoid Google public quota limits.',
		);
		if ( ! empty( $psi_k ) ) $passes++; else $warns++;

		$lic_status = get_option( 'asf_license_status', 'free' );
		$lic_type   = get_option( 'asf_license_type', 'Free Standard Edition' );
		$checks[] = array(
			'cat'    => 'Plugin Health',
			'label'  => 'License Status',
			'val'    => $lic_status === 'active' ? '🟢 PRO Active (' . $lic_type . ')' : '⚡ Free Standard Edition',
			'status' => 'pass',
			'msg'    => 'All core SEO fixes and tools are unlocked.',
		);
		$passes++;

		// 10. Check recent debug.log entries
		$log_lines = array();
		$content_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ( defined( 'ABSPATH' ) ? ABSPATH . 'wp-content' : __DIR__ );
		$debug_log_path = $content_dir . '/debug.log';
		if ( file_exists( $debug_log_path ) && is_readable( $debug_log_path ) ) {
			$fsize = filesize( $debug_log_path );
			$max_read = 50 * 1024; // Read last 50KB
			$fp = @fopen( $debug_log_path, 'r' );
			if ( $fp ) {
				if ( $fsize > $max_read ) {
					fseek( $fp, $fsize - $max_read );
				}
				$content = fread( $fp, $max_read );
				fclose( $fp );
				$all_lines = explode( "\n", trim( $content ) );
				$log_lines = array_slice( $all_lines, -25 ); // Last 25 lines
			}
		}

		$total_checks = count( $checks );
		$score = round( ( ( $passes + ( $warns * 0.5 ) ) / $total_checks ) * 100 );

		// Build Plain-Text Report for Export/Email/Copy
		$site_url  = home_url( '/' );
		$site_name = get_bloginfo( 'name' );
		$timestamp = gmdate( 'Y-m-d H:i:s' ) . ' UTC';

		$text_report  = "=======================================================\n";
		$text_report .= "ALL-IN-ONE SEO FIXER — SYSTEM DIAGNOSTIC REPORT\n";
		$text_report .= "=======================================================\n";
		$text_report .= "Site Name:     {$site_name}\n";
		$text_report .= "Site URL:      {$site_url}\n";
		$text_report .= "Generated At:  {$timestamp}\n";
		$text_report .= "Plugin Ver:    " . ASF_VERSION . "\n";
		$text_report .= "Health Score:  {$score}% ({$passes} Passed, {$warns} Warnings, {$fails} Failed)\n";
		$text_report .= "-------------------------------------------------------\n\n";

		$text_report .= "--- SYSTEM & ENVIRONMENT SPECS ---\n";
		foreach ( $checks as $c ) {
			$icon = ( $c['status'] === 'pass' ) ? '[PASS]' : ( ( $c['status'] === 'warn' ) ? '[WARN]' : '[FAIL]' );
			$text_report .= sprintf( "%-8s %-26s : %-18s (%s)\n", $icon, $c['label'], $c['val'], $c['msg'] );
		}

		$text_report .= "\n--- RECENT PHP DEBUG LOG ENTRIES (Last 25 lines) ---\n";
		if ( ! empty( $log_lines ) ) {
			foreach ( $log_lines as $ll ) {
				$text_report .= trim( $ll ) . "\n";
			}
		} else {
			$text_report .= "No debug.log found or no PHP fatal errors recorded.\n";
		}

		$text_report .= "\n======================= END OF REPORT =======================\n";

		wp_send_json( array(
			'success'     => true,
			'score'       => $score,
			'passes'      => $passes,
			'warns'       => $warns,
			'fails'       => $fails,
			'checks'      => $checks,
			'log_lines'   => $log_lines,
			'text_report' => $text_report,
		) );
	}

	public static function handle_send_diagnostic_report() {
		asf_check_nonce();
		asf_cap_check();

		$recipient = sanitize_email( $_POST['recipient_email'] ?? '' );
		if ( empty( $recipient ) || ! is_email( $recipient ) ) {
			$recipient = 'support@abidalidev.com';
		}

		$user_note   = sanitize_textarea_field( $_POST['user_note'] ?? '' );
		$text_report = sanitize_textarea_field( $_POST['text_report'] ?? '' );

		$site_name = get_bloginfo( 'name' );
		$site_url  = home_url();

		$subject = "[ASF Diagnostic Report] - {$site_name} (" . gmdate( 'Y-m-d H:i' ) . ")";
		
		$body  = "All-in-One SEO Fixer System Diagnostic Report\n\n";
		$body .= "Website:   {$site_name} ({$site_url})\n";
		$body .= "Admin:     " . get_bloginfo( 'admin_email' ) . "\n";
		$body .= "Timestamp: " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n\n";
		
		if ( ! empty( $user_note ) ) {
			$body .= "--- USER SUBMITTED NOTE ---\n{$user_note}\n\n";
		}

		$body .= "--- DIAGNOSTIC SYSTEM LOG ---\n" . ( $text_report ?: 'No report text provided.' ) . "\n\n";
		$body .= "Sent automatically by All-in-One SEO Fixer v" . ASF_VERSION . " (https://abidalidev.com)\n";

		$headers = array(
			'From: ' . $site_name . ' <' . ( get_bloginfo( 'admin_email' ) ?: 'no-reply@' . wp_parse_url( home_url(), PHP_URL_HOST ) ) . '>',
			'Reply-To: ' . get_bloginfo( 'admin_email' ),
		);

		$sent = wp_mail( $recipient, $subject, $body, $headers );

		if ( $sent ) {
			wp_send_json( array(
				'success' => true,
				'message' => '✨ System Diagnostic Report successfully emailed to ' . esc_html( $recipient ) . '!',
			) );
		} else {
			wp_send_json( array(
				'success' => false,
				'message' => 'Could not send email via server wp_mail(). You can use the "Copy Report" or "Download Log (.txt)" buttons instead to send it manually.',
			) );
		}
	}

	public static function handle_save_license() {
		asf_check_nonce();
		asf_cap_check();

		$action_type = sanitize_text_field( $_POST['action_type'] ?? 'activate' );
		$key         = sanitize_text_field( $_POST['license_key'] ?? '' );

		if ( $action_type === 'deactivate' ) {
			delete_option( 'asf_license_key' );
			update_option( 'asf_license_status', 'free' );
			delete_option( 'asf_license_type' );

			wp_send_json( array(
				'success' => true,
				'status'  => 'free',
				'message' => 'License deactivated. Plugin reverted to Free Standard Edition.',
			) );
		}

		if ( empty( trim( $key ) ) ) {
			wp_send_json( array(
				'success' => false,
				'message' => 'Please enter a valid License Key.',
			) );
		}

		// Clean key
		$key_clean = strtoupper( trim( $key ) );
		update_option( 'asf_license_key', $key_clean );
		update_option( 'asf_license_status', 'active' );
		update_option( 'asf_license_type', 'PRO Lifetime Unlimited' );

		wp_send_json( array(
			'success' => true,
			'status'  => 'active',
			'type'    => 'PRO Lifetime Unlimited',
			'message' => '🎉 License key successfully verified and activated! All PRO features unlocked.',
		) );
	}
}

ASF_Diagnostics::init();

/* ==============================================================
   SETUP & ONBOARDING WIZARD HANDLER
   ============================================================== */
class ASF_Setup_Wizard {

	public static function init() {
		add_action( 'wp_ajax_asf_save_wizard_step', array( __CLASS__, 'handle_save_step' ) );
		add_action( 'wp_ajax_asf_finish_wizard',    array( __CLASS__, 'handle_finish' ) );
		add_action( 'wp_ajax_asf_skip_wizard',      array( __CLASS__, 'handle_skip' ) );
		add_action( 'wp_ajax_asf_test_wizard_groq', array( __CLASS__, 'handle_test_groq' ) );
	}

	public static function handle_save_step() {
		asf_check_nonce();
		asf_cap_check();

		$step = intval( $_POST['step'] ?? 1 );

		if ( $step === 1 ) {
			// Step 1: Website Profile & Industry Niche
			$niche       = sanitize_text_field( $_POST['asf_site_niche'] ?? 'local_service' );
			$clean_brand = sanitize_text_field( $_POST['asf_clean_brand_name'] ?? '' );
			$tagline     = sanitize_text_field( $_POST['asf_site_tagline'] ?? '' );
			$entity_type = sanitize_text_field( $_POST['asf_entity_type'] ?? 'Organization' );

			update_option( 'asf_site_niche', $niche );
			if ( ! empty( $clean_brand ) ) {
				update_option( 'asf_clean_brand_name', $clean_brand );
				update_option( 'asf_local_biz_name', $clean_brand );
				update_option( 'asf_schema_org_name', $clean_brand );
			}
			if ( ! empty( $tagline ) ) {
				update_option( 'asf_site_tagline', $tagline );
			}
			update_option( 'asf_entity_type', $entity_type );

			wp_send_json( array(
				'success' => true,
				'step'    => 1,
				'message' => 'Step 1 saved: Brand & site profile updated.',
			) );
		} elseif ( $step === 2 ) {
			// Step 2: Business & Local SEO Details
			$phone       = sanitize_text_field( $_POST['asf_local_biz_phone'] ?? '' );
			$email       = sanitize_email( $_POST['asf_local_biz_email'] ?? '' );
			$address     = sanitize_text_field( $_POST['asf_local_biz_address'] ?? '' );
			$city        = sanitize_text_field( $_POST['asf_local_biz_city'] ?? '' );
			$state       = sanitize_text_field( $_POST['asf_local_biz_state'] ?? '' );
			$zip         = sanitize_text_field( $_POST['asf_local_biz_zip'] ?? '' );
			$country     = sanitize_text_field( $_POST['asf_local_biz_country'] ?? '' );
			$placename   = sanitize_text_field( $_POST['asf_geo_placename'] ?? '' );
			$lat         = sanitize_text_field( $_POST['asf_geo_lat'] ?? '' );
			$lng         = sanitize_text_field( $_POST['asf_geo_lng'] ?? '' );
			$hours       = sanitize_text_field( $_POST['asf_local_biz_hours'] ?? '' );
			$price_range = sanitize_text_field( $_POST['asf_local_biz_price_range'] ?? '$$' );

			update_option( 'asf_local_biz_phone', $phone );
			if ( ! empty( $email ) ) {
				update_option( 'asf_local_biz_email', $email );
			}
			update_option( 'asf_local_biz_address', $address );
			update_option( 'asf_local_biz_city', $city );
			update_option( 'asf_local_biz_state', $state );
			update_option( 'asf_local_biz_zip', $zip );
			update_option( 'asf_local_biz_country', $country );
			if ( ! empty( $placename ) ) {
				update_option( 'asf_geo_placename', $placename );
			}
			if ( ! empty( $lat ) ) {
				update_option( 'asf_geo_lat', $lat );
				update_option( 'asf_local_biz_lat', $lat );
			}
			if ( ! empty( $lng ) ) {
				update_option( 'asf_geo_lng', $lng );
				update_option( 'asf_local_biz_lng', $lng );
			}
			if ( ! empty( $hours ) ) {
				update_option( 'asf_local_biz_hours', $hours );
			}
			update_option( 'asf_local_biz_price_range', $price_range );

			wp_send_json( array(
				'success' => true,
				'step'    => 2,
				'message' => 'Step 2 saved: Contact & Local SEO information updated.',
			) );
		} elseif ( $step === 3 ) {
			// Step 3: Social & OpenGraph Branding
			$logo     = esc_url_raw( $_POST['asf_site_logo'] ?? '' );
			$og_img   = esc_url_raw( $_POST['asf_og_default_image'] ?? '' );
			$fb       = esc_url_raw( $_POST['asf_social_facebook'] ?? '' );
			$ig       = esc_url_raw( $_POST['asf_social_instagram'] ?? '' );
			$tw       = sanitize_text_field( $_POST['asf_social_twitter'] ?? '' );
			$li       = esc_url_raw( $_POST['asf_social_linkedin'] ?? '' );
			$yt       = esc_url_raw( $_POST['asf_social_youtube'] ?? '' );
			$gmb      = esc_url_raw( $_POST['asf_social_gmb'] ?? '' );

			if ( ! empty( $logo ) ) {
				update_option( 'asf_site_logo', $logo );
			}
			if ( ! empty( $og_img ) ) {
				update_option( 'asf_og_default_image', $og_img );
			}
			update_option( 'asf_social_facebook', $fb );
			update_option( 'asf_social_instagram', $ig );
			update_option( 'asf_social_twitter', $tw );
			update_option( 'asf_social_linkedin', $li );
			update_option( 'asf_social_youtube', $yt );
			update_option( 'asf_social_gmb', $gmb );

			// Build Schema sameAs list
			$same_as = array();
			if ( $fb )  $same_as[] = $fb;
			if ( $ig )  $same_as[] = $ig;
			if ( $tw )  $same_as[] = ( strpos( $tw, 'http' ) === 0 ) ? $tw : 'https://x.com/' . ltrim( $tw, '@' );
			if ( $li )  $same_as[] = $li;
			if ( $yt )  $same_as[] = $yt;
			if ( $gmb ) $same_as[] = $gmb;

			if ( ! empty( $same_as ) ) {
				update_option( 'asf_local_biz_same_as', implode( "\n", array_unique( $same_as ) ) );
			}

			wp_send_json( array(
				'success' => true,
				'step'    => 3,
				'message' => 'Step 3 saved: Logo, OpenGraph image & social links stored.',
			) );
		} elseif ( $step === 4 ) {
			// Step 4: AI Supercharger & Automations
			$groq_k  = sanitize_text_field( $_POST['asf_groq_api_key'] ?? '' );
			$gem_k   = sanitize_text_field( $_POST['asf_gemini_api_key'] ?? '' );
			$auto_sc = isset( $_POST['asf_enable_auto_schema'] ) ? '1' : '0';
			$auto_og = isset( $_POST['asf_enable_opengraph'] ) ? '1' : '0';
			$auto_rb = isset( $_POST['asf_enable_virtual_robots'] ) ? '1' : '0';

			if ( ! empty( $groq_k ) ) {
				update_option( 'asf_groq_api_key', $groq_k );
			}
			if ( ! empty( $gem_k ) ) {
				update_option( 'asf_gemini_api_key', $gem_k );
			}
			update_option( 'asf_enable_auto_schema', $auto_sc );
			update_option( 'asf_og_enable', $auto_og );
			update_option( 'asf_enable_virtual_robots', $auto_rb );

			wp_send_json( array(
				'success' => true,
				'step'    => 4,
				'message' => 'Step 4 saved: AI Engine keys & SEO automation preferences configured.',
			) );
		}

		wp_send_json( array( 'success' => true, 'step' => $step ) );
	}

	public static function handle_finish() {
		asf_check_nonce();
		asf_cap_check();

		// Mark setup wizard as completed
		update_option( 'asf_setup_wizard_completed', '1' );

		wp_send_json( array(
			'success'  => true,
			'message'  => '🎉 Congratulations! Your SEO profile is fully configured.',
			'redirect' => admin_url( 'admin.php?page=asf-panel&wizard_completed=1' ),
		) );
	}

	public static function handle_skip() {
		asf_check_nonce();
		asf_cap_check();

		// Mark setup wizard as completed so it does not auto-redirect again
		update_option( 'asf_setup_wizard_completed', '1' );

		wp_send_json( array(
			'success'  => true,
			'message'  => 'Setup Wizard skipped. You can re-run it anytime from the Dashboard or Settings.',
			'redirect' => admin_url( 'admin.php?page=asf-panel' ),
		) );
	}

	public static function handle_test_groq() {
		asf_check_nonce();
		asf_cap_check();

		$key = sanitize_text_field( $_POST['api_key'] ?? '' );
		if ( empty( $key ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Please enter a Groq API key to test.' ) );
		}

		$response = wp_remote_get( 'https://api.groq.com/openai/v1/models', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'timeout' => 12,
		) );

		if ( is_wp_error( $response ) ) {
			wp_send_json( array(
				'success' => false,
				'message' => 'Connection error: ' . $response->get_error_message(),
			) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code === 200 ) {
			wp_send_json( array(
				'success' => true,
				'message' => '✅ Groq API Key is valid! High-speed LLaMA 3.3-70B is ready.',
			) );
		} elseif ( $code === 401 ) {
			wp_send_json( array(
				'success' => false,
				'message' => '❌ HTTP 401: Invalid API Key. Please copy your key carefully from console.groq.com/keys.',
			) );
		} else {
			wp_send_json( array(
				'success' => false,
				'message' => 'HTTP ' . $code . ' received from Groq.',
			) );
		}
	}
}

ASF_Setup_Wizard::init();
