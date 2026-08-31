<?php
/**
 * View: Security, CDN, Header & Domain Reputation Audit
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
			<h1>Security, Headers & Domain Audit</h1>
			<p class="asf-header-desc">Inspect SSL encryption status, HTTP Security Headers (HSTS, CSP), CDN detection, server compression, and live DNSBL email/domain blacklists.</p>
		</div>
	</div>

	<div class="asf-card">
		<h2>Security Audit</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<button class="button button-primary" id="asf-security-btn">Audit Security Headers & DNSBL Blacklists</button>
		</div>
		<div id="asf-sec-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-sec-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
