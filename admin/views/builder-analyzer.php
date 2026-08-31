<?php
/**
 * View: Page Builder & Elementor Overhead Optimizer
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
			<h1>Page Builder & Elementor Optimizer</h1>
			<p class="asf-header-desc">Inspect heavy page builder overhead (Elementor, Divi, Oxygen, Beaver Builder), DOM complexity, and database JSON payload size.</p>
		</div>
	</div>

	<div class="asf-card">
		<h2>Builder Overhead Inspection</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<button class="button button-primary" id="asf-builder-scan-btn">Scan Active Builders & DOM Payload</button>
		</div>
		<div id="asf-builder-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-builder-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
