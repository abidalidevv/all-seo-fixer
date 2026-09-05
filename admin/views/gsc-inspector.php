<?php
/**
 * View: Google Search Console Indexing API & Rank Tracker
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$conn_mode = ASF_GSC::is_connected();
$auth_url  = ASF_GSC::get_google_auth_url();
$client_id = get_option( 'asf_gsc_client_id', '' );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Google Search Console & Indexing Helper</h1>
			<p class="asf-header-desc">Submit URLs directly to Google via Indexing API v3 and fetch live 30-day keyword rankings with average position, clicks, and impressions.</p>
		</div>
		<div class="asf-header-actions">
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>" class="button button-primary"><span class="dashicons dashicons-key" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Search Console Credentials</a>
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>" class="button button-secondary">API Settings</a>
		</div>
	</div>

	<?php if ( ! empty( $_GET['gsc_connected'] ) ) : ?>
	<div class="asf-notice asf-notice-success">
		<strong>✓ Google Account Connected!</strong> Your Google Search Console integration is now active. You can track live keyword rankings and submit URLs for instant Google indexing.
	</div>
	<?php elseif ( ! empty( $_GET['gsc_disconnected'] ) ) : ?>
	<div class="asf-notice asf-notice-info">
		Google Search Console disconnected.
	</div>
	<?php endif; ?>

	<?php if ( $conn_mode === 'oauth' ) : ?>
	<div class="asf-notice asf-notice-success" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
		<div>
			<strong>🟢 Connected via Google OAuth:</strong> Live Search Analytics, Keyword Ranks, and Indexing API are active.
		</div>
		<div>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=asf_gsc_disconnect' ), 'asf_disconnect_gsc' ) ); ?>" class="button button-secondary button-small" onclick="return confirm('Disconnect Google Search Console?');">Disconnect Google Account</a>
		</div>
	</div>
	<?php elseif ( $conn_mode === 'service_account' ) : ?>
	<div class="asf-notice asf-notice-success">
		<strong>🟢 Google Search Console Connected:</strong> Service Account JSON configured. Indexing API v3 and Search Analytics rank tracking are active.
	</div>
	<?php else : ?>
	<div class="asf-notice asf-notice-warn">
		<strong>Google Search Console Not Connected:</strong> Connect your Google Account via 1-Click Google Sign-In or provide a Service Account JSON key to unlock live keyword rankings, crawl inspections, and instant Google indexing.
	</div>

	<!-- 1-CLICK GOOGLE CONNECT CARD -->
	<div class="asf-card" style="border-left:4px solid #1a73e8;">
		<h2><span class="dashicons dashicons-google" style="color:#1a73e8;vertical-align:middle;"></span> Connect with Google Search Console</h2>
		<p>Connect your existing Google Account (the same Gmail you use for Google Search Console) to grant instant access:</p>
		<div style="margin-top:12px;">
			<?php if ( ! empty( $client_id ) && ! empty( $auth_url ) ) : ?>
				<a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary" style="background:#1a73e8;border-color:#1a73e8;padding:4px 16px;height:auto;font-size:14px;">
					<span class="dashicons dashicons-google" style="vertical-align:text-top;margin-right:6px;"></span> Sign in with Google (Connect GSC)
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>" class="button button-primary" style="background:#1a73e8;border-color:#1a73e8;">
					<span class="dashicons dashicons-google" style="vertical-align:text-top;margin-right:6px;"></span> Setup Google OAuth in Settings →
				</a>
				<span style="font-size:12px;color:#64748b;margin-left:12px;">Or paste a Service Account JSON key in Settings.</span>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- INSTANT INDEXING SUBMITTER -->
	<div class="asf-card">
		<h2>Submit URL for Instant Google Indexing</h2>
		<p>Submits URL directly to Google's Indexing API v3 using your authorized credentials:</p>
		<div class="asf-action-bar" style="margin-top:0;">
			<input id="asf-gsc-url-input" type="url" class="asf-input" value="<?php echo esc_attr( home_url( '/' ) ); ?>" style="max-width:450px;" placeholder="https://domain.com/new-post/" />
			<button class="button button-primary" id="asf-gsc-submit-btn">Submit URL to Google</button>
		</div>
		<div id="asf-gsc-status" style="margin-top:16px;"></div>
	</div>

	<!-- LIVE GSC KEYWORD RANK TRACKER -->
	<div class="asf-card">
		<h2>Live GSC Keyword Rank Tracker</h2>
		<p>Query Search Console Search Analytics API for your site's top 50 search keywords, average rank position (Rank #1–100), clicks, and impressions:</p>
		<div class="asf-action-bar" style="margin-top:0;">
			<button class="button button-primary" id="asf-gsc-rank-btn">Fetch Live Keyword Ranks</button>
		</div>
		<div id="asf-gsc-rank-status" style="margin-top:16px;"></div>
		<div id="asf-gsc-rank-results" style="margin-top:16px;"></div>
	</div>

	<!-- SETUP GUIDE -->
	<div class="asf-card">
		<h2>Google Search Console Connection Methods</h2>
		<table class="widefat striped" style="margin-top:10px;">
			<thead>
				<tr>
					<th>Method</th>
					<th>How It Works</th>
					<th>Best For</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Method 1: 1-Click Google OAuth (Recommended)</strong></td>
					<td>Uses your logged-in Gmail account. You click "Sign in with Google", approve Search Console read access, and the plugin automatically manages tokens.</td>
					<td><span class="asf-badge asf-badge-green">Fast &amp; Simple</span></td>
				</tr>
				<tr>
					<td><strong>Method 2: Service Account JSON Key</strong></td>
					<td>Download a JSON key from Google Cloud Console and paste it into Settings. Ideal for headless background cron jobs without personal Gmail logins.</td>
					<td><span class="asf-badge asf-badge-blue">Automated / Developers</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
