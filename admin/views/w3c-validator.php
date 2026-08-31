<?php
/**
 * View: W3C Nu HTML Checker Validator
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
			<h1>W3C HTML Validator & Syntax Checker</h1>
			<p class="asf-header-desc">Validate front-end HTML markup against the official W3C Nu HTML Checker API to identify broken tags, unclosed elements, and syntax errors.</p>
		</div>
	</div>

	<!-- PRE-SCAN INFORMATION CARD -->
	<div class="asf-card asf-card-info">
		<h2>About W3C HTML Validation</h2>
		<p>Clean HTML code ensures search engine crawlers (Googlebot, Bingbot) can parse page structure and content without encountering unclosed tags or invalid attributes.</p>
		<table class="widefat striped" style="margin-top:10px;">
			<thead>
				<tr>
					<th>Validation Category</th>
					<th>Check Criteria</th>
					<th>Impact on SEO & Accessibility</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Syntax Errors</strong></td>
					<td>Unclosed tags, duplicate IDs, missing quotes</td>
					<td><span class="asf-badge asf-badge-red">High Impact</span></td>
				</tr>
				<tr>
					<td><strong>HTML5 Standard</strong></td>
					<td>Valid DOCTYPE, element nesting, deprecations</td>
					<td><span class="asf-badge asf-badge-yellow">Medium Impact</span></td>
				</tr>
				<tr>
					<td><strong>Accessibility Attributes</strong></td>
					<td>ARIA role compliance and element labelling</td>
					<td><span class="asf-badge asf-badge-green">Recommended</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- VALIDATION CONTROLS -->
	<div class="asf-card">
		<h2>Validate Page Markup</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<input id="asf-w3c-url" type="url" class="asf-input" value="<?php echo esc_attr(home_url('/')); ?>" style="max-width:450px;" placeholder="https://example.com/page/" />
			<button type="button" class="button button-primary asf-w3c-scan-trigger" id="asf-w3c-btn">Run W3C HTML Audit</button>
		</div>
		<div id="asf-w3c-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-w3c-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
