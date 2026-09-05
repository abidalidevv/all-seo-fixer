<?php
/**
 * View: Page Builder & Elementor Overhead Optimizer
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
			<h1>Page Builder & Elementor Bloat Optimizer</h1>
			<p class="asf-header-desc">Inspect heavy page builder overhead (Elementor, Divi, Oxygen, Beaver Builder), DOM complexity depth, and database JSON payload size.</p>
		</div>
		<div class="asf-header-actions">
			<button class="button button-primary" id="asf-builder-scan-btn">
				<span class="dashicons dashicons-hammer" style="vertical-align:text-top;margin-top:1px;"></span> Scan Active Builders & Bloat
			</button>
		</div>
	</div>

	<!-- PRE-SCAN INFORMATION CARD -->
	<div class="asf-card asf-card-info">
		<h2><span class="dashicons dashicons-performance" style="font-size:20px;vertical-align:middle;color:#2271b1;"></span> Why Page Builder Optimization Matters for Core Web Vitals</h2>
		<p>Visual page builders often generate deeply nested HTML wrappers (DIV ceilings of 20+ levels) and store megabytes of serialized JSON in <code>wp_postmeta</code>, hurting <strong>Interaction to Next Paint (INP)</strong> and <strong>Largest Contentful Paint (LCP)</strong>.</p>
		<table class="widefat striped" style="margin-top:12px;">
			<thead>
				<tr>
					<th>Inspection Vector</th>
					<th>Target Threshold</th>
					<th>Performance Impact</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>DOM Node Count</strong></td>
					<td>&lt; 800 Nodes / Page</td>
					<td><span class="asf-badge asf-badge-green">Speeds up browser DOM parsing & layout</span></td>
				</tr>
				<tr>
					<td><strong>Maximum DOM Depth</strong></td>
					<td>&lt; 15 Nested Levels</td>
					<td><span class="asf-badge asf-badge-green">Reduces CSS recalculation cost & INP delay</span></td>
				</tr>
				<tr>
					<td><strong>Database JSON Payload (<code>_elementor_data</code>)</strong></td>
					<td>&lt; 500 KB / Post</td>
					<td><span class="asf-badge asf-badge-green">Reduces PHP memory usage & TTFB</span></td>
				</tr>
				<tr>
					<td><strong>Unused Font / Icon Enqueues</strong></td>
					<td>Eicons & Google Fonts Dequeued</td>
					<td><span class="asf-badge asf-badge-green">Saves 150KB+ render-blocking HTTP requests</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- SCAN STATUS & CONTROLS -->
	<div id="asf-builder-status"></div>

	<!-- RESULTS CONTAINER -->
	<div id="asf-builder-results">
		<div class="asf-card">
			<h2>Ready to Inspect Page Builders</h2>
			<p>Click <strong>"Scan Active Builders & Bloat"</strong> above to measure database payload size, DOM node depth, and optimize active builders.</p>
		</div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
