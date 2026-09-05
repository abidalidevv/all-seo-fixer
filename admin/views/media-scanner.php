<?php
/**
 * View: Media Scanner & 1-Click Auto Alt-Text Generator
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
			<h1>Media Library Scanner & Alt-Text Generator</h1>
			<p class="asf-header-desc">Inspect media attachments for missing alt attributes and orphan unused files. Auto-generates clean Alt text from file titles or page context.</p>
		</div>
	</div>

	<div class="asf-card">
		<h2>Scan Media Library &amp; Alt Text Health</h2>
		<div class="asf-action-bar" style="margin-top:0;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
			<button class="button button-primary asf-media-scan-trigger" id="asf-media-scan-btn">Scan Media Library (All Images)</button>
			<button class="button button-secondary" id="asf-media-auto-alt-btn">⚡ 1-Click Scan &amp; Fill Missing Alt Texts</button>
			<div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
				<label for="asf-media-filter" style="font-weight:600;font-size:13px;">Filter View:</label>
				<select id="asf-media-filter" class="asf-select" style="min-width:180px;">
					<option value="all">All Images</option>
					<option value="missing_alt">Missing Alt Text Only</option>
					<option value="orphans">Unused / Orphan Images Only</option>
					<option value="in_use">In-Use Images Only</option>
				</select>
			</div>
		</div>
		<div id="asf-media-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-media-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
