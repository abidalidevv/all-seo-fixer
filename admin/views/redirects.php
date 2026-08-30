<?php
/**
 * View: 301 Redirect Manager
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

// Handle save
if ( isset( $_POST['asf_save_redirects'] ) && check_admin_referer( 'asf_save_redirects', 'asf_redirects_nonce' ) ) {
	$sources = array_map( 'sanitize_text_field', $_POST['asf_source'] ?? array() );
	$targets = array_map( 'esc_url_raw',          $_POST['asf_target'] ?? array() );
	$new     = array();
	for ( $i = 0; $i < count( $sources ); $i++ ) {
		$s = trim( $sources[ $i ] );
		$t = trim( $targets[ $i ] );
		if ( $s && $t ) $new[ $s ] = $t;
	}
	update_option( ASF_OPT_REDIRECTS, $new );
	echo '<div class="asf-notice asf-notice-success">✅ <strong>' . count($new) . ' redirect rule(s) saved successfully!</strong></div>';
}

$redirects = get_option( ASF_OPT_REDIRECTS, array() );
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🔀 301 Canonical Redirect Manager</h1>
		<p>Manage permanent 301 redirects for old URLs, renamed slugs, or Google Search Console 404 errors — no .htaccess editing, no extra plugins needed.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
			<span class="asf-hero-badge"><?php echo count($redirects); ?> Active Rules</span>
		</div>
	</div>

	<div class="asf-card asf-card-info">
		<h3>💡 When to Use 301 Redirects</h3>
		<ul style="margin:0;padding-left:20px;line-height:2;font-size:13.5px;columns:2;">
			<li>Old blog post URL changed</li>
			<li>Page slug renamed</li>
			<li>Google Search Console reports 404 errors</li>
			<li>/about → /about-us migration</li>
			<li>Category URL restructuring</li>
			<li>Legacy campaign URLs</li>
		</ul>
	</div>

	<form method="post">
		<?php wp_nonce_field( 'asf_save_redirects', 'asf_redirects_nonce' ); ?>
		<div class="asf-card">
			<h2>🔀 Redirect Rules <span class="asf-count-pill green"><?php echo count($redirects); ?></span></h2>
			<table class="asf-table widefat" id="asf-redir-table" style="margin-bottom:14px;">
				<thead>
					<tr>
						<th>Source Path (legacy URL, e.g. /old-slug/)</th>
						<th>Target URL (destination, e.g. https://domain.com/new-slug/)</th>
						<th style="width:80px;">Action</th>
					</tr>
				</thead>
				<tbody>
				<?php if ( $redirects ) : foreach ( $redirects as $src => $tgt ) : ?>
					<tr>
						<td><input type="text" name="asf_source[]" value="<?php echo esc_attr($src); ?>" class="asf-input" placeholder="/legacy-path/" /></td>
						<td><input type="text" name="asf_target[]" value="<?php echo esc_attr($tgt); ?>" class="asf-input" placeholder="https://domain.com/new-url/" /></td>
						<td><button type="button" class="asf-btn-danger asf-btn-xs button asf-remove-row">✕</button></td>
					</tr>
				<?php endforeach; else : ?>
					<tr>
						<td><input type="text" name="asf_source[]" class="asf-input" placeholder="/old-blog-post-slug/" /></td>
						<td><input type="text" name="asf_target[]" class="asf-input" placeholder="https://yourdomain.com/new-slug/" /></td>
						<td><button type="button" class="asf-btn-danger asf-btn-xs button asf-remove-row">✕</button></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
			<div class="asf-action-bar">
				<button type="button" class="asf-btn-secondary" id="asf-add-row">+ Add Rule</button>
				<input type="submit" name="asf_save_redirects" class="asf-btn-primary button" value="💾 Save All Redirect Rules" />
			</div>
		</div>
	</form>

	<!-- Built-in Always-Active Protections -->
	<div class="asf-card asf-card-ok">
		<h3>🛡️ Always-Active Built-In Protections</h3>
		<table class="asf-table widefat"><tbody>
			<tr><td><span class="asf-badge asf-badge-green">✓ Active</span></td><td>RSS/Atom Feed pages → <code>X-Robots-Tag: noindex, follow</code> (prevents feed crawling)</td></tr>
			<tr><td><span class="asf-badge asf-badge-green">✓ Active</span></td><td>Elementor, Divi, Oxygen, Beaver Builder template CPTs → excluded from XML sitemap index</td></tr>
		</tbody></table>
	</div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
