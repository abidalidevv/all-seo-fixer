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
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>360° SEO Audit & Health Dashboard</h1>
			<p class="asf-header-desc">Run a full site audit across technical SEO, meta tags, schema markup, Core Web Vitals, and indexability.</p>
		</div>
		<div class="asf-header-actions">
			<button type="button" class="button button-primary" id="asf-run-full-btn"><span class="dashicons dashicons-update" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Run Full 360° SEO Audit</button>
			<button type="button" class="button button-secondary" id="asf-download-pdf-btn"><span class="dashicons dashicons-pdf" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Download Audit PDF Report</button>
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-ai-assistant') ); ?>" class="button button-secondary"><span class="dashicons dashicons-rest-api" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> AI SEO Copilot</a>
			<button type="button" class="button button-secondary" id="asf-ping-btn">Ping Search Engines</button>
		</div>
	</div>

	<!-- COMPACT STATS GRID -->
	<div class="asf-stats-grid">
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

	<!-- SMART HEALTH BANNER (auto-runs on load) -->
	<div id="asf-health-banner" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
		<div style="display:flex;align-items:center;gap:16px;">
			<div id="asf-score-ring" style="width:64px;height:64px;border-radius:50%;border:5px solid #cbd5e1;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
				<span id="asf-score-num" style="font-size:18px;font-weight:800;color:#64748b;">?</span>
			</div>
			<div>
				<div style="font-size:15px;font-weight:700;color:#1e293b;">&#x1F6E1; Site SEO Health Score</div>
				<div id="asf-health-checks" style="font-size:12px;color:#475569;margin-top:4px;">Running quick health check...</div>
				<div id="asf-health-issues" style="font-size:12px;color:#dc2626;margin-top:4px;"></div>
			</div>
		</div>
		<div style="display:flex;gap:8px;align-items:center;">
			<span id="asf-health-grade" style="font-size:28px;font-weight:900;color:#cbd5e1;">?</span>
			<button type="button" class="button" id="asf-health-recheck-btn" style="font-size:12px;">&#x21BB; Re-check Now</button>
		</div>
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
