<?php
/**
 * View: Console & Error Doctor — Script & Debug Log Fixer
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$force_https = get_option( 'asf_opt_force_https_scripts', '1' );
$fix_jquery  = get_option( 'asf_opt_fix_jquery', '1' );
$monitor_on  = get_option( 'asf_opt_monitor_console', '1' );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Console & Error Doctor — Script & Debug Log Fixer</h1>
			<p class="asf-header-desc">Detect, diagnose, and auto-fix browser JavaScript console errors, mixed content HTTP/HTTPS script blocks, jQuery $ conflicts, and PHP runtime fatal errors with AI-assisted solutions.</p>
		</div>
		<div class="asf-header-actions">
			<button class="button button-primary" id="asf-error-scan-btn">
				<span class="dashicons dashicons-search" style="vertical-align:text-top;margin-top:1px;"></span> Scan Console & Error Logs
			</button>
		</div>
	</div>

	<!-- AUTO-FIX & REAL-TIME PROTECTION CONTROLS -->
	<div class="asf-card">
		<h2><span class="dashicons dashicons-shield" style="font-size:20px;vertical-align:middle;color:#2271b1;"></span> 1-Click Script Protection & Auto-Fix Toggles</h2>
		<p>Enable real-time front-end guards to automatically suppress and fix common WordPress console crashes:</p>

		<table class="widefat striped" style="margin-top:12px;">
			<thead>
				<tr>
					<th>Protection Engine</th>
					<th>Problem It Fixes</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Mixed Content HTTPS Enforcer</strong></td>
					<td>Rewrites <code>http://</code> script/style URLs to <code>https://</code> to prevent browser Mixed Active Content blocks.</td>
					<td>
						<span id="asf-status-badge-https" class="asf-badge <?php echo $force_https === '1' ? 'asf-badge-green' : 'asf-badge-red'; ?>">
							<?php echo $force_https === '1' ? 'Active' : 'Disabled'; ?>
						</span>
					</td>
					<td>
						<button class="button button-small asf-toggle-btn" data-feature="force_https" data-state="<?php echo $force_https === '1' ? '0' : '1'; ?>">
							<?php echo $force_https === '1' ? 'Disable' : 'Enable & Auto-Fix'; ?>
						</button>
					</td>
				</tr>
				<tr>
					<td><strong>Safe jQuery Compatibility Layer</strong></td>
					<td>Injects safe <code>window.$ = jQuery</code> wrapper to fix <code>$ is not a function</code> and jQuery conflict errors.</td>
					<td>
						<span id="asf-status-badge-jquery" class="asf-badge <?php echo $fix_jquery === '1' ? 'asf-badge-green' : 'asf-badge-red'; ?>">
							<?php echo $fix_jquery === '1' ? 'Active' : 'Disabled'; ?>
						</span>
					</td>
					<td>
						<button class="button button-small asf-toggle-btn" data-feature="fix_jquery" data-state="<?php echo $fix_jquery === '1' ? '0' : '1'; ?>">
							<?php echo $fix_jquery === '1' ? 'Disable' : 'Enable & Auto-Fix'; ?>
						</button>
					</td>
				</tr>
				<tr>
					<td><strong>Client Console Error Monitor</strong></td>
					<td>Captures real front-end runtime JS errors, failed resources, and uncaught exceptions silently into admin view.</td>
					<td>
						<span id="asf-status-badge-monitor" class="asf-badge <?php echo $monitor_on === '1' ? 'asf-badge-green' : 'asf-badge-red'; ?>">
							<?php echo $monitor_on === '1' ? 'Active' : 'Disabled'; ?>
						</span>
					</td>
					<td>
						<button class="button button-small asf-toggle-btn" data-feature="monitor_console" data-state="<?php echo $monitor_on === '1' ? '0' : '1'; ?>">
							<?php echo $monitor_on === '1' ? 'Disable' : 'Enable'; ?>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- SCAN STATUS & LOG TOOLS -->
	<div id="asf-error-status"></div>

	<!-- RESULTS CONTAINER -->
	<div id="asf-error-results">
		<div class="asf-card asf-card-info">
			<h2><span class="dashicons dashicons-info" style="font-size:20px;vertical-align:middle;color:#2271b1;"></span> Ready to Inspect</h2>
			<p>Click <strong>"Scan Console & Error Logs"</strong> above to inspect active front-end JavaScript console errors, WordPress <code>debug.log</code> PHP errors, and asset integrity issues.</p>
		</div>
	</div>

	<!-- AI DIAGNOSIS MODAL OVERLAY (POPULATED DYNAMICALLY) -->
	<div id="asf-ai-error-modal-container"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
