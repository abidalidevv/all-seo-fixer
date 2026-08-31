<?php
/**
 * View: On-Page SEO Health Score & Link Equity Estimator
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
			<h1>On-Page SEO Health & Link Equity Estimator</h1>
			<p class="asf-header-desc">Calculates an On-Page SEO Health Score (0–100) based on internal link graph connectivity, content depth, and title/meta tag completeness.</p>
		</div>
	</div>

	<div class="asf-card">
		<h2>Calculate Health Score</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<button class="button button-primary" id="asf-auth-btn">Run On-Page Health Assessment</button>
		</div>
		<div id="asf-auth-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-auth-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
