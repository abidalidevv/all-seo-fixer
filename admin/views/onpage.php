<?php
/**
 * View: On-Page SEO Checker & Auditor
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
			<h1>On-Page SEO Checker & Tag Auditor</h1>
			<p class="asf-header-desc">Per-page audit for title length, meta description quality, H1/H2 headings, missing image alt text, thin content (<300 words), and Schema markup.</p>
		</div>
	</div>

	<div class="asf-card">
		<h2>Scan All Published Pages</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<button type="button" class="button button-primary asf-onpage-scan-trigger" id="asf-onpage-btn">Scan All Pages for SEO Errors</button>
		</div>
		<div id="asf-onpage-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-onpage-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
