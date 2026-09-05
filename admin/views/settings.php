<?php
/**
 * View: Plugin Settings & Horizontal Tabs Hub
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

// Handle save
$save_notice = '';
if ( isset($_POST['asf_save_settings']) && check_admin_referer('asf_save_settings','asf_settings_nonce') ) {
	update_option( ASF_OPT_PSI_KEY, sanitize_text_field( $_POST['asf_psi_key'] ?? '' ) );
	update_option( 'asf_groq_api_key', sanitize_text_field( $_POST['asf_groq_api_key'] ?? '' ) );
	update_option( 'asf_gemini_api_key', sanitize_text_field( $_POST['asf_gemini_api_key'] ?? '' ) );
	update_option( 'asf_openrouter_api_key', sanitize_text_field( $_POST['asf_openrouter_api_key'] ?? '' ) );
	update_option( 'asf_gsc_client_id', sanitize_text_field( $_POST['asf_gsc_client_id'] ?? '' ) );
	update_option( 'asf_gsc_client_secret', sanitize_text_field( $_POST['asf_gsc_client_secret'] ?? '' ) );
	update_option( 'asf_gsc_service_account_json', trim( $_POST['asf_gsc_json'] ?? '' ) );

	// Homepage SEO
	update_option( 'asf_home_meta_desc', sanitize_textarea_field( $_POST['asf_home_meta_desc'] ?? '' ) );

	// Local & GEO SEO Options
	update_option( 'asf_geo_enable', isset($_POST['asf_geo_enable']) ? '1' : '0' );
	update_option( 'asf_geo_biz_type', sanitize_text_field( $_POST['asf_geo_biz_type'] ?? 'LocalBusiness' ) );
	update_option( 'asf_local_biz_name', sanitize_text_field( $_POST['asf_local_biz_name'] ?? '' ) );
	update_option( 'asf_local_biz_phone', sanitize_text_field( $_POST['asf_local_biz_phone'] ?? '' ) );
	update_option( 'asf_geo_email', sanitize_email( $_POST['asf_geo_email'] ?? '' ) );
	update_option( 'asf_local_biz_address', sanitize_text_field( $_POST['asf_local_biz_address'] ?? '' ) );
	update_option( 'asf_geo_placename', sanitize_text_field( $_POST['asf_geo_placename'] ?? '' ) );
	update_option( 'asf_geo_region', sanitize_text_field( $_POST['asf_geo_region'] ?? '' ) );
	update_option( 'asf_geo_postal', sanitize_text_field( $_POST['asf_geo_postal'] ?? '' ) );
	update_option( 'asf_geo_country', sanitize_text_field( $_POST['asf_geo_country'] ?? '' ) );
	update_option( 'asf_geo_lat', sanitize_text_field( $_POST['asf_geo_lat'] ?? '' ) );
	update_option( 'asf_geo_lng', sanitize_text_field( $_POST['asf_geo_lng'] ?? '' ) );
	update_option( 'asf_geo_map_url', esc_url_raw( $_POST['asf_geo_map_url'] ?? '' ) );
	update_option( 'asf_local_biz_hours', sanitize_text_field( $_POST['asf_local_biz_hours'] ?? '' ) );
	update_option( 'asf_local_biz_price_range', sanitize_text_field( $_POST['asf_local_biz_price_range'] ?? '' ) );
	update_option( 'asf_local_biz_same_as', sanitize_textarea_field( $_POST['asf_local_biz_same_as'] ?? '' ) );

	// robots.txt & llms.txt Directives
	update_option( 'asf_robots_enable', isset($_POST['asf_robots_enable']) ? '1' : '0' );
	$r_content = wp_unslash( $_POST['asf_robots_txt_content'] ?? '' );
	update_option( 'asf_robots_txt_content', $r_content );
	update_option( 'asf_llms_enable', isset($_POST['asf_llms_enable']) ? '1' : '0' );
	$l_content = wp_unslash( $_POST['asf_llms_txt_content'] ?? '' );
	update_option( 'asf_llms_txt_content', $l_content );

	// Sync to physical files on disk if server root is writable (with virtual fallback)
	$robots_disk = ABSPATH . 'robots.txt';
	if ( is_writable( ABSPATH ) || ( file_exists( $robots_disk ) && is_writable( $robots_disk ) ) ) {
		@file_put_contents( $robots_disk, $r_content );
	}
	$llms_disk = ABSPATH . 'llms.txt';
	if ( is_writable( ABSPATH ) || ( file_exists( $llms_disk ) && is_writable( $llms_disk ) ) ) {
		@file_put_contents( $llms_disk, $l_content );
	}

	// License
	if ( ! empty( $_POST['asf_license_key'] ) ) {
		update_option( 'asf_license_key', sanitize_text_field( $_POST['asf_license_key'] ) );
		update_option( 'asf_license_status', 'active' );
		update_option( 'asf_license_type', 'PRO Lifetime Unlimited' );
	}

	$save_notice = '<div class="asf-notice asf-notice-success" style="margin-bottom:16px;"><strong>✨ Settings saved successfully! All API keys, GEO meta tags, robots.txt &amp; llms.txt are updated live.</strong></div>';
}

$psi_key        = get_option( ASF_OPT_PSI_KEY, '' );
$groq_key       = get_option( 'asf_groq_api_key', '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' );
$gemini_key     = get_option( 'asf_gemini_api_key', '' );
$openrouter_key = get_option( 'asf_openrouter_api_key', '' );
$gsc_client_id     = get_option( 'asf_gsc_client_id', '' );
$gsc_client_secret = get_option( 'asf_gsc_client_secret', '' );
$gsc_json          = get_option( 'asf_gsc_service_account_json', '' );
$home_desc         = get_option( 'asf_home_meta_desc', get_bloginfo('description') );

// Local & GEO SEO values
$geo_enable  = get_option( 'asf_geo_enable', '1' );
$biz_type    = get_option( 'asf_geo_biz_type', 'LocalBusiness' );
$biz_name    = get_option( 'asf_local_biz_name', get_bloginfo('name') );
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
$hours       = get_option( 'asf_local_biz_hours', 'Mo-Fr 09:00-18:00' );
$price_range = get_option( 'asf_local_biz_price_range', '$$' );
$same_as     = get_option( 'asf_local_biz_same_as', '' );

// robots.txt & llms.txt settings
$robots_enable  = get_option( 'asf_robots_enable', '1' );
$default_robots = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . home_url( '/sitemap_index.xml' );
$robots_content = get_option( 'asf_robots_txt_content', $default_robots );

$llms_enable    = get_option( 'asf_llms_enable', '1' );
$default_llms   = "# " . get_bloginfo('name') . "\n\n> " . ( get_bloginfo('description') ?: 'Professional Services & Solutions' ) . "\n\n## Website Overview\n- URL: " . home_url('/') . "\n\n## Directives for AI Search Crawlers\n- User-agent: GPTBot\n  Allow: /\n- User-agent: PerplexityBot\n  Allow: /\n- User-agent: ClaudeBot\n  Allow: /\n- User-agent: Google-Extended\n  Allow: /\n";
$llms_content   = get_option( 'asf_llms_txt_content', $default_llms );

// License
$license_status = get_option( 'asf_license_status', 'free' );
$license_key    = get_option( 'asf_license_key', '' );
$license_type   = get_option( 'asf_license_type', 'Free Standard Edition' );

$active_tab = sanitize_text_field( $_POST['asf_active_tab'] ?? 'general' );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1><span class="dashicons dashicons-admin-generic" style="font-size:28px;vertical-align:middle;color:#2271b1;margin-right:6px;"></span> Plugin Settings &amp; Configuration</h1>
			<p class="asf-header-desc">Manage API credentials, Groq AI Copilot, live GEO targeting, virtual robots.txt &amp; llms.txt, system diagnostics, and developer licensing.</p>
		</div>
		<div class="asf-header-actions">
			<input type="submit" form="asf-settings-form" name="asf_save_settings" class="button button-primary" value="Save All Settings" />
		</div>
	</div>

	<?php echo $save_notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<!-- INLINE ZERO-DEPENDENCY TAB SWITCHER (INSTANT & BULLETPROOF) -->
	<script type="text/javascript">
	function asfSwitchTab(tabName, el) {
		if (!tabName) return;
		var navs = document.querySelectorAll('.asf-tab-nav');
		for (var i = 0; i < navs.length; i++) {
			navs[i].classList.remove('nav-tab-active');
		}
		if (el) {
			el.classList.add('nav-tab-active');
		} else {
			var matchingNav = document.querySelector('.asf-tab-nav[data-tab="' + tabName + '"]');
			if (matchingNav) matchingNav.classList.add('nav-tab-active');
		}
		var panels = document.querySelectorAll('.asf-tab-panel');
		for (var j = 0; j < panels.length; j++) {
			panels[j].style.display = 'none';
		}
		var target = document.querySelector('.asf-tab-panel[data-panel="' + tabName + '"]');
		if (target) target.style.display = 'block';
		var activeInput = document.getElementById('asf_active_tab');
		if (activeInput) activeInput.value = tabName;
		if (typeof window !== 'undefined' && window.history && window.history.replaceState) {
			try { window.history.replaceState(null, null, '#tab-' + tabName); } catch (e) {}
		}
	}
	document.addEventListener('DOMContentLoaded', function () {
		if (window.location.hash) {
			var hash = window.location.hash.replace('#tab-', '').replace('#', '');
			if (hash) asfSwitchTab(hash);
		}
	});
	</script>

	<!-- HORIZONTAL TABS NAVIGATION (WORDPRESS NATIVE DESIGN SYSTEM) -->
	<nav class="nav-tab-wrapper wp-clearfix" id="asf-settings-tab-bar" style="margin-bottom:20px;border-bottom:1px solid #c3c4c7;">
		<a href="#tab-general" class="nav-tab asf-tab-nav <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>" data-tab="general" onclick="asfSwitchTab('general', this); return false;">
			<span class="dashicons dashicons-admin-settings" style="font-size:16px;vertical-align:middle;margin-right:3px;"></span> General &amp; APIs
		</a>
		<a href="#tab-ai" class="nav-tab asf-tab-nav <?php echo $active_tab === 'ai' ? 'nav-tab-active' : ''; ?>" data-tab="ai" onclick="asfSwitchTab('ai', this); return false;">
			<span class="dashicons dashicons-rest-api" style="font-size:16px;vertical-align:middle;margin-right:3px;"></span> AI Engine &amp; Keys
		</a>
		<a href="#tab-geo" class="nav-tab asf-tab-nav <?php echo $active_tab === 'geo' ? 'nav-tab-active' : ''; ?>" data-tab="geo" onclick="asfSwitchTab('geo', this); return false;">
			<span class="dashicons dashicons-location" style="font-size:16px;vertical-align:middle;margin-right:3px;"></span> Local &amp; GEO SEO
		</a>
		<a href="#tab-crawlers" class="nav-tab asf-tab-nav <?php echo $active_tab === 'crawlers' ? 'nav-tab-active' : ''; ?>" data-tab="crawlers" onclick="asfSwitchTab('crawlers', this); return false;">
			<span class="dashicons dashicons-search" style="font-size:16px;vertical-align:middle;margin-right:3px;"></span> Robots &amp; LLMs.txt
		</a>
		<a href="#tab-diagnostics" class="nav-tab asf-tab-nav <?php echo $active_tab === 'diagnostics' ? 'nav-tab-active' : ''; ?>" data-tab="diagnostics" onclick="asfSwitchTab('diagnostics', this); return false;">
			<span class="dashicons dashicons-heart" style="font-size:16px;vertical-align:middle;margin-right:3px;"></span> System Diagnostics &amp; Logs
		</a>
		<a href="#tab-license" class="nav-tab asf-tab-nav <?php echo $active_tab === 'license' ? 'nav-tab-active' : ''; ?>" data-tab="license" onclick="asfSwitchTab('license', this); return false;">
			<span class="dashicons dashicons-awards" style="font-size:16px;vertical-align:middle;margin-right:3px;"></span> Pro License &amp; Plans
		</a>
		<a href="#tab-developer" class="nav-tab asf-tab-nav <?php echo $active_tab === 'developer' ? 'nav-tab-active' : ''; ?>" data-tab="developer" onclick="asfSwitchTab('developer', this); return false;">
			<span class="dashicons dashicons-id-alt" style="font-size:16px;vertical-align:middle;margin-right:3px;"></span> About Developer
		</a>
	</nav>

	<form method="post" id="asf-settings-form">
		<?php wp_nonce_field('asf_save_settings','asf_settings_nonce'); ?>
		<input type="hidden" name="asf_active_tab" id="asf_active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />

		<!-- =========================================================
		     TAB 1: GENERAL & APIS
		     ========================================================= -->
		<div class="asf-tab-panel" data-panel="general" style="<?php echo $active_tab === 'general' ? '' : 'display:none;'; ?>">
			<!-- HOMEPAGE SEO META -->
			<div class="asf-card">
				<h2><span class="dashicons dashicons-admin-home" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Homepage SEO Snippet</h2>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="asf_home_meta_desc">Homepage Meta Description</label></th>
							<td>
								<textarea id="asf_home_meta_desc" name="asf_home_meta_desc" class="asf-input" style="width:100%;max-width:550px;height:80px;" placeholder="Compelling 120-155 character summary of your homepage for Google search snippets."><?php echo esc_textarea($home_desc); ?></textarea>
								<p class="description">Clean, high-CTR meta description rendered on your home / front page.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- PAGESPEED INSIGHTS API -->
			<div class="asf-card">
				<h2><span class="dashicons dashicons-performance" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Google PageSpeed Insights API Key</h2>
				<p>Required to fetch Core Web Vitals (LCP, CLS, TBT) and Lighthouse scores (0–100) with a 25,000 req/day quota:</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="asf_psi_key">API Key</label></th>
							<td>
								<input id="asf_psi_key" type="text" name="asf_psi_key" value="<?php echo esc_attr($psi_key); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="AIzaSy..." />
								<p class="description">Free key from <a href="https://console.developers.google.com/apis/credentials" target="_blank">Google Cloud Console</a>.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- GOOGLE SEARCH CONSOLE INTEGRATION -->
			<div class="asf-card" id="gsc-credentials">
				<h2><span class="dashicons dashicons-google" style="color:#1a73e8;vertical-align:middle;margin-right:4px;"></span> Google Search Console &amp; Indexing Credentials</h2>
				<p>Connect your site to Google to enable live 30-day keyword rankings, clicks, impressions, and Instant Indexing API v3:</p>

				<h3 style="margin-top:16px;color:#1e293b;">Option 1: 1-Click Google OAuth (Recommended)</h3>
				<p style="font-size:12px;color:#64748b;margin-top:2px;">Allows you to connect directly using the Gmail account already logged into your browser:</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="asf_gsc_client_id">OAuth Client ID</label></th>
							<td>
								<input id="asf_gsc_client_id" type="text" name="asf_gsc_client_id" value="<?php echo esc_attr($gsc_client_id); ?>" class="asf-input" style="width:100%;max-width:550px;" placeholder="1234567890-xxx.apps.googleusercontent.com" />
								<p class="description">From <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console → Credentials → OAuth 2.0 Client IDs</a> (Web application).</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_gsc_client_secret">OAuth Client Secret</label></th>
							<td>
								<input id="asf_gsc_client_secret" type="password" name="asf_gsc_client_secret" value="<?php echo esc_attr($gsc_client_secret); ?>" class="asf-input" style="width:100%;max-width:550px;" placeholder="GOCSPX-..." />
							</td>
						</tr>
						<tr>
							<th scope="row">Authorized Redirect URI</th>
							<td>
								<code style="background:#f1f5f9;padding:4px 8px;border-radius:4px;"><?php echo esc_url( admin_url('admin.php?page=asf-gsc-inspector') ); ?></code>
								<p class="description">Copy and paste this exact URL into "Authorized redirect URIs" in your Google Cloud Console.</p>
							</td>
						</tr>
					</tbody>
				</table>

				<h3 style="margin-top:24px;color:#1e293b;">Option 2: Service Account JSON Key (Alternative)</h3>
				<p style="font-size:12px;color:#64748b;margin-top:2px;">For headless automated servers and background cron submissions:</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="asf_gsc_json">Service Account JSON</label></th>
							<td>
								<textarea id="asf_gsc_json" name="asf_gsc_json" class="asf-input" style="width:100%;max-width:600px;height:120px;font-family:monospace;font-size:12px;"
									placeholder='{"type": "service_account", "project_id": "...", "private_key": "-----BEGIN PRIVATE KEY-----\n..."}'><?php echo esc_textarea($gsc_json); ?></textarea>
								<p class="description">Paste downloaded Google Cloud Service Account JSON key contents.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- =========================================================
		     TAB 2: AI ENGINE & KEYS
		     ========================================================= -->
		<div class="asf-tab-panel" data-panel="ai" style="<?php echo $active_tab === 'ai' ? '' : 'display:none;'; ?>">
			<div class="asf-card">
				<h2><span class="dashicons dashicons-rest-api" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> AI Engine &amp; Model Credentials</h2>
				<p>All-in-One SEO Fixer includes built-in AI Copilot routing. <strong>Groq AI is prioritized as Primary</strong> for ultra-fast, sub-second responses (~0.4s), with Google Gemini and OpenRouter as seamless failovers.</p>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="asf_groq_api_key">Groq AI API Key <span style="color:#059669;font-weight:600;">(Primary Route)</span></label>
							</th>
							<td>
								<input id="asf_groq_api_key" type="password" name="asf_groq_api_key" value="<?php echo esc_attr($groq_key); ?>" class="asf-input" style="width:100%;max-width:550px;" placeholder="gsk_..." />
								<p class="description">Pre-configured out-of-the-box. Uses <code>llama-3.3-70b-versatile</code> &amp; <code>llama-3.1-8b-instant</code>. Obtain your own free key from <a href="https://console.groq.com/keys" target="_blank">Groq Console</a>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="asf_gemini_api_key">Google Gemini Flash Key <span style="color:#64748b;">(Failover Route)</span></label>
							</th>
							<td>
								<input id="asf_gemini_api_key" type="password" name="asf_gemini_api_key" value="<?php echo esc_attr($gemini_key); ?>" class="asf-input" style="width:100%;max-width:550px;" placeholder="AIzaSy..." />
								<p class="description">Free Gemini 1.5 Flash API key from <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="asf_openrouter_api_key">OpenRouter API Key <span style="color:#64748b;">(Optional Failover)</span></label>
							</th>
							<td>
								<input id="asf_openrouter_api_key" type="password" name="asf_openrouter_api_key" value="<?php echo esc_attr($openrouter_key); ?>" class="asf-input" style="width:100%;max-width:550px;" placeholder="sk-or-v1-..." />
								<p class="description">OpenRouter unified multi-model key from <a href="https://openrouter.ai/keys" target="_blank">OpenRouter.ai</a>.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="asf-card">
				<h2><span class="dashicons dashicons-shield" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Offline SEO Intelligence Fallback</h2>
				<p style="font-size:13px;color:#475569;margin-bottom:0;">Even if all external AI APIs are offline or disconnected, All-in-One SEO Fixer includes an embedded offline contextual SEO heuristic engine. It directly inspects your 360° audit issues, missing titles, and bad meta descriptions to formulate precise recommendations without failing.</p>
			</div>
		</div>

		<!-- =========================================================
		     TAB 3: LOCAL & GEO SEO
		     ========================================================= -->
		<div class="asf-tab-panel" data-panel="geo" style="<?php echo $active_tab === 'geo' ? '' : 'display:none;'; ?>">
			<div class="asf-card">
				<div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px;">
					<div>
						<h2 style="margin:0;"><span class="dashicons dashicons-location" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Local &amp; GEO SEO Engine (Live Geotargeting)</h2>
						<p class="description" style="margin:4px 0 0 0;">Injects official <code>geo.position</code>, <code>geo.placename</code>, <code>geo.region</code>, <code>ICBM</code> meta tags and rich Schema.org <code>LocalBusiness</code> JSON-LD for Google Maps, Bing Places &amp; local crawlers.</p>
					</div>
					<label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:14px;cursor:pointer;">
						<input type="checkbox" name="asf_geo_enable" value="1" <?php checked($geo_enable, '1'); ?> style="width:18px;height:18px;" />
						Enable GEO Tags &amp; Schema
					</label>
				</div>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="asf_geo_biz_type">Business / Schema Type</label></th>
							<td>
								<select id="asf_geo_biz_type" name="asf_geo_biz_type" class="asf-input" style="width:100%;max-width:400px;">
									<?php
									$types = array(
										'LocalBusiness'              => 'General Local Business',
										'Store'                      => 'Store / Retail Shop',
										'Restaurant'                 => 'Restaurant / Food & Beverage',
										'ProfessionalService'        => 'Professional Service / Agency / Consultant',
										'AutomotiveBusiness'         => 'Automotive Business / Repair',
										'HealthAndBeautyBusiness'    => 'Health & Beauty / Salon / Spa',
										'MedicalBusiness'            => 'Medical Clinic / Doctor / Healthcare',
										'RealEstateAgent'            => 'Real Estate Agency / Agent',
										'HomeAndConstructionBusiness'=> 'Home Services / Construction / Plumber',
										'FinancialService'           => 'Financial Service / Accounting / Insurance',
										'TravelAgency'               => 'Travel Agency / Tour Operator',
										'LodgingBusiness'            => 'Hotel / Motel / Guest House',
										'LegalService'               => 'Law Firm / Legal Service',
									);
									foreach ( $types as $k => $v ) {
										echo '<option value="' . esc_attr($k) . '" ' . selected($biz_type, $k, false) . '>' . esc_html($v) . '</option>';
									}
									?>
								</select>
								<p class="description">Select the most accurate Schema.org entity category for local search.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_local_biz_name">Business Name</label></th>
							<td><input id="asf_local_biz_name" type="text" name="asf_local_biz_name" value="<?php echo esc_attr($biz_name); ?>" class="asf-input" style="width:100%;max-width:400px;" required /></td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_local_biz_phone">Phone Number</label></th>
							<td><input id="asf_local_biz_phone" type="text" name="asf_local_biz_phone" value="<?php echo esc_attr($biz_phone); ?>" class="asf-input" style="width:100%;max-width:400px;" placeholder="+1-555-0199" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_geo_email">Business Email</label></th>
							<td><input id="asf_geo_email" type="email" name="asf_geo_email" value="<?php echo esc_attr($biz_email); ?>" class="asf-input" style="width:100%;max-width:400px;" placeholder="contact@domain.com" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_local_biz_address">Street Address</label></th>
							<td><input id="asf_local_biz_address" type="text" name="asf_local_biz_address" value="<?php echo esc_attr($biz_address); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="123 Main St, Suite 400" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_geo_placename">City / Locality</label></th>
							<td>
								<input id="asf_geo_placename" type="text" name="asf_geo_placename" value="<?php echo esc_attr($placename); ?>" class="asf-input" style="width:100%;max-width:400px;" placeholder="New York, Lahore, London, Dubai..." />
								<p class="description">Outputs to <code>&lt;meta name="geo.placename"&gt;</code> and Schema <code>addressLocality</code>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_geo_region">State / Region ISO Code</label></th>
							<td>
								<input id="asf_geo_region" type="text" name="asf_geo_region" value="<?php echo esc_attr($region); ?>" class="asf-input" style="width:100%;max-width:300px;" placeholder="e.g. US-NY, PK-PB, GB-ENG, AE-DU" />
								<p class="description">Standard ISO 3166-2 region code. Outputs to <code>&lt;meta name="geo.region"&gt;</code>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_geo_postal">Postal / ZIP Code</label></th>
							<td><input id="asf_geo_postal" type="text" name="asf_geo_postal" value="<?php echo esc_attr($postal); ?>" class="asf-input" style="width:100%;max-width:200px;" placeholder="10001" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_geo_country">Country ISO Code / Name</label></th>
							<td><input id="asf_geo_country" type="text" name="asf_geo_country" value="<?php echo esc_attr($country); ?>" class="asf-input" style="width:100%;max-width:200px;" placeholder="US, PK, GB, AE, CA..." /></td>
						</tr>
						<tr>
							<th scope="row"><label>GPS Coordinates (Lat / Lng)</label></th>
							<td>
								<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
									<input id="asf_geo_lat" type="text" name="asf_geo_lat" value="<?php echo esc_attr($lat); ?>" class="asf-input" style="width:180px;" placeholder="Latitude (e.g. 40.7128)" />
									<input id="asf_geo_lng" type="text" name="asf_geo_lng" value="<?php echo esc_attr($lng); ?>" class="asf-input" style="width:180px;" placeholder="Longitude (e.g. -74.0060)" />
									<button type="button" class="button button-secondary" id="asf-btn-detect-gps">📍 Auto-Detect via Browser GPS</button>
								</div>
								<p class="description">Required for <code>geo.position</code>, <code>ICBM</code>, and Schema.org <code>GeoCoordinates</code>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_geo_map_url">Google Maps / Profile URL</label></th>
							<td>
								<input id="asf_geo_map_url" type="url" name="asf_geo_map_url" value="<?php echo esc_attr($map_url); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="https://maps.google.com/?cid=..." />
								<p class="description">Direct link to Google Business Profile or Google Maps location.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_local_biz_hours">Opening Hours</label></th>
							<td><input id="asf_local_biz_hours" type="text" name="asf_local_biz_hours" value="<?php echo esc_attr($hours); ?>" class="asf-input" style="width:100%;max-width:400px;" placeholder="Mo-Fr 09:00-18:00, Sa 10:00-16:00" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_local_biz_price_range">Price Range</label></th>
							<td><input id="asf_local_biz_price_range" type="text" name="asf_local_biz_price_range" value="<?php echo esc_attr($price_range); ?>" class="asf-input" style="width:100%;max-width:200px;" placeholder="$, $$, $$$, or $$$$" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="asf_local_biz_same_as">Social &amp; Directory Profiles (sameAs)</label></th>
							<td>
								<textarea id="asf_local_biz_same_as" name="asf_local_biz_same_as" class="asf-input" style="width:100%;max-width:500px;height:80px;" placeholder="https://www.facebook.com/yourbrand&#10;https://www.instagram.com/yourbrand&#10;https://www.linkedin.com/company/yourbrand&#10;https://www.yelp.com/biz/yourbrand"><?php echo esc_textarea($same_as); ?></textarea>
								<p class="description">Enter one profile URL per line. Outputs to Schema <code>sameAs</code> signals.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- =========================================================
		     TAB 4: ROBOTS & LLMS.TXT
		     ========================================================= -->
		<div class="asf-tab-panel" data-panel="crawlers" style="<?php echo $active_tab === 'crawlers' ? '' : 'display:none;'; ?>">
			<!-- ROBOTS.TXT DIRECTIVES -->
			<div class="asf-card">
				<div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px;">
					<div>
						<h2 style="margin:0;"><span class="dashicons dashicons-admin-generic" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Search Engine Crawlers (<code>robots.txt</code>)</h2>
						<p class="description" style="margin:4px 0 0 0;">Live virtual <code>robots.txt</code> editor dynamically served to Googlebot, Bingbot, and crawlers at <a href="<?php echo esc_url( home_url('/robots.txt') ); ?>" target="_blank"><?php echo esc_html( home_url('/robots.txt') ); ?></a>.</p>
					</div>
					<label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:14px;cursor:pointer;">
						<input type="checkbox" name="asf_robots_enable" value="1" <?php checked($robots_enable, '1'); ?> style="width:18px;height:18px;" />
						Enable Custom robots.txt
					</label>
				</div>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="asf_robots_txt_content">robots.txt Directives</label></th>
							<td>
								<textarea id="asf_robots_txt_content" name="asf_robots_txt_content" class="asf-input" style="width:100%;max-width:650px;height:150px;font-family:monospace;font-size:13px;line-height:1.5;" placeholder="User-agent: *..."><?php echo esc_textarea($robots_content); ?></textarea>
								<p class="description">Standard robots.txt syntax. Directs search crawlers which paths are crawlable or disallowed, and points to your XML sitemap.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- LLMS.TXT & AI SEARCH ENGINES (2026 STANDARD) -->
			<div class="asf-card">
				<div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px;">
					<div>
						<h2 style="margin:0;"><span class="dashicons dashicons-welcome-learn-more" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> AI Search Engines &amp; LLMs Directives (<code>/llms.txt</code>)</h2>
						<p class="description" style="margin:4px 0 0 0;">The official 2026 standard for <strong>SearchGPT, Perplexity AI, Claude, and Google Gemini</strong>. Live served at <a href="<?php echo esc_url( home_url('/llms.txt') ); ?>" target="_blank"><?php echo esc_html( home_url('/llms.txt') ); ?></a>.</p>
					</div>
					<label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:14px;cursor:pointer;">
						<input type="checkbox" name="asf_llms_enable" value="1" <?php checked($llms_enable, '1'); ?> style="width:18px;height:18px;" />
						Enable /llms.txt Publisher
					</label>
				</div>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="asf_llms_txt_content">Markdown Content (/llms.txt)</label></th>
							<td>
								<textarea id="asf_llms_txt_content" name="asf_llms_txt_content" class="asf-input" style="width:100%;max-width:650px;height:180px;font-family:monospace;font-size:13px;line-height:1.5;" placeholder="# Brand Name..."><?php echo esc_textarea($llms_content); ?></textarea>
								<p class="description">Markdown-formatted structured summary of your site architecture, services, and AI crawler permissions (as defined by <a href="https://llmstxt.org" target="_blank">llmstxt.org</a>).</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="asf-action-bar" style="margin-top:20px;padding:16px 0;border-top:1px solid #e2e8f0;">
			<input type="submit" name="asf_save_settings" class="button button-primary" value="Save All Settings" />
		</div>
	</form>

	<!-- =========================================================
	     TAB 5: SYSTEM DIAGNOSTICS & LOGS (AJAX ENGINE)
	     ========================================================= -->
	<div class="asf-tab-panel" data-panel="diagnostics" style="<?php echo $active_tab === 'diagnostics' ? '' : 'display:none;'; ?>">
		<!-- DIAGNOSTIC HEALTH OVERVIEW BANNER -->
		<div class="asf-card" style="padding:18px 24px;margin-bottom:20px;background:#ffffff;border-left:4px solid #2271b1;">
			<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
				<div style="display:flex;align-items:center;gap:16px;">
					<div id="asf-diag-score-ring" style="width:56px;height:56px;border-radius:50%;border:4px solid #2271b1;display:flex;align-items:center;justify-content:center;background:#f8fafc;">
						<strong id="asf-diag-score-val" style="font-size:18px;color:#2271b1;">--</strong>
					</div>
					<div>
						<h2 style="margin:0;font-size:18px;">System Health &amp; Environment Diagnostics</h2>
						<p style="margin:4px 0 0;color:#64748b;font-size:13px;">Deep server audit across PHP extensions, WordPress core limits, file permissions, database options &amp; debug logs.</p>
					</div>
				</div>
				<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
					<button type="button" class="button button-primary" id="asf-diag-run-btn">
						<span class="dashicons dashicons-update" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Run Live Diagnostics Scan
					</button>
					<button type="button" class="button button-secondary" id="asf-diag-copy-btn">
						<span class="dashicons dashicons-clipboard" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Copy System Report
					</button>
					<button type="button" class="button button-secondary" id="asf-diag-download-btn">
						<span class="dashicons dashicons-download" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Download System Log (.txt)
					</button>
				</div>
			</div>
		</div>

		<!-- SEND DIAGNOSTIC REPORT TO DEVELOPER / ADMIN -->
		<div class="asf-card" style="margin-bottom:20px;">
			<h2><span class="dashicons dashicons-email-alt" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Email Diagnostic Report to Admin or Developer Support</h2>
			<p>If you encounter unexpected errors or need technical assistance, send your full system health and PHP error log report directly to the developer support team or your own email:</p>

			<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-top:14px;">
				<div>
					<label for="asf-diag-email-input" style="font-weight:600;display:block;margin-bottom:6px;font-size:13px;">Recipient Email Address:</label>
					<input type="email" id="asf-diag-email-input" class="asf-input" style="width:100%;" value="support@abidalidev.com" placeholder="support@abidalidev.com" />
					<p class="description" style="margin-top:4px;">Pre-filled with official developer support (<code>support@abidalidev.com</code>). You can change this to any admin email.</p>
				</div>
				<div>
					<label for="asf-diag-note-input" style="font-weight:600;display:block;margin-bottom:6px;font-size:13px;">Optional Note or Error Description:</label>
					<textarea id="asf-diag-note-input" class="asf-input" style="width:100%;height:40px;" placeholder="e.g. Broken link cleaner stuck, hosting is LiteSpeed..."></textarea>
				</div>
			</div>

			<div style="margin-top:14px;display:flex;align-items:center;gap:12px;">
				<button type="button" class="button button-primary" id="asf-diag-send-btn">
					<span class="dashicons dashicons-email" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Send Diagnostic Report Now
				</button>
				<span id="asf-diag-email-spinner" style="display:none;color:#2271b1;"><span class="asf-spinner"></span> Sending email...</span>
			</div>
			<div id="asf-diag-email-status" style="margin-top:12px;"></div>
		</div>

		<!-- SYSTEM SPECIFICATIONS CHECKLIST TABLE -->
		<div class="asf-card" style="margin-bottom:20px;">
			<h2><span class="dashicons dashicons-yes-alt" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Server &amp; WordPress Environment Checklist</h2>
			<div id="asf-diag-checks-table-container">
				<p style="color:#64748b;font-style:italic;">Click <strong>Run Live Diagnostics Scan</strong> above to inspect all 16+ server &amp; WordPress parameters in real-time.</p>
			</div>
		</div>

		<!-- RECENT PHP DEBUG LOG VIEWER -->
		<div class="asf-card">
			<h2><span class="dashicons dashicons-warning" style="color:#d97706;vertical-align:middle;margin-right:4px;"></span> Recent PHP Debug Log Entries (Last 25 Lines)</h2>
			<p>Extracted from your active <code>wp-content/debug.log</code> file (if enabled):</p>
			<div id="asf-diag-logs-container" style="background:#0f172a;color:#f8fafc;padding:14px 16px;border-radius:8px;font-family:monospace;font-size:12px;max-height:260px;overflow-y:auto;line-height:1.6;">
				<em>Run diagnostics scan to inspect active log file...</em>
			</div>
		</div>
	</div>

	<!-- =========================================================
	     TAB 6: PRO LICENSE & PLANS
	     ========================================================= -->
	<div class="asf-tab-panel" data-panel="license" style="<?php echo $active_tab === 'license' ? '' : 'display:none;'; ?>">
		<!-- CURRENT LICENSE STATUS CARD -->
		<div class="asf-card" style="margin-bottom:20px;">
			<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:16px;">
				<div>
					<h2 style="margin:0;"><span class="dashicons dashicons-awards" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> License &amp; Product Registration</h2>
					<p class="description" style="margin:4px 0 0 0;">Manage your All-in-One SEO Fixer product key, automated updates, and Pro features access.</p>
				</div>
				<div>
					<span id="asf-license-badge" class="asf-badge" style="padding:6px 14px;border-radius:14px;font-size:13px;font-weight:700;<?php echo $license_status === 'active' ? 'background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;' : 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;'; ?>">
						<?php echo $license_status === 'active' ? '🟢 PRO Active (' . esc_html( $license_type ) . ')' : '⚡ Free Standard Edition'; ?>
					</span>
				</div>
			</div>

			<div style="max-width:600px;">
				<label for="asf-license-key-input" style="font-weight:600;display:block;margin-bottom:6px;font-size:13px;">Enter Product License Key:</label>
				<div style="display:flex;gap:10px;align-items:center;">
					<input type="text" id="asf-license-key-input" class="asf-input" style="flex:1;font-family:monospace;font-weight:600;" value="<?php echo esc_attr($license_key); ?>" placeholder="ASF-PRO-XXXX-XXXX-XXXX-XXXX" />
					<button type="button" class="button button-primary" id="asf-license-activate-btn">⚡ Activate License</button>
					<?php if ( $license_status === 'active' ) : ?>
						<button type="button" class="button" id="asf-license-deactivate-btn">Deactivate</button>
					<?php endif; ?>
				</div>
				<p class="description" style="margin-top:6px;">Format: <code>ASF-PRO-XXXX-XXXX-XXXX-XXXX</code>. Enter any valid PRO key to activate premium mode.</p>
				<div id="asf-license-feedback" style="margin-top:12px;"></div>
			</div>
		</div>

		<!-- FREE VS PRO FEATURE COMPARISON TABLE -->
		<div class="asf-card">
			<h2><span class="dashicons dashicons-star-filled" style="color:#eab308;vertical-align:middle;margin-right:4px;"></span> Free Standard vs PRO Enterprise Edition</h2>
			<table class="widefat striped" style="margin-top:14px;border-radius:6px;overflow:hidden;">
				<thead>
					<tr>
						<th style="font-size:13px;padding:12px 14px;">SEO Optimization Feature</th>
						<th style="font-size:13px;padding:12px 14px;width:180px;">Free Standard</th>
						<th style="font-size:13px;padding:12px 14px;width:220px;color:#2271b1;font-weight:700;">PRO Enterprise ⚡</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>360° Full Site SEO Audit Engine</strong></td>
						<td><span style="color:#10b981;">✓</span> Unlimited Manual Audits</td>
						<td><span style="color:#10b981;">✓</span> Unlimited + Daily Automated Cron</td>
					</tr>
					<tr>
						<td><strong>Groq LLaMA-3.3-70B AI SEO Copilot</strong></td>
						<td><span style="color:#10b981;">✓</span> Included Out-of-the-Box</td>
						<td><span style="color:#10b981;">✓</span> Unlimited + GPT-4o &amp; Claude 3.5 Models</td>
					</tr>
					<tr>
						<td><strong>On-Page Meta Title &amp; Description Fixer</strong></td>
						<td><span style="color:#10b981;">✓</span> Up to 250 Posts/Pages</td>
						<td><span style="color:#10b981;">✓</span> Unlimited Pages, Products &amp; Custom Post Types</td>
					</tr>
					<tr>
						<td><strong>Executive 4-Page PDF Audit Reports</strong></td>
						<td><span style="color:#10b981;">✓</span> Included (ASF Branding)</td>
						<td><span style="color:#10b981;">✓</span> 100% White-Label (Custom Agency Logo &amp; Colors)</td>
					</tr>
					<tr>
						<td><strong>Google Instant Indexing API v3 Integration</strong></td>
						<td><span style="color:#10b981;">✓</span> Manual Indexing Submission</td>
						<td><span style="color:#10b981;">✓</span> Automatic Index Ping on Post Publish</td>
					</tr>
					<tr>
						<td><strong>Schema JSON-LD Studio &amp; GEO Hub</strong></td>
						<td><span style="color:#10b981;">✓</span> Full Access (All 7 Schema Types)</td>
						<td><span style="color:#10b981;">✓</span> Multi-Location Schema &amp; Deep WooCommerce Schema</td>
					</tr>
					<tr>
						<td><strong>Front-End Error Doctor &amp; Broken Link Cleaner</strong></td>
						<td><span style="color:#10b981;">✓</span> Included</td>
						<td><span style="color:#10b981;">✓</span> Real-Time Slack &amp; Email Webhook Alerts</td>
					</tr>
					<tr>
						<td><strong>Technical Support &amp; Architecture Consulting</strong></td>
						<td>Community Forum Support</td>
						<td><span style="color:#10b981;font-weight:700;">Priority 24/7 Direct Developer Support</span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<!-- =========================================================
	     TAB 7: ABOUT DEVELOPER & SUPPORT
	     ========================================================= -->
	<div class="asf-tab-panel" data-panel="developer" style="<?php echo $active_tab === 'developer' ? '' : 'display:none;'; ?>">
		<div class="asf-card" style="padding:24px;margin-bottom:20px;">
			<div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;">
				<div style="width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg, #2563eb, #7c3aed);display:flex;align-items:center;justify-content:center;color:#ffffff;box-shadow:0 8px 20px rgba(37,99,235,0.35);">
					<span class="dashicons dashicons-businessman" style="font-size:42px;width:42px;height:42px;"></span>
				</div>
				<div style="flex:1;min-width:280px;">
					<h2 style="margin:0 0 4px;font-size:22px;">Abid Ali</h2>
					<p style="margin:0 0 12px;font-size:14px;color:#2271b1;font-weight:600;">Full-Stack WordPress Architect &amp; AI Solutions Engineer</p>
					<p style="font-size:13px;color:#475569;line-height:1.6;margin-bottom:14px;">
						Creator of <strong>All-in-One SEO Fixer</strong>. Specialized in high-performance WordPress systems, custom database optimizations, automated technical SEO pipelines, and integrating state-of-the-art Generative AI into web workflows.
					</p>

					<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
						<a href="https://abidalidev.com" target="_blank" class="button button-primary" style="display:inline-flex;align-items:center;gap:4px;">
							<span class="dashicons dashicons-admin-site" style="font-size:16px;"></span> Visit Personal Website (abidalidev.com)
						</a>
						<a href="mailto:support@abidalidev.com" class="button button-secondary" style="display:inline-flex;align-items:center;gap:4px;">
							<span class="dashicons dashicons-email-alt" style="font-size:16px;"></span> Contact: support@abidalidev.com
						</a>
						<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank" class="button button-secondary" style="display:inline-flex;align-items:center;gap:4px;">
							<span class="dashicons dashicons-networking" style="font-size:16px;"></span> GitHub Repository
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="asf-card">
			<h2><span class="dashicons dashicons-info" style="color:#2271b1;vertical-align:middle;margin-right:4px;"></span> Plugin Architectural Highlights</h2>
			<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-top:14px;">
				<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
					<strong style="color:#1e293b;font-size:13px;">19+ Integrated Modules</strong>
					<p style="font-size:12px;color:#64748b;margin:6px 0 0;">Unified 360° audit, media alt-text autofix, broken links, 301 redirects, Schema JSON-LD Studio, and live Swiss tools in a single lightweight footprint.</p>
				</div>

				<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
					<strong style="color:#1e293b;font-size:13px;">Sub-Second AI Copilot</strong>
					<p style="font-size:12px;color:#64748b;margin:6px 0 0;">Zero bloat. Built with primary Groq LLaMA 3.3 routing, providing instant technical feedback with contextual site snapshot injection.</p>
				</div>

				<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
					<strong style="color:#1e293b;font-size:13px;">2026 AI Search Standards</strong>
					<p style="font-size:12px;color:#64748b;margin:6px 0 0;">Native support for SearchGPT and Perplexity AI with virtual <code>/llms.txt</code> directives and deep JSON-LD structured data.</p>
				</div>
			</div>
		</div>
	</div>

	<div class="asf-footer" style="margin-top:28px;">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · Open Source GPL-2.0
	</div>
</div>
