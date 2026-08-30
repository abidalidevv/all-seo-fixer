<?php
/**
 * View: 360° SEO Audit Dashboard
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

$total_posts = wp_count_posts('post')->publish ?? 0;
$total_pages = wp_count_posts('page')->publish ?? 0;
$total_media = wp_count_posts('attachment')->inherit ?? 0;
$redirects   = count( get_option( ASF_OPT_REDIRECTS, array() ) );
$psi_key     = get_option( ASF_OPT_PSI_KEY, '' );
?>
<div class="wrap asf-wrap">

	<!-- HERO -->
	<div class="asf-hero">
		<h1>🛡️ All-in-One SEO Fixer & Auditor</h1>
		<p>Free, open-source 360° SEO engine for ANY WordPress site. Scan → Fix → Guide → Re-index — no subscriptions, no upsells, forever free.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ GitHub: @abidalidevv</a>
			<span class="asf-hero-badge">v<?php echo esc_html( ASF_VERSION ); ?></span>
			<span class="asf-hero-badge">GPL-2.0 Open Source</span>
			<span class="asf-hero-badge">100% Free</span>
		</div>
	</div>

	<!-- STAT CARDS -->
	<div class="asf-stats-grid">
		<?php
		$stats = array(
			array( '📄', $total_posts, 'Published Posts' ),
			array( '📋', $total_pages, 'Published Pages' ),
			array( '🖼️', $total_media, 'Media Files' ),
			array( '🔀', $redirects,   '301 Redirects' ),
			array( '⚡',  $psi_key ? '✓ Ready' : '⚠ Not Set', 'PageSpeed API' ),
			array( '🌐',  parse_url( home_url(), PHP_URL_HOST ), 'Site Domain' ),
		);
		foreach ( $stats as $s ) {
			printf(
				'<div class="asf-stat-card"><div class="asf-stat-icon">%s</div><div class="asf-stat-value">%s</div><div class="asf-stat-label">%s</div></div>',
				esc_html( $s[0] ), esc_html( $s[1] ), esc_html( $s[2] )
			);
		}
		?>
	</div>

	<!-- QUICK ACTIONS -->
	<div class="asf-card">
		<h2>🚀 Quick Actions</h2>
		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-run-full-btn">🔍 Run Full 360° SEO Audit</button>
			<button class="asf-btn-secondary" id="asf-ping-btn">🚀 Ping Search Engines (Re-Index)</button>
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-pagespeed') ); ?>" class="asf-btn-secondary">⚡ PageSpeed Test</a>
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-onpage') ); ?>" class="asf-btn-secondary">🔧 On-Page Checker</a>
			<button class="asf-btn-secondary" id="asf-download-report-btn" style="display:none;border-color:#16a34a;color:#15803d;">📄 Download / Print Report</button>
		</div>
		<div id="asf-dash-status" style="margin-top:16px;"></div>
	</div>

	<!-- PSI WARNING -->
	<?php if ( ! $psi_key ) : ?>
	<div class="asf-card asf-card-warn">
		<h3 style="color:#a16207;">⚡ Enable Core Web Vitals & PageSpeed Scores (Free)</h3>
		<p>Add your free Google PageSpeed Insights API key to unlock Performance, Accessibility, SEO scores (0–100) and LCP, CLS, TBT metrics.</p>
		<div class="asf-action-bar">
			<a href="<?php echo esc_url( admin_url('admin.php?page=asf-settings') ); ?>" class="asf-btn-primary asf-btn-sm">⚙️ Add API Key (2 min)</a>
			<a href="https://developers.google.com/speed/docs/insights/v5/get-started" target="_blank" class="asf-btn-secondary asf-btn-sm">Get Free Key →</a>
		</div>
	</div>
	<?php endif; ?>

	<!-- AUDIT RESULTS -->
	<div id="asf-dash-results"></div>

	<!-- FEATURE LIST -->
	<div class="asf-card">
		<h2>📦 What This Plugin Checks & Fixes</h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:8px;">
			<?php
			$features = array(
				'✅ Missing Title Tags & length checks',
				'✅ Missing Meta Descriptions & length checks',
				'✅ Duplicate Title Tags across all pages',
				'✅ Duplicate Meta Descriptions across all pages',
				'✅ Missing H1 headings & multiple H1 detection',
				'✅ Missing H2 headings on long content',
				'✅ Image alt text (per image in content)',
				'✅ Thin content (< 300 words on blog posts)',
				'✅ Schema JSON-LD markup presence',
				'✅ Open Graph & Social meta tags',
				'✅ Noindex flags on public pages',
				'✅ Internal links count per page',
				'✅ Broken double-domain URL typos (comhttps)',
				'✅ Orphan media files (unused images)',
				'✅ Robots.txt live HTTP accessibility check',
				'✅ XML Sitemap live HTTP accessibility check',
				'✅ Builder CPT exclusion from sitemap index',
				'✅ RSS Feed noindex protection',
				'✅ Google PageSpeed Insights (Core Web Vitals)',
				'✅ LCP · TBT · CLS · FCP · Speed Index',
				'✅ 301 Redirect Manager (no .htaccess needed)',
				'✅ IndexNow re-indexing (Bing, Yandex, Naver)',
				'✅ Google Sitemap Ping on demand',
				'✅ Rank Math & Yoast sitemap cache purge',
			);
			foreach ( $features as $feat ) {
				echo '<div style="padding:8px 12px;background:#f8fafc;border-radius:6px;font-size:13px;">' . esc_html( $feat ) . '</div>';
			}
			?>
		</div>
	</div>

	<!-- FOOTER -->
	<div class="asf-footer">
		Built with ❤️ by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> &nbsp;·&nbsp;
		<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ Star on GitHub</a> &nbsp;·&nbsp;
		<a href="https://github.com/abidalidevv/all-seo-fixer/issues" target="_blank">Report an Issue</a> &nbsp;·&nbsp;
		GPL-2.0 License — Free Forever
	</div>

</div>
