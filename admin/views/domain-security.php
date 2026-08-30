<?php
/**
 * View: Domain Security, Spam Reputation & Security Headers Audit
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🛡️ Domain Security, Spam & Header Audit</h1>
		<p>Check HTTPS SSL encryption, HTTP Security Headers, CDN & WAF detection, server compression, and domain blacklist / spam reputation (Spamhaus, SpamCop, Barracuda).</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card">
		<h2>🔍 Run Domain & Security Audit</h2>
		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-security-btn">🛡️ Run Security & Spam Audit</button>
		</div>
		<div id="asf-sec-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-sec-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
