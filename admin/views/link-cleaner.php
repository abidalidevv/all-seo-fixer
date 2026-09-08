<?php
/**
 * View: Broken Link & Double-Domain Typo Cleaner
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
			<h1>Broken Link & Double-Domain Typo Cleaner</h1>
			<p class="asf-header-desc">Scan database for double-domain URL typos (e.g. <code>domain.comhttps://domain.com/path</code>) in post content and Elementor metadata.</p>
		</div>
	</div>

	<!-- PRE-SCAN INFORMATION CARD -->
	<div class="asf-card asf-card-info">
		<h2>About Double-Domain Link Typo Repair</h2>
		<p>During domain migrations or manual copy-pasting, links can accidentally become concatenated (e.g., <code>https://old-domain.comhttps://new-domain.com/path</code>). This tool performs regex pattern matching across all database content and Elementor JSON metadata to repair them automatically.</p>
		<table class="widefat striped" style="margin-top:10px;">
			<thead>
				<tr>
					<th>Target Database Location</th>
					<th>Search Pattern</th>
					<th>Action Taken</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Post Content (<code>wp_posts</code>)</strong></td>
					<td><code>%comhttps://%</code> or <code>%orghttps://%</code></td>
					<td><span class="asf-badge asf-badge-green">Auto-Stripped & Repaired</span></td>
				</tr>
				<tr>
					<td><strong>Elementor Data (<code>wp_postmeta</code>)</strong></td>
					<td><code>_elementor_data</code> JSON payload</td>
					<td><span class="asf-badge asf-badge-green">Auto-Decoded & Re-encoded</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- SCAN CONTROLS -->
	<div class="asf-card">
		<h2>Scan Double-Domain Link Typos</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<button type="button" class="button button-primary asf-link-scan-trigger" id="asf-link-btn">Scan Database for URL Typos</button>
		</div>
		<div id="asf-link-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-link-results"></div>

	<!-- SMART INTERNAL LINKING & CATEGORY SILO HUB -->
	<?php
	$silo_stats = class_exists( 'ASF_StatsTracker' ) ? ASF_StatsTracker::get_stats() : array();
	?>
	<div class="asf-card">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
			<h2 style="margin:0;">Smart Internal Linking &amp; Category Silo Hub</h2>
			<span class="asf-badge asf-badge-green" style="font-size:12px;padding:4px 10px;">
				🔗 <strong id="asf-silo-links-count"><?php echo esc_html( $silo_stats['links_generated'] ?? 0 ); ?></strong> Contextual Links Generated
			</span>
		</div>
		<p style="margin-top:10px;">Automatically scans all published pages, posts, and WooCommerce products. Matches text against your category names (e.g. <em>iPhone Repair</em>, <em>MacBook Screen</em>) and pillar services to intelligently insert contextual <code>&lt;a href="..."&gt;</code> internal links without modifying headings or existing links.</p>
		<div class="asf-action-bar" style="margin-top:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
			<button type="button" class="button button-primary" id="asf-silo-autolink-btn">⚡ Auto-Generate Internal Links Site-Wide (Silo Engine)</button>
			<button type="button" class="button button-secondary" id="asf-view-targets-btn">👁️ View Indexed Category Targets &amp; Keywords</button>
		</div>
		<div id="asf-silo-progress-bar" style="display:none;margin-top:14px;background:#e2e8f0;border-radius:4px;overflow:hidden;height:10px;">
			<div id="asf-silo-progress-fill" style="background:#10b981;height:100%;width:0%;transition:width 0.2s;"></div>
		</div>
		<div id="asf-silo-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-silo-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
