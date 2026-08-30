<?php
/**
 * View: Page Builder & Elementor Overhead Inspector & Optimizer
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🧱 Page Builder & Elementor Overhead Optimizer</h1>
		<p>Detect active page builders (Elementor, Divi, Oxygen, Beaver Builder, WPBakery, Brizy), analyze database JSON bloat, identify heavy DOM pages, and strip unused font/icon scripts in one click.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card">
		<h2>🔍 Run Page Builder & Elementor Audit</h2>
		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-builder-scan-btn">🧱 Scan Page Builder Overhead</button>
		</div>
		<div id="asf-builder-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-builder-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
