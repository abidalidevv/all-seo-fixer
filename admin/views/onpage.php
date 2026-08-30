<?php
/**
 * View: On-Page SEO Checker
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

$post_count = wp_count_posts('post')->publish + wp_count_posts('page')->publish;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🔧 On-Page SEO Checker</h1>
		<p>Scans every page & post for <?php echo (int)$post_count; ?> SEO factors including title length, meta descriptions, H1/H2 headings, image alt texts, content length, schema markup, internal links, and noindex flags.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<!-- What Is Checked -->
	<div class="asf-card asf-card-info">
		<h3>📋 Checks Performed Per Page</h3>
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:6px;font-size:13px;">
			<?php
			$checks = array(
				'Title Tag (missing, too short < 30, too long > 60)',
				'Meta Description (missing, short < 80, long > 160)',
				'H1 Heading (missing or multiple H1s)',
				'H2 Subheadings (on long content)',
				'Image Alt Text (per image in content)',
				'Content Length (thin < 300 words on posts)',
				'Schema JSON-LD markup presence',
				'NOINDEX flag detection',
				'Internal Links count',
			);
			foreach ( $checks as $c ) {
				echo '<div style="padding:7px 10px;background:#eff6ff;border-radius:5px;">✓ ' . esc_html($c) . '</div>';
			}
			?>
		</div>
	</div>

	<div class="asf-card">
		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-onpage-run-btn">🔧 Run On-Page SEO Scan</button>
		</div>
		<div id="asf-onpage-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-onpage-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
