<?php
/**
 * View: PageSpeed Insights
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

$psi_key = get_option( ASF_OPT_PSI_KEY, '' );
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>⚡ PageSpeed Insights & Core Web Vitals</h1>
		<p>Powered by Google's free PageSpeed Insights API v5. Get Performance, Accessibility, Best Practices, and SEO scores (0–100) + Core Web Vitals for any URL.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<?php if ( ! $psi_key ) : ?>
	<div class="asf-card asf-card-error">
		<h3 class="asf-err">🔑 Free API Key Required</h3>
		<ol style="margin:0 0 14px;padding-left:20px;color:#555;line-height:2;">
			<li>Visit <a href="https://console.developers.google.com/apis/credentials" target="_blank"><strong>console.developers.google.com</strong></a></li>
			<li>Create or select a Project → click <strong>Enable APIs & Services</strong></li>
			<li>Search for <strong>"PageSpeed Insights API"</strong> → Enable</li>
			<li>Go to <strong>Credentials</strong> → + Create Credentials → API Key → Copy</li>
			<li>Paste key in <a href="<?php echo esc_url(admin_url('admin.php?page=asf-settings')); ?>"><strong>⚙️ Settings</strong></a></li>
		</ol>
		<p style="margin:0;font-size:13px;color:#6b7280;">100% free. No credit card. 25,000 requests/day.</p>
	</div>
	<?php endif; ?>

	<div class="asf-card">
		<h2>🔍 Test a URL</h2>
		<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
			<input id="asf-psi-url" type="url" class="asf-input" value="<?php echo esc_attr(home_url('/')); ?>" style="max-width:380px;" placeholder="https://example.com/page/" />
			<select id="asf-psi-strategy" class="asf-input asf-select" style="max-width:160px;">
				<option value="MOBILE">📱 Mobile</option>
				<option value="DESKTOP">🖥️ Desktop</option>
			</select>
			<button class="asf-btn-primary" id="asf-psi-run-btn" <?php echo !$psi_key ? 'disabled title="Add API key in Settings first"' : ''; ?>>
				⚡ Run PageSpeed Test
			</button>
		</div>
		<div id="asf-psi-status" style="margin-top:16px;"></div>
	</div>

	<!-- Score Legend -->
	<div class="asf-card asf-card-info" style="padding:16px 22px;">
		<div style="display:flex;gap:20px;flex-wrap:wrap;align-items:center;font-size:13px;">
			<strong>Score Legend:</strong>
			<span><span class="asf-badge asf-badge-green">● 90–100</span> Good</span>
			<span><span class="asf-badge asf-badge-yellow">● 50–89</span> Needs Improvement</span>
			<span><span class="asf-badge asf-badge-red">● 0–49</span> Poor</span>
		</div>
	</div>

	<div id="asf-psi-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
