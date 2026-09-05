<?php
/**
 * Generative Engine Optimization (GEO) & AI Search Hub
 *
 * Optimizes the website for 2025-2026 AI Search Engines & LLMs:
 *  - OpenAI SearchGPT / ChatGPT (GPTBot)
 *  - Perplexity AI (PerplexityBot)
 *  - Google Gemini & AI Overviews (Google-Extended)
 *  - Anthropic Claude (ClaudeBot)
 *  - Apple Intelligence (Applebot)
 *
 * Features:
 *  - AI Citability Audit & Readiness Score (0-100)
 *  - Bot Permissions & Crawl Directives
 *  - Dynamic /llms.txt Site Knowledge Index Generator
 *  - Conversational Q&A / FAQ Schema Builder
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_GEO {

	public static function init() {
		// Inject AI snippet directives
		add_action( 'wp_head', array( __CLASS__, 'inject_ai_search_directives' ), 1 );

		// AJAX endpoints
		add_action( 'wp_ajax_asf_save_geo_hub',        array( __CLASS__, 'handle_save' ) );
		add_action( 'wp_ajax_asf_scan_geo_readiness', array( __CLASS__, 'handle_readiness_audit' ) );
		add_action( 'wp_ajax_asf_regenerate_llms_txt',array( __CLASS__, 'handle_regenerate_llms' ) );
	}

	/**
	 * Injects search engine snippet permissions into <head>
	 */
	public static function inject_ai_search_directives() {
		if ( is_admin() || is_feed() ) return;

		// Maximum snippet permission for AI answer engines
		if ( get_option( 'asf_geo_max_snippets', '1' ) === '1' ) {
			echo '<meta name="robots" content="max-snippet:-1, max-image-preview:large, max-video-preview:-1" />' . "\n";
		}
	}

	/**
	 * Audits website for Generative Engine Optimization (GEO) readiness
	 */
	public static function handle_readiness_audit() {
		asf_check_nonce();
		asf_cap_check();

		$score = 100;
		$checks = array();
		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );

		// 1. Check /llms.txt
		$llms_active = ( get_option( 'asf_llms_enable', '1' ) === '1' );
		if ( $llms_active ) {
			$checks[] = array(
				'label'  => '/llms.txt AI Knowledge Index',
				'status' => 'pass',
				'desc'   => 'Active and serving Markdown documentation at /llms.txt for ChatGPT, Perplexity & Claude.',
			);
		} else {
			$score -= 25;
			$checks[] = array(
				'label'  => '/llms.txt AI Knowledge Index',
				'status' => 'fail',
				'desc'   => 'Disabled. AI crawlers have no structured /llms.txt map to ingest.',
			);
		}

		// 2. Check Schema.org Entity Clarity
		$schema_active = ( get_option( 'asf_schema_enable', '1' ) === '1' );
		if ( $schema_active ) {
			$checks[] = array(
				'label'  => 'Authoritative Schema.org Entity Graph',
				'status' => 'pass',
				'desc'   => 'Active Schema.org JSON-LD graph provides unambiguous brand identity to LLM training sets.',
			);
		} else {
			$score -= 25;
			$checks[] = array(
				'label'  => 'Authoritative Schema.org Entity Graph',
				'status' => 'fail',
				'desc'   => 'Disabled. Missing structured data decreases AI engine citation probability.',
			);
		}

		// 3. Check Geo / Local Entity Coordinates
		$lat = get_option( 'asf_geo_lat', '' );
		$lng = get_option( 'asf_geo_lng', '' );
		if ( is_numeric( $lat ) && is_numeric( $lng ) && $lat != 0 ) {
			$checks[] = array(
				'label'  => 'Geotargeting & Physical Coordinates (ICBM)',
				'status' => 'pass',
				'desc'   => 'Verified GPS coordinates anchored for local AI search (e.g. "near me" in Gemini/Perplexity).',
			);
		} else {
			$score -= 15;
			$checks[] = array(
				'label'  => 'Geotargeting & Physical Coordinates (ICBM)',
				'status' => 'warn',
				'desc'   => 'GPS coordinates missing. Add latitude/longitude to anchor local AI search queries.',
			);
		}

		// 4. Check Maximum Snippet Permissions
		$max_snip = ( get_option( 'asf_geo_max_snippets', '1' ) === '1' );
		if ( $max_snip ) {
			$checks[] = array(
				'label'  => 'Unrestricted AI Answer Snippets (max-snippet:-1)',
				'status' => 'pass',
				'desc'   => 'Permits AI engines to cite complete sentences and paragraphs in generated responses.',
			);
		} else {
			$score -= 15;
			$checks[] = array(
				'label'  => 'Unrestricted AI Answer Snippets (max-snippet:-1)',
				'status' => 'warn',
				'desc'   => 'Disabled. Search engines may truncate citations in AI summaries.',
			);
		}

		// 5. Check Content Clarity & Breadth
		$pages_count = wp_count_posts('page')->publish ?? 0;
		$posts_count = wp_count_posts('post')->publish ?? 0;
		$total_docs  = $pages_count + $posts_count;

		if ( $total_docs >= 10 ) {
			$checks[] = array(
				'label'  => 'Indexable Content Volume',
				'status' => 'pass',
				'desc'   => $total_docs . ' published documents available for LLM knowledge retrieval.',
			);
		} else {
			$score -= 10;
			$checks[] = array(
				'label'  => 'Indexable Content Volume',
				'status' => 'warn',
				'desc'   => 'Thin content volume (' . $total_docs . ' pages). Publish more in-depth topic guides to increase citations.',
			);
		}

		$score = max( 20, min( 100, $score ) );
		$grade = $score >= 90 ? 'A+' : ( $score >= 80 ? 'A' : ( $score >= 65 ? 'B' : ( $score >= 50 ? 'C' : 'D' ) ) );

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'score'       => $score,
				'grade'       => $grade,
				'checks'      => $checks,
				'llms_url'    => home_url( '/llms.txt' ),
				'total_docs'  => $total_docs,
			),
		) );
	}

	/**
	 * Automatically regenerates /llms.txt content from published WordPress pages
	 */
	public static function handle_regenerate_llms() {
		asf_check_nonce();
		asf_cap_check();

		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' ) ?: 'Official Website & Services';
		$home_url  = home_url( '/' );

		$md  = "# " . $site_name . "\n\n";
		$md .= "> " . $site_desc . "\n\n";
		$md .= "## Website Overview\n";
		$md .= "- Official URL: " . $home_url . "\n";
		$md .= "- Entity Type: " . get_option( 'asf_geo_biz_type', 'LocalBusiness' ) . "\n";
		if ( $phone = get_option( 'asf_local_biz_phone', '' ) ) {
			$md .= "- Contact: " . $phone . "\n";
		}
		if ( $loc = get_option( 'asf_geo_placename', '' ) ) {
			$md .= "- Location: " . $loc . "\n";
		}
		$md .= "\n";

		// Directives for Bots
		$md .= "## AI Search Crawler Directives\n";
		$md .= "- User-agent: GPTBot\n  Allow: /\n";
		$md .= "- User-agent: PerplexityBot\n  Allow: /\n";
		$md .= "- User-agent: ClaudeBot\n  Allow: /\n";
		$md .= "- User-agent: Google-Extended\n  Allow: /\n\n";

		// Published Pages
		$md .= "## Core Pages & Services\n";
		$pages = get_pages( array( 'number' => 25, 'post_status' => 'publish', 'sort_column' => 'menu_order' ) );
		if ( ! empty( $pages ) ) {
			foreach ( $pages as $pg ) {
				$clean_excerpt = wp_html_excerpt( wp_strip_all_tags( $pg->post_content ), 120, '…' );
				$md .= "- [" . get_the_title( $pg->ID ) . "](" . get_permalink( $pg->ID ) . ")";
				if ( $clean_excerpt ) {
					$md .= ": " . $clean_excerpt;
				}
				$md .= "\n";
			}
		}

		// Published Posts
		$posts = get_posts( array( 'numberposts' => 15, 'post_status' => 'publish' ) );
		if ( ! empty( $posts ) ) {
			$md .= "\n## Key Guides & Articles\n";
			foreach ( $posts as $pst ) {
				$md .= "- [" . get_the_title( $pst->ID ) . "](" . get_permalink( $pst->ID ) . ")\n";
			}
		}

		update_option( 'asf_llms_enable', '1' );
		update_option( 'asf_llms_txt_content', $md );

		wp_send_json( array(
			'success' => true,
			'message' => '✓ /llms.txt AI site index successfully regenerated!',
			'content' => $md,
		) );
	}

	public static function handle_save() {
		asf_check_nonce();
		asf_cap_check();

		update_option( 'asf_geo_max_snippets', isset( $_POST['asf_geo_max_snippets'] ) ? '1' : '0' );
		update_option( 'asf_bot_gptbot', isset( $_POST['asf_bot_gptbot'] ) ? '1' : '0' );
		update_option( 'asf_bot_perplexity', isset( $_POST['asf_bot_perplexity'] ) ? '1' : '0' );
		update_option( 'asf_bot_claudebot', isset( $_POST['asf_bot_claudebot'] ) ? '1' : '0' );
		update_option( 'asf_bot_gemini', isset( $_POST['asf_bot_gemini'] ) ? '1' : '0' );

		wp_send_json( array(
			'success' => true,
			'message' => '✨ GEO & AI Search settings successfully updated!',
		) );
	}
}
