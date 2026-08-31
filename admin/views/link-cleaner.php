<?php
/**
 * View: Broken Link & Double-Domain Typo Cleaner
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Broken Link & Double-Domain Typo Cleaner</h1>
			<p class="asf-header-desc">Scan database for double-domain URL typos (e.g. <code>domain.comhttps://domain.com/path</code>) in post content and Elementor metadata.</p>
		</div>
	</div>

	<!-- PRE-SCAN INFORMATION CARD -->
	<div class="asf-card asf-card-info">
		<h2>About Double-Domain Link Typo Repair</h2>
		<p>During domain migrations or manual copy-pasting, links can accidentally become concatenated (e.g., <code>https://old-domain.comhttps://new-domain.com/path</code>). This tool performs regex pattern matching across all database content and Elementor JSON metadata to repair them automatically.</p>
		<table class="widefat striped" style="margin-top:10px;">
			<thead>
				<tr>
					<th>Target Database Location</th>
					<th>Search Pattern</th>
					<th>Action Taken</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Post Content (<code>wp_posts</code>)</strong></td>
					<td><code>%comhttps://%</code> or <code>%orghttps://%</code></td>
					<td><span class="asf-badge asf-badge-green">Auto-Stripped & Repaired</span></td>
				</tr>
				<tr>
					<td><strong>Elementor Data (<code>wp_postmeta</code>)</strong></td>
					<td><code>_elementor_data</code> JSON payload</td>
					<td><span class="asf-badge asf-badge-green">Auto-Decoded & Re-encoded</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- SCAN CONTROLS -->
	<div class="asf-card">
		<h2>Scan Double-Domain Link Typos</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<button type="button" class="button button-primary asf-link-scan-trigger" id="asf-link-btn">Scan Database for URL Typos</button>
		</div>
		<div id="asf-link-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-link-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
