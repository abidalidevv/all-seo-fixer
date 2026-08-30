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
		add_action( 'wp_ajax_asf_onpage_scan', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		asf_check_nonce();
		asf_cap_check();

		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => 200,
		) );

		$results = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			$issues  = array();

			// ── Title checks ─────────────────────────────────────────
			$title = get_post_meta( $post->ID, 'rank_math_title', true )
				?: get_post_meta( $post->ID, '_yoast_wpseo_title', true )
				?: get_the_title( $post->ID );
			$title_len = mb_strlen( trim( strip_tags( $title ) ) );

			if ( $title_len === 0 )     $issues[] = array( 'type' => 'error', 'msg' => 'Missing Title Tag — add a unique SEO title.' );
			elseif ( $title_len < 30 )  $issues[] = array( 'type' => 'warn',  'msg' => 'Title too short (' . $title_len . ' chars). Aim for 50–60 characters.' );
			elseif ( $title_len > 60 )  $issues[] = array( 'type' => 'warn',  'msg' => 'Title too long (' . $title_len . ' chars). Keep under 60 to avoid truncation in Google.' );

			// ── Meta Description checks ───────────────────────────────
			$meta = get_post_meta( $post->ID, 'rank_math_description', true )
				?: get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
			$meta_len = mb_strlen( trim( strip_tags( $meta ) ) );

			if ( $meta_len === 0 )      $issues[] = array( 'type' => 'error', 'msg' => 'Missing Meta Description — write a 120–155 char summary.' );
			elseif ( $meta_len < 80 )   $issues[] = array( 'type' => 'warn',  'msg' => 'Meta Description too short (' . $meta_len . ' chars). Aim for 120–155.' );
			elseif ( $meta_len > 160 )  $issues[] = array( 'type' => 'warn',  'msg' => 'Meta Description too long (' . $meta_len . ' chars). Keep under 160.' );

			// ── H1 check ─────────────────────────────────────────────
			if ( ! preg_match( '/<h1[\s>]/i', $content ) ) {
				$issues[] = array( 'type' => 'error', 'msg' => 'Missing H1 heading — every page needs exactly one H1 tag with the primary keyword.' );
			} elseif ( preg_match_all( '/<h1[\s>]/i', $content ) > 1 ) {
				$issues[] = array( 'type' => 'warn', 'msg' => 'Multiple H1 tags detected — a page should have only one H1.' );
			}

			// ── H2 check ─────────────────────────────────────────────
			if ( ! preg_match( '/<h2[\s>]/i', $content ) && mb_strlen( strip_tags( $content ) ) > 300 ) {
				$issues[] = array( 'type' => 'warn', 'msg' => 'No H2 subheadings found — use H2 tags to structure long content for readability and SEO.' );
			}

			// ── Image alt text ────────────────────────────────────────
			if ( preg_match_all( '/<img[^>]+>/i', $content, $imgs ) ) {
				$no_alt = 0;
				foreach ( $imgs[0] as $img ) {
					if ( ! preg_match( '/alt=["\'][^"\']+["\']/', $img ) ) $no_alt++;
				}
				if ( $no_alt ) {
					$issues[] = array( 'type' => 'warn', 'msg' => $no_alt . ' image(s) missing alt text — critical for accessibility and image SEO.' );
				}
			}

			// ── Content length ────────────────────────────────────────
			$content_words = str_word_count( strip_tags( $content ) );
			if ( $content_words < 300 && $post->post_type === 'post' ) {
				$issues[] = array( 'type' => 'warn', 'msg' => 'Thin content (' . $content_words . ' words) — blog posts should have at least 600 words for ranking.' );
			}

			// ── Schema JSON-LD ────────────────────────────────────────
			if ( ! preg_match( '/<script[^>]*application\/ld\+json/i', $content ) ) {
				$issues[] = array( 'type' => 'warn', 'msg' => 'No Schema JSON-LD markup — add Organization/Article schema via Rank Math for rich results.' );
			}

			// ── Canonical tag ─────────────────────────────────────────
			$noindex = get_post_meta( $post->ID, 'rank_math_robots', true ) ?: get_post_meta( $post->ID, '_yoast_wpseo_meta-robots-noindex', true );
			if ( $noindex === '1' || $noindex === 'noindex' ) {
				$issues[] = array( 'type' => 'warn', 'msg' => 'This page is set to NOINDEX — intentional? Remove if you want Google to index it.' );
			}

			// ── Internal links ────────────────────────────────────────
			preg_match_all( '/<a[^>]+href=["\']([^"\']*)["\'][^>]*>/i', $content, $links );
			$internal_links = 0;
			$site_host = parse_url( home_url(), PHP_URL_HOST );
			foreach ( $links[1] as $link ) {
				$link_host = parse_url( $link, PHP_URL_HOST );
				if ( ! $link_host || $link_host === $site_host || strpos( $link, '/' ) === 0 ) {
					$internal_links++;
				}
			}
			if ( $internal_links === 0 && mb_strlen( strip_tags( $content ) ) > 200 ) {
				$issues[] = array( 'type' => 'warn', 'msg' => 'No internal links found — add links to related pages to improve crawlability and reduce bounce rate.' );
			}

			$results[] = array(
				'id'       => $post->ID,
				'title'    => esc_html( get_the_title( $post->ID ) ),
				'url'      => get_permalink( $post->ID ),
				'edit_url' => get_edit_post_link( $post->ID ),
				'issues'   => $issues,
			);
		}

		usort( $results, function ( $a, $b ) {
			return count( $b['issues'] ) - count( $a['issues'] );
		} );

		wp_send_json( array( 'success' => true, 'data' => $results ) );
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

		$rows = $wpdb->get_results(
			"SELECT meta_id, post_id, meta_key, meta_value
			 FROM {$wpdb->postmeta}
			 WHERE meta_value LIKE '%comhttps%'"
		);

		foreach ( $rows as $row ) {
			$results['found']++;
			$old_val = $row->meta_value;
			$new_val = preg_replace( '#(https?://[^/\s"\']+)https?://[^/\s"\']+/#i', '$1/', $old_val );

			if ( $old_val !== $new_val ) {
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

		wp_send_json( array( 'success' => true, 'data' => $results ) );
	}
}

/* ==============================================================
   MEDIA & ORPHAN SCANNER
   ============================================================== */
class ASF_Media {

	public static function init() {
		add_action( 'wp_ajax_asf_scan_media',      array( __CLASS__, 'handle_scan' ) );
		add_action( 'wp_ajax_asf_trash_media',     array( __CLASS__, 'handle_trash' ) );
		add_action( 'wp_ajax_asf_auto_alt_media',  array( __CLASS__, 'handle_auto_alt' ) );
	}

	public static function handle_scan() {
		asf_check_nonce();
		asf_cap_check();

		global $wpdb;

		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => 50,
		) );

		$logo_id     = (int) get_theme_mod( 'custom_logo' );
		$logo_url    = $logo_id ? basename( wp_get_attachment_url( $logo_id ) ?: '' ) : '';
		$icon_id     = (int) get_option( 'site_icon' );
		$icon_url    = $icon_id ? basename( wp_get_attachment_url( $icon_id ) ?: '' ) : '';
		$header_img  = basename( get_header_image() ?: '' );
		$bg_img      = basename( get_background_image() ?: '' );

		$items   = array();
		$orphans = 0;

		foreach ( $attachments as $att ) {
			$url   = wp_get_attachment_url( $att->ID );
			$fname = $url ? basename( $url ) : '';
			if ( ! $fname ) continue;

			// Always mark logo, favicon, header, background as "in use"
			if ( ( $logo_url && $fname === $logo_url ) || ( $icon_url && $fname === $icon_url ) || ( $header_img && $fname === $header_img ) || ( $bg_img && $fname === $bg_img ) ) {
				$items[] = array( 'id' => $att->ID, 'filename' => $fname, 'is_orphan' => false );
				continue;
			}

			// 1. Post Content (Classic, Gutenberg)
			$in_content = $wpdb->get_var( $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_status='publish' AND post_content LIKE %s LIMIT 1",
				'%' . $wpdb->esc_like( $fname ) . '%'
			) );
			if ( $in_content ) { $items[] = array( 'id' => $att->ID, 'filename' => $fname, 'is_orphan' => false ); continue; }

			// 2. Elementor JSON Data
			$in_elementor = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND meta_value LIKE %s LIMIT 1",
				'%' . $wpdb->esc_like( $fname ) . '%'
			) );
			if ( $in_elementor ) { $items[] = array( 'id' => $att->ID, 'filename' => $fname, 'is_orphan' => false ); continue; }

			// 3. Featured Image
			$is_featured = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id' AND meta_value=%s LIMIT 1",
				(string) $att->ID
			) );
			if ( $is_featured ) { $items[] = array( 'id' => $att->ID, 'filename' => $fname, 'is_orphan' => false ); continue; }

			// 4. WooCommerce Product Gallery & General Postmeta reference
			$in_meta = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_value LIKE %s OR meta_value LIKE %s LIMIT 1",
				'%' . $wpdb->esc_like( $fname ) . '%',
				'%' . $wpdb->esc_like( (string) $att->ID ) . '%'
			) );
			if ( $in_meta ) { $items[] = array( 'id' => $att->ID, 'filename' => $fname, 'is_orphan' => false ); continue; }

			$is_orphan = true;
			$orphans++;
			$items[] = array( 'id' => $att->ID, 'filename' => $fname, 'is_orphan' => true );
		}

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'total'   => count( $attachments ),
				'used'    => count( $attachments ) - $orphans,
				'orphans' => $orphans,
				'items'   => $items,
			),
		) );
	}

	public static function handle_trash() {
		asf_check_nonce();
		asf_cap_check();

		$att_id = intval( $_GET['id'] ?? 0 );
		if ( $att_id && wp_trash_post( $att_id ) ) {
			wp_send_json( array( 'success' => true ) );
		} else {
			wp_send_json( array( 'success' => false, 'message' => 'Failed to trash attachment #' . $att_id ) );
		}
	}

	/**
	 * Auto-generates clean, human-readable Alt Text for all images missing Alt Text in Media Library.
	 * Formats filenames: commercial-air-freight_v2.jpg -> Commercial Air Freight V2
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
					$filename = pathinfo( basename( $url ), PATHINFO_FILENAME );
				}
				if ( $filename ) {
					// Clean up separators and numbers
					$clean_alt = preg_replace( '/[_\-\s]+/', ' ', $filename );
					$clean_alt = ucwords( trim( $clean_alt ) );

					if ( $clean_alt ) {
						update_post_meta( $att->ID, '_wp_attachment_image_alt', $clean_alt );
						$updated_count++;
						$fixed_details[] = array(
							'id'       => $att->ID,
							'filename' => $filename,
							'alt'      => $clean_alt,
						);
					}
				}
			}
		}

		wp_send_json( array(
			'success' => true,
			'message' => '✨ Auto-generated alt text for ' . $updated_count . ' image(s) based on filenames!',
			'data'    => array(
				'updated_count' => $updated_count,
				'details'       => $fixed_details,
			),
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

		// 1. Google Sitemap Ping
		wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap_url ), array( 'timeout' => 5 ) );

		// 2. IndexNow (Bing, Yandex, Seznam, Naver)
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

		$indexnow = array(
			'host'        => $host,
			'key'         => md5( $host ),
			'keyLocation' => home_url( '/' . md5( $host ) . '.txt' ),
			'urlList'     => array_unique( $urls ),
		);
		wp_remote_post( 'https://api.indexnow.org/indexnow', array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $indexnow ),
			'timeout' => 5,
		) );

		// 3. Purge Rank Math + Yoast sitemap caches from DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%rank_math_sitemap%' OR option_name LIKE '%wpseo_xml_sitemap%'" );

		wp_send_json( array(
			'success' => true,
			'message' => '🚀 Done! Google Sitemap Ping sent + IndexNow alert delivered to Bing/Yandex for ' . count( $urls ) . ' URLs + Sitemap caches purged!',
		) );
	}
}

/* ==============================================================
   1-CLICK AUTO-FIXER ENGINE
   ============================================================== */
class ASF_AutoFixer {

	public static function init() {
		add_action( 'wp_ajax_asf_autofix_missing_h1', array( __CLASS__, 'handle_autofix_h1' ) );
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

		wp_send_json( array(
			'success' => true,
			'message' => '✨ Auto-fixed missing H1 headings on ' . $fixed_count . ' page(s)! Added <h1>[Page Title]</h1> tag to top of content.',
		) );
	}
}
