<?php
/**
 * View: Settings & API Keys
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

// Handle save
if ( isset($_POST['asf_save_settings']) && check_admin_referer('asf_save_settings','asf_settings_nonce') ) {
	update_option( ASF_OPT_PSI_KEY, sanitize_text_field( $_POST['asf_psi_key'] ?? '' ) );
	echo '<div class="asf-notice asf-notice-success">✅ <strong>Settings saved successfully!</strong></div>';
}

$psi_key = get_option( ASF_OPT_PSI_KEY, '' );
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>⚙️ Settings & API Keys</h1>
		<p>Configure free API keys to unlock full features. All keys are stored securely in your WordPress database — never exposed in source code.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<form method="post">
		<?php wp_nonce_field('asf_save_settings','asf_settings_nonce'); ?>

		<!-- PageSpeed API -->
		<div class="asf-card">
			<h2>⚡ Google PageSpeed Insights API Key <span class="asf-badge asf-badge-green">Free</span></h2>
			<p>Required to use the <a href="<?php echo esc_url(admin_url('admin.php?page=asf-pagespeed')); ?>">PageSpeed Insights</a> tab. Free tier allows 25,000 requests/day — more than enough for any agency.</p>

			<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
				<input type="text" name="asf_psi_key" value="<?php echo esc_attr($psi_key); ?>"
					class="asf-input" style="max-width:480px;font-family:monospace;"
					placeholder="AIzaSy..." autocomplete="off" />
				<?php if ( $psi_key ) : ?>
				<span class="asf-badge asf-badge-green">✓ Configured</span>
				<?php else : ?>
				<span class="asf-badge asf-badge-red">⚠ Not Set</span>
				<?php endif; ?>
			</div>

			<h3 style="margin-top:16px;">How to Get Your Free Key (3 steps):</h3>
			<ol style="line-height:2.2;padding-left:20px;font-size:13.5px;color:#374151;">
				<li>Go to <a href="https://console.developers.google.com/apis/credentials" target="_blank"><strong>console.developers.google.com → Credentials</strong></a></li>
				<li>Create a Project → click <strong>Enable APIs & Services</strong> → search <em>"PageSpeed Insights API"</em> → Enable</li>
				<li>Go to <strong>Credentials</strong> → <strong>+ Create Credentials</strong> → API Key → copy and paste above</li>
			</ol>
		</div>

		<input type="submit" name="asf_save_settings" class="asf-btn-primary button" value="💾 Save Settings" />
	</form>

	<!-- Plugin Info -->
	<div class="asf-card" style="margin-top:20px;">
		<h2>📘 Plugin Information</h2>
		<table class="asf-table widefat"><tbody>
			<tr><td style="width:200px;"><strong>Plugin Name</strong></td><td>All-in-One SEO Fixer & Auditor</td></tr>
			<tr><td><strong>Version</strong></td><td><?php echo esc_html(ASF_VERSION); ?></td></tr>
			<tr><td><strong>Author</strong></td><td><a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a></td></tr>
			<tr><td><strong>GitHub</strong></td><td><a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">github.com/abidalidevv/all-seo-fixer</a></td></tr>
			<tr><td><strong>License</strong></td><td>GPL-2.0-or-later (Free & Open Source)</td></tr>
			<tr><td><strong>Requires WordPress</strong></td><td>5.8+</td></tr>
			<tr><td><strong>Requires PHP</strong></td><td>7.4+</td></tr>
			<tr><td><strong>Free APIs Used</strong></td>
				<td>
					<span class="asf-badge asf-badge-blue">Google PageSpeed Insights v5</span>
					<span class="asf-badge asf-badge-green">IndexNow (Bing/Yandex)</span>
					<span class="asf-badge asf-badge-purple">Google Sitemap Ping</span>
					<span class="asf-badge asf-badge-gray">Chart.js (jsDelivr CDN)</span>
				</td>
			</tr>
			<tr><td><strong>SEO Plugins Supported</strong></td><td>Rank Math, Yoast SEO, AIOSEO (meta + sitemap integration)</td></tr>
			<tr><td><strong>Page Builders Supported</strong></td><td>Elementor, Divi, Oxygen, Beaver Builder, Brizy</td></tr>
		</tbody></table>
	</div>

	<!-- Robots.txt Quick Reference -->
	<div class="asf-card">
		<h2>🤖 Recommended robots.txt</h2>
		<p>Copy this to your <code>robots.txt</code> at your site root (or via Rank Math → General → Edit Robots.txt):</p>
		<div class="asf-code-block">
			<span class="asf-code-gray"># All-in-One SEO Fixer — Recommended robots.txt</span><br>
			<span class="asf-code-blue">User-agent</span>: *<br>
			<span class="asf-code-blue">Disallow</span>: /wp-admin/<br>
			<span class="asf-code-blue">Allow</span>: /wp-admin/admin-ajax.php<br>
			<br>
			<span class="asf-code-blue">Sitemap</span>: <span class="asf-code-green"><?php echo esc_html( home_url('/sitemap_index.xml') ); ?></span>
		</div>
	</div>

	<!-- Sitemap Submission Guide -->
	<div class="asf-card">
		<h2>🗺️ Google Search Console Sitemap Submission</h2>
		<p>Submit your sitemap index to Google Search Console to ensure all pages are indexed:</p>
		<ol style="line-height:2.2;padding-left:20px;font-size:13.5px;">
			<li>Go to <a href="https://search.google.com/search-console" target="_blank"><strong>Google Search Console</strong></a></li>
			<li>Select your property → <strong>Sitemaps</strong> in left sidebar</li>
			<li>Enter: <code><?php echo esc_html( home_url('/sitemap_index.xml') ); ?></code> → <strong>Submit</strong></li>
		</ol>
		<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;font-size:13px;">
			<strong>Your sitemaps:</strong>
			<?php
			$sitemaps = array( 'sitemap_index.xml', 'post-sitemap.xml', 'page-sitemap.xml', 'category-sitemap.xml' );
			foreach ( $sitemaps as $sm ) {
				$url = home_url('/'.$sm);
				echo '<a href="' . esc_url($url) . '" target="_blank" class="asf-badge asf-badge-blue">' . esc_html($sm) . '</a>';
			}
			?>
		</div>
	</div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ Star on GitHub</a> · GPL-2.0 License
	</div>
</div>
