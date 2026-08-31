<?php
/**
 * View: PageSpeed Insights & Core Web Vitals
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

$psi_key = get_option( ASF_OPT_PSI_KEY, '' );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>PageSpeed Insights & Core Web Vitals</h1>
			<p class="asf-header-desc">Analyze performance, LCP, TBT, CLS, and Lighthouse scores via Google's official PageSpeed Insights v5 API.</p>
		</div>
		<div class="asf-header-actions">
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>" class="button button-secondary">API Settings</a>
		</div>
	</div>

	<?php if ( ! $psi_key ) : ?>
	<div class="asf-notice asf-notice-error">
		<strong>PageSpeed API Key Required:</strong> Please configure your free Google PageSpeed Insights API key in <a href="<?php echo esc_url(admin_url('admin.php?page=asf-settings')); ?>">Settings</a> to run diagnostics. Free quota is 25,000 requests/day.
	</div>
	<?php endif; ?>

	<div class="asf-card">
		<h2>Test URL Performance</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<input id="asf-psi-url" type="url" class="asf-input" value="<?php echo esc_attr(home_url('/')); ?>" style="max-width:400px;" placeholder="https://example.com/page/" />
			<select id="asf-psi-strategy" class="asf-input">
				<option value="MOBILE">Mobile</option>
				<option value="DESKTOP">Desktop</option>
			</select>
			<button class="button button-primary" id="asf-psi-run-btn" <?php echo !$psi_key ? 'disabled title="Add API key in Settings first"' : ''; ?>>
				Run PageSpeed Audit
			</button>
		</div>
		<div id="asf-psi-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-psi-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
