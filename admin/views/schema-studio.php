<?php
/**
 * View: Schema.org JSON-LD Studio & Structured Data Engine
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

$schema_enable       = get_option( 'asf_schema_enable', '1' );
$website_enable      = get_option( 'asf_schema_website_enable', '1' );
$org_enable          = get_option( 'asf_schema_org_enable', '1' );
$breadcrumbs_enable  = get_option( 'asf_schema_breadcrumbs_enable', '1' );
$custom_json         = get_option( 'asf_schema_custom_json', '' );

$biz_name    = get_option( 'asf_local_biz_name', get_bloginfo('name') );
$biz_type    = get_option( 'asf_geo_biz_type', 'LocalBusiness' );
$biz_phone   = get_option( 'asf_local_biz_phone', '' );
$biz_address = get_option( 'asf_local_biz_address', '' );
$placename   = get_option( 'asf_geo_placename', '' );

// Recent published posts/pages for preview
$preview_posts = get_posts( array(
	'post_type'      => array( 'post', 'page' ),
	'post_status'    => 'publish',
	'posts_per_page' => 12,
) );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Schema.org JSON-LD Studio &amp; Rich Snippets</h1>
			<p class="asf-header-desc">Automatically generates and injects Google-compliant structured data graphs (Organization, LocalBusiness, Breadcrumbs, Article, Service, and WebSite with Sitelinks Searchbox).</p>
		</div>
		<div class="asf-header-actions">
			<a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(home_url('/')); ?>" target="_blank" class="button button-secondary">
				<span class="dashicons dashicons-external" style="vertical-align:text-top;font-size:16px;"></span> Google Rich Results Test
			</a>
		</div>
	</div>

	<!-- CARD 1: SCHEMA ENGINE MASTER TOGGLES -->
	<div class="asf-card">
		<div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
			<div>
				<h2 style="margin:0;">⚡ Live Schema.org Auto-Injection Engine</h2>
				<p class="description" style="margin:4px 0 0 0;">Automatically builds a unified <code>@graph</code> and outputs valid JSON-LD directly into <code>&lt;head&gt;</code> on every page.</p>
			</div>
			<label style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px;cursor:pointer;">
				<input type="checkbox" id="asf-schema-enable" <?php checked($schema_enable, '1'); ?> style="width:18px;height:18px;" />
				Active Live Injection
			</label>
		</div>

		<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:14px;margin-top:16px;">
			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
				<label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px;cursor:pointer;">
					<input type="checkbox" id="asf-schema-website" <?php checked($website_enable, '1'); ?> />
					🌐 WebSite + Sitelinks Searchbox
				</label>
				<p style="font-size:12px;color:#64748b;margin:6px 0 0 24px;">Enables Google to display an inline site search box right in SERP listings.</p>
			</div>

			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
				<label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px;cursor:pointer;">
					<input type="checkbox" id="asf-schema-org" <?php checked($org_enable, '1'); ?> />
					🏢 Organization / LocalBusiness
				</label>
				<p style="font-size:12px;color:#64748b;margin:6px 0 0 24px;">Includes name, logo, phone, address, GPS coordinates, opening hours, and social profiles.</p>
			</div>

			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
				<label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px;cursor:pointer;">
					<input type="checkbox" id="asf-schema-bc" <?php checked($breadcrumbs_enable, '1'); ?> />
					🍞 BreadcrumbList Hierarchy
				</label>
				<p style="font-size:12px;color:#64748b;margin:6px 0 0 24px;">Renders navigational breadcrumb trails on search result snippets.</p>
			</div>
		</div>

		<div style="margin-top:18px;display:flex;gap:10px;align-items:center;">
			<button type="button" class="button button-primary" id="asf-save-schema-settings-btn">💾 Save Schema Configurations</button>
			<span id="asf-schema-save-msg" style="font-size:13px;font-weight:600;"></span>
		</div>
	</div>

	<!-- CARD 2: LIVE PREVIEW & CODE INSPECTOR -->
	<div class="asf-card">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
			<div>
				<h2 style="margin:0;">🔍 Live JSON-LD Code Inspector &amp; Validator</h2>
				<p class="description" style="margin:4px 0 0 0;">Inspect the exact Schema.org JSON-LD code generated for any URL on your site:</p>
			</div>
			<div style="display:flex;gap:8px;align-items:center;">
				<select id="asf-schema-preview-target" class="asf-select" style="min-width:240px;">
					<option value="0">Homepage (<?php echo esc_html(home_url('/')); ?>)</option>
					<?php foreach ( $preview_posts as $pp ) : ?>
						<option value="<?php echo esc_attr($pp->ID); ?>"><?php echo esc_html(get_the_title($pp->ID)); ?> (<?php echo esc_html($pp->post_type); ?>)</option>
					<?php endforeach; ?>
				</select>
				<button type="button" class="button button-primary" id="asf-schema-preview-btn">🔄 Generate Preview</button>
			</div>
		</div>

		<div id="asf-schema-preview-status" style="margin-bottom:10px;"></div>

		<div style="position:relative;">
			<div style="position:absolute;top:10px;right:10px;display:flex;gap:6px;z-index:2;">
				<button type="button" class="button button-small asf-copy-btn" data-target="#asf-schema-preview-code">📋 Copy JSON-LD</button>
				<a href="#" id="asf-google-test-btn" target="_blank" class="button button-small button-secondary">🧪 Test with Google</a>
				<a href="#" id="asf-schemaorg-test-btn" target="_blank" class="button button-small button-secondary">🔍 Schema.org Validator</a>
			</div>
			<pre style="background:#0f172a;color:#38bdf8;padding:18px 16px;border-radius:8px;overflow-x:auto;max-height:420px;font-family:Consolas,Monaco,monospace;font-size:12px;line-height:1.5;margin:0;"><code id="asf-schema-preview-code">// Click "Generate Preview" above to view live JSON-LD...</code></pre>
		</div>
	</div>

	<!-- CARD 3: ENTITY PROFILE QUICK SYNC -->
	<div class="asf-card">
		<h2>🏢 Business Entity Summary (from Settings)</h2>
		<p>The structured data graph uses your verified local entity values configured in <a href="<?php echo esc_url(admin_url('admin.php?page=asf-settings')); ?>">Settings</a>:</p>
		<table class="asf-table widefat">
			<tbody>
				<tr><td style="width:200px;"><strong>Business / Entity Name</strong></td><td><code><?php echo esc_html($biz_name); ?></code></td></tr>
				<tr><td><strong>Entity Schema Type</strong></td><td><span class="asf-badge asf-badge-blue"><?php echo esc_html($biz_type); ?></span></td></tr>
				<tr><td><strong>Phone Contact</strong></td><td><code><?php echo esc_html($biz_phone ?: 'Not specified'); ?></code></td></tr>
				<tr><td><strong>Physical Address</strong></td><td><code><?php echo esc_html($biz_address ?: 'Not specified'); ?></code> (<?php echo esc_html($placename ?: 'Global'); ?>)</td></tr>
			</tbody>
		</table>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
