<?php
/**
 * View: Lazy Load Images & Media Performance Optimizer
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
			<h1>Lazy Load Images & Media Speed Optimizer</h1>
			<p class="asf-header-desc">Enforce native HTML5 <code>loading="lazy"</code> on all image and iframe tags to reduce initial page payload, boost Core Web Vitals, and improve mobile PageSpeed scores.</p>
		</div>
	</div>

	<!-- PRE-SCAN INFORMATION CARD -->
	<div class="asf-card asf-card-info">
		<h2><span class="dashicons dashicons-performance" style="font-size:20px;vertical-align:middle;color:#2271b1;"></span> Why Native Lazy Loading Matters for SEO</h2>
		<p>Images account for up to 60% of total page weight on average. Native <code>loading="lazy"</code> defers offscreen images until users scroll near them, significantly improving <strong>Largest Contentful Paint (LCP)</strong> and saving server bandwidth.</p>
		<table class="widefat striped" style="margin-top:12px;">
			<thead>
				<tr>
					<th>Optimization Rule</th>
					<th>Standard Behavior</th>
					<th>SEO & Speed Benefit</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Content Images (<code>&lt;img&gt;</code>)</strong></td>
					<td>Adds <code>loading="lazy"</code> attribute</td>
					<td><span class="asf-badge asf-badge-green">Deploys Offscreen Deferral</span></td>
				</tr>
				<tr>
					<td><strong>Embedded Videos (<code>&lt;iframe&gt;</code>)</strong></td>
					<td>Adds <code>loading="lazy"</code> to YouTube/Vimeo embeds</td>
					<td><span class="asf-badge asf-badge-green">Reduces Third-Party JS</span></td>
				</tr>
				<tr>
					<td><strong>First Image (LCP Guard)</strong></td>
					<td>Excludes 1st featured image from lazy load</td>
					<td><span class="asf-badge asf-badge-green">Prevents LCP Delay</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- LAZY LOAD CONTROLS -->
	<div class="asf-card">
		<h2>Native Lazy Load Status & Settings</h2>
		<p>Configure automated lazy loading enforcement across all post content, pages, and media attachments:</p>

		<div class="asf-notice asf-notice-success" style="margin-bottom:16px;">
			✓ Native WordPress <code>loading="lazy"</code> is active on <code>img</code> and <code>iframe</code> elements in <strong><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></strong>.
		</div>

<?php
$enable_lazy   = get_option( 'asf_enable_lazy', '1' );
$enable_iframe = get_option( 'asf_enable_iframe_lazy', '1' );
$exclude_first = get_option( 'asf_exclude_first', '1' );
?>
		<form id="asf-lazy-form">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="asf_enable_lazy">Enable Content Image Lazy Load</label></th>
					<td>
						<input type="checkbox" id="asf_enable_lazy" name="asf_enable_lazy" value="1" <?php checked( $enable_lazy, '1' ); ?> />
						<span class="description">Automatically add <code>loading="lazy"</code> to all inline content images in published posts & pages.</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="asf_enable_iframe_lazy">Enable Video/Iframe Lazy Load</label></th>
					<td>
						<input type="checkbox" id="asf_enable_iframe_lazy" name="asf_enable_iframe_lazy" value="1" <?php checked( $enable_iframe, '1' ); ?> />
						<span class="description">Defer offscreen YouTube, Vimeo, and Google Maps iframe embeds until user scrolls.</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="asf_exclude_first">LCP Hero Guard (Exclude 1st Image)</label></th>
					<td>
						<input type="checkbox" id="asf_exclude_first" name="asf_exclude_first" value="1" <?php checked( $exclude_first, '1' ); ?> />
						<span class="description">Keep 1st above-the-fold image eager-loaded so Google PageSpeed LCP score remains 90+.</span>
					</td>
				</tr>
			</table>

			<div class="asf-action-bar" style="margin-top:16px;">
				<button type="button" class="button button-primary" id="asf-save-lazy-btn">Save Lazy Load Settings</button>
				<button type="button" class="button button-secondary" id="asf-enforce-lazy-all-btn">1-Click Apply Lazy Load to All Existing Posts</button>
			</div>
		</form>

		<div id="asf-lazy-status" style="margin-top:16px;"></div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
