<?php
/**
 * View: 360° SEO Audit & Health Dashboard
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$posts_count = wp_count_posts( 'post' );
$total_posts = isset( $posts_count->publish ) ? (int) $posts_count->publish : 0;
$pages_count = wp_count_posts( 'page' );
$total_pages = isset( $pages_count->publish ) ? (int) $pages_count->publish : 0;
$media_count = wp_count_posts( 'attachment' );
$total_media = isset( $media_count->inherit ) ? (int) $media_count->inherit : 0;
$redirects   = count( get_option( ASF_OPT_REDIRECTS, array() ) );
$psi_key     = get_option( ASF_OPT_PSI_KEY, '' );
$lifetime_stats = class_exists( 'ASF_StatsTracker' ) ? ASF_StatsTracker::get_stats() : array();
$total_fixes    = (int) ( $lifetime_stats['total_fixes'] ?? 0 );
?>
<div class="wrap asf-wrap">

	<?php if ( ! get_option( 'asf_setup_wizard_completed', false ) ) : ?>
	<!-- SETUP WIZARD ONBOARDING BANNER -->
	<div class="asf-card" style="background:linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);color:#fff;padding:18px 22px;border-radius:12px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;box-shadow:0 8px 20px -4px rgba(79,70,229,0.35);">
		<div style="display:flex;align-items:center;gap:14px;">
			<div style="font-size:32px;background:rgba(255,255,255,0.15);width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
				🧙‍♂️
			</div>
			<div>
				<strong style="font-size:16px;display:block;font-weight:700;">Complete Your 3-Minute SEO Setup Wizard</strong>
				<span style="font-size:13px;opacity:0.9;">Configure your website niche, Schema markup, social OpenGraph sharing, and AI credentials to unlock automatic optimizations.</span>
			</div>
		</div>
		<div style="display:flex;gap:8px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=asf-setup-wizard' ) ); ?>" class="button button-primary" style="background:#fff;color:#4f46e5;font-weight:700;border:none;padding:8px 18px;border-radius:6px;box-shadow:0 3px 10px rgba(0,0,0,0.15);font-size:13px;">
				🚀 Launch Setup Wizard &rarr;
			</a>
		</div>
	</div>
	<?php endif; ?>

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>360° SEO Audit & Health Dashboard</h1>
			<p class="asf-header-desc">Run a full site audit across technical SEO, meta tags, schema markup, Core Web Vitals, and indexability.</p>
		</div>
		<div class="asf-header-actions">
			<button type="button" class="button button-primary" id="asf-run-full-btn"><span class="dashicons dashicons-update" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Run Full 360° SEO Audit</button>
			<button type="button" class="button button-secondary" id="asf-download-pdf-btn"><span class="dashicons dashicons-pdf" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Download Audit PDF Report</button>
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-setup-wizard') ); ?>" class="button button-secondary" title="Re-run the initial setup wizard"><span class="dashicons dashicons-admin-settings" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Setup Wizard</a>
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-ai-assistant') ); ?>" class="button button-secondary"><span class="dashicons dashicons-rest-api" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> AI SEO Copilot</a>
			<button type="button" class="button button-secondary" id="asf-ping-btn">Ping Search Engines</button>
		</div>
	</div>

	<!-- COMPACT STATS GRID -->
	<div class="asf-stats-grid">
		<div class="asf-stat-box" style="border-top:3px solid #10b981;">
			<div class="asf-stat-num" id="asf-stat-total-fixes" style="color:#059669;font-weight:800;"><?php echo esc_html( $total_fixes ); ?></div>
			<div class="asf-stat-label">SEO Fixes Applied</div>
		</div>
		<div class="asf-stat-box">
			<div class="asf-stat-num"><?php echo esc_html( $total_posts ); ?></div>
			<div class="asf-stat-label">Published Posts</div>
		</div>
		<div class="asf-stat-box">
			<div class="asf-stat-num"><?php echo esc_html( $total_pages ); ?></div>
			<div class="asf-stat-label">Published Pages</div>
		</div>
		<div class="asf-stat-box">
			<div class="asf-stat-num"><?php echo esc_html( $total_media ); ?></div>
			<div class="asf-stat-label">Media Files</div>
		</div>
		<div class="asf-stat-box">
			<div class="asf-stat-num"><?php echo esc_html( $redirects ); ?></div>
			<div class="asf-stat-label">301 Redirect Rules</div>
		</div>
		<div class="asf-stat-box">
			<div class="asf-stat-num asf-stat-num-sm">
				<?php echo $psi_key ? '<span class="asf-ok">Configured</span>' : '<span class="asf-warn">Not Set</span>'; ?>
			</div>
			<div class="asf-stat-label">PageSpeed API</div>
		</div>
		<div class="asf-stat-box">
			<div class="asf-stat-num asf-stat-num-domain">
				<?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?>
			</div>
			<div class="asf-stat-label">Site Domain</div>
		</div>
	</div>

	<div id="asf-dash-status"></div>
	<div id="asf-dash-results" style="margin-top:16px;"></div>

	<!-- SMART HEALTH BANNER (auto-runs on load & hydrates from saved state) -->
	<?php
	$cached_health = get_option( 'asf_health_score_cache', array() );
	$has_cache     = ! empty( $cached_health ) && isset( $cached_health['score'] );
	$cache_score   = $has_cache ? (int) $cached_health['score'] : '?';
	$cache_grade   = $has_cache ? 'Grade ' . (string) ( $cached_health['grade'] ?? 'A' ) : '?';
	$cache_col     = $has_cache ? ( $cache_score >= 80 ? '#10b981' : ( $cache_score >= 50 ? '#f59e0b' : '#ef4444' ) ) : '#cbd5e1';
	$cache_num_col = $has_cache ? $cache_col : '#64748b';
	?>
	<div id="asf-health-banner" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
		<div style="display:flex;align-items:center;gap:16px;">
			<div id="asf-score-ring" style="width:64px;height:64px;border-radius:50%;border:5px solid <?php echo esc_attr( $cache_col ); ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
				<span id="asf-score-num" style="font-size:18px;font-weight:800;color:<?php echo esc_attr( $cache_num_col ); ?>;"><?php echo esc_html( $cache_score ); ?></span>
			</div>
			<div>
				<div style="font-size:15px;font-weight:700;color:#1e293b;">&#x1F6E1; Site SEO Health Score</div>
				<div id="asf-health-checks" style="font-size:12px;color:#475569;margin-top:4px;">
					<?php
					if ( $has_cache && ! empty( $cached_health['checks'] ) && is_array( $cached_health['checks'] ) ) {
						foreach ( $cached_health['checks'] as $chk ) {
							$st   = $chk['status'] ?? 'ok';
							$icon = ( $st === 'ok' ) ? '✓' : ( ( $st === 'warn' ) ? '⚠' : '✕' );
							$pcol = ( $st === 'ok' ) ? '#10b981' : ( ( $st === 'warn' ) ? '#f59e0b' : '#ef4444' );
							echo '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 8px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;font-weight:600;margin-right:6px;margin-bottom:4px;"><span style="color:' . esc_attr( $pcol ) . ';">' . esc_html( $icon ) . '</span> ' . esc_html( $chk['label'] ?? '' ) . '</span>';
						}
					} else {
						echo 'Running quick health check...';
					}
					?>
				</div>
				<div id="asf-health-issues" style="font-size:12px;color:#dc2626;margin-top:4px;">
					<?php
					if ( $has_cache ) {
						if ( ! empty( $cached_health['issues'] ) && is_array( $cached_health['issues'] ) ) {
							echo '<ul style="margin:4px 0 0;padding-left:18px;color:#dc2626;font-size:12px;line-height:1.5;">';
							foreach ( $cached_health['issues'] as $iss ) {
								echo '<li>' . esc_html( $iss ) . '</li>';
							}
							echo '</ul>';
						} else {
							echo '<span style="color:#10b981;font-weight:600;font-size:12px;">✓ All vital checks passing cleanly!</span>';
						}
					}
					?>
				</div>
			</div>
		</div>
		<div style="display:flex;gap:8px;align-items:center;">
			<span id="asf-health-grade" style="font-size:28px;font-weight:900;color:<?php echo esc_attr( $cache_col ); ?>;"><?php echo esc_html( $cache_grade ); ?></span>
			<button type="button" class="button" id="asf-health-recheck-btn" style="font-size:12px;">&#x21BB; Re-check Now</button>
		</div>
	</div>

	<!-- APPLIED SEO FIXES & ACTIVITY TRACKER -->
	<?php
	$fixes_total = (int) ( $lifetime_stats['total_fixes'] ?? 0 );
	$recent_logs = $lifetime_stats['recent_activity'] ?? array();
	?>
	<div class="asf-card" style="margin-bottom:16px;">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
			<h2 style="margin:0;display:flex;align-items:center;gap:8px;">
				<span class="dashicons dashicons-yes-alt" style="color:#10b981;font-size:20px;"></span>
				Applied SEO Fixes &amp; Optimization Tracker
			</h2>
			<span class="asf-badge asf-badge-green" style="font-size:12px;padding:4px 10px;">
				<strong id="asf-tracker-badge"><?php echo esc_html( $fixes_total ); ?></strong> Fixes Saved in Database
			</span>
		</div>

		<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:10px;margin-bottom:16px;">
			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;text-align:center;">
				<div style="font-size:18px;font-weight:800;color:#2563eb;" id="asf-stat-titles"><?php echo esc_html( $lifetime_stats['titles_fixed'] ?? 0 ); ?></div>
				<div style="font-size:11px;color:#64748b;font-weight:600;margin-top:2px;">Titles Fixed</div>
			</div>
			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;text-align:center;">
				<div style="font-size:18px;font-weight:800;color:#059669;" id="asf-stat-metas"><?php echo esc_html( $lifetime_stats['metas_fixed'] ?? 0 ); ?></div>
				<div style="font-size:11px;color:#64748b;font-weight:600;margin-top:2px;">Metas Generated</div>
			</div>
			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;text-align:center;">
				<div style="font-size:18px;font-weight:800;color:#0284c7;" id="asf-stat-links"><?php echo esc_html( $lifetime_stats['links_generated'] ?? 0 ); ?></div>
				<div style="font-size:11px;color:#64748b;font-weight:600;margin-top:2px;">Internal Links Built</div>
			</div>
			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;text-align:center;">
				<div style="font-size:18px;font-weight:800;color:#d97706;" id="asf-stat-alts"><?php echo esc_html( $lifetime_stats['alts_fixed'] ?? 0 ); ?></div>
				<div style="font-size:11px;color:#64748b;font-weight:600;margin-top:2px;">Image ALTs Fixed</div>
			</div>
			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;text-align:center;">
				<div style="font-size:18px;font-weight:800;color:#7c3aed;" id="asf-stat-typos"><?php echo esc_html( $lifetime_stats['typos_fixed'] ?? 0 ); ?></div>
				<div style="font-size:11px;color:#64748b;font-weight:600;margin-top:2px;">URL Typos Cleaned</div>
			</div>
		</div>

		<?php if ( ! empty( $recent_logs ) && is_array( $recent_logs ) ) : ?>
			<div style="border-top:1px solid #f1f5f9;padding-top:12px;">
				<div style="font-size:12px;font-weight:700;color:#334155;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Recent Optimization Activity</div>
				<div style="max-height:160px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;background:#fff;">
					<table class="widefat striped" style="border:none;font-size:12px;">
						<tbody>
							<?php foreach ( array_slice( $recent_logs, 0, 8 ) as $log ) : ?>
								<tr>
									<td style="width:140px;color:#64748b;white-space:nowrap;padding:6px 10px;">
										<span class="dashicons dashicons-clock" style="font-size:13px;vertical-align:middle;margin-right:2px;"></span>
										<?php echo esc_html( $log['date'] ?? '' ); ?>
									</td>
									<td style="padding:6px 10px;">
										<strong><?php echo esc_html( $log['message'] ?? '' ); ?></strong>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php else : ?>
			<p style="margin:0;font-size:12px;color:#64748b;">No optimizations performed yet. Open <a href="<?php echo esc_url( admin_url('admin.php?page=asf-onpage') ); ?>">On-Page SEO Checker</a> or <a href="<?php echo esc_url( admin_url('admin.php?page=asf-broken-links') ); ?>">Link Cleaner</a> to start optimizing.</p>
		<?php endif; ?>
	</div>

	<!-- API NOTICE IF KEY NOT SET -->
	<?php if ( ! $psi_key ) : ?>
	<div class="asf-notice asf-notice-warn">
		<strong>Enable Core Web Vitals & PageSpeed Diagnostics:</strong> Add your free Google PageSpeed Insights API key in <a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>">Settings</a> to retrieve LCP, CLS, TBT metrics and 0–100 scores.
	</div>
	<?php endif; ?>

	<!-- CORE FEATURE CHECKLIST -->
	<div class="asf-card">
		<h2>System Diagnostic Capabilities</h2>
		<p>The 360° engine scans your WordPress database and live HTTP headers against the following core parameters:</p>
		<table class="widefat striped" style="margin-top:12px;">
			<thead>
				<tr>
					<th>Category</th>
					<th>Audit Check / Rule</th>
					<th>Automatic Fix Support</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Meta Tags</strong></td>
					<td>Title tags and Meta descriptions (missing, too short, too long, duplicates)</td>
					<td><span class="asf-badge asf-badge-green">Auto-Fix Assistant</span></td>
				</tr>
				<tr>
					<td><strong>Headings</strong></td>
					<td>Missing H1 headings & heading structure hierarchy</td>
					<td><span class="asf-badge asf-badge-green">Auto-Inject H1</span></td>
				</tr>
				<tr>
					<td><strong>Media & Alt Text</strong></td>
					<td>Missing image alt attributes and orphan media attachments</td>
					<td><span class="asf-badge asf-badge-green">Auto-Alt Generator</span></td>
				</tr>
				<tr>
					<td><strong>Security Headers</strong></td>
					<td>HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy</td>
					<td><span class="asf-badge asf-badge-green">Auto-Fix Headers</span></td>
				</tr>
				<tr>
					<td><strong>Database</strong></td>
					<td>Revisions, auto-drafts, trashed posts, spam comments, expired transients</td>
					<td><span class="asf-badge asf-badge-green">Batched DB Optimizer</span></td>
				</tr>
				<tr>
					<td><strong>Indexing</strong></td>
					<td>Robots.txt, XML sitemaps, IndexNow, Google Indexing API v3</td>
					<td><span class="asf-badge asf-badge-green">Robots & Indexing</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- SEO COMMAND CENTER & QUICK TOOLS GRID (BOTTOM POSITION) -->
	<div class="asf-card">
		<h2><span class="dashicons dashicons-menu-alt3" style="font-size:20px;vertical-align:middle;color:#2271b1;"></span> SEO Tools Command Center</h2>
		<p>Quick access to all 15+ optimization & diagnostic modules built into All-in-One SEO Fixer:</p>

		<div class="asf-tools-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:16px;margin-top:14px;">
			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-search" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">On-Page SEO Checker</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Per-page audit for title length, meta description quality, H1 tags, Schema.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-onpage') ); ?>" class="button button-primary button-small" style="text-align:center;">Run On-Page Audit →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-performance" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">PageSpeed Insights</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Live Core Web Vitals (LCP, CLS, TBT) & 0–100 Google performance score.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-pagespeed') ); ?>" class="button button-primary button-small" style="text-align:center;">Launch PageSpeed Test →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-images-alt2" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Lazy Load Images</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Native HTML5 image & iframe lazy loading with LCP Hero Guard.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-lazy-load') ); ?>" class="button button-primary button-small" style="text-align:center;">Configure Lazy Load →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-admin-media" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Media Scanner & Alt-Text</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Auto-Alt text generator & orphan unused media cleaner.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-media') ); ?>" class="button button-primary button-small" style="text-align:center;">Scan Media Library →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-database" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Speed & DB Optimizer</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Clean post revisions, auto-drafts, spam comments, expired transients.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-performance') ); ?>" class="button button-primary button-small" style="text-align:center;">Optimize Database →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-layout" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Page Builder Optimizer</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Elementor bloat inspector, eicons dequeueing & DB payload reduction.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-builder-analyzer') ); ?>" class="button button-primary button-small" style="text-align:center;">Optimize Builder →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-chart-line" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Search Console & Ranks</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Google Search Console API indexing submitter & 30-day rank tracker.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-gsc-inspector') ); ?>" class="button button-primary button-small" style="text-align:center;">Open GSC Inspector →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-editor-code" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">W3C HTML Validator</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Query official W3C Nu HTML Checker API for unclosed tags & syntax errors.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-w3c-validator') ); ?>" class="button button-primary button-small" style="text-align:center;">Validate HTML →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-editor-spellcheck" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Keyword Density Analyzer</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Extract 1-word and 2-word n-grams to prevent keyword stuffing.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-keyword-analyzer') ); ?>" class="button button-primary button-small" style="text-align:center;">Analyze Keywords →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-awards" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Domain Rating (DA)</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Internal link equity graph & on-page Domain Rating calculation.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-authority') ); ?>" class="button button-primary button-small" style="text-align:center;">Calculate Rating →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-desktop" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">SERP & Social Preview</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Live Google snippet preview & Facebook Open Graph card simulator.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-serp-preview') ); ?>" class="button button-primary button-small" style="text-align:center;">Preview SERP →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-shield" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Security & Headers</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">HSTS, X-Frame-Options, CDN detection & Spamhaus IP blacklist check.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-security') ); ?>" class="button button-primary button-small" style="text-align:center;">Check Site Security →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-admin-links" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Broken Link Cleaner</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Auto-repair double-domain typos in content & postmeta.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-link-cleaner') ); ?>" class="button button-primary button-small" style="text-align:center;">Clean Link Typos →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-external" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">301 Redirects & 404s</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Manage 301 redirect rules & monitor 404 error logs in real-time.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-redirects') ); ?>" class="button button-primary button-small" style="text-align:center;">Manage Redirects →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-editor-code" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Schema JSON-LD Studio</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Organization, LocalBusiness, FAQ, Article, Product &amp; Breadcrumb rich structured data.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-schema') ); ?>" class="button button-primary button-small" style="text-align:center;">Configure Schema →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-rest-api" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">GEO &amp; AI Search Hub</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Generative Engine Optimization for ChatGPT, Perplexity &amp; Gemini with live llms.txt standard.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-geo') ); ?>" class="button button-primary button-small" style="text-align:center;">Open GEO Hub →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-warning" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Console &amp; Error Doctor</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Monitor live front-end JS console errors &amp; PHP debug logs with AI one-click fix assistant.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-error-doctor') ); ?>" class="button button-primary button-small" style="text-align:center;">Diagnose Errors →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-admin-tools" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">🛠️ Swiss-Knife Multi-Tools</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">19+ network, DNS, WHOIS, IP lookup, robots, and HTTP diagnostic utilities in one unified suite.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-swiss-tools') ); ?>" class="button button-primary button-small" style="text-align:center;">Launch Swiss Tools →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-rest-api" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">AI Assistant &amp; Copilot</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">Real-time AI chatbot powered by Groq LLaMA-3.3 with master JSON export/import.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-ai-assistant') ); ?>" class="button button-primary button-small" style="text-align:center;">Launch AI Copilot →</a>
			</div>

			<div class="asf-tool-card" style="border:1px solid #dcdcde;border-radius:6px;padding:16px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;">
				<div>
					<span class="dashicons dashicons-admin-generic" style="font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;display:block;"></span>
					<strong style="font-size:14px;color:#1d2327;">Plugin Settings</strong>
					<p style="font-size:12px;color:#646970;margin:4px 0 12px;">API keys for Groq, Gemini, PageSpeed, and GSC Service Account.</p>
				</div>
				<a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>" class="button button-primary button-small" style="text-align:center;">Open Settings →</a>
			</div>
		</div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · Open Source GPL-2.0
	</div>
</div>
