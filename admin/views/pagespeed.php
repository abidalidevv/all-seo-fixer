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

	<!-- CARD 1: INSTANT SERVER SPEED BENCHMARK (FREE / OFFLINE / ZERO-KEY) -->
	<div class="asf-card">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
			<h2 style="margin:0;"><span class="dashicons dashicons-dashboard" style="font-size:22px;vertical-align:middle;color:#2271b1;"></span> ⚡ Instant Server Speed &amp; TTFB Benchmark <span class="asf-badge asf-badge-green" style="font-size:11px;">Free / No Key Needed</span></h2>
			<span style="font-size:12px;color:#64748b;">Measures TTFB, download speed, Gzip/Brotli, document payload size &amp; server latency</span>
		</div>
		<div class="asf-action-bar" style="margin-top:0;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
			<input id="asf-benchmark-url" type="url" class="asf-input" value="<?php echo esc_attr(home_url('/')); ?>" style="flex:1;min-width:280px;" placeholder="https://example.com/page/" />
			<button class="button button-primary" id="asf-benchmark-run-btn">
				⚡ Run Instant Speed Benchmark
			</button>
		</div>
		<div id="asf-benchmark-status" style="margin-top:14px;"></div>
		<div id="asf-benchmark-results" style="margin-top:14px;"></div>
	</div>

	<!-- CARD 2: GOOGLE PAGESPEED INSIGHTS V5 API -->
	<div class="asf-card">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
			<h2 style="margin:0;"><span class="dashicons dashicons-google" style="font-size:22px;vertical-align:middle;color:#ea4335;"></span> Google PageSpeed Insights &amp; Core Web Vitals <span class="asf-badge asf-badge-blue" style="font-size:11px;">Google Cloud API</span></h2>
			<?php if ( ! $psi_key ) : ?>
				<span class="asf-badge asf-badge-yellow" style="font-size:11px;">Optional API Key Missing</span>
			<?php endif; ?>
		</div>

		<?php if ( ! $psi_key ) : ?>
		<div class="asf-notice asf-notice-info" style="margin-bottom:14px;">
			ℹ️ <strong>Google PSI Key:</strong> You can run tests directly, but adding a free Google Cloud API key in <a href="<?php echo esc_url(admin_url('admin.php?page=asf-settings')); ?>">Settings</a> ensures higher quota (25,000 requests/day).
		</div>
		<?php endif; ?>

		<div class="asf-action-bar" style="margin-top:0;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
			<input id="asf-psi-url" type="url" class="asf-input" value="<?php echo esc_attr(home_url('/')); ?>" style="flex:1;min-width:280px;" placeholder="https://example.com/page/" />
			<select id="asf-psi-strategy" class="asf-input" style="min-width:120px;">
				<option value="MOBILE">Mobile</option>
				<option value="DESKTOP">Desktop</option>
			</select>
			<button class="button button-secondary" id="asf-psi-run-btn">
				Run Official Google Audit
			</button>
		</div>
		<div id="asf-psi-status" style="margin-top:14px;"></div>
		<div id="asf-psi-results" style="margin-top:14px;"></div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
