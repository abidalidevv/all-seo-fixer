<?php
/**
 * View: Performance, Caching & Database Optimizer
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Speed & Database Optimizer</h1>
			<p class="asf-header-desc">Native image lazy loading, 50-item batched database cleaner, and Apache/Nginx speed configuration snippets.</p>
		</div>
	</div>

	<!-- LAZY LOADING STATUS -->
	<div class="asf-notice asf-notice-success">
		<strong>Native Lazy Loading Active:</strong> All images and iframe embeds in post/page content automatically render with <code>loading="lazy"</code> to optimize initial render and LCP.
	</div>

	<!-- DATABASE CLEANER & OPTIMIZER -->
	<div class="asf-card">
		<h2>Database Cleanup & Table Optimization</h2>
		<div class="asf-notice asf-notice-warn" style="margin-bottom:14px;">
			<strong>Backup Recommendation:</strong> It is good practice to take a database backup (via UpdraftPlus or hosting cPanel) before executing database optimizations.
		</div>
		<p>Deletes post revisions, auto-drafts, trashed posts, spam comments, and expired transients in 50-item batches via WordPress native cleanup APIs without PHP timeouts.</p>

		<div class="asf-action-bar">
			<button class="button button-primary" id="asf-db-opt-btn">Run 1-Click Database Optimization</button>
		</div>
		<div id="asf-db-opt-status" style="margin-top:16px;"></div>
	</div>

	<!-- SPEED SNIPPETS: APACHE & NGINX -->
	<div class="asf-card">
		<h2>Apache / LiteSpeed .htaccess Speed Rules</h2>
		<p>Copy and paste this snippet at the top of your <code>.htaccess</code> file to enable Gzip compression and Browser Caching headers:</p>
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

	<div class="asf-card">
		<h2>Nginx Server Speed Configuration</h2>
		<p>Add this snippet inside your Nginx server block (<code>nginx.conf</code>) to enable Gzip compression and static asset caching:</p>
		<div class="asf-code-block">
			<span class="asf-code-gray"># All-in-One SEO Fixer — Nginx Compression & Static Cache</span><br>
			gzip on;<br>
			gzip_comp_level 5;<br>
			gzip_min_length 256;<br>
			gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;<br>
			<br>
			location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp|svg)$ {<br>
			&nbsp;&nbsp;expires 365d;<br>
			&nbsp;&nbsp;add_header Cache-Control "public, no-transform";<br>
			}
		</div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
