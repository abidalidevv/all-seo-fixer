<?php
/**
 * View: Media & Orphan Scanner
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🖼️ Image & Orphan Media Scanner</h1>
		<p>Cross-references every image in your Media Library against post_content, Elementor JSON data, featured images, and site logos. Safely trash unused files in one click (fully reversible).</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card asf-card-info">
		<h3>🔍 How Orphan Detection Works</h3>
		<p>An image is marked as <strong>"Orphan"</strong> only if it is NOT found in any of these places:</p>
		<ul style="margin:0;padding-left:20px;line-height:2;font-size:13.5px;">
			<li>Post / Page <strong>content</strong> (Gutenberg, Classic Editor)</li>
			<li><strong>Elementor</strong> <code>_elementor_data</code> JSON</li>
			<li><strong>Featured Image</strong> (<code>_thumbnail_id</code> meta)</li>
			<li><strong>Site Logo</strong> (theme mod <code>custom_logo</code>)</li>
		</ul>
		<p style="margin:8px 0 0;"><strong>Trashing is reversible:</strong> Items go to WordPress Trash and can be restored anytime from Media → Trash.</p>
	</div>

	<div class="asf-card">
		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-media-btn">🔍 Scan Media Library</button>
			<button class="asf-btn-secondary" id="asf-auto-alt-btn">✨ Auto-Generate Missing Alt Texts</button>
		</div>
		<div id="asf-media-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-media-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
