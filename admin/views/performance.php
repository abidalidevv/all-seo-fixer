<?php
/**
 * View: Performance, Caching & Database Optimizer
 * (WP Rocket + Smush Pro style)
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>⚡ Performance, Caching & Database Optimizer</h1>
		<p>Optimize speed without paid plugins like WP Rocket or Smush Pro. Native image & iframe lazy loading, 1-click database cleanup, and speed configuration snippets.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<!-- NATIVE LAZY LOADING BADGE -->
	<div class="asf-card asf-card-ok">
		<h3>🚀 Native Image & Iframe Lazy Loading: <span class="asf-badge asf-badge-green">ACTIVE & ENABLED</span></h3>
		<p style="margin:0;">All images and iframe embeds in your post and page content are automatically served with <code>loading="lazy"</code> to improve Largest Contentful Paint (LCP) and initial page load time.</p>
	</div>

	<!-- DATABASE CLEANER & OPTIMIZER -->
	<div class="asf-card">
		<h2>🧹 Database Cleanup & Table Optimization</h2>
		<p>Safely delete post revisions, auto-drafts, trashed posts, spam comments, and expired transients to reduce database size and speed up MySQL queries.</p>

		<div class="asf-action-bar">
			<button class="asf-btn-primary" id="asf-db-opt-btn">🧹 Run 1-Click Database Optimization</button>
		</div>
		<div id="asf-db-opt-status" style="margin-top:16px;"></div>
	</div>

	<!-- HTACCESS SPEED SNIPPET -->
	<div class="asf-card">
		<h2>⚡ Recommended .htaccess Speed Snippet (Apache / Litespeed)</h2>
		<p>Copy and paste this snippet into the top of your <code>.htaccess</code> file to enable Gzip compression and Browser Caching headers:</p>
		<div class="asf-code-block">
			<span class="asf-code-gray"># All-in-One SEO Fixer — Speed & Caching Rules</span><br>
			&lt;IfModule mod_deflate.c&gt;<br>
			&nbsp;&nbsp;AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json image/svg+xml<br>
			&lt;/IfModule&gt;<br>
			<br>
			&lt;IfModule mod_expires.c&gt;<br>
			&nbsp;&nbsp;ExpiresActive On<br>
			&nbsp;&nbsp;ExpiresByType image/jpg "access plus 1 year"<br>
			&nbsp;&nbsp;ExpiresByType image/jpeg "access plus 1 year"<br>
			&nbsp;&nbsp;ExpiresByType image/gif "access plus 1 year"<br>
			&nbsp;&nbsp;ExpiresByType image/png "access plus 1 year"<br>
			&nbsp;&nbsp;ExpiresByType image/webp "access plus 1 year"<br>
			&nbsp;&nbsp;ExpiresByType text/css "access plus 1 month"<br>
			&nbsp;&nbsp;ExpiresByType application/javascript "access plus 1 month"<br>
			&lt;/IfModule&gt;
		</div>
	</div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
