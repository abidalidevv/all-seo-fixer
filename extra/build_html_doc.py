import re

doc_md_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\DOCUMENTATION.md"
html_out_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\documentation.html"

with open(doc_md_path, "r", encoding="utf-8") as f:
    md_content = f.read()

# Let's create an elegant, responsive offline documentation HTML viewer
html_template = """<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>All-in-One SEO Fixer & Auditor — Master User Manual & Technical Docs</title>
	<style>
		:root {
			--doc-bg: #f0f2f5;
			--doc-surface: #ffffff;
			--doc-border: #d0d7de;
			--doc-text: #1f2328;
			--doc-text-muted: #656d76;
			--doc-primary: #2271b1;
			--doc-primary-hover: #135e96;
			--doc-sidebar-bg: #1e293b;
			--doc-sidebar-text: #e2e8f0;
			--doc-sidebar-hover: #334155;
			--doc-accent: #0284c7;
			--doc-code-bg: #f6f8fa;
			--badge-green-bg: #dcfce7;
			--badge-green-text: #166534;
			--badge-blue-bg: #e0f2fe;
			--badge-blue-text: #0369a1;
			--badge-warn-bg: #fef3c7;
			--badge-warn-text: #92400e;
			--badge-err-bg: #fee2e2;
			--badge-err-text: #991b1b;
		}

		* { box-sizing: border-box; margin: 0; padding: 0; }
		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			background-color: var(--doc-bg);
			color: var(--doc-text);
			line-height: 1.6;
			font-size: 14px;
		}

		.doc-layout {
			display: flex;
			min-height: 100vh;
		}

		/* SIDEBAR */
		.doc-sidebar {
			width: 300px;
			background-color: var(--doc-sidebar-bg);
			color: var(--doc-sidebar-text);
			position: fixed;
			top: 0;
			bottom: 0;
			left: 0;
			overflow-y: auto;
			z-index: 1000;
			padding: 24px 16px;
			box-shadow: 2px 0 8px rgba(0,0,0,0.1);
		}

		.doc-brand {
			font-size: 18px;
			font-weight: 700;
			color: #ffffff;
			display: flex;
			align-items: center;
			gap: 8px;
			margin-bottom: 4px;
		}

		.doc-version {
			display: inline-block;
			font-size: 11px;
			font-weight: 700;
			background: #2563eb;
			color: #ffffff;
			padding: 2px 8px;
			border-radius: 12px;
			margin-bottom: 20px;
		}

		.doc-nav-group {
			margin-bottom: 20px;
		}

		.doc-nav-title {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.8px;
			color: #94a3b8;
			font-weight: 700;
			margin-bottom: 6px;
			padding-left: 8px;
		}

		.doc-nav {
			list-style: none;
		}

		.doc-nav li a {
			display: block;
			padding: 7px 10px;
			color: #cbd5e1;
			text-decoration: none;
			border-radius: 6px;
			font-size: 13px;
			transition: all 0.15s ease;
		}

		.doc-nav li a:hover {
			background: var(--doc-sidebar-hover);
			color: #ffffff;
		}

		/* CONTENT */
		.doc-main {
			margin-left: 300px;
			flex: 1;
			padding: 40px;
			max-width: 1080px;
		}

		.doc-hero {
			background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
			color: #ffffff;
			border-radius: 12px;
			padding: 32px;
			margin-bottom: 32px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.08);
		}

		.doc-hero h1 {
			font-size: 28px;
			font-weight: 800;
			margin-bottom: 8px;
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.doc-hero p {
			font-size: 15px;
			color: #94a3b8;
			line-height: 1.5;
		}

		.doc-meta-pills {
			display: flex;
			gap: 10px;
			margin-top: 16px;
			flex-wrap: wrap;
		}

		.doc-pill {
			background: rgba(255,255,255,0.1);
			padding: 4px 12px;
			border-radius: 16px;
			font-size: 12px;
			font-weight: 600;
			color: #e2e8f0;
		}

		.doc-card {
			background: var(--doc-surface);
			border: 1px solid var(--doc-border);
			border-radius: 8px;
			padding: 28px;
			margin-bottom: 28px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.04);
		}

		.doc-card h2 {
			font-size: 20px;
			font-weight: 700;
			color: #0f172a;
			margin-bottom: 12px;
			border-bottom: 2px solid #f1f5f9;
			padding-bottom: 8px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.doc-card h3 {
			font-size: 16px;
			font-weight: 600;
			color: #1e293b;
			margin: 20px 0 8px;
		}

		.doc-card p {
			margin-bottom: 14px;
			line-height: 1.6;
		}

		.doc-card ol, .doc-card ul {
			margin-left: 20px;
			margin-bottom: 16px;
		}

		.doc-card li {
			margin-bottom: 6px;
		}

		/* TABLES */
		table.doc-table {
			width: 100%;
			border-collapse: collapse;
			margin: 16px 0 20px;
			font-size: 13px;
		}

		table.doc-table th, table.doc-table td {
			padding: 10px 14px;
			border: 1px solid var(--doc-border);
			text-align: left;
		}

		table.doc-table th {
			background: #f8fafc;
			font-weight: 600;
			color: #0f172a;
		}

		table.doc-table tr:nth-child(even) td {
			background: #fafbfc;
		}

		/* BADGES */
		.badge {
			display: inline-block;
			padding: 2px 8px;
			border-radius: 4px;
			font-size: 11px;
			font-weight: 600;
		}
		.badge-green { background: var(--badge-green-bg); color: var(--badge-green-text); }
		.badge-blue  { background: var(--badge-blue-bg);  color: var(--badge-blue-text); }
		.badge-warn  { background: var(--badge-warn-bg);  color: var(--badge-warn-text); }
		.badge-err   { background: var(--badge-err-bg);   color: var(--badge-err-text); }

		/* CALLOUTS */
		.callout {
			background: #f0f9ff;
			border-left: 4px solid #0284c7;
			padding: 14px 18px;
			border-radius: 0 6px 6px 0;
			margin: 16px 0;
			font-size: 13px;
		}

		.callout-title {
			font-weight: 700;
			color: #0369a1;
			margin-bottom: 4px;
		}

		/* CODE */
		code {
			background: var(--doc-code-bg);
			border: 1px solid #e1e4e8;
			padding: 2px 6px;
			border-radius: 4px;
			font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
			font-size: 12px;
			color: #0969da;
		}

		pre {
			background: #1e293b;
			color: #f8fafc;
			padding: 16px;
			border-radius: 6px;
			overflow-x: auto;
			font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
			font-size: 12px;
			margin: 14px 0;
			line-height: 1.5;
		}

		pre code {
			background: transparent;
			border: none;
			padding: 0;
			color: inherit;
		}

		.doc-footer {
			text-align: center;
			padding: 30px 0;
			color: var(--doc-text-muted);
			font-size: 13px;
			border-top: 1px solid var(--doc-border);
			margin-top: 40px;
		}

		.doc-footer a {
			color: var(--doc-primary);
			text-decoration: none;
			font-weight: 600;
		}
	</style>
</head>
<body>

<div class="doc-layout">

	<!-- SIDEBAR -->
	<aside class="doc-sidebar">
		<div class="doc-brand">🛡️ All-in-One SEO Fixer</div>
		<div class="doc-version">v2.3.0 Release</div>

		<div class="doc-nav-group">
			<div class="doc-nav-title">Overview</div>
			<ul class="doc-nav">
				<li><a href="#overview">Plugin Philosophy</a></li>
				<li><a href="#modules-index">20-Module Directory</a></li>
				<li><a href="#state-persistence">Database State Persistence</a></li>
				<li><a href="#disk-permissions">Dual-Engine File Access</a></li>
			</ul>
		</div>

		<div class="doc-nav-group">
			<div class="doc-nav-title">Core SEO & Auditing</div>
			<ul class="doc-nav">
				<li><a href="#asf-panel">360° SEO Audit Dashboard</a></li>
				<li><a href="#asf-ai-assistant">🤖 AI SEO Assistant & Copilot</a></li>
				<li><a href="#floating-widget">💬 Floating AI Copilot Widget</a></li>
				<li><a href="#asf-onpage">On-Page Tag Checker</a></li>
				<li><a href="#asf-serp-preview">Live SERP & Social Simulator</a></li>
				<li><a href="#asf-authority">On-Page Authority & E-E-A-T</a></li>
				<li><a href="#asf-keyword-analyzer">Keyword Density & N-Gram</a></li>
			</ul>
		</div>

		<div class="doc-nav-group">
			<div class="doc-nav-title">Speed, Media & Builders</div>
			<ul class="doc-nav">
				<li><a href="#asf-pagespeed">PageSpeed Insights & CWV</a></li>
				<li><a href="#asf-lazy-load">Lazy Load & LCP Guard</a></li>
				<li><a href="#asf-media">Media Scanner & Alt-Text</a></li>
				<li><a href="#asf-performance">Speed & Database Optimizer</a></li>
				<li><a href="#asf-builder-analyzer">Page Builder & Elementor</a></li>
			</ul>
		</div>

		<div class="doc-nav-group">
			<div class="doc-nav-title">Technical, GEO & Security</div>
			<ul class="doc-nav">
				<li><a href="#asf-schema">Schema JSON-LD Studio</a></li>
				<li><a href="#asf-geo">GEO & AI Search Hub (/llms.txt)</a></li>
				<li><a href="#asf-redirects">301 Redirects & 404 Monitor</a></li>
				<li><a href="#asf-link-cleaner">Broken Link Cleaner</a></li>
				<li><a href="#asf-security">Domain Security & Headers</a></li>
				<li><a href="#asf-error-doctor">Console & Error Doctor</a></li>
				<li><a href="#asf-w3c-validator">W3C Nu HTML Validator</a></li>
				<li><a href="#asf-gsc-inspector">Google Search Console</a></li>
			</ul>
		</div>

		<div class="doc-nav-group">
			<div class="doc-nav-title">Tools & Settings</div>
			<ul class="doc-nav">
				<li><a href="#asf-swiss-tools">🛠️ Swiss-Knife 19-in-1 Suite</a></li>
				<li><a href="#asf-settings">⚙️ Settings Hub (7 Tabs)</a></li>
				<li><a href="#diagnostics">🩺 System Diagnostics</a></li>
				<li><a href="#toast-alerts">Modern Toast Notifications</a></li>
				<li><a href="#developer-api">Developer API & Hooks</a></li>
			</ul>
		</div>
	</aside>

	<!-- MAIN CONTENT -->
	<main class="doc-main">

		<!-- HERO -->
		<div class="doc-hero">
			<h1>🛡️ All-in-One SEO Fixer & Auditor</h1>
			<p>Complete enterprise-grade technical SEO, AI Copilot, performance acceleration, structured schema graphs, and site diagnostics for WordPress.</p>
			<div class="doc-meta-pills">
				<span class="doc-pill">Version: 2.3.0</span>
				<span class="doc-pill">Author: Abid Ali</span>
				<span class="doc-pill">License: GPL-2.0</span>
				<span class="doc-pill">Zero Bloat / Native WP</span>
			</div>
		</div>

		<!-- SECTION 1: PHILOSOPHY -->
		<section class="doc-card" id="overview">
			<h2>1. Plugin Overview & Philosophy</h2>
			<p><strong>All-in-One SEO Fixer</strong> is built with high-performance native WordPress architecture. It avoids sluggish React or Vue bundles, delivering sub-50ms page renders and total theme/builder compatibility.</p>
			<div class="callout">
				<div class="callout-title">⚡ The Three Golden Architectural Principles:</div>
				<ol>
					<li><strong>100% Real Database Persistence</strong>: Every meta description, title tag, image alt attribute, redirect rule, and schema option writes directly to native WordPress database tables (<code>wp_postmeta</code>, <code>wp_options</code>, <code>wp_asf_404_logs</code>).</li>
					<li><strong>Dual Physical & Virtual Fallback Engine</strong>: Writes physical static files (<code>robots.txt</code>, <code>llms.txt</code>) when server disk permissions allow, and automatically falls back to virtual WordPress query filters when hostings are read-only.</li>
					<li><strong>Non-Destructive Safety</strong>: Safe chunked batch operations (50 records/batch), reversible media trash, and non-blocking toast notifications instead of jarring browser alert popups.</li>
				</ol>
			</div>
		</section>

		<!-- SECTION 2: MODULE INDEX -->
		<section class="doc-card" id="modules-index">
			<h2>2. Master 20-Module System Index</h2>
			<p>Access every module under the <strong>All SEO Fixer</strong> WordPress menu:</p>
			<table class="doc-table">
				<thead>
					<tr>
						<th>#</th>
						<th>Menu Item Title</th>
						<th>Page Slug</th>
						<th>Core Capabilities</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td><strong>360° SEO Audit</strong></td>
						<td><code>asf-panel</code></td>
						<td>Site health score (0–100), issue audit, and 4-page branded PDF generator.</td>
					</tr>
					<tr>
						<td>2</td>
						<td><strong>🤖 AI Assistant</strong></td>
						<td><code>asf-ai-assistant</code></td>
						<td>Groq LLaMA-3.3 full-page copilot, prompt chips, Master JSON prompt exporter & importer.</td>
					</tr>
					<tr>
						<td>3</td>
						<td><strong>On-Page Checker</strong></td>
						<td><code>asf-onpage</code></td>
						<td>Per-page title/meta audit, H1 hierarchy, thin content check, 1-click bulk auto-fixer.</td>
					</tr>
					<tr>
						<td>4</td>
						<td><strong>PageSpeed Insights</strong></td>
						<td><code>asf-pagespeed</code></td>
						<td>Lighthouse API v5 metrics: Performance, Accessibility, SEO, and Core Web Vitals (LCP, CLS, TBT).</td>
					</tr>
					<tr>
						<td>5</td>
						<td><strong>Lazy Load Images</strong></td>
						<td><code>asf-lazy-load</code></td>
						<td>Native HTML5 lazy load with LCP Hero Guard (prevents 1st image deferral).</td>
					</tr>
					<tr>
						<td>6</td>
						<td><strong>Media Scanner</strong></td>
						<td><code>asf-media</code></td>
						<td>Missing image alt detector, 1-click intelligent alt generator & orphan file manager.</td>
					</tr>
					<tr>
						<td>7</td>
						<td><strong>Speed & DB Optimizer</strong></td>
						<td><code>asf-performance</code></td>
						<td>Safe 50-batch revision pruner, spam cleaner, transient purger, and MySQL table optimizer.</td>
					</tr>
					<tr>
						<td>8</td>
						<td><strong>Page Builder Optimizer</strong></td>
						<td><code>asf-builder-analyzer</code></td>
						<td>Elementor JSON footprint inspector, DOM depth reducer, unused font/icon dequeuer.</td>
					</tr>
					<tr>
						<td>9</td>
						<td><strong>Google Search Console</strong></td>
						<td><code>asf-gsc-inspector</code></td>
						<td>OAuth/Service Account ranking queries, CTR metrics, and Google Indexing API v3.</td>
					</tr>
					<tr>
						<td>10</td>
						<td><strong>W3C HTML Validator</strong></td>
						<td><code>asf-w3c-validator</code></td>
						<td>Official W3C Nu HTML Checker with line-by-line syntax error reports.</td>
					</tr>
					<tr>
						<td>11</td>
						<td><strong>Keyword Density</strong></td>
						<td><code>asf-keyword-analyzer</code></td>
						<td>N-gram 1, 2, and 3-word phrase frequency calculator with keyword stuffing alerts.</td>
					</tr>
					<tr>
						<td>12</td>
						<td><strong>On-Page SEO Health</strong></td>
						<td><code>asf-authority</code></td>
						<td>Flesch-Kincaid readability scoring, E-E-A-T signals, and heading hierarchy verification.</td>
					</tr>
					<tr>
						<td>13</td>
						<td><strong>SERP Simulator</strong></td>
						<td><code>asf-serp-preview</code></td>
						<td>Live Desktop/Mobile Google search snippet & Facebook OG card simulator with direct save.</td>
					</tr>
					<tr>
						<td>14</td>
						<td><strong>Security & Headers</strong></td>
						<td><code>asf-security</code></td>
						<td>HTTP security headers (HSTS, CSP, X-Frame-Options), SSL expiry & DNSBL check.</td>
					</tr>
					<tr>
						<td>15</td>
						<td><strong>Console & Error Doctor</strong></td>
						<td><code>asf-error-doctor</code></td>
						<td>Front-end JS crash detector, mixed content script rewriter, debug.log error analyzer.</td>
					</tr>
					<tr>
						<td>16</td>
						<td><strong>Broken Link Cleaner</strong></td>
						<td><code>asf-link-cleaner</code></td>
						<td>404 broken hyperlink finder & double-domain typo auto-cleaner.</td>
					</tr>
					<tr>
						<td>17</td>
						<td><strong>301 Redirects</strong></td>
						<td><code>asf-redirects</code></td>
						<td>Permanent 301 redirect manager without .htaccess & real-time 404 hit logger table.</td>
					</tr>
					<tr>
						<td>18</td>
						<td><strong>Schema JSON-LD Studio</strong></td>
						<td><code>asf-schema</code></td>
						<td>Visual structured data graph builder for WebSite, Organization, LocalBusiness, Breadcrumbs.</td>
					</tr>
					<tr>
						<td>19</td>
						<td><strong>GEO & AI Search Hub</strong></td>
						<td><code>asf-geo</code></td>
						<td>Generative Engine Optimization (SearchGPT, Perplexity) & markdown /llms.txt generator.</td>
					</tr>
					<tr>
						<td>20</td>
						<td><strong>🛠️ Swiss-Knife Tools</strong></td>
						<td><code>asf-swiss-tools</code></td>
						<td>19-in-1 DNS, WHOIS, IP Geolocation, Ping, Port Scanner & cryptographic suite.</td>
					</tr>
					<tr>
						<td>21</td>
						<td><strong>Settings Hub</strong></td>
						<td><code>asf-settings</code></td>
						<td>7 organized horizontal tabs: General, AI, Local SEO, Robots, Diagnostics, License, Developer.</td>
					</tr>
				</tbody>
			</table>
		</section>

		<!-- SECTION 3.1: 360 AUDIT -->
		<section class="doc-card" id="asf-panel">
			<h2>3.1 360° SEO Audit & Health Dashboard (asf-panel)</h2>
			<p>The main dashboard provides an instant, unified view of your entire website's search readiness.</p>
			<h3>How It Works</h3>
			<p>On initial page load, the dashboard automatically reads the cached health score from <code>wp_options</code> (<code>asf_health_score_cache</code>) so you see your score, grade, and checkmark pills immediately. A background ping validates live status.</p>
			<h3>Step-by-Step Instructions</h3>
			<ol>
				<li>Open <strong>All SEO Fixer → 360° SEO Audit</strong>.</li>
				<li>Inspect the top <strong>Health Score Banner</strong>. Click <strong>↻ Re-check Now</strong> to re-run the 7-point health check.</li>
				<li>Click the primary button: <strong>Run Full 360° SEO Audit</strong>.</li>
				<li>Review the categorised findings (Technical SEO, Meta Tags, Media, Security).</li>
				<li>Click <strong>Download Audit PDF Report</strong> to download a client-ready, branded 4-page PDF summary.</li>
				<li>Click <strong>Ping Search Engines</strong> to notify Google and Bing crawlers of recent content updates.</li>
			</ol>
		</section>

		<!-- SECTION 3.2: AI ASSISTANT -->
		<section class="doc-card" id="asf-ai-assistant">
			<h2>3.2 🤖 AI SEO Assistant & Copilot Hub (asf-ai-assistant)</h2>
			<p>A full-page dedicated AI copilot pre-loaded with live context from your website.</p>
			<h3>Multi-Route AI Routing</h3>
			<ul>
				<li><strong>Route 1 (Primary)</strong>: <strong>Groq AI</strong> (LLaMA-3.3-70B) with sub-second response times.</li>
				<li><strong>Route 2 (Failover)</strong>: <strong>Google Gemini 1.5 Flash</strong>.</li>
				<li><strong>Route 3 (Failover)</strong>: <strong>OpenRouter LLaMA-3.3</strong>.</li>
				<li><strong>Route 4 (Offline)</strong>: Built-in contextual SEO heuristic rules engine.</li>
			</ul>
			<h3>Master AI JSON Workflow</h3>
			<ol>
				<li>Scroll to the <strong>Import & Apply External AI JSON Config</strong> card at the bottom.</li>
				<li>Click <strong>📥 Download Master AI Prompt File</strong>. This generates a complete structured JSON representation of your website.</li>
				<li>Upload this file to <strong>ChatGPT Plus (GPT-4o)</strong> or <strong>Claude 3.5 Sonnet</strong>.</li>
				<li>Copy the optimized JSON returned by the external AI.</li>
				<li>Paste it into the console and click <strong>⚡ Apply AI JSON Settings to Site</strong>. Your site's titles, descriptions, and settings will update in seconds.</li>
			</ol>
		</section>

		<!-- SECTION 3.3: FLOATING WIDGET -->
		<section class="doc-card" id="floating-widget">
			<h2>3.3 💬 Global Floating AI Assistant Bubble (Widget)</h2>
			<p>Available in the bottom-right corner across every page of the plugin.</p>
			<h3>How to Use It</h3>
			<ol>
				<li>Click the purple-blue gradient bubble with the pulsing green dot at the bottom-right.</li>
				<li>A <code>390px × 540px</code> chat drawer smoothly slides into view.</li>
				<li>Ask any technical SEO question or click a prompt chip (Audit Fixes, Meta Descs, Speed & CWV, Local Schema).</li>
				<li>Receive instant AI guidance while remaining on your active working screen.</li>
				<li>Click the minimize bar to hide the drawer.</li>
			</ol>
		</section>

		<!-- SECTION 3.4: ON-PAGE CHECKER -->
		<section class="doc-card" id="asf-onpage">
			<h2>3.4 On-Page SEO Checker & Tag Auditor (asf-onpage)</h2>
			<p>Audits every published post and page for title length, meta description quality, H1 tags, and content depth.</p>
			<h3>How to Use It</h3>
			<ol>
				<li>Select your post type filter (All, Pages, Posts, Products).</li>
				<li>Click <strong>Scan All Pages for SEO Errors</strong>.</li>
				<li>Inspect the flagged issues in the results table.</li>
				<li>Click <strong>⚡ 1-Click Auto-Fix Missing Meta Descriptions & Titles</strong> to automatically generate clean 145-character descriptions and standard SEO titles across all empty posts.</li>
				<li>Or click <strong>⚡ Quick Fix</strong> next to any row to open the editing modal and customize metadata directly.</li>
			</ol>
		</section>

		<!-- SECTION 3.5: PAGESPEED -->
		<section class="doc-card" id="asf-pagespeed">
			<h2>3.5 Google PageSpeed & Core Web Vitals (asf-pagespeed)</h2>
			<p>Direct integration with Google PageSpeed Insights API v5 (Lighthouse engine).</p>
			<h3>How to Use It</h3>
			<ol>
				<li>Add your free PageSpeed API Key in <strong>Settings → General & APIs</strong>.</li>
				<li>Choose <strong>Mobile</strong> or <strong>Desktop</strong> mode.</li>
				<li>Click <strong>Analyze PageSpeed & Core Web Vitals</strong>.</li>
				<li>Review radial score rings: Performance, Accessibility, Best Practices, SEO.</li>
				<li>Check your Core Web Vitals (LCP, CLS, TBT, FCP) against Google's passing thresholds.</li>
			</ol>
		</section>

		<!-- SECTION 3.6: LAZY LOAD -->
		<section class="doc-card" id="asf-lazy-load">
			<h2>3.6 Native Lazy Load Images & Media Optimizer (asf-lazy-load)</h2>
			<p>Enforces native HTML5 <code>loading="lazy"</code> on images and iframes with LCP Hero Guard protection.</p>
			<h3>Settings Explained</h3>
			<ul>
				<li><strong>Content Images</strong>: Adds <code>loading="lazy"</code> to all post content images.</li>
				<li><strong>Video/Iframe</strong>: Defers YouTube, Vimeo, and Google Maps iframe embeds.</li>
				<li><strong>LCP Hero Guard</strong>: Excludes the first above-the-fold image from lazy loading to protect your Largest Contentful Paint (LCP) score.</li>
			</ul>
			<p>Click <strong>Save Lazy Load Settings</strong> to store your configuration. Toggles persistently retain state on reload.</p>
		</section>

		<!-- SECTION 3.7: MEDIA SCANNER -->
		<section class="doc-card" id="asf-media">
			<h2>3.7 Media Library Scanner & Alt-Text Generator (asf-media)</h2>
			<p>Detects missing alt text and discovers orphan media attachments taking up disk space.</p>
			<h3>How to Use It</h3>
			<ol>
				<li>Click <strong>Scan Media Library (All Images)</strong>.</li>
				<li>Filter by <em>Missing Alt Text Only</em> or <em>Orphan Images Only</em>.</li>
				<li>Click <strong>⚡ 1-Click Scan & Fill Missing Alt Texts</strong> to auto-generate readable alt attributes from clean filenames.</li>
				<li>Or edit alt text directly inside any table row input field and click <strong>Save</strong>.</li>
				<li>For unused orphan images, click <strong>Move to Trash</strong> to safely stage them in the native WordPress Media Trash.</li>
			</ol>
		</section>

		<!-- SECTION 3.8: PERFORMANCE -->
		<section class="doc-card" id="asf-performance">
			<h2>3.8 Speed & Database Optimizer (asf-performance)</h2>
			<p>Cleans historical revisions, auto-drafts, trashed items, spam comments, and expired transients.</p>
			<div class="callout">
				<div class="callout-title">🛡️ Safe Batch Execution (50 Records / Batch)</div>
				<p>To prevent server crashes, memory limits, or database locks on shared hosting, the optimizer executes queries in controlled chunks of 50 records.</p>
			</div>
			<ol>
				<li>Click <strong>Scan Database Bloat</strong>.</li>
				<li>Check the items you want to purge.</li>
				<li>Click <strong>⚡ Optimize Database & Purge Bloat</strong>.</li>
			</ol>
		</section>

		<!-- SECTION 3.9: PAGE BUILDER -->
		<section class="doc-card" id="asf-builder-analyzer">
			<h2>3.9 Page Builder & Elementor Overhead Optimizer (asf-builder-analyzer)</h2>
			<p>Measures Elementor JSON postmeta bloat, deep DOM ceilings, and font overhead.</p>
			<ol>
				<li>Click <strong>Scan Active Builders & Bloat</strong>.</li>
				<li>Review pages exceeding 500 KB payload.</li>
				<li>Enable toggles: <em>Disable Elementor Eicons</em>, <em>Disable Elementor Google Fonts</em>, and <em>Purge Orphaned Builder Revisions</em>.</li>
				<li>Click <strong>🚀 Optimize Page Builders & Clean Revisions</strong>.</li>
			</ol>
		</section>

		<!-- SECTION 3.10: GSC -->
		<section class="doc-card" id="asf-gsc-inspector">
			<h2>3.10 Google Search Console & Instant Indexer (asf-gsc-inspector)</h2>
			<p>Connects with Google Search Console and Indexing API v3.</p>
			<ol>
				<li>Enter OAuth credentials or Google Cloud Service Account JSON key in Settings.</li>
				<li>Click <strong>Fetch Search Console Performance</strong> to inspect top ranking keywords, CTR, and average positions.</li>
				<li>To index a new URL immediately, enter the URL and click <strong>⚡ Request Google Indexing</strong>.</li>
			</ol>
		</section>

		<!-- SECTION 3.11: W3C -->
		<section class="doc-card" id="asf-w3c-validator">
			<h2>3.11 W3C Nu HTML Markup Validator (asf-w3c-validator)</h2>
			<p>Validates front-end HTML against the official W3C Nu HTML Checker.</p>
			<ol>
				<li>Select a page from the dropdown or enter a custom URL.</li>
				<li>Click <strong>Validate HTML Syntax</strong>.</li>
				<li>Inspect line numbers, offending tags, and clear error explanations to fix broken markup.</li>
			</ol>
		</section>

		<!-- SECTION 3.12: KEYWORD DENSITY -->
		<section class="doc-card" id="asf-keyword-analyzer">
			<h2>3.12 Keyword Density & N-Gram Analyzer (asf-keyword-analyzer)</h2>
			<p>Analyzes 1-word, 2-word, and 3-word n-gram frequencies to avoid keyword stuffing penalties.</p>
			<ol>
				<li>Select a post and optionally enter your focus keyword.</li>
				<li>Click <strong>Analyze Keyword Density</strong>.</li>
				<li>Review the keyword tables. Ensure primary terms stay between <strong>1.5% and 2.5%</strong>. Terms over 3.5% will be flagged with a warning.</li>
			</ol>
		</section>

		<!-- SECTION 3.13: AUTHORITY -->
		<section class="doc-card" id="asf-authority">
			<h2>3.13 On-Page SEO Health & Authority Hub (asf-authority)</h2>
			<p>Evaluates Flesch-Kincaid readability, heading flow, and Google E-E-A-T trust signals.</p>
			<ol>
				<li>Select a published post and click <strong>Analyze On-Page Authority & Readability</strong>.</li>
				<li>Verify that your Flesch Reading Ease score is in the optimal 60–70 range.</li>
				<li>Ensure no heading levels are skipped (e.g. H1 directly to H3).</li>
				<li>Verify author bio boxes and outbound authority citation links are present.</li>
			</ol>
		</section>

		<!-- SECTION 3.14: SERP PREVIEW -->
		<section class="doc-card" id="asf-serp-preview">
			<h2>3.14 Live SERP & Social Card Simulator (asf-serp-preview)</h2>
			<p>Real-time visual preview of Google Search results (Desktop and Mobile) and Facebook Open Graph cards.</p>
			<ol>
				<li>Select a page from the dropdown. Saved titles and descriptions load automatically.</li>
				<li>Edit the SEO Title and Meta Description.</li>
				<li>Check pixel-width meters (580px desktop ceiling, 960px mobile ceiling).</li>
				<li>Click <strong>Save Snippet to Page</strong> to save changes directly to the database.</li>
			</ol>
		</section>

		<!-- SECTION 3.15: SECURITY -->
		<section class="doc-card" id="asf-security">
			<h2>3.15 Domain Security & HTTP Headers (asf-security)</h2>
			<p>Scans server response headers for HSTS, X-Frame-Options, X-Content-Type-Options, and SSL validity.</p>
			<ol>
				<li>Click <strong>Scan Security & HTTP Headers</strong>.</li>
				<li>Inspect status badges.</li>
				<li>Click <strong>⚡ 1-Click Enforce Security Headers</strong> to automatically inject missing headers on all front-end requests.</li>
			</ol>
		</section>

		<!-- SECTION 3.16: ERROR DOCTOR -->
		<section class="doc-card" id="asf-error-doctor">
			<h2>3.16 Console & Error Doctor (asf-error-doctor)</h2>
			<p>Monitors front-end JavaScript errors, mixed content scripts, and PHP runtime fatal errors.</p>
			<ol>
				<li>Enable toggles: <em>Mixed Content HTTPS Enforcer</em>, <em>Safe jQuery Compatibility Layer</em>, <em>Console Monitor</em>.</li>
				<li>Click <strong>Scan Console & Error Logs</strong>.</li>
				<li>Click <strong>Auto-Fix</strong> next to identified issues to deploy safe compatibility shims.</li>
			</ol>
		</section>

		<!-- SECTION 3.17: LINK CLEANER -->
		<section class="doc-card" id="asf-link-cleaner">
			<h2>3.17 Broken Link & Typo Cleaner (asf-link-cleaner)</h2>
			<p>Finds broken 404 hyperlinks and common double-domain concatenation typos.</p>
			<ol>
				<li>Click <strong>Scan All Content for Broken Links</strong>.</li>
				<li>Review the flagged URLs table.</li>
				<li>Click <strong>⚡ Auto-Fix Malformed URLs</strong> to instantly clean concatenated domain prefixes.</li>
			</ol>
		</section>

		<!-- SECTION 3.18: REDIRECTS -->
		<section class="doc-card" id="asf-redirects">
			<h2>3.18 301 Canonical Redirect Manager & 404 Monitor (asf-redirects)</h2>
			<p>Database-driven 301 permanent redirect engine operating on <code>template_redirect</code> without modifying <code>.htaccess</code>.</p>
			<ol>
				<li>Click <strong>+ Add Redirect Rule</strong>.</li>
				<li>Enter the legacy Source Path (e.g. <code>/old-slug/</code>) and the Target Destination URL.</li>
				<li>Click <strong>Save All Redirect Rules</strong>.</li>
				<li>Scroll to the <strong>Real-Time 404 URL Monitor</strong> table below to see which missing URLs visitors are hitting, and click <strong>+ Convert to 301</strong> to fix them in one click.</li>
			</ol>
		</section>

		<!-- SECTION 3.19: SCHEMA -->
		<section class="doc-card" id="asf-schema">
			<h2>3.19 Schema.org JSON-LD Studio (asf-schema)</h2>
			<p>Visual generator for Google-compliant structured data graphs (WebSite, Organization, LocalBusiness, Breadcrumbs).</p>
			<ol>
				<li>Turn on the master toggle: <strong>Active Live Injection</strong>.</li>
				<li>Select your desired schema components.</li>
				<li>Fill in your business name, address, phone number, and logo URL.</li>
				<li>Click <strong>Save Schema Settings</strong>.</li>
				<li>Click <strong>Google Rich Results Test</strong> to verify live rich snippets with Google.</li>
			</ol>
		</section>

		<!-- SECTION 3.20: GEO HUB -->
		<section class="doc-card" id="asf-geo">
			<h2>3.20 GEO & AI Search Hub (/llms.txt) (asf-geo)</h2>
			<p>Prepares your website for AI search engines (ChatGPT Search, Perplexity AI, Claude, Gemini) and serves <code>/llms.txt</code>.</p>
			<ol>
				<li>Click <strong>🔍 Run AI Readiness Audit</strong>.</li>
				<li>Under <strong>AI Crawler Permissions</strong>, select which bots are allowed (GPTBot, PerplexityBot, ClaudeBot, Google-Extended).</li>
				<li>Edit your markdown summary in the <strong>LLMs.txt Editor</strong>.</li>
				<li>Click <strong>Regenerate & Save /llms.txt</strong>.</li>
				<li>Click <strong>View Live /llms.txt</strong> to view the served file live in your browser.</li>
			</ol>
		</section>

		<!-- SECTION 3.21: SWISS TOOLS -->
		<section class="doc-card" id="asf-swiss-tools">
			<h2>3.21 🛠️ Swiss-Knife 19-in-1 Diagnostic Suite (asf-swiss-tools)</h2>
			<p>19 built-in network, DNS, WHOIS, IP, and cryptographic developer tools right inside WordPress:</p>
			<table class="doc-table">
				<thead>
					<tr>
						<th>Category</th>
						<th>Tools Available</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>Network & DNS</strong></td>
						<td>DNS Lookup, WHOIS Lookup, Server Ping, Port Scanner (80, 443, 22, 21, 3306), HTTP Headers.</td>
					</tr>
					<tr>
						<td><strong>IP & Location</strong></td>
						<td>IP Geolocation, Reverse IP Domain Lookup, User-Agent Parser.</td>
					</tr>
					<tr>
						<td><strong>Security & Crypto</strong></td>
						<td>SSL Certificate Checker, MD5 & SHA256 Hash Generator, Random Password Generator.</td>
					</tr>
					<tr>
						<td><strong>Encoding & Text</strong></td>
						<td>Base64 Encode/Decode, URL Encode/Decode, Word & Character Counter, Case Converter, Lorem Ipsum Generator, JSON Formatter, HTML Entity Encoder.</td>
					</tr>
				</tbody>
			</table>
			<p>Select any tool, enter the input target, click <strong>Run Utility</strong>, and click <strong>Copy Result</strong>.</p>
		</section>

		<!-- SECTION 3.22: SETTINGS -->
		<section class="doc-card" id="asf-settings">
			<h2>3.22 ⚙️ Settings Hub (7 Horizontal Tabs) (asf-settings)</h2>
			<p>Central configuration organized into 7 native WordPress tabs:</p>
			<ol>
				<li><strong>⚙️ General & APIs</strong>: Homepage SEO description, PageSpeed Insights API key, Google Search Console OAuth & Service Account JSON.</li>
				<li><strong>🤖 AI Engine & Keys</strong>: Groq API key (Primary), Google Gemini Flash key, OpenRouter key.</li>
				<li><strong>📍 Local & GEO SEO</strong>: Geotargeting meta tags, LocalBusiness schema, GPS coordinate auto-detection.</li>
				<li><strong>🤖 Robots & LLMs.txt</strong>: Physical and virtual robots.txt & llms.txt configuration.</li>
				<li><strong>🩺 System Diagnostics & Logs</strong>: 16-point server environment health check, debug log viewer, 1-click email report dispatcher.</li>
				<li><strong>👑 Pro License & Plans</strong>: Product key activation and feature comparison.</li>
				<li><strong>👨‍💻 About Developer</strong>: Bio for creator Abid Ali, portfolio links, and support contact.</li>
			</ol>
		</section>

		<!-- SECTION 4: PERSISTENCE -->
		<section class="doc-card" id="state-persistence">
			<h2>4. Data Persistence & State Management Architecture</h2>
			<p>All-in-One SEO Fixer guarantees that every action creates real database records:</p>
			<table class="doc-table">
				<thead>
					<tr>
						<th>Module / Feature</th>
						<th>Database Table</th>
						<th>Database Key / Column</th>
						<th>Reload Hydration</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>SEO Title Tags</td>
						<td><code>wp_postmeta</code></td>
						<td><code>_asf_seo_title</code>, <code>rank_math_title</code>, <code>_yoast_wpseo_title</code></td>
						<td><code>get_post_meta()</code></td>
					</tr>
					<tr>
						<td>Meta Descriptions</td>
						<td><code>wp_postmeta</code></td>
						<td><code>_asf_meta_description</code>, <code>rank_math_description</code>, <code>_yoast_wpseo_metadesc</code></td>
						<td><code>get_post_meta()</code></td>
					</tr>
					<tr>
						<td>Image Alt Texts</td>
						<td><code>wp_postmeta</code></td>
						<td><code>_wp_attachment_image_alt</code></td>
						<td><code>get_post_meta()</code></td>
					</tr>
					<tr>
						<td>301 Redirect Rules</td>
						<td><code>wp_options</code></td>
						<td><code>asf_redirects</code></td>
						<td><code>get_option()</code></td>
					</tr>
					<tr>
						<td>404 Error Hits</td>
						<td><code>wp_asf_404_logs</code></td>
						<td><code>url</code>, <code>hits</code>, <code>last_seen</code></td>
						<td><code>$wpdb->get_results()</code></td>
					</tr>
					<tr>
						<td>Health Score Cache</td>
						<td><code>wp_options</code></td>
						<td><code>asf_health_score_cache</code></td>
						<td>Pre-rendered on page load</td>
					</tr>
					<tr>
						<td>Lazy Load Toggles</td>
						<td><code>wp_options</code></td>
						<td><code>asf_enable_lazy</code>, <code>asf_exclude_first</code></td>
						<td><code>checked()</code></td>
					</tr>
					<tr>
						<td>Robots & LLMs Content</td>
						<td><code>wp_options</code> / Disk</td>
						<td><code>asf_robots_txt_content</code>, <code>asf_llms_txt_content</code></td>
						<td>Disk check + <code>get_option()</code></td>
					</tr>
				</tbody>
			</table>
		</section>

		<!-- SECTION 5: DISK ACCESS -->
		<section class="doc-card" id="disk-permissions">
			<h2>5. Dual-Engine File Access (Physical vs Virtual)</h2>
			<p>The plugin is designed to operate seamlessly across both write-enabled and locked-down hosting architectures:</p>
			<div class="callout">
				<div class="callout-title">How the Dual Fallback Works:</div>
				<ul>
					<li><strong>Physical Mode (Writable Disk)</strong>: If <code>is_writable(ABSPATH)</code> is true, physical <code>robots.txt</code> and <code>llms.txt</code> files are written to disk. Web servers (Nginx/Apache) serve these static files directly without loading PHP or WordPress, maximizing performance.</li>
					<li><strong>Virtual Mode (Read-Only Disk)</strong>: If file manager write access is restricted, the plugin seamlessly falls back to WordPress core filters (<code>do_robotstxt</code> and <code>template_redirect</code>). Crawlers receive the exact same dynamic output.</li>
				</ul>
			</div>
		</section>

		<!-- SECTION 6: TOAST NOTIFICATIONS -->
		<section class="doc-card" id="toast-alerts">
			<h2>6. Modern Toast Notification System</h2>
			<p>In version 2.3.0, the default browser alert dialog (<code>window.alert</code>) has been replaced by a sleek, modern toast notification system:</p>
			<ul>
				<li><span class="badge badge-green">Success</span>: Emerald green toast with checkmark icon.</li>
				<li><span class="badge badge-err">Error</span>: Crimson red toast with warning icon.</li>
				<li><span class="badge badge-warn">Warning</span>: Amber gold toast with caution symbol.</li>
				<li><span class="badge badge-blue">Info</span>: Deep indigo toast with info badge.</li>
			</ul>
			<p>Toasts float at the bottom-right of the screen, animate into view, auto-dismiss after 4.5 seconds, and never freeze the browser window.</p>
		</section>

		<!-- SECTION 7: DEVELOPER API -->
		<section class="doc-card" id="developer-api">
			<h2>7. Developer API & AJAX Endpoints</h2>
			<p>All AJAX actions route through <code>admin-ajax.php</code> with nonce validation and <code>manage_options</code> capability checks:</p>
			<pre><code>// Example: Hooking into the 360° Audit completion
add_action( 'asf_after_audit_completed', function( $audit_data ) {
    // Custom post-audit webhook or notification
} );</code></pre>
		</section>

		<!-- FOOTER -->
		<footer class="doc-footer">
			All-in-One SEO Fixer v2.3.0 · Engineered with ❤️ by <a href="https://abidalidev.com" target="_blank">Abid Ali</a> · <a href="mailto:support@abidalidev.com">support@abidalidev.com</a>
		</footer>

	</main>
</div>

</body>
</html>
"""

with open(html_out_path, "w", encoding="utf-8") as f:
    f.write(html_template)

print("documentation.html updated successfully!")
