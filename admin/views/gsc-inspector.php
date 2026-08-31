<?php
/**
 * View: Google Search Console Indexing API & Rank Tracker
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Google Search Console & Indexing Helper</h1>
			<p class="asf-header-desc">Submit URLs directly to Google via Indexing API v3 and fetch live 30-day keyword rankings with average position, clicks, and impressions.</p>
		</div>
		<div class="asf-header-actions">
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>" class="button button-secondary">API Settings</a>
		</div>
	</div>

	<!-- INSTANT INDEXING SUBMITTER -->
	<div class="asf-card">
		<h2>Submit URL for Instant Google Indexing</h2>
		<p>Submits URL directly to Google's Indexing API v3 using your Service Account JSON credentials:</p>
		<div class="asf-action-bar" style="margin-top:0;">
			<input id="asf-gsc-url-input" type="url" class="asf-input" value="<?php echo esc_attr( home_url( '/' ) ); ?>" style="max-width:450px;" placeholder="https://domain.com/new-post/" />
			<button class="button button-primary" id="asf-gsc-submit-btn">Submit URL to Google</button>
		</div>
		<div id="asf-gsc-status" style="margin-top:16px;"></div>
	</div>

	<!-- LIVE GSC KEYWORD RANK TRACKER -->
	<div class="asf-card">
		<h2>Live GSC Keyword Rank Tracker</h2>
		<p>Query Search Console Search Analytics API for your site's top 50 search keywords, average rank position (Rank #1–100), clicks, and impressions:</p>
		<div class="asf-action-bar" style="margin-top:0;">
			<button class="button button-primary" id="asf-gsc-rank-btn">Fetch Live Keyword Ranks</button>
		</div>
		<div id="asf-gsc-rank-status" style="margin-top:16px;"></div>
		<div id="asf-gsc-rank-results" style="margin-top:16px;"></div>
	</div>

	<!-- SETUP GUIDE -->
	<div class="asf-card">
		<h2>Service Account Setup Guide</h2>
		<ol style="line-height:2;padding-left:20px;font-size:13px;color:var(--asf-text-body);">
			<li>Go to <a href="https://console.cloud.google.com/" target="_blank"><strong>Google Cloud Console</strong></a> → Create a Project</li>
			<li>Enable <strong>"Web Search Inspection API"</strong> and <strong>"Indexing API"</strong></li>
			<li>Go to <strong>IAM & Admin → Service Accounts</strong> → Create Service Account (Role: Owner)</li>
			<li>Keys tab → <strong>Add Key → Create New Key (JSON)</strong> → Download JSON file</li>
			<li>In <a href="https://search.google.com/search-console" target="_blank"><strong>Google Search Console</strong></a> → Settings → Users & Permissions → Add Service Account email as <strong>Owner</strong></li>
			<li>Paste JSON key in <a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>"><strong>Settings</strong></a></li>
		</ol>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
