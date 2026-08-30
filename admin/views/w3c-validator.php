<?php
/**
 * View: W3C HTML Code Quality & Markup Validator
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>✅ W3C HTML Code Quality & Validator</h1>
		<p>Powered by the official, 100% free W3C Nu HTML Checker service (no API key required). Audit HTML syntax errors, unclosed tags, duplicate element IDs, and invalid markup that hinder search engine crawlers.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card">
		<h2>🔍 Audit HTML Syntax with W3C Service</h2>
		<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
			<input id="asf-w3c-url-input" type="url" class="asf-input" value="<?php echo esc_attr( home_url( '/' ) ); ?>" style="max-width:450px;" placeholder="https://domain.com/page/" />
			<button class="asf-btn-primary" id="asf-w3c-btn">✅ Run W3C HTML Audit</button>
		</div>
		<div id="asf-w3c-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-w3c-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
