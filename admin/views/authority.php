<?php
/**
 * View: Domain Authority (DA / DR Estimator) & Link Equity
 * (Ahrefs + Moz + Semrush style metrics)
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>📈 Domain Rating (DR) & Link Equity Estimator</h1>
		<p>Estimate your On-Page Domain Rating (0–100), analyze internal link equity distribution, outbound link ratio, and content depth metrics (Ahrefs & Moz style).</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card">
		<h2>📊 Calculate Domain Rating & Link Equity</h2>
		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-auth-btn">📈 Run Domain Authority Audit</button>
		</div>
		<div id="asf-auth-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-auth-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
