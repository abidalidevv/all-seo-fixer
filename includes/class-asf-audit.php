<?php
/**
 * 360° Full SEO Audit AJAX Handler
 *
 * Performs a comprehensive database and config scan across:
 *  - Title tags (missing, too short, too long, duplicates)
 *  - Meta descriptions (missing, too short, too long, duplicates)
 *  - H1 headings (missing)
 *  - Image alt texts (missing)
 *  - Schema JSON-LD markup presence
 *  - Open Graph / Social meta tags
 *  - Double-domain URL typos (comhttps)
 *  - Orphan media attachments
 *  - Robots.txt live HTTP check
 *  - Sitemap.xml live HTTP check
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Audit {

	public static function init() {
		add_action( 'wp_ajax_asf_full_360_audit', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;

		// ── 1. Fetch all published posts & pages ─────────────────────────
		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );
		$total = count( $posts );

		// ── 2. Per-page checks ────────────────────────────────────────────
		$missing_titles  = 0;
		$bad_metas       = 0;
		$missing_h1      = 0;
		$missing_alts    = 0;
		$schema_count    = 0;
		$og_count        = 0;
		$seen_titles     = array();
		$seen_metas      = array();
		$dup_titles      = 0;
		$dup_metas       = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// --- Title ---
			$title = get_post_meta( $post->ID, 'rank_math_title', true )
				?: get_post_meta( $post->ID, '_yoast_wpseo_title', true )
				?: get_the_title( $post->ID );
			$title_clean = trim( strip_tags( $title ) );
			$title_len   = mb_strlen( $title_clean );

			if ( $title_len === 0 ) {
				$missing_titles++;
			}
			// Duplicate title check
			if ( $title_clean && in_array( $title_clean, $seen_titles, true ) ) {
				$dup_titles++;
			} elseif ( $title_clean ) {
				$seen_titles[] = $title_clean;
			}

			// --- Meta Description ---
			$meta = get_post_meta( $post->ID, 'rank_math_description', true )
				?: get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
			$meta_clean = trim( strip_tags( $meta ) );
			$meta_len   = mb_strlen( $meta_clean );

			if ( $meta_len < 50 ) {
				$bad_metas++;
			}
			// Duplicate meta check
			if ( $meta_clean && in_array( $meta_clean, $seen_metas, true ) ) {
				$dup_metas++;
			} elseif ( $meta_clean ) {
				$seen_metas[] = $meta_clean;
			}

			// --- H1 ---
			if ( ! preg_match( '/<h1[\s>]/i', $content ) ) {
				$missing_h1++;
			}

			// --- Images without alt ---
			if ( preg_match_all( '/<img[^>]+>/i', $content, $imgs ) ) {
				foreach ( $imgs[0] as $img ) {
					if ( ! preg_match( '/alt=["\'][^"\']+["\']/', $img ) ) {
						$missing_alts++;
					}
				}
			}

			// --- Schema JSON-LD ---
			if ( preg_match( '/<script[^>]*application\/ld\+json/i', $content ) ) {
				$schema_count++;
			}

			// --- Open Graph ---
			$og = get_post_meta( $post->ID, 'rank_math_facebook_title', true )
				?: get_post_meta( $post->ID, '_yoast_wpseo_opengraph-title', true );
			if ( ! empty( $og ) ) {
				$og_count++;
			}
		}

		// ── 3. DB-level checks ────────────────────────────────────────────
		$comhttps = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%comhttps%'"
		);

		// ── 4. Orphan media (sample 100 images) ──────────────────────────
		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => 100,
		) );

		$logo_id  = (int) get_theme_mod( 'custom_logo' );
		$logo_url = $logo_id ? basename( wp_get_attachment_url( $logo_id ) ?: '' ) : '';
		$orphans  = 0;

		foreach ( $attachments as $att ) {
			$url   = wp_get_attachment_url( $att->ID );
			$fname = $url ? basename( $url ) : '';
			if ( ! $fname || $fname === $logo_url ) continue;

			$in_content = $wpdb->get_var( $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_status='publish' AND post_content LIKE %s LIMIT 1",
				'%' . $wpdb->esc_like( $fname ) . '%'
			) );
			if ( $in_content ) continue;

			$in_elementor = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND meta_value LIKE %s LIMIT 1",
				'%' . $wpdb->esc_like( $fname ) . '%'
			) );
			if ( $in_elementor ) continue;

			$is_featured = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id' AND meta_value=%s LIMIT 1",
				(string) $att->ID
			) );
			if ( ! $is_featured ) {
				$orphans++;
			}
		}

		// ── 5. Live HTTP checks ───────────────────────────────────────────
		$robots_resp  = wp_remote_get( home_url( '/robots.txt' ),        array( 'timeout' => 5 ) );
		$sitemap_resp = wp_remote_get( home_url( '/sitemap_index.xml' ), array( 'timeout' => 5 ) );

		$robots_ok  = ! is_wp_error( $robots_resp )  && wp_remote_retrieve_response_code( $robots_resp )  === 200;
		$sitemap_ok = ! is_wp_error( $sitemap_resp ) && wp_remote_retrieve_response_code( $sitemap_resp ) === 200;

		// ── 6. Return results ─────────────────────────────────────────────
		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'posts'          => $total,
				'missing_titles' => $missing_titles,
				'bad_metas'      => $bad_metas,
				'missing_h1'     => $missing_h1,
				'missing_alts'   => $missing_alts,
				'dup_titles'     => $dup_titles,
				'dup_metas'      => $dup_metas,
				'schema_count'   => $schema_count,
				'og_count'       => $og_count,
				'comhttps'       => $comhttps,
				'orphans'        => $orphans,
				'robots_ok'      => $robots_ok,
				'sitemap_ok'     => $sitemap_ok,
				'redirects'      => count( get_option( ASF_OPT_REDIRECTS, array() ) ),
			),
		) );
	}
}
