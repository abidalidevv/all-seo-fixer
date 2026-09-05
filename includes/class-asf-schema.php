<?php
/**
 * Schema JSON-LD Studio & Automated Structured Data Engine
 *
 * Automatically generates, validates, and injects Schema.org compliant
 * JSON-LD structured data graphs across all site pages:
 *  - WebSite with Sitelinks Searchbox
 *  - Organization / LocalBusiness with GeoCoordinates, Logo & sameAs
 *  - BreadcrumbList navigation hierarchy
 *  - Article & BlogPosting with Author & Publisher
 *  - Service & ProfessionalService for business landing pages
 *  - WooCommerce Product rich snippets with price & availability
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Schema {

	public static function init() {
		// Inject into front-end <head>
		add_action( 'wp_head', array( __CLASS__, 'inject_live_schema_jsonld' ), 2 );

		// AJAX endpoints
		add_action( 'wp_ajax_asf_save_schema_studio',      array( __CLASS__, 'handle_save' ) );
		add_action( 'wp_ajax_asf_generate_schema_preview', array( __CLASS__, 'handle_preview' ) );
	}

	/**
	 * Injects complete Schema.org JSON-LD graph into front-end <head>
	 */
	public static function inject_live_schema_jsonld() {
		if ( is_admin() || is_feed() ) return;

		// Master toggle check
		if ( get_option( 'asf_schema_enable', '1' ) !== '1' ) return;

		$graph = self::build_full_schema_graph();
		if ( empty( $graph ) || empty( $graph['@graph'] ) ) return;

		echo "\n<!-- All-in-One SEO Fixer — Live Schema.org JSON-LD Graph -->\n";
		echo '<script type="application/ld+json" class="asf-schema-graph">' . "\n";
		echo wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n<!-- /All-in-One SEO Fixer Schema -->\n";
	}

	/**
	 * Builds full JSON-LD Graph array
	 */
	public static function build_full_schema_graph( $target_post_id = null ) {
		$home_url  = trailingslashit( home_url() );
		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );

		// Logo URL
		$logo_id  = (int) get_theme_mod( 'custom_logo' );
		$logo_url = $logo_id ? wp_get_attachment_url( $logo_id ) : '';

		$graph_nodes = array();

		// ── 1. WebSite Node with SearchAction ─────────────────────
		if ( get_option( 'asf_schema_website_enable', '1' ) === '1' ) {
			$website_node = array(
				'@type'           => 'WebSite',
				'@id'             => $home_url . '#website',
				'url'             => $home_url,
				'name'            => $site_name,
				'description'     => $site_desc,
				'inLanguage'      => get_bloginfo( 'language' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => array(
						'@type'       => 'EntryPoint',
						'urlTemplate' => $home_url . '?s={search_term_string}',
					),
					'query-input' => 'required name=search_term_string',
				),
			);
			if ( $logo_url ) {
				$website_node['publisher'] = array( '@id' => $home_url . '#organization' );
			}
			$graph_nodes[] = $website_node;
		}

		// ── 2. Organization / LocalBusiness Node ──────────────────
		if ( get_option( 'asf_schema_org_enable', '1' ) === '1' ) {
			$biz_type    = get_option( 'asf_geo_biz_type', 'LocalBusiness' );
			$biz_name    = get_option( 'asf_local_biz_name', $site_name );
			$biz_phone   = get_option( 'asf_local_biz_phone', '' );
			$biz_email   = get_option( 'asf_geo_email', get_bloginfo('admin_email') );
			$biz_address = get_option( 'asf_local_biz_address', '' );
			$placename   = get_option( 'asf_geo_placename', '' );
			$region      = get_option( 'asf_geo_region', '' );
			$postal      = get_option( 'asf_geo_postal', '' );
			$country     = get_option( 'asf_geo_country', '' );
			$lat         = get_option( 'asf_geo_lat', '' );
			$lng         = get_option( 'asf_geo_lng', '' );
			$map_url     = get_option( 'asf_geo_map_url', '' );
			$price_range = get_option( 'asf_local_biz_price_range', '$$' );
			$hours       = get_option( 'asf_local_biz_hours', '' );
			$same_as_raw = get_option( 'asf_local_biz_same_as', '' );

			$same_as_array = array();
			if ( $same_as_raw ) {
				$lines = explode( "\n", str_replace( "\r", "", $same_as_raw ) );
				foreach ( $lines as $line ) {
					$trimmed = trim( $line );
					if ( filter_var( $trimmed, FILTER_VALIDATE_URL ) ) {
						$same_as_array[] = esc_url_raw( $trimmed );
					}
				}
			}

			$org_node = array(
				'@type'       => $biz_type ?: 'Organization',
				'@id'         => $home_url . '#organization',
				'name'        => $biz_name,
				'url'         => $home_url,
			);

			if ( $logo_url ) {
				$org_node['logo'] = array(
					'@type'      => 'ImageObject',
					'@id'        => $home_url . '#logo',
					'inLanguage' => get_bloginfo( 'language' ),
					'url'        => $logo_url,
					'caption'    => $biz_name,
				);
				$org_node['image'] = array( '@id' => $home_url . '#logo' );
			}

			if ( $biz_phone )   $org_node['telephone'] = $biz_phone;
			if ( $biz_email )   $org_node['email']     = $biz_email;
			if ( $price_range ) $org_node['priceRange'] = $price_range;
			if ( $map_url )     $org_node['hasMap']    = $map_url;

			// Address
			if ( $biz_address || $placename || $country ) {
				$org_node['address'] = array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => $biz_address,
					'addressLocality' => $placename,
					'addressRegion'   => $region,
					'postalCode'      => $postal,
					'addressCountry'  => $country,
				);
			}

			// Geo coordinates
			if ( is_numeric( $lat ) && is_numeric( $lng ) && $lat != 0 && $lng != 0 ) {
				$org_node['geo'] = array(
					'@type'     => 'GeoCoordinates',
					'latitude'  => (float) $lat,
					'longitude' => (float) $lng,
				);
			}

			// Hours
			if ( $hours ) {
				$org_node['openingHours'] = $hours;
			}

			if ( ! empty( $same_as_array ) ) {
				$org_node['sameAs'] = $same_as_array;
			}

			$graph_nodes[] = $org_node;
		}

		// ── 3. Page Specific Nodes (Breadcrumbs, Article, WebPage, Service, Product) ──
		$post_id = $target_post_id ?: ( is_singular() ? get_the_ID() : 0 );

		if ( $post_id ) {
			$post       = get_post( $post_id );
			$permalink  = get_permalink( $post_id );
			$post_title = get_the_title( $post_id );

			// Meta description
			$desc = get_post_meta( $post_id, '_asf_meta_description', true )
				?: ( get_post_meta( $post_id, 'rank_math_description', true )
				?: ( get_post_meta( $post_id, '_yoast_wpseo_metadesc', true )
				?: wp_html_excerpt( wp_strip_all_tags( $post->post_content ), 150, '…' ) ) );

			// Featured image
			$thumb_id  = get_post_thumbnail_id( $post_id );
			$thumb_url = $thumb_id ? wp_get_attachment_url( $thumb_id ) : $logo_url;

			// A. BreadcrumbList Node
			if ( get_option( 'asf_schema_breadcrumbs_enable', '1' ) === '1' && ! is_front_page() ) {
				$bc_items = array(
					array(
						'@type'    => 'ListItem',
						'position' => 1,
						'name'     => 'Home',
						'item'     => $home_url,
					),
				);

				$pos = 2;
				if ( $post->post_type === 'post' ) {
					$cats = get_the_category( $post_id );
					if ( ! empty( $cats ) ) {
						$cat = $cats[0];
						$bc_items[] = array(
							'@type'    => 'ListItem',
							'position' => $pos++,
							'name'     => $cat->name,
							'item'     => get_category_link( $cat->term_id ),
						);
					}
				} elseif ( $post->post_parent ) {
					$parent = get_post( $post->post_parent );
					if ( $parent ) {
						$bc_items[] = array(
							'@type'    => 'ListItem',
							'position' => $pos++,
							'name'     => get_the_title( $parent->ID ),
							'item'     => get_permalink( $parent->ID ),
						);
					}
				}

				$bc_items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos,
					'name'     => $post_title,
					'item'     => $permalink,
				);

				$graph_nodes[] = array(
					'@type'           => 'BreadcrumbList',
					'@id'             => $permalink . '#breadcrumb',
					'itemListElement' => $bc_items,
				);
			}

			// B. Specific Content Node
			$explicit_type = get_post_meta( $post_id, '_asf_schema_type', true );

			if ( $post->post_type === 'post' || $explicit_type === 'Article' ) {
				// Article / BlogPosting
				$author_name = get_the_author_meta( 'display_name', $post->post_author );
				$article_node = array(
					'@type'            => 'BlogPosting',
					'@id'              => $permalink . '#article',
					'isPartOf'         => array( '@id' => $permalink ),
					'headline'         => $post_title,
					'description'      => $desc,
					'datePublished'    => get_the_date( 'c', $post_id ),
					'dateModified'     => get_the_modified_date( 'c', $post_id ),
					'mainEntityOfPage' => $permalink,
					'wordCount'        => str_word_count( strip_tags( $post->post_content ) ),
					'inLanguage'       => get_bloginfo( 'language' ),
					'author'           => array(
						'@type' => 'Person',
						'name'  => $author_name ?: $site_name,
					),
					'publisher'        => array( '@id' => $home_url . '#organization' ),
				);
				if ( $thumb_url ) {
					$article_node['image'] = $thumb_url;
				}
				$graph_nodes[] = $article_node;

			} elseif ( $post->post_type === 'product' || $explicit_type === 'Product' ) {
				// WooCommerce Product
				$product_node = array(
					'@type'       => 'Product',
					'@id'         => $permalink . '#product',
					'name'        => $post_title,
					'description' => $desc,
					'url'         => $permalink,
				);
				if ( $thumb_url ) {
					$product_node['image'] = $thumb_url;
				}

				if ( function_exists( 'wc_get_product' ) ) {
					$product = wc_get_product( $post_id );
					if ( $product ) {
						$product_node['sku'] = $product->get_sku() ?: 'SKU-' . $post_id;
						$product_node['offers'] = array(
							'@type'         => 'Offer',
							'price'         => $product->get_price() ?: '0.00',
							'priceCurrency' => get_woocommerce_currency(),
							'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
							'url'           => $permalink,
						);
					}
				}
				$graph_nodes[] = $product_node;

			} elseif ( $explicit_type === 'Service' || stripos( $post->post_name, 'service' ) !== false || stripos( $post_title, 'repair' ) !== false ) {
				// Service Schema
				$service_node = array(
					'@type'        => 'Service',
					'@id'          => $permalink . '#service',
					'name'         => $post_title,
					'description'  => $desc,
					'provider'     => array( '@id' => $home_url . '#organization' ),
					'areaServed'   => get_option( 'asf_geo_placename', 'Worldwide' ),
					'url'          => $permalink,
				);
				if ( $thumb_url ) {
					$service_node['image'] = $thumb_url;
				}
				$graph_nodes[] = $service_node;

			} else {
				// Default WebPage
				$webpage_node = array(
					'@type'       => 'WebPage',
					'@id'         => $permalink . '#webpage',
					'url'         => $permalink,
					'name'        => $post_title,
					'description' => $desc,
					'isPartOf'    => array( '@id' => $home_url . '#website' ),
					'inLanguage'  => get_bloginfo( 'language' ),
				);
				if ( $thumb_url ) {
					$webpage_node['primaryImageOfPage'] = $thumb_url;
				}
				$graph_nodes[] = $webpage_node;
			}
		}

		return array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph_nodes,
		);
	}

	public static function handle_save() {
		asf_check_nonce();
		asf_cap_check();

		update_option( 'asf_schema_enable', isset( $_POST['asf_schema_enable'] ) ? '1' : '0' );
		update_option( 'asf_schema_website_enable', isset( $_POST['asf_schema_website_enable'] ) ? '1' : '0' );
		update_option( 'asf_schema_org_enable', isset( $_POST['asf_schema_org_enable'] ) ? '1' : '0' );
		update_option( 'asf_schema_breadcrumbs_enable', isset( $_POST['asf_schema_breadcrumbs_enable'] ) ? '1' : '0' );
		update_option( 'asf_schema_custom_json', trim( wp_unslash( $_POST['asf_schema_custom_json'] ?? '' ) ) );

		wp_send_json( array(
			'success' => true,
			'message' => '✨ Schema JSON-LD Studio settings successfully saved and applied live!',
		) );
	}

	public static function handle_preview() {
		asf_check_nonce();
		asf_cap_check();

		$post_id = intval( $_REQUEST['post_id'] ?? 0 );
		$graph   = self::build_full_schema_graph( $post_id ?: null );
		$json    = wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

		wp_send_json( array(
			'success' => true,
			'json'    => $json,
			'nodes'   => count( $graph['@graph'] ?? array() ),
		) );
	}
}
