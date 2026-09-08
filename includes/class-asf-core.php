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
		add_action( 'wp_head',           array( __CLASS__, 'inject_canonical_and_robots_meta' ), 2 );
		add_action( 'wp_head',           array( __CLASS__, 'inject_title_and_description_meta' ), 3 );
		add_action( 'wp_head',           array( __CLASS__, 'inject_geo_seo_meta_tags' ), 4 );
		add_filter( 'the_content',       array( __CLASS__, 'filter_lazy_load_content' ), 99 );
		add_filter( 'pre_get_document_title',              array( __CLASS__, 'filter_pre_get_document_title' ), 99 );
		add_filter( 'document_title_parts',                array( __CLASS__, 'filter_document_title_parts' ), 99 );
		add_filter( 'rank_math/sitemap/exclude_post_type', array( __CLASS__, 'exclude_builder_cpts_from_sitemap' ), 10, 2 );
		add_filter( 'wpseo_sitemap_exclude_post_type',     array( __CLASS__, 'exclude_builder_cpts_from_sitemap' ), 10, 2 );
		add_filter( 'robots_txt',                          array( __CLASS__, 'filter_robots_txt' ), 99, 2 );
	}

	/**
	 * Runs on every front-end request:
	 *  0. Deliver /llms.txt for AI Search Engines (OpenAI, Perplexity, Claude, Gemini)
	 *  1. Noindex RSS/Atom feeds
	 *  2. DB-driven 301 Redirect Engine
	 */
	public static function run_redirects_and_feed_protection() {

		// 0. AI Search llms.txt & llms-full.txt Publisher
		$req_path = parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
		if ( $req_path === '/llms.txt' || $req_path === '/llms-full.txt' ) {
			if ( get_option( 'asf_llms_enable', '1' ) === '1' ) {
				header( 'Content-Type: text/markdown; charset=utf-8' );
				header( 'X-Robots-Tag: all', true );
				$llms_content = get_option( 'asf_llms_txt_content', '' );
				if ( empty( trim( (string) $llms_content ) ) ) {
					$site_name = get_bloginfo( 'name' );
					$site_desc = get_bloginfo( 'description' );
					$llms_content  = "# " . $site_name . "\n\n";
					if ( $site_desc ) {
						$llms_content .= "> " . $site_desc . "\n\n";
					}
					$llms_content .= "## Website Overview\n- URL: " . home_url( '/' ) . "\n\n";
					$llms_content .= "## Key Pages & Services\n";
					$pages = get_pages( array( 'number' => 20, 'post_status' => 'publish' ) );
					if ( ! empty( $pages ) ) {
						foreach ( $pages as $pg ) {
							$llms_content .= "- [" . get_the_title( $pg->ID ) . "](" . get_permalink( $pg->ID ) . ")\n";
						}
					}
				}
				echo $llms_content;
				exit;
			}
		}

		// 1. RSS Feed protection — prevents feed crawling diluting index
		if ( is_feed() ) {
			header( 'X-Robots-Tag: noindex, follow', true );
		}

		// 2. Custom 301 Redirect Engine (from DB option)
		$redirects = get_option( ASF_OPT_REDIRECTS, array() );
		if ( ! empty( $redirects ) && is_array( $redirects ) ) {
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

		// 3. 404 URL Hit Monitor Logger (Dedicated DB Table with Index)
		if ( is_404() ) {
			$uri = strtok( $_SERVER['REQUEST_URI'] ?? '', '?' );
			if ( $uri && strpos( $uri, '/wp-content/' ) === false && strpos( $uri, '/wp-includes/' ) === false && strpos( $uri, 'favicon' ) === false && strpos( $uri, '.php' ) === false && strpos( $uri, '.xml' ) === false && strpos( $uri, 'xmlrpc' ) === false ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'asf_404_logs';
				$clean_uri  = sanitize_text_field( $uri );
				$time       = time();

				// Fast INSERT or UPDATE hits count
				$wpdb->query( $wpdb->prepare(
					"INSERT INTO {$table_name} (url, hits, last_seen) VALUES (%s, 1, %d)
					 ON DUPLICATE KEY UPDATE hits = hits + 1, last_seen = %d",
					$clean_uri,
					$time,
					$time
				) );

				// Auto-purge old entries (older than 30 days) when table size > 500
				if ( rand( 1, 50 ) === 1 ) {
					$cutoff = $time - ( 30 * DAY_IN_SECONDS );
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE last_seen < %d AND hits < 3", $cutoff ) );
				}
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
	 * Checks if a major SEO plugin is handling OG tags.
	 * Used to avoid duplicate OG/Twitter card output.
	 */
	private static function has_seo_plugin_og() {
		return ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || class_exists( 'All_in_One_SEO_Pack' ) || defined( 'SEOPRESS_VERSION' ) );
	}

	/**
	 * Checks if a major SEO plugin is handling Schema JSON-LD.
	 */
	private static function has_seo_plugin_schema() {
		return ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) );
	}

	/**
	 * FIX #1: Injects <link rel="canonical"> on front-end.
	 * FIX #2: Injects <meta name="robots"> with max-image-preview:large for Google Discover eligibility.
	 *
	 * Only outputs when no other SEO plugin is managing these tags.
	 */
	public static function inject_canonical_and_robots_meta() {
		if ( is_admin() || is_feed() ) return;

		// Guard: Don't output if Rank Math, Yoast, AIOSEO, or SEOPress handle these
		if ( self::has_seo_plugin_og() ) return;

		// ── Canonical URL ──────────────────────────────────────────
		$canonical = '';
		if ( is_singular() ) {
			$canonical = wp_get_canonical_url( get_the_ID() );
			if ( ! $canonical ) {
				$canonical = get_permalink( get_the_ID() );
			}
		} elseif ( is_front_page() || is_home() ) {
			$canonical = home_url( '/' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term ) {
				$canonical = get_term_link( $term );
			}
		} elseif ( is_author() ) {
			$canonical = get_author_posts_url( get_queried_object_id() );
		} elseif ( is_post_type_archive() ) {
			$canonical = get_post_type_archive_link( get_queried_object()->name );
		}

		if ( $canonical && ! is_wp_error( $canonical ) ) {
			// Strip pagination query args for cleanliness, preserve page number
			$canonical = remove_query_arg( array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'fbclid', 'gclid', 'ref' ), $canonical );
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
		}

		// ── Meta Robots ──────────────────────────────────────────
		// Google Discover requires max-image-preview:large; max-snippet:-1 prevents truncation
		$robots_content = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

		// Check if current singular page is set to noindex via Rank Math or Yoast meta
		if ( is_singular() ) {
			$noindex_rm = get_post_meta( get_the_ID(), 'rank_math_robots', true );
			$noindex_yo = get_post_meta( get_the_ID(), '_yoast_wpseo_meta-robots-noindex', true );
			if ( $noindex_rm === 'noindex' || $noindex_yo === '1' ) {
				$robots_content = 'noindex, follow';
			}
		}

		echo '<meta name="robots" content="' . esc_attr( $robots_content ) . '" />' . "\n";
	}

	/**
	 * Outputs <title> tag and <meta name="description"> when no other SEO plugin is active.
	 */
	public static function inject_title_and_description_meta() {
		if ( is_admin() || is_feed() ) return;

		// Guard: Don't output if Rank Math, Yoast, AIOSEO, or SEOPress handle titles/metas
		if ( self::has_seo_plugin_og() ) return;

		$site_name = get_bloginfo( 'name' );

		// 1. Output <title> if theme doesn't handle title-tag
		if ( ! current_theme_supports( 'title-tag' ) ) {
			$title = '';
			if ( is_singular() ) {
				$post_id = get_the_ID();
				$title   = get_post_meta( $post_id, '_asf_seo_title', true )
					?: get_post_meta( $post_id, 'rank_math_title', true )
					?: get_post_meta( $post_id, '_yoast_wpseo_title', true )
					?: get_the_title( $post_id );
				$title   = $title . ' - ' . $site_name;
			} elseif ( is_front_page() || is_home() ) {
				$site_desc = get_bloginfo( 'description' );
				$title     = $site_name . ( $site_desc ? ' - ' . $site_desc : '' );
			} elseif ( is_category() || is_tag() || is_tax() ) {
				$title = single_term_title( '', false ) . ' - ' . $site_name;
			} elseif ( is_author() ) {
				$title = get_the_author_meta( 'display_name', get_queried_object_id() ) . ' - ' . $site_name;
			} elseif ( is_search() ) {
				$title = 'Search Results for "' . get_search_query() . '" - ' . $site_name;
			} elseif ( is_404() ) {
				$title = 'Page Not Found - ' . $site_name;
			}

			if ( $title ) {
				echo '<title>' . esc_html( $title ) . '</title>' . "\n";
			}
		}

		// 2. Output <meta name="description">
		$desc = '';
		if ( is_singular() ) {
			$post_id = get_the_ID();
			$desc    = get_post_meta( $post_id, '_asf_meta_description', true )
				?: get_post_meta( $post_id, 'rank_math_description', true )
				?: get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );

			if ( empty( trim( (string) $desc ) ) ) {
				$excerpt = get_the_excerpt( $post_id );
				if ( ! empty( $excerpt ) ) {
					$desc = wp_strip_all_tags( $excerpt );
				} else {
					$content = get_post_field( 'post_content', $post_id );
					$desc    = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $content ) ), 25 );
				}
			}
		} elseif ( is_front_page() || is_home() ) {
			$desc = get_option( 'asf_home_meta_desc', '' ) ?: get_bloginfo( 'description' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$desc = wp_strip_all_tags( term_description() );
		} elseif ( is_author() ) {
			$desc = wp_strip_all_tags( get_the_author_meta( 'description', get_queried_object_id() ) );
		}

		if ( ! empty( trim( (string) $desc ) ) ) {
			$desc_clean = esc_attr( trim( preg_replace( '/\s+/', ' ', strip_tags( (string) $desc ) ) ) );
			echo '<meta name="description" content="' . $desc_clean . '" />' . "\n";
		}
	}

	/**
	 * Overrides standard WordPress document title tag with saved SEO title
	 */
	public static function filter_pre_get_document_title( $title ) {
		if ( is_singular() ) {
			$post_id   = get_the_ID();
			$seo_title = get_post_meta( $post_id, '_asf_seo_title', true );
			if ( ! empty( $seo_title ) ) {
				return esc_html( $seo_title );
			}
		}
		return $title;
	}

	/**
	 * Filters document title parts for themes supporting native WordPress title-tag
	 */
	public static function filter_document_title_parts( $parts ) {
		if ( is_singular() ) {
			$post_id   = get_the_ID();
			$seo_title = get_post_meta( $post_id, '_asf_seo_title', true );
			if ( ! empty( $seo_title ) ) {
				$parts['title'] = esc_html( $seo_title );
			}
		}
		return $parts;
	}

	/**
	 * Injects Geotargeting & Local GEO SEO Meta Tags into <head>
	 * Empowers Google Local, Apple Maps, Bing Places, and geo-targeted crawlers.
	 */
	public static function inject_geo_seo_meta_tags() {
		if ( is_admin() || is_feed() ) return;

		$enable_geo = get_option( 'asf_geo_enable', '1' );
		if ( $enable_geo !== '1' ) return;

		$lat       = trim( (string) get_option( 'asf_geo_lat', get_option( 'asf_local_biz_lat', '' ) ) );
		$lng       = trim( (string) get_option( 'asf_geo_lng', get_option( 'asf_local_biz_lng', '' ) ) );
		$placename = trim( (string) get_option( 'asf_geo_placename', '' ) );
		$region    = trim( (string) get_option( 'asf_geo_region', '' ) );
		$street    = trim( (string) get_option( 'asf_local_biz_address', '' ) );
		$country   = trim( (string) get_option( 'asf_geo_country', '' ) );
		$postal    = trim( (string) get_option( 'asf_geo_postal', '' ) );

		if ( empty( $placename ) && ! empty( $street ) ) {
			$parts = explode( ',', $street );
			$placename = trim( end( $parts ) );
		}

		if ( ( $lat && $lng ) || $placename || $region ) {
			echo "\n<!-- All-in-One SEO Fixer: Real-Time GEO SEO & Geotargeting -->\n";
			if ( $lat && $lng ) {
				echo '<meta name="geo.position" content="' . esc_attr( $lat . ';' . $lng ) . '" />' . "\n";
				echo '<meta name="ICBM" content="' . esc_attr( $lat . ', ' . $lng ) . '" />' . "\n";
				echo '<meta property="place:location:latitude" content="' . esc_attr( $lat ) . '" />' . "\n";
				echo '<meta property="place:location:longitude" content="' . esc_attr( $lng ) . '" />' . "\n";
			}
			if ( $placename ) {
				echo '<meta name="geo.placename" content="' . esc_attr( $placename ) . '" />' . "\n";
				echo '<meta property="business:contact_data:locality" content="' . esc_attr( $placename ) . '" />' . "\n";
			}
			if ( $region ) {
				echo '<meta name="geo.region" content="' . esc_attr( $region ) . '" />' . "\n";
				echo '<meta property="business:contact_data:region" content="' . esc_attr( $region ) . '" />' . "\n";
			}
			if ( $street ) {
				echo '<meta property="business:contact_data:street_address" content="' . esc_attr( $street ) . '" />' . "\n";
			}
			if ( $postal ) {
				echo '<meta property="business:contact_data:postal_code" content="' . esc_attr( $postal ) . '" />' . "\n";
			}
			if ( $country ) {
				echo '<meta property="business:contact_data:country_name" content="' . esc_attr( $country ) . '" />' . "\n";
			}
			echo "<!-- / GEO SEO Meta Tags -->\n\n";
		}
	}

	/**
	 * Automatically injects Schema JSON-LD and Open Graph / Twitter Card meta tags
	 * into <head> on front-end for pages missing schema/OG tags.
	 *
	 * FIX #10: Granular guard — OG and basic Schema respect SEO plugin guard,
	 *          but LocalBusiness schema ALWAYS outputs (Rank Math Free doesn't include it).
	 * FIX #8:  Article schema includes author, datePublished, dateModified.
	 * FIX #9:  BreadcrumbList schema added.
	 * FIX #11: og:locale tag added.
	 * FIX #13: mainEntityOfPage added to Article schema.
	 */
	public static function inject_auto_schema_and_og_tags() {
		if ( is_admin() || is_feed() ) return;

		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );
		$site_url  = home_url( '/' );

		// Determine current page metadata
		$title   = '';
		$desc    = '';
		$url     = '';
		$image   = '';
		$post_id = 0;

		if ( is_singular() ) {
			$post_id = get_the_ID();
			$title   = get_post_meta( $post_id, 'rank_math_title', true )
				?: get_post_meta( $post_id, '_yoast_wpseo_title', true )
				?: get_the_title( $post_id ) . ' - ' . $site_name;

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
			$title = $site_name . ( $site_desc ? ' - ' . $site_desc : '' );
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

		// ── OG & TWITTER TAGS (only if no SEO plugin manages them) ──
		if ( ! self::has_seo_plugin_og() ) {
			echo "\n<!-- All-in-One SEO Fixer: Auto Open Graph & Twitter Cards -->\n";
			echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
			echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '" />' . "\n";
			echo '<meta property="og:title" content="' . $title . '" />' . "\n";
			echo '<meta property="og:description" content="' . $desc . '" />' . "\n";
			echo '<meta property="og:url" content="' . $url . '" />' . "\n";
			echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />' . "\n";
			if ( $image ) {
				echo '<meta property="og:image" content="' . $image . '" />' . "\n";
				echo '<meta name="twitter:image" content="' . $image . '" />' . "\n";
			}
			echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
			echo '<meta name="twitter:title" content="' . $title . '" />' . "\n";
			echo '<meta name="twitter:description" content="' . $desc . '" />' . "\n";
		}

		// ── SCHEMA JSON-LD (only if no SEO plugin manages schema) ──
		if ( ! self::has_seo_plugin_schema() ) {
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
				),
			);

			// WebPage or Article node with author, dates, mainEntityOfPage
			$page_node = array(
				'@type'           => is_single() ? 'Article' : 'WebPage',
				'@id'             => $url . '#webpage',
				'url'             => $url,
				'name'            => $title,
				'description'     => $desc,
				'isPartOf'        => array( '@id' => $site_url . '#website' ),
				'inLanguage'      => get_locale(),
			);

			// FIX #8 & #13: Article-specific properties (author, dates, mainEntityOfPage)
			if ( is_singular() && $post_id ) {
				$post_obj    = get_post( $post_id );
				$author_name = get_the_author_meta( 'display_name', $post_obj->post_author );
				$author_url  = get_author_posts_url( $post_obj->post_author );

				$page_node['mainEntityOfPage'] = array(
					'@type' => 'WebPage',
					'@id'   => $url,
				);

				$page_node['author'] = array(
					'@type' => 'Person',
					'name'  => $author_name,
					'url'   => $author_url,
				);

				$page_node['datePublished'] = get_the_date( 'c', $post_id );
				$page_node['dateModified']  = get_the_modified_date( 'c', $post_id );

				if ( $image ) {
					$page_node['image'] = $image;
				}
			}

			$schema['@graph'][] = $page_node;

			// FIX #9: BreadcrumbList Schema
			$breadcrumbs = array(
				array( 'name' => 'Home', 'url' => $site_url ),
			);

			if ( is_singular() && $post_id ) {
				$categories = get_the_category( $post_id );
				if ( ! empty( $categories ) ) {
					$cat = $categories[0];
					$breadcrumbs[] = array( 'name' => $cat->name, 'url' => get_category_link( $cat->term_id ) );
				}
				$breadcrumbs[] = array( 'name' => get_the_title( $post_id ), 'url' => get_permalink( $post_id ) );
			} elseif ( is_category() || is_tag() || is_tax() ) {
				$term = get_queried_object();
				if ( $term ) {
					$breadcrumbs[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );
				}
			}

			if ( count( $breadcrumbs ) > 1 ) {
				$breadcrumb_items = array();
				foreach ( $breadcrumbs as $pos => $crumb ) {
					$breadcrumb_items[] = array(
						'@type'    => 'ListItem',
						'position' => $pos + 1,
						'name'     => $crumb['name'],
						'item'     => is_wp_error( $crumb['url'] ) ? '' : $crumb['url'],
					);
				}
				$schema['@graph'][] = array(
					'@type'           => 'BreadcrumbList',
					'itemListElement' => $breadcrumb_items,
				);
			}

			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
		}

		// ── LocalBusiness Schema (ALWAYS outputs — Rank Math Free doesn't include it) ──
		// FIX #10: Granular guard — LocalBusiness is independent of SEO plugin detection
		// FIX #12: Enriched with openingHours, geo, sameAs, priceRange, hasMap, email, postal address
		$biz_name    = get_option( 'asf_local_biz_name', $site_name );
		$biz_phone   = get_option( 'asf_local_biz_phone', '' );
		$biz_address = get_option( 'asf_local_biz_address', '' );
		$biz_type    = get_option( 'asf_geo_biz_type', 'LocalBusiness' );
		$biz_email   = get_option( 'asf_geo_email', '' );
		$biz_city    = get_option( 'asf_geo_placename', '' );
		$biz_region  = get_option( 'asf_geo_region', '' );
		$biz_postal  = get_option( 'asf_geo_postal', '' );
		$biz_country = get_option( 'asf_geo_country', '' );
		$biz_map_url = get_option( 'asf_geo_map_url', '' );

		if ( $biz_phone || $biz_address || $biz_city || $biz_name !== $site_name ) {
			$local_schema = array(
				'@context'  => 'https://schema.org',
				'@type'     => $biz_type ?: 'LocalBusiness',
				'@id'       => $site_url . '#localbusiness',
				'name'      => $biz_name,
				'url'       => $site_url,
			);

			if ( $biz_phone ) {
				$local_schema['telephone'] = $biz_phone;
			}

			if ( $biz_email ) {
				$local_schema['email'] = sanitize_email( $biz_email );
			}

			if ( $biz_map_url ) {
				$local_schema['hasMap'] = esc_url_raw( $biz_map_url );
			}

			$postal_addr = array(
				'@type' => 'PostalAddress',
			);
			if ( $biz_address ) $postal_addr['streetAddress']   = $biz_address;
			if ( $biz_city )    $postal_addr['addressLocality'] = $biz_city;
			if ( $biz_region )  $postal_addr['addressRegion']   = $biz_region;
			if ( $biz_postal )  $postal_addr['postalCode']      = $biz_postal;
			if ( $biz_country ) $postal_addr['addressCountry']  = $biz_country;

			if ( count( $postal_addr ) > 1 ) {
				$local_schema['address'] = $postal_addr;
			}

			if ( $image ) {
				$local_schema['image'] = $image;
			}

			// Optional enriched fields from settings
			$biz_hours    = get_option( 'asf_local_biz_hours', '' );
			$biz_lat      = get_option( 'asf_geo_lat', get_option( 'asf_local_biz_lat', '' ) );
			$biz_lng      = get_option( 'asf_geo_lng', get_option( 'asf_local_biz_lng', '' ) );
			$biz_price    = get_option( 'asf_local_biz_price_range', '' );
			$biz_same_as  = get_option( 'asf_local_biz_same_as', '' );

			if ( $biz_hours ) {
				$local_schema['openingHours'] = $biz_hours;
			}

			if ( $biz_lat && $biz_lng ) {
				$local_schema['geo'] = array(
					'@type'     => 'GeoCoordinates',
					'latitude'  => (float) $biz_lat,
					'longitude' => (float) $biz_lng,
				);
			}

			if ( $biz_price ) {
				$local_schema['priceRange'] = $biz_price;
			}

			if ( $biz_same_as ) {
				$same_as_arr = array_filter( array_map( 'trim', explode( "\n", $biz_same_as ) ) );
				if ( ! empty( $same_as_arr ) ) {
					$local_schema['sameAs'] = array_values( $same_as_arr );
				}
			}

			echo '<script type="application/ld+json">' . wp_json_encode( $local_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
		}

		echo "<!-- / All-in-One SEO Fixer -->\n\n";
	}

	/**
	 * Enforces native HTML5 loading="lazy" on all <img> and <iframe> tags in post_content on front-end.
	 * Includes LCP Hero Guard (skips 1st image so LCP metric remains fast).
	 */
	public static function filter_lazy_load_content( $content ) {
		if ( empty( $content ) || is_admin() || is_feed() ) return $content;

		$enable_img    = get_option( 'asf_enable_lazy', get_option( 'asf_opt_enable_lazy', '1' ) );
		$enable_iframe = get_option( 'asf_enable_iframe_lazy', get_option( 'asf_opt_enable_iframe_lazy', '1' ) );
		$exclude_first = get_option( 'asf_exclude_first', get_option( 'asf_opt_exclude_first_lazy', '1' ) );

		if ( $enable_img === '1' ) {
			$count = 0;
			$content = preg_replace_callback( '/<img\s+([^>]+)>/i', function( $matches ) use ( &$count, $exclude_first ) {
				$count++;
				$img_html = $matches[0];
				if ( strpos( $img_html, 'loading=' ) !== false ) return $img_html;

				if ( $count === 1 && $exclude_first === '1' ) {
					return str_replace( '<img ', '<img loading="eager" fetchpriority="high" ', $img_html );
				}

				return str_replace( '<img ', '<img loading="lazy" ', $img_html );
			}, $content );
		}

		if ( $enable_iframe === '1' ) {
			$content = preg_replace_callback( '/<iframe\s+([^>]+)>/i', function( $matches ) {
				$iframe_html = $matches[0];
				if ( strpos( $iframe_html, 'loading=' ) !== false ) return $iframe_html;
				return str_replace( '<iframe ', '<iframe loading="lazy" ', $iframe_html );
			}, $content );
		}

		return $content;
	}

	/**
	 * Filters WordPress robots.txt output to provide live custom crawler directives.
	 */
	public static function filter_robots_txt( $output, $public ) {
		$custom_robots = get_option( 'asf_robots_txt_content', '' );
		if ( ! empty( trim( (string) $custom_robots ) ) ) {
			return $custom_robots . "\n";
		}
		return $output;
	}
}
