<?php
/**
 * View: All-in-One SEO Fixer - Onboarding & Setup Wizard
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

// Pre-load current or detected settings
$site_name   = get_bloginfo( 'name' );
$site_desc   = get_bloginfo( 'description' );
$admin_email = get_option( 'admin_email', '' );
$site_url    = home_url( '/' );

$clean_brand = get_option( 'asf_clean_brand_name', '' );
if ( empty( $clean_brand ) ) {
	$clean_brand = class_exists( 'ASF_AIChatbot' ) ? ASF_AIChatbot::get_clean_brand_name() : $site_name;
}

$site_niche  = get_option( 'asf_site_niche', 'local_service' );
$entity_type = get_option( 'asf_entity_type', 'Organization' );

// Step 2 values
$biz_phone   = get_option( 'asf_local_biz_phone', '' );
$biz_email   = get_option( 'asf_local_biz_email', $admin_email );
$biz_address = get_option( 'asf_local_biz_address', '' );
$biz_city    = get_option( 'asf_local_biz_city', '' );
$biz_state   = get_option( 'asf_local_biz_state', '' );
$biz_zip     = get_option( 'asf_local_biz_zip', '' );
$biz_country = get_option( 'asf_local_biz_country', '' );
$placename   = get_option( 'asf_geo_placename', '' );
$biz_lat     = get_option( 'asf_geo_lat', get_option( 'asf_local_biz_lat', '' ) );
$biz_lng     = get_option( 'asf_geo_lng', get_option( 'asf_local_biz_lng', '' ) );
$biz_hours   = get_option( 'asf_local_biz_hours', 'Mo-Fr 09:00-18:00' );
$price_range = get_option( 'asf_local_biz_price_range', '$$' );

// Step 3 values
$site_logo   = get_option( 'asf_site_logo', '' );
if ( empty( $site_logo ) ) {
	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$site_logo = wp_get_attachment_image_url( $custom_logo_id, 'full' );
	}
}
$og_default  = get_option( 'asf_og_default_image', '' );
$fb_url      = get_option( 'asf_social_facebook', '' );
$ig_url      = get_option( 'asf_social_instagram', '' );
$tw_handle   = get_option( 'asf_social_twitter', '' );
$li_url      = get_option( 'asf_social_linkedin', '' );
$yt_url      = get_option( 'asf_social_youtube', '' );
$gmb_url     = get_option( 'asf_social_gmb', '' );

// Step 4 values
$groq_key    = get_option( 'asf_groq_api_key', '' );
$gemini_key  = get_option( 'asf_gemini_api_key', '' );
$auto_schema = get_option( 'asf_enable_auto_schema', '1' );
$auto_og     = get_option( 'asf_og_enable', '1' );
$auto_robots = get_option( 'asf_enable_virtual_robots', '1' );
?>

<div class="wrap asf-wrap asf-wizard-container" style="max-width:960px;margin:30px auto;padding:0 15px;">

	<!-- WIZARD TOP HEADER -->
	<div class="asf-card" style="margin-bottom:20px;padding:24px;border-radius:12px;background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;box-shadow:0 10px 25px -5px rgba(0,0,0,0.2);">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
			<div style="display:flex;align-items:center;gap:14px;">
				<div style="width:48px;height:48px;background:linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:24px;box-shadow:0 4px 12px rgba(99,102,241,0.4);">
					⚡
				</div>
				<div>
					<h1 style="margin:0;font-size:22px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px;">
						All-in-One SEO Fixer
						<span style="font-size:11px;font-weight:600;background:rgba(255,255,255,0.15);padding:3px 8px;border-radius:20px;letter-spacing:0.5px;text-transform:uppercase;">Setup Wizard</span>
					</h1>
					<p style="margin:4px 0 0 0;font-size:13px;color:#94a3b8;">
						Configure your website niche, Schema markup, social branding, and AI engine in under 3 minutes.
					</p>
				</div>
			</div>
			<div>
				<button type="button" id="asf-wizard-skip-btn" class="button" style="background:rgba(255,255,255,0.1);color:#cbd5e1;border:1px solid rgba(255,255,255,0.2);padding:6px 14px;border-radius:6px;font-size:12px;cursor:pointer;">
					Skip Setup &amp; Go to Dashboard &rarr;
				</button>
			</div>
		</div>

		<!-- PROGRESS STEPPER -->
		<div style="margin-top:24px;">
			<div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:8px;text-align:center;">
				<div class="asf-wizard-step-indicator active" data-step="1" style="cursor:pointer;">
					<div class="asf-step-num" style="width:32px;height:32px;margin:0 auto 6px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;background:#6366f1;color:#fff;transition:all 0.3s;">1</div>
					<span style="font-size:12px;font-weight:600;display:block;">Website Niche</span>
				</div>
				<div class="asf-wizard-step-indicator" data-step="2" style="cursor:pointer;">
					<div class="asf-step-num" style="width:32px;height:32px;margin:0 auto 6px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;background:rgba(255,255,255,0.15);color:#cbd5e1;transition:all 0.3s;">2</div>
					<span style="font-size:12px;font-weight:600;display:block;">Local &amp; Contact</span>
				</div>
				<div class="asf-wizard-step-indicator" data-step="3" style="cursor:pointer;">
					<div class="asf-step-num" style="width:32px;height:32px;margin:0 auto 6px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;background:rgba(255,255,255,0.15);color:#cbd5e1;transition:all 0.3s;">3</div>
					<span style="font-size:12px;font-weight:600;display:block;">Social &amp; Logo</span>
				</div>
				<div class="asf-wizard-step-indicator" data-step="4" style="cursor:pointer;">
					<div class="asf-step-num" style="width:32px;height:32px;margin:0 auto 6px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;background:rgba(255,255,255,0.15);color:#cbd5e1;transition:all 0.3s;">4</div>
					<span style="font-size:12px;font-weight:600;display:block;">AI Supercharger</span>
				</div>
				<div class="asf-wizard-step-indicator" data-step="5" style="cursor:pointer;">
					<div class="asf-step-num" style="width:32px;height:32px;margin:0 auto 6px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;background:rgba(255,255,255,0.15);color:#cbd5e1;transition:all 0.3s;">5</div>
					<span style="font-size:12px;font-weight:600;display:block;">Ready &amp; Launch</span>
				</div>
			</div>

			<!-- PROGRESS BAR -->
			<div style="background:rgba(255,255,255,0.1);height:6px;border-radius:3px;margin-top:16px;overflow:hidden;">
				<div id="asf-wizard-progress-bar" style="background:linear-gradient(90deg, #6366f1, #38bdf8);height:100%;width:20%;transition:width 0.4s ease;"></div>
			</div>
		</div>
	</div>

	<!-- WIZARD STEP CARDS -->
	<form id="asf-wizard-form">

		<!-- STEP 1: WEBSITE PROFILE & NICHE -->
		<div class="asf-wizard-step-pane" id="asf-wizard-step-1" style="display:block;">
			<div class="asf-card" style="padding:28px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
				<div style="border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:20px;">
					<h2 style="margin:0;font-size:18px;color:#0f172a;display:flex;align-items:center;gap:8px;">
						🏢 Step 1: Website Profile &amp; Industry Niche
					</h2>
					<p class="description" style="margin:4px 0 0 0;">
						Tell the SEO engine what kind of website this is so it generates exact industry-tailored metadata and Schema.
					</p>
				</div>

				<!-- NICHE SELECTION GRID -->
				<label style="font-weight:600;color:#1e293b;display:block;margin-bottom:8px;">Select Industry / Business Category:</label>
				<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-bottom:22px;">
					<?php
					$niches = array(
						'local_service' => array( 'icon' => '🛠️', 'title' => 'Local Service / Repairs', 'desc' => 'Clinics, Contractors, Repair shops, Salons' ),
						'ecommerce'     => array( 'icon' => '🛒', 'title' => 'E-Commerce / Store', 'desc' => 'WooCommerce, physical or digital products' ),
						'corporate'     => array( 'icon' => '🏢', 'title' => 'Corporate / Agency', 'desc' => 'B2B, Legal, Finance, Consulting services' ),
						'healthcare'    => array( 'icon' => '🩺', 'title' => 'Healthcare & Medical', 'desc' => 'Dental clinics, Doctors, Wellness centers' ),
						'blog'          => array( 'icon' => '📰', 'title' => 'Blog & Publishing', 'desc' => 'News, personal blogs, affiliate reviews' ),
						'restaurant'    => array( 'icon' => '🍽️', 'title' => 'Restaurant & Food', 'desc' => 'Cafes, Bakeries, Catering, Dining' ),
					);
					foreach ( $niches as $n_key => $n_info ) :
						$is_checked = ( $site_niche === $n_key );
					?>
					<label class="asf-niche-card" style="border:2px solid <?php echo $is_checked ? '#6366f1' : '#e2e8f0'; ?>;border-radius:10px;padding:14px;cursor:pointer;background:<?php echo $is_checked ? '#f5f3ff' : '#ffffff'; ?>;transition:all 0.2s;display:flex;flex-direction:column;gap:4px;">
						<input type="radio" name="asf_site_niche" value="<?php echo esc_attr($n_key); ?>" <?php checked($is_checked); ?> style="margin-bottom:6px;" />
						<div style="font-size:22px;margin-bottom:2px;"><?php echo $n_info['icon']; ?></div>
						<strong style="color:#0f172a;font-size:14px;"><?php echo esc_html($n_info['title']); ?></strong>
						<span style="font-size:11px;color:#64748b;line-height:1.3;"><?php echo esc_html($n_info['desc']); ?></span>
					</label>
					<?php endforeach; ?>
				</div>

				<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
					<div>
						<label for="asf_clean_brand_name" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
							Official Brand Name (Clean): <span style="color:#dc2626;">*</span>
						</label>
						<input type="text" id="asf_clean_brand_name" name="asf_clean_brand_name" value="<?php echo esc_attr($clean_brand); ?>" class="asf-input" style="width:100%;font-size:14px;font-weight:600;" placeholder="e.g. Acme Studio" required />
						<span style="font-size:11px;color:#64748b;display:block;margin-top:3px;">
							Short brand name without slogans. Used in title tags (e.g. <code> | Acme Studio</code>).
						</span>
					</div>
					<div>
						<label for="asf_entity_type" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
							Site Entity Representation:
						</label>
						<select id="asf_entity_type" name="asf_entity_type" class="asf-select" style="width:100%;height:38px;">
							<option value="Organization" <?php selected($entity_type, 'Organization'); ?>>Organization / Company / Business</option>
							<option value="Person" <?php selected($entity_type, 'Person'); ?>>Person / Individual / Solo Professional</option>
						</select>
						<span style="font-size:11px;color:#64748b;display:block;margin-top:3px;">
							Used for Google Knowledge Graph and Schema.org publisher type.
						</span>
					</div>
				</div>

				<div>
					<label for="asf_site_tagline" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
						Business Tagline or Mission Statement:
					</label>
					<input type="text" id="asf_site_tagline" name="asf_site_tagline" value="<?php echo esc_attr($site_desc); ?>" class="asf-input" style="width:100%;font-size:13px;" placeholder="e.g. Certified electronics repair specialists and tech solutions" />
					<span style="font-size:11px;color:#64748b;display:block;margin-top:3px;">
						Provides context to AI models and search engines about what you do.
					</span>
				</div>
			</div>
		</div>

		<!-- STEP 2: BUSINESS & LOCAL SEO DETAILS -->
		<div class="asf-wizard-step-pane" id="asf-wizard-step-2" style="display:none;">
			<div class="asf-card" style="padding:28px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
				<div style="border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:20px;">
					<h2 style="margin:0;font-size:18px;color:#0f172a;display:flex;align-items:center;gap:8px;">
						📍 Step 2: Business Contact &amp; Local SEO Details
					</h2>
					<p class="description" style="margin:4px 0 0 0;">
						Essential data for Google Maps, LocalBusiness Schema JSON-LD, and click-to-call mobile CTR.
					</p>
				</div>

				<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
					<div>
						<label for="asf_local_biz_phone" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
							Primary Business Phone Number:
						</label>
						<input type="text" id="asf_local_biz_phone" name="asf_local_biz_phone" value="<?php echo esc_attr($biz_phone); ?>" class="asf-input" style="width:100%;font-size:14px;" placeholder="+1 (555) 019-2834 or +971 50 123 4567" />
						<span style="font-size:11px;color:#64748b;display:block;margin-top:3px;">
							Enables click-to-call in Google Search rich snippets.
						</span>
					</div>
					<div>
						<label for="asf_local_biz_email" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
							Customer Support Email:
						</label>
						<input type="email" id="asf_local_biz_email" name="asf_local_biz_email" value="<?php echo esc_attr($biz_email); ?>" class="asf-input" style="width:100%;font-size:14px;" placeholder="contact@example.com" />
						<span style="font-size:11px;color:#64748b;display:block;margin-top:3px;">
							Public contact email for citations and Schema contactPoint.
						</span>
					</div>
				</div>

				<div style="margin-bottom:16px;">
					<label for="asf_local_biz_address" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
						Physical Street Address:
					</label>
					<input type="text" id="asf_local_biz_address" name="asf_local_biz_address" value="<?php echo esc_attr($biz_address); ?>" class="asf-input" style="width:100%;font-size:14px;" placeholder="123 Main Street, Suite 400" />
				</div>

				<div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;margin-bottom:18px;">
					<div>
						<label for="asf_local_biz_city" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">City:</label>
						<input type="text" id="asf_local_biz_city" name="asf_local_biz_city" value="<?php echo esc_attr($biz_city); ?>" class="asf-input" style="width:100%;" placeholder="New York / Dubai" />
					</div>
					<div>
						<label for="asf_local_biz_state" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">State / Region:</label>
						<input type="text" id="asf_local_biz_state" name="asf_local_biz_state" value="<?php echo esc_attr($biz_state); ?>" class="asf-input" style="width:100%;" placeholder="NY / DXB" />
					</div>
					<div>
						<label for="asf_local_biz_zip" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">Postal / Zip:</label>
						<input type="text" id="asf_local_biz_zip" name="asf_local_biz_zip" value="<?php echo esc_attr($biz_zip); ?>" class="asf-input" style="width:100%;" placeholder="10001" />
					</div>
					<div>
						<label for="asf_local_biz_country" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">Country (2-letter):</label>
						<input type="text" id="asf_local_biz_country" name="asf_local_biz_country" value="<?php echo esc_attr($biz_country); ?>" class="asf-input" style="width:100%;" placeholder="US, AE, GB, CA" />
					</div>
				</div>

				<!-- GPS & LOCATION COORDINATES -->
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:18px;">
					<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
						<div>
							<strong style="color:#0f172a;font-size:14px;">📍 Geographic Coordinates &amp; Placename</strong>
							<p style="margin:2px 0 0 0;font-size:12px;color:#64748b;">Powers geo.position, ICBM tags, and local map ranking signals.</p>
						</div>
						<button type="button" id="asf-wizard-gps-btn" class="button button-secondary" style="display:flex;align-items:center;gap:6px;color:#2563eb;font-weight:600;">
							<span class="dashicons dashicons-location-alt" style="font-size:16px;vertical-align:middle;"></span>
							Auto-Detect GPS Coordinates
						</button>
					</div>

					<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
						<div>
							<label for="asf_geo_placename" style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">Placename / Region:</label>
							<input type="text" id="asf_geo_placename" name="asf_geo_placename" value="<?php echo esc_attr($placename); ?>" class="asf-input" style="width:100%;" placeholder="e.g. Manhattan, New York" />
						</div>
						<div>
							<label for="asf_geo_lat" style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">Latitude:</label>
							<input type="text" id="asf_geo_lat" name="asf_geo_lat" value="<?php echo esc_attr($biz_lat); ?>" class="asf-input" style="width:100%;" placeholder="e.g. 40.7128" />
						</div>
						<div>
							<label for="asf_geo_lng" style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;">Longitude:</label>
							<input type="text" id="asf_geo_lng" name="asf_geo_lng" value="<?php echo esc_attr($biz_lng); ?>" class="asf-input" style="width:100%;" placeholder="e.g. -74.0060" />
						</div>
					</div>
					<div id="asf-wizard-gps-status" style="margin-top:8px;font-size:12px;display:none;"></div>
				</div>

				<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
					<div>
						<label for="asf_local_biz_hours" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
							Operating Hours:
						</label>
						<input type="text" id="asf_local_biz_hours" name="asf_local_biz_hours" value="<?php echo esc_attr($biz_hours); ?>" class="asf-input" style="width:100%;" placeholder="Mo-Fr 09:00-18:00, Sa 10:00-16:00" />
					</div>
					<div>
						<label for="asf_local_biz_price_range" style="font-weight:600;display:block;margin-bottom:4px;color:#1e293b;">
							Price Indicator:
						</label>
						<select id="asf_local_biz_price_range" name="asf_local_biz_price_range" class="asf-select" style="width:100%;height:38px;">
							<option value="$" <?php selected($price_range, '$'); ?>>$ (Budget Friendly)</option>
							<option value="$$" <?php selected($price_range, '$$'); ?>>$$ (Moderate / Standard)</option>
							<option value="$$$" <?php selected($price_range, '$$$'); ?>>$$$ (High End / Premium)</option>
							<option value="$$$$" <?php selected($price_range, '$$$$'); ?>>$$$$ (Luxury / Enterprise)</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- STEP 3: SOCIAL PROFILES & OPENGRAPH BRANDING -->
		<div class="asf-wizard-step-pane" id="asf-wizard-step-3" style="display:none;">
			<div class="asf-card" style="padding:28px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
				<div style="border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:20px;">
					<h2 style="margin:0;font-size:18px;color:#0f172a;display:flex;align-items:center;gap:8px;">
						🌐 Step 3: Social Profiles &amp; OpenGraph Branding
					</h2>
					<p class="description" style="margin:4px 0 0 0;">
						Control how your website appears when shared on Facebook, WhatsApp, LinkedIn, and X/Twitter.
					</p>
				</div>

				<!-- LOGO & OG IMAGE PICKER -->
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
					<!-- LOGO -->
					<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
						<label style="font-weight:600;color:#0f172a;display:block;margin-bottom:6px;">Official Website Logo:</label>
						<div style="display:flex;gap:12px;align-items:center;">
							<div id="asf-wizard-logo-preview" style="width:70px;height:70px;border-radius:8px;border:1px dashed #cbd5e1;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;">
								<?php if ( ! empty( $site_logo ) ) : ?>
									<img src="<?php echo esc_url($site_logo); ?>" style="max-width:100%;max-height:100%;object-fit:contain;" />
								<?php else : ?>
									<span style="color:#94a3b8;font-size:24px;">🖼️</span>
								<?php endif; ?>
							</div>
							<div style="flex:1;">
								<input type="text" id="asf_site_logo" name="asf_site_logo" value="<?php echo esc_url($site_logo); ?>" class="asf-input" style="width:100%;margin-bottom:6px;font-size:12px;" placeholder="https://example.com/logo.png" />
								<button type="button" class="button button-secondary asf-wizard-media-btn" data-target="#asf_site_logo" data-preview="#asf-wizard-logo-preview">
									📁 Choose Logo from Media
								</button>
							</div>
						</div>
					</div>

					<!-- OPENGRAPH FALLBACK IMAGE -->
					<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
						<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
							<label style="font-weight:600;color:#0f172a;">Default Social Share Image (OG):</label>
							<span style="font-size:10px;background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-weight:600;">1200 &times; 630 px</span>
						</div>
						<div style="display:flex;gap:12px;align-items:center;">
							<div id="asf-wizard-og-preview" style="width:90px;height:55px;border-radius:8px;border:1px dashed #cbd5e1;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;">
								<?php if ( ! empty( $og_default ) ) : ?>
									<img src="<?php echo esc_url($og_default); ?>" style="max-width:100%;max-height:100%;object-fit:cover;" />
								<?php else : ?>
									<span style="color:#94a3b8;font-size:20px;">📱</span>
								<?php endif; ?>
							</div>
							<div style="flex:1;">
								<input type="text" id="asf_og_default_image" name="asf_og_default_image" value="<?php echo esc_url($og_default); ?>" class="asf-input" style="width:100%;margin-bottom:6px;font-size:12px;" placeholder="https://example.com/social-card.jpg" />
								<button type="button" class="button button-secondary asf-wizard-media-btn" data-target="#asf_og_default_image" data-preview="#asf-wizard-og-preview">
									📁 Choose Social Image
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- SOCIAL PROFILES (Schema sameAs) -->
				<label style="font-weight:600;color:#0f172a;display:block;margin-bottom:8px;">Brand Social Profiles (Schema.org <code>sameAs</code>):</label>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
					<div>
						<label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:2px;">Facebook Page URL:</label>
						<input type="url" name="asf_social_facebook" value="<?php echo esc_url($fb_url); ?>" class="asf-input" style="width:100%;" placeholder="https://facebook.com/yourbrand" />
					</div>
					<div>
						<label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:2px;">Instagram Profile URL:</label>
						<input type="url" name="asf_social_instagram" value="<?php echo esc_url($ig_url); ?>" class="asf-input" style="width:100%;" placeholder="https://instagram.com/yourbrand" />
					</div>
					<div>
						<label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:2px;">X (Twitter) Handle or URL:</label>
						<input type="text" name="asf_social_twitter" value="<?php echo esc_attr($tw_handle); ?>" class="asf-input" style="width:100%;" placeholder="@yourbrand or https://x.com/yourbrand" />
					</div>
					<div>
						<label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:2px;">LinkedIn Company URL:</label>
						<input type="url" name="asf_social_linkedin" value="<?php echo esc_url($li_url); ?>" class="asf-input" style="width:100%;" placeholder="https://linkedin.com/company/yourbrand" />
					</div>
					<div>
						<label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:2px;">YouTube Channel URL:</label>
						<input type="url" name="asf_social_youtube" value="<?php echo esc_url($yt_url); ?>" class="asf-input" style="width:100%;" placeholder="https://youtube.com/@yourbrand" />
					</div>
					<div>
						<label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:2px;">Google Business Profile / Maps Link:</label>
						<input type="url" name="asf_social_gmb" value="<?php echo esc_url($gmb_url); ?>" class="asf-input" style="width:100%;" placeholder="https://maps.google.com/..." />
					</div>
				</div>
			</div>
		</div>

		<!-- STEP 4: AI SUPERCHARGER & AUTOMATION -->
		<div class="asf-wizard-step-pane" id="asf-wizard-step-4" style="display:none;">
			<div class="asf-card" style="padding:28px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
				<div style="border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:20px;">
					<h2 style="margin:0;font-size:18px;color:#0f172a;display:flex;align-items:center;gap:8px;">
						🤖 Step 4: AI SEO Supercharger &amp; Automations
					</h2>
					<p class="description" style="margin:4px 0 0 0;">
						Connect state-of-the-art AI for 1-click meta title &amp; description writing, and enable core automated SEO safeguards.
					</p>
				</div>

				<!-- GROQ AI CARD -->
				<div style="background:linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);border:1px solid #86efac;border-radius:10px;padding:18px;margin-bottom:20px;">
					<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
						<div style="display:flex;align-items:center;gap:8px;">
							<strong style="color:#14532d;font-size:15px;">⚡ Groq AI (LLaMA 3.3-70B) — Recommended Primary Engine</strong>
							<span style="background:#22c55e;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;">100% FREE &bull; ULTRA FAST</span>
						</div>
						<a href="https://console.groq.com/keys" target="_blank" style="font-size:12px;font-weight:600;color:#15803d;text-decoration:none;">
							&rarr; Get Free API Key at console.groq.com
						</a>
					</div>
					<p style="margin:0 0 10px 0;font-size:12px;color:#166534;">
						Groq provides ultra-low latency response times (under 500ms) with zero monthly fee. Powers the floating copilot and title optimizer.
					</p>
					<div style="display:flex;gap:8px;align-items:center;">
						<input type="password" id="asf_wizard_groq_key" name="asf_groq_api_key" value="<?php echo esc_attr($groq_key); ?>" class="asf-input" style="flex:1;font-family:monospace;font-size:13px;" placeholder="gsk_..." />
						<button type="button" id="asf-wizard-test-groq-btn" class="button button-secondary" style="font-weight:600;">
							⚡ Test Groq Key
						</button>
					</div>
					<div id="asf-wizard-groq-status" style="margin-top:8px;font-size:12px;display:none;"></div>
				</div>

				<!-- GEMINI OPTIONAL -->
				<div style="margin-bottom:22px;">
					<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
						<label for="asf_wizard_gemini_key" style="font-weight:600;color:#1e293b;font-size:13px;">
							Google Gemini API Key (Optional Backup):
						</label>
						<a href="https://aistudio.google.com/app/apikey" target="_blank" style="font-size:11px;color:#2563eb;text-decoration:none;">
							Get Gemini Key &rarr;
						</a>
					</div>
					<input type="password" id="asf_wizard_gemini_key" name="asf_gemini_api_key" value="<?php echo esc_attr($gemini_key); ?>" class="asf-input" style="width:100%;font-family:monospace;font-size:13px;" placeholder="AIzaSy..." />
				</div>

				<!-- AUTOMATION SWITCHES -->
				<label style="font-weight:600;color:#0f172a;display:block;margin-bottom:10px;">Automated Core SEO Protections:</label>
				<div style="display:flex;flex-direction:column;gap:10px;">
					<label style="display:flex;align-items:center;gap:10px;cursor:pointer;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;">
						<input type="checkbox" name="asf_enable_auto_schema" value="1" <?php checked($auto_schema, '1'); ?> />
						<div>
							<strong style="color:#0f172a;font-size:13px;">Automated Schema.org JSON-LD</strong>
							<span style="display:block;font-size:11px;color:#64748b;">Injects Google-compliant structured data (Organization, LocalBusiness, WebSite, Breadcrumbs).</span>
						</div>
					</label>
					<label style="display:flex;align-items:center;gap:10px;cursor:pointer;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;">
						<input type="checkbox" name="asf_enable_opengraph" value="1" <?php checked($auto_og, '1'); ?> />
						<div>
							<strong style="color:#0f172a;font-size:13px;">OpenGraph &amp; Twitter Card Meta Tags</strong>
							<span style="display:block;font-size:11px;color:#64748b;">Ensures high-CTR social previews when links are shared on Facebook, WhatsApp, X, and LinkedIn.</span>
						</div>
					</label>
					<label style="display:flex;align-items:center;gap:10px;cursor:pointer;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;">
						<input type="checkbox" name="asf_enable_virtual_robots" value="1" <?php checked($auto_robots, '1'); ?> />
						<div>
							<strong style="color:#0f172a;font-size:13px;">Virtual Robots.txt &amp; AI Search Crawler Directives (/llms.txt)</strong>
							<span style="display:block;font-size:11px;color:#64748b;">Empowers Generative Engine Optimization (GEO) for ChatGPT Search, Perplexity, and Claude.</span>
						</div>
					</label>
				</div>
			</div>
		</div>

		<!-- STEP 5: REVIEW, CONFIRMATION & BASELINE AUDIT -->
		<div class="asf-wizard-step-pane" id="asf-wizard-step-5" style="display:none;">
			<div class="asf-card" style="padding:28px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
				<div style="text-align:center;max-width:600px;margin:0 auto 24px;">
					<div style="width:64px;height:64px;background:linear-gradient(135deg, #10b981 0%, #059669 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 12px;box-shadow:0 6px 16px rgba(16,185,129,0.3);">
						🎉
					</div>
					<h2 style="margin:0;font-size:22px;color:#0f172a;font-weight:700;">You're All Set &amp; Ready to Launch!</h2>
					<p style="margin:6px 0 0 0;font-size:14px;color:#64748b;">
						Here is a quick summary of your configured SEO profile. Click the button below to save all settings and launch your initial 360&deg; Baseline SEO Audit.
					</p>
				</div>

				<!-- CONFIGURATION SUMMARY TABLE -->
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:18px;margin-bottom:24px;">
					<h3 style="margin:0 0 12px 0;font-size:14px;color:#0f172a;font-weight:700;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">
						📋 Configured Profile Summary:
					</h3>
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
						<div><strong>Official Brand Name:</strong> <span id="asf-sum-brand"><?php echo esc_html($clean_brand); ?></span></div>
						<div><strong>Entity Representation:</strong> <span id="asf-sum-entity"><?php echo esc_html($entity_type); ?></span></div>
						<div><strong>Primary Phone:</strong> <span id="asf-sum-phone"><?php echo esc_html($biz_phone ?: 'Not specified'); ?></span></div>
						<div><strong>Contact Email:</strong> <span id="asf-sum-email"><?php echo esc_html($biz_email); ?></span></div>
						<div><strong>Location:</strong> <span id="asf-sum-loc"><?php echo esc_html($placename ?: ( $biz_city ?: 'Configured' )); ?></span></div>
						<div><strong>Operating Hours:</strong> <span id="asf-sum-hours"><?php echo esc_html($biz_hours); ?></span></div>
						<div><strong>OpenGraph Image:</strong> <span id="asf-sum-og"><?php echo ! empty($og_default) ? '✅ Uploaded' : 'Theme logo fallback'; ?></span></div>
						<div><strong>AI Engine Connected:</strong> <span id="asf-sum-ai"><?php echo ! empty($groq_key) ? '✅ Groq LLaMA 3.3 Active' : 'Offline contextual engine'; ?></span></div>
					</div>
				</div>

				<!-- LAUNCH CTA -->
				<div style="text-align:center;">
					<button type="button" id="asf-wizard-finish-btn" class="button button-primary" style="background:linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);border:none;padding:12px 28px;font-size:16px;font-weight:700;border-radius:8px;box-shadow:0 6px 18px rgba(99,102,241,0.4);cursor:pointer;">
						🚀 Complete Setup &amp; Run 360&deg; Baseline SEO Audit
					</button>
					<span style="display:block;margin-top:8px;font-size:11px;color:#94a3b8;">
						Marks setup as complete and redirects directly to your SEO Command Center.
					</span>
				</div>
			</div>
		</div>

		<!-- WIZARD BOTTOM CONTROLS -->
		<div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;">
			<button type="button" id="asf-wizard-prev-btn" class="button button-secondary" style="display:none;padding:8px 16px;font-weight:600;">
				&larr; Previous Step
			</button>
			<div id="asf-wizard-step-label" style="font-size:13px;font-weight:600;color:#64748b;margin:0 auto;">
				Step 1 of 5
			</div>
			<button type="button" id="asf-wizard-next-btn" class="button button-primary" style="padding:8px 20px;font-weight:700;background:#4f46e5;border-color:#4338ca;">
				Save &amp; Continue &rarr;
			</button>
		</div>

	</form>

</div>
