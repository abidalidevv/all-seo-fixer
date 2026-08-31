<?php
/**
 * View: 360° SEO Audit & Health Dashboard
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$total_posts = (int) wp_count_posts('post')->publish;
$total_pages = (int) wp_count_posts('page')->publish;
$total_media = (int) wp_count_attachments();
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

	<!-- AI SEO ASSISTANT CHATBOT WIDGET -->
	<div class="asf-card">
		<h2><span class="dashicons dashicons-rest-api" style="font-size:22px;vertical-align:middle;color:#2271b1;"></span> AI SEO Assistant & Copilot</h2>
		<p>Ask custom questions or generate instant SEO recommendations powered by Groq AI / Google Gemini AI:</p>

		<div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="Analyze my site's SEO Health Audit and list top 3 priority fixes."><span class="dashicons dashicons-lightbulb" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Audit Priority Analysis</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How do I optimize meta descriptions to increase Google click-through rate (CTR)?"><span class="dashicons dashicons-edit" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Meta Description Tips</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How to fix Largest Contentful Paint (LCP) and Total Blocking Time (TBT) on WordPress?"><span class="dashicons dashicons-performance" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Speed & CWV Guide</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How to generate schema JSON-LD for a local business website?"><span class="dashicons dashicons-location" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Local SEO Schema Guide</button>
		</div>

		<div id="asf-ai-chat-box" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:14px;max-height:280px;overflow-y:auto;margin-bottom:12px;font-size:13px;line-height:1.6;">
			<div style="color:#64748b;">👋 Hello! I am your AI SEO Assistant. Ask me any SEO question or click a quick prompt pill above!</div>
		</div>

		<div style="display:flex;gap:8px;">
			<input type="text" id="asf-ai-input" class="asf-input" style="flex:1;" placeholder="Ask AI SEO Assistant a question (e.g. How to rank for local keywords?)..." />
			<button type="button" class="button button-primary" id="asf-ai-send-btn">Ask AI Assistant</button>
		</div>
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
