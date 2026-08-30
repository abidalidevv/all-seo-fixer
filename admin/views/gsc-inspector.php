<?php
/**
 * View: Google Search Console (GSC) Indexing API & Inspection Helper
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🔍 Google Search Console (GSC) & Indexing Helper</h1>
		<p>Submit URLs directly to Google for instant indexing via Google Search Console Indexing API v3 and monitor sitemap status.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card">
		<h2>🚀 Submit URL for Instant Google Indexing</h2>
		<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
			<input id="asf-gsc-url-input" type="url" class="asf-input" value="<?php echo esc_attr( home_url( '/' ) ); ?>" style="max-width:450px;" placeholder="https://domain.com/new-post/" />
			<button class="asf-btn-primary" id="asf-gsc-submit-btn">🚀 Submit URL to Google</button>
		</div>
		<div id="asf-gsc-status" style="margin-top:16px;"></div>
	</div>

	<!-- GSC SETUP GUIDE -->
	<div class="asf-card asf-card-info">
		<h3>🔑 Google Search Console Indexing API Setup (100% Free)</h3>
		<ol style="line-height:2.2;padding-left:20px;font-size:13.5px;color:#374151;">
			<li>Go to <a href="https://console.cloud.google.com/" target="_blank"><strong>Google Cloud Console</strong></a> → Create a Project</li>
			<li>Enable <strong>"Web Search Inspection API"</strong> and <strong>"Indexing API"</strong></li>
			<li>Go to <strong>IAM & Admin → Service Accounts</strong> → Create Service Account (Role: Owner)</li>
			<li>Keys tab → <strong>Add Key → Create New Key (JSON)</strong> → Download JSON file</li>
			<li>In <a href="https://search.google.com/search-console" target="_blank"><strong>Google Search Console</strong></a> → Settings → Users & Permissions → Add the Service Account email as <strong>Owner</strong></li>
		</ol>
	</div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
