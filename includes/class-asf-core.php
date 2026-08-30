<?php
/**
 * Core Protection Engine — Always-On Hooks
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Core {

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'run_redirects_and_feed_protection' ) );
		add_action( 'wp_head',           array( __CLASS__, 'inject_auto_schema_and_og_tags' ), 1 );
		add_filter( 'rank_math/sitemap/exclude_post_type', array( __CLASS__, 'exclude_builder_cpts_from_sitemap' ), 10, 2 );
		add_filter( 'wpseo_sitemap_exclude_post_type',     array( __CLASS__, 'exclude_builder_cpts_from_sitemap' ), 10, 2 );
	}

	/**
	 * Runs on every front-end request:
	 *  1. Noindex RSS/Atom feeds
	 *  2. DB-driven 301 Redirect Engine
	 */
	public static function run_redirects_and_feed_protection() {

		// 1. RSS Feed protection — prevents feed crawling diluting index
		if ( is_feed() ) {
			header( 'X-Robots-Tag: noindex, follow', true );
		}

		// 2. Custom 301 Redirect Engine (from DB option)
		$redirects = get_option( ASF_OPT_REDIRECTS, array() );
		if ( empty( $redirects ) || ! is_array( $redirects ) ) return;

		$path = strtok( $_SERVER['REQUEST_URI'] ?? '', '?' );

		foreach ( $redirects as $src => $tgt ) {
			if ( empty( $src ) || empty( $tgt ) ) continue;
			$clean_src = '/' . ltrim( trim( $src ), '/' );
			if ( rtrim( $path, '/' ) === rtrim( $clean_src, '/' ) ) {
				wp_redirect( esc_url_raw( $tgt ), 301 );
				exit;
			}
		}
	}

	/**
	 * Exclude Elementor, Divi, Thrive, and other builder CPTs from XML Sitemap index.
	 * Prevents /tahefobu_header-sitemap.xml type entries polluting Google's sitemap index.
	 *
	 * @param  bool   $exclude Current exclusion state.
	 * @param  string $type    Post type slug.
	 * @return bool
	 */
	public static function exclude_builder_cpts_from_sitemap( $exclude, $type ) {
		$builder_types = array(
			'elementor_library',   // Elementor
			'tahefobu_header',     // Elementor Header/Footer Builder
			'tahefobu_footer',
			'ct_builder',          // Oxygen Builder
			'et_pb_layout',        // Divi
			'fl-builder-template', // Beaver Builder
			'brizy-global',        // Brizy
			'nf_sub',              // Ninja Forms
			'oembed_cache',        // WordPress core
		);
		return in_array( $type, $builder_types, true ) ? true : $exclude;
	}

	/**
	 * Automatically injects Schema JSON-LD and Open Graph / Twitter Card meta tags
	 * into <head> on front-end for pages missing schema/OG tags.
	 */
	public static function inject_auto_schema_and_og_tags() {
		if ( is_admin() || is_feed() ) return;

		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );
		$site_url  = home_url( '/' );

		// Determine current page metadata
		$title = '';
		$desc  = '';
		$url   = '';
		$image = '';

		if ( is_singular() ) {
			$post_id = get_the_ID();
			$title   = get_post_meta( $post_id, 'rank_math_title', true )
				?: get_post_meta( $post_id, '_yoast_wpseo_title', true )
				?: get_the_title( $post_id ) . ' — ' . $site_name;

			$desc    = get_post_meta( $post_id, 'rank_math_description', true )
				?: get_post_meta( $post_id, '_yoast_wpseo_metadesc', true )
				?: wp_strip_all_tags( get_the_excerpt( $post_id ) );

			if ( empty( $desc ) ) {
				$desc = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 25 );
			}

			$url = get_permalink( $post_id );

			if ( has_post_thumbnail( $post_id ) ) {
				$image = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'full' );
			}
		} else {
			$title = $site_name . ( $site_desc ? ' — ' . $site_desc : '' );
			$desc  = $site_desc ?: 'Welcome to ' . $site_name;
			$url   = $site_url;
		}

		if ( ! $image ) {
			$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
			if ( $custom_logo_id ) {
				$image = wp_get_attachment_image_url( $custom_logo_id, 'full' );
			}
		}

		$title = esc_attr( trim( strip_tags( $title ) ) );
		$desc  = esc_attr( trim( strip_tags( $desc ) ) );
		$url   = esc_url( $url );
		$image = esc_url( $image );

		// 1. OPEN GRAPH & TWITTER TAGS
		echo "\n<!-- All-in-One SEO Fixer: Auto Open Graph & Twitter Cards -->\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
		echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '" />' . "\n";
		echo '<meta property="og:title" content="' . $title . '" />' . "\n";
		echo '<meta property="og:description" content="' . $desc . '" />' . "\n";
		echo '<meta property="og:url" content="' . $url . '" />' . "\n";
		if ( $image ) {
			echo '<meta property="og:image" content="' . $image . '" />' . "\n";
			echo '<meta name="twitter:image" content="' . $image . '" />' . "\n";
		}
		echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
		echo '<meta name="twitter:title" content="' . $title . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . $desc . '" />' . "\n";

		// 2. SCHEMA JSON-LD MARKUP
		$schema = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type' => 'Organization',
					'@id'   => $site_url . '#organization',
					'name'  => $site_name,
					'url'   => $site_url,
					'logo'  => $image ?: null,
				),
				array(
					'@type'       => 'WebSite',
					'@id'         => $site_url . '#website',
					'url'         => $site_url,
					'name'        => $site_name,
					'description' => $site_desc,
					'publisher'   => array( '@id' => $site_url . '#organization' ),
				),
				array(
					'@type'           => is_single() ? 'Article' : 'WebPage',
					'@id'             => $url . '#webpage',
					'url'             => $url,
					'name'            => $title,
					'description'     => $desc,
					'isPartOf'        => array( '@id' => $site_url . '#website' ),
					'inLanguage'      => get_locale(),
				),
			),
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
		echo "<!-- / All-in-One SEO Fixer -->\n\n";
	}
}
