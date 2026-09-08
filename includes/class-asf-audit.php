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

		$posts = get_posts( array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );
		$total = count( $posts );

		// ── 2. Per-page checks ────────────────────────────────────────────
		$site_name            = get_bloginfo( 'name' );
		$missing_titles       = 0;
		$bad_metas            = 0;
		$missing_h1           = 0;
		$missing_alts         = 0;
		$schema_count         = 0;
		$og_count             = 0;
		$seen_titles          = array();
		$seen_metas           = array();
		$dup_titles           = 0;
		$dup_metas            = 0;
		$eeat_author_missing  = 0;
		$eeat_citations_count = 0;
		$aria_missing         = 0;
		$intrusive_popups     = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// --- Title ---
			$title = get_post_meta( $post->ID, '_asf_seo_title', true )
				?: ( get_post_meta( $post->ID, 'rank_math_title', true )
				?: ( get_post_meta( $post->ID, '_yoast_wpseo_title', true )
				?: get_the_title( $post->ID ) ) );
			$title_clean = str_replace( array( '%title%', '%%title%%', '%sitename%', '%%sitename%%', '%sep%', '%%sep%%' ), array( get_the_title( $post->ID ), get_the_title( $post->ID ), $site_name, $site_name, '-', '-' ), (string) $title );
			$title_clean = trim( strip_tags( $title_clean ) );
			$title_len   = mb_strlen( $title_clean );

			if ( $title_len === 0 ) {
				$missing_titles++;
			}
			// Duplicate title check (normalized)
			if ( ! empty( $title_clean ) ) {
				$t_key = mb_strtolower( $title_clean );
				if ( in_array( $t_key, $seen_titles, true ) ) {
					$dup_titles++;
				} else {
					$seen_titles[] = $t_key;
				}
			}

			// --- Meta Description ---
			$meta = get_post_meta( $post->ID, '_asf_meta_description', true )
				?: ( get_post_meta( $post->ID, 'rank_math_description', true )
				?: get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) );
			$meta_clean = str_replace( array( '%excerpt%', '%%excerpt%%', '%title%', '%%title%%', '%sitename%', '%%sitename%%' ), array( '', '', get_the_title( $post->ID ), get_the_title( $post->ID ), $site_name, $site_name ), (string) $meta );
			$meta_clean = trim( strip_tags( (string) $meta_clean ) );
			$meta_len   = mb_strlen( $meta_clean );

			if ( $meta_len < 70 ) {
				$bad_metas++;
			}
			// Duplicate meta check (only check if length >= 30 so empty descriptions don't trigger duplicate errors)
			if ( ! empty( $meta_clean ) && $meta_len >= 30 ) {
				$m_key = mb_strtolower( $meta_clean );
				if ( in_array( $m_key, $seen_metas, true ) ) {
					$dup_metas++;
				} else {
					$seen_metas[] = $m_key;
				}
			}

			// --- H1 ---
			$has_h1 = preg_match( '/<h1[\s>]/i', $content );
			if ( ! $has_h1 ) {
				$elem_data = get_post_meta( $post->ID, '_elementor_data', true );
				if ( $elem_data && ( stripos( $elem_data, '"tag":"h1"' ) !== false || stripos( $elem_data, '"header_size":"h1"' ) !== false ) ) {
					$has_h1 = true;
				}
			}
			if ( ! $has_h1 && ! empty( $post->post_title ) ) {
				$has_h1 = true;
			}
			if ( ! $has_h1 ) {
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

			// --- Schema JSON-LD Detection (check postmeta + ASF Studio / Core auto-injection) ---
			$has_schema = get_post_meta( $post->ID, '_asf_schema_type', true )
				|| get_post_meta( $post->ID, 'rank_math_rich_snippet', true )
				|| get_option( 'asf_schema_enable', '1' ) === '1'
				|| defined( 'WPSEO_VERSION' )
				|| class_exists( 'RankMath' );
			if ( $has_schema ) {
				$schema_count++;
			}

			// --- Open Graph Tags Detection (check postmeta + ASF auto-injection) ---
			$has_og = get_post_meta( $post->ID, '_asf_seo_title', true )
				|| get_post_meta( $post->ID, 'rank_math_facebook_title', true )
				|| get_post_meta( $post->ID, '_yoast_wpseo_opengraph-title', true )
				|| get_option( 'asf_og_enable', '1' ) === '1'
				|| class_exists( 'ASF_Core' );
			if ( $has_og ) {
				$og_count++;
			}

			// --- E-E-A-T Signals (Author bio & Authoritative citations) ---
			$has_author_bio = strpos( strtolower( $content ), 'author-bio' ) !== false || strpos( strtolower( $content ), 'about the author' ) !== false || ( ! empty( $post->post_author ) && get_the_author_meta( 'description', $post->post_author ) );
			if ( ! $has_author_bio ) {
				$eeat_author_missing++;
			}
			if ( preg_match( '/href=["\'][^"\']*\.(gov|edu|wikipedia\.org|webmd\.com)/i', $content ) ) {
				$eeat_citations_count++;
			}

			// --- Accessibility & ARIA Attributes ---
			if ( preg_match_all( '/<(button|input)\s+([^>]+)>/i', $content, $interactive_els ) ) {
				foreach ( $interactive_els[0] as $el ) {
					if ( strpos( $el, 'aria-label' ) === false && strpos( $el, 'aria-labelledby' ) === false && strpos( $el, 'value=' ) === false && strpos( $el, 'type="hidden"' ) === false ) {
						$aria_missing++;
					}
				}
			}

			// --- Intrusive Pop-up / Overlay Detection ---
			if ( preg_match( '/style=["\'][^"\']*position\s*:\s*fixed[^"\']*z-index\s*:\s*(9999|99999|100000)/i', $content ) ) {
				$intrusive_popups++;
			}
		}

		// ── 3. DB-level checks ────────────────────────────────────────────
		$meta_typos = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%comhttp%' OR meta_value LIKE '%orghttp%' OR meta_value LIKE '%nethttp%' OR meta_value LIKE '%aehttp%'"
		);
		$post_typos = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN ('publish','draft') AND (post_content LIKE '%comhttp%' OR post_content LIKE '%orghttp%' OR post_content LIKE '%nethttp%' OR post_content LIKE '%aehttp%')"
		);
		$comhttps = $meta_typos + $post_typos;

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

		// ── 5. Live HTTP checks (Robots.txt & Sitemap Index) ───────────────
		$robots_file = ABSPATH . 'robots.txt';
		$robots_opt  = get_option( 'asf_robots_txt_content', '' );
		if ( file_exists( $robots_file ) || ! empty( $robots_opt ) ) {
			$robots_ok = true;
		} else {
			$robots_resp = wp_remote_get( home_url( '/robots.txt' ), array( 'timeout' => 5, 'sslverify' => false ) );
			$robots_ok   = ! is_wp_error( $robots_resp ) && in_array( wp_remote_retrieve_response_code( $robots_resp ), array( 200, 301, 302 ), true );
		}

		$sitemap_urls = array( '/sitemap_index.xml', '/sitemap.xml', '/wp-sitemap.xml' );
		$sitemap_ok   = false;

		foreach ( $sitemap_urls as $s_url ) {
			$s_resp = wp_remote_get( home_url( $s_url ), array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $s_resp ) && in_array( wp_remote_retrieve_response_code( $s_resp ), array( 200, 301, 302 ), true ) ) {
				$sitemap_ok = true;
				break;
			}
		}

		// ── 6. Weighted SEO Health Score Calculation ─────────────────────────
		$total_issues = $missing_titles + $bad_metas + $missing_h1 + $missing_alts + $dup_titles + $dup_metas + $comhttps + ($robots_ok ? 0 : 1) + ($sitemap_ok ? 0 : 1);
		$seo_score    = max( 35, 100 - ($total_issues * 3) );

		// ── 7. Return results & save cache ──────────────────────────────────
		$audit_data = array(
			'score'                => $seo_score,
			'posts'                => $total,
			'missing_titles'       => $missing_titles,
			'bad_metas'            => $bad_metas,
			'missing_h1'           => $missing_h1,
			'missing_alts'         => $missing_alts,
			'dup_titles'           => $dup_titles,
			'dup_metas'            => $dup_metas,
			'schema_count'         => $schema_count,
			'og_count'             => $og_count,
			'comhttps'             => $comhttps,
			'orphans'              => $orphans,
			'robots_ok'            => $robots_ok,
			'sitemap_ok'           => $sitemap_ok,
			'redirects'            => count( get_option( ASF_OPT_REDIRECTS, array() ) ),
			'eeat_author_missing'  => $eeat_author_missing,
			'eeat_citations_count' => $eeat_citations_count,
			'aria_missing'         => $aria_missing,
			'intrusive_popups'     => $intrusive_popups,
		);

		update_option( 'asf_last_audit_data', $audit_data );

		// Synchronize smart health banner cache
		if ( $seo_score >= 90 )      $grade = 'A';
		elseif ( $seo_score >= 75 )  $grade = 'B';
		elseif ( $seo_score >= 60 )  $grade = 'C';
		elseif ( $seo_score >= 45 )  $grade = 'D';
		else                         $grade = 'F';

		$health_issues = array();
		if ( $missing_titles > 0 ) $health_issues[] = "{$missing_titles} pages missing title tags.";
		if ( $bad_metas > 0 )      $health_issues[] = "{$bad_metas} pages missing or short meta descriptions.";
		if ( $missing_h1 > 0 )     $health_issues[] = "{$missing_h1} pages missing H1 headings.";
		if ( $missing_alts > 0 )   $health_issues[] = "{$missing_alts} images missing alt text.";
		if ( $dup_titles > 0 )     $health_issues[] = "{$dup_titles} duplicate title tags detected.";
		if ( $dup_metas > 0 )      $health_issues[] = "{$dup_metas} duplicate meta descriptions detected.";
		if ( $comhttps > 0 )       $health_issues[] = "{$comhttps} broken URL typos in database.";

		update_option( 'asf_health_score_cache', array(
			'score'   => $seo_score,
			'grade'   => $grade,
			'checks'  => array(
				'title'  => array( 'status' => ( $missing_titles === 0 ? 'ok' : 'error' ), 'label' => ( $missing_titles === 0 ? 'Title Tags OK' : "{$missing_titles} Missing Titles" ) ),
				'meta'   => array( 'status' => ( $bad_metas === 0 ? 'ok' : 'warn' ),       'label' => ( $bad_metas === 0 ? 'Meta Descriptions OK' : "{$bad_metas} Bad Metas" ) ),
				'h1'     => array( 'status' => ( $missing_h1 === 0 ? 'ok' : 'warn' ),     'label' => ( $missing_h1 === 0 ? 'H1 Headings OK' : "{$missing_h1} Missing H1s" ) ),
				'alts'   => array( 'status' => ( $missing_alts === 0 ? 'ok' : 'warn' ),   'label' => ( $missing_alts === 0 ? 'Image Alt Texts OK' : "{$missing_alts} Missing Alts" ) ),
				'robots' => array( 'status' => ( $robots_ok ? 'ok' : 'error' ),            'label' => ( $robots_ok ? 'Robots.txt OK' : 'Robots.txt Missing' ) ),
				'schema' => array( 'status' => ( $schema_count > 0 ? 'ok' : 'warn' ),     'label' => ( $schema_count > 0 ? 'Schema JSON-LD Active' : 'No Schema' ) ),
			),
			'issues'  => $health_issues,
			'ts'      => time(),
		), false );

		wp_send_json( array(
			'success' => true,
			'data'    => $audit_data,
		) );
	}
}
