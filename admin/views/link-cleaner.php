<?php
/**
 * View: Broken Link & Typo Cleaner
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🔗 Broken Link & Double-Domain Typo Cleaner</h1>
		<p>Detects and auto-fixes malformed URLs like <code style="background:rgba(255,255,255,0.15);padding:1px 6px;border-radius:4px;">domain.comhttps://domain.com/page/</code> in Elementor, Gutenberg, and Classic Editor content and postmeta.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card asf-card-info">
		<h3>ℹ️ What This Fixes</h3>
		<p>When a URL is saved with a double domain prefix (e.g., <code>https://site.comhttps://site.com/page/</code>), it creates a broken link that returns a 404 or redirect loop. This scanner finds all instances in the WordPress database and auto-corrects them.</p>
		<p style="margin:0;"><strong>Safe to run:</strong> A detailed before/after report is shown before any changes are confirmed.</p>
	</div>

	<div class="asf-card">
		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-clean-btn">🔍 Scan & Auto-Fix Broken Links</button>
		</div>
		<div id="asf-clean-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-clean-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
