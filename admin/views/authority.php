<?php
/**
 * View: On-Page SEO Health Score & Link Equity Estimator
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
			<h1>On-Page SEO Health & Link Equity Estimator</h1>
			<p class="asf-header-desc">Calculates an On-Page SEO Health Score (0–100) based on internal link graph connectivity, content depth, and title/meta tag completeness.</p>
		</div>
		<div class="asf-header-actions">
			<button class="button button-primary" id="asf-auth-btn">
				<span class="dashicons dashicons-analytics" style="vertical-align:text-top;margin-top:1px;"></span> Run Health Assessment
			</button>
		</div>
	</div>

	<!-- PRE-SCAN INFORMATION CARD -->
	<div class="asf-card asf-card-info">
		<h2><span class="dashicons dashicons-chart-pie" style="font-size:20px;vertical-align:middle;color:#2271b1;"></span> How On-Page Domain Rating is Calculated</h2>
		<p>Unlike third-party metrics (Ahrefs DR / Moz DA) that rely on external backlinks, this engine calculates an <strong>On-Page Technical Domain Rating (0–100)</strong> based on signals directly controlled on your server:</p>
		<table class="widefat striped" style="margin-top:12px;">
			<thead>
				<tr>
					<th>Signal Category</th>
					<th>Weight</th>
					<th>Optimization Impact</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Internal Link Graph & Connectivity</strong></td>
					<td>35%</td>
					<td><span class="asf-badge asf-badge-green">Distributes Link Equity / PageRank</span></td>
				</tr>
				<tr>
					<td><strong>Content Depth & Word Count (>600 words)</strong></td>
					<td>25%</td>
					<td><span class="asf-badge asf-badge-green">Prevents Thin Content Penalties</span></td>
				</tr>
				<tr>
					<td><strong>Schema & Structured Data Coverage</strong></td>
					<td>20%</td>
					<td><span class="asf-badge asf-badge-green">Unlocks Google Rich Snippets</span></td>
				</tr>
				<tr>
					<td><strong>Meta Tags & H1 Completeness</strong></td>
					<td>20%</td>
					<td><span class="asf-badge asf-badge-green">Improves Organic CTR</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- CONTROLS & STATUS -->
	<div id="asf-auth-status"></div>

	<!-- RESULTS CONTAINER -->
	<div id="asf-auth-results">
		<div class="asf-card">
			<h2>Ready to Assess On-Page Authority</h2>
			<p>Click <strong>"Run Health Assessment"</strong> above to crawl internal link graphs, calculate internal PageRank distribution, and compute your On-Page Domain Rating.</p>
		</div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
