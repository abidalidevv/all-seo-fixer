import os
import re

md_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\DOCUMENTATION.md"
html_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\documentation.html"

with open(md_path, "r", encoding="utf-8") as f:
    md_text = f.read()

css_styles = """
:root {
	--bg: #f8fafc;
	--surface: #ffffff;
	--border: #e2e8f0;
	--border-light: #edf2f7;
	--text-main: #0f172a;
	--text-body: #334155;
	--text-muted: #64748b;
	--sidebar-bg: #0f172a;
	--sidebar-text: #94a3b8;
	--sidebar-text-hover: #ffffff;
	--sidebar-hover-bg: #1e293b;
	--sidebar-active-bg: #1e293b;
	--sidebar-border: #1e293b;
	--accent: #0f172a;
	--accent-subtle: #f1f5f9;
	--badge-green-bg: #ecfdf5;
	--badge-green-text: #065f46;
	--badge-blue-bg: #f1f5f9;
	--badge-blue-text: #0f172a;
	--badge-warn-bg: #fffbeb;
	--badge-warn-text: #92400e;
	--badge-err-bg: #fef2f2;
	--badge-err-text: #991b1b;
	--code-bg: #0f172a;
	--inline-code-bg: #f1f5f9;
	--inline-code-border: #e2e8f0;
	--inline-code-text: #0f172a;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	background-color: var(--bg);
	color: var(--text-body);
	line-height: 1.65;
	font-size: 14.5px;
	-webkit-font-smoothing: antialiased;
}

.layout {
	display: flex;
	min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
	width: 290px;
	background-color: var(--sidebar-bg);
	color: var(--sidebar-text);
	position: fixed;
	top: 0;
	bottom: 0;
	left: 0;
	overflow-y: auto;
	padding: 24px 16px;
	display: flex;
	flex-direction: column;
	border-right: 1px solid var(--sidebar-border);
	z-index: 100;
}

.brand {
	display: flex;
	align-items: center;
	gap: 10px;
	font-size: 16px;
	font-weight: 700;
	color: #ffffff;
	letter-spacing: -0.3px;
	margin-bottom: 4px;
}

.brand-icon {
	font-size: 20px;
}

.brand-meta {
	font-size: 11px;
	color: #64748b;
	margin-bottom: 16px;
	display: flex;
	align-items: center;
	gap: 6px;
}

.brand-tag {
	background: #1e293b;
	color: #94a3b8;
	padding: 2px 6px;
	border-radius: 4px;
	font-weight: 600;
}

.search-box {
	margin-bottom: 16px;
}

.search-input {
	width: 100%;
	padding: 8px 12px;
	background: #1e293b;
	border: 1px solid #334155;
	border-radius: 6px;
	color: #ffffff;
	font-size: 13px;
	outline: none;
	transition: border-color 0.15s ease;
}

.search-input:focus {
	border-color: #64748b;
}

.nav-section-title {
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.8px;
	color: #64748b;
	margin: 18px 0 6px 8px;
}

.nav-list {
	list-style: none;
}

.nav-list li a {
	display: block;
	padding: 7px 10px;
	color: var(--sidebar-text);
	text-decoration: none;
	font-size: 13px;
	border-radius: 5px;
	transition: all 0.15s ease;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.nav-list li a:hover,
.nav-list li a.active {
	background: var(--sidebar-hover-bg);
	color: var(--sidebar-text-hover);
}

/* MAIN CONTENT */
.main-content {
	margin-left: 290px;
	flex: 1;
	padding: 48px 56px;
	max-width: 1040px;
}

/* HEADER CARD */
.hero-card {
	background: var(--surface);
	border: 1px solid var(--border);
	border-radius: 10px;
	padding: 36px 40px;
	margin-bottom: 36px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.hero-title {
	font-size: 28px;
	font-weight: 800;
	color: var(--text-main);
	letter-spacing: -0.5px;
	margin-bottom: 8px;
	display: flex;
	align-items: center;
	gap: 12px;
}

.hero-desc {
	font-size: 15px;
	color: var(--text-muted);
	line-height: 1.6;
	margin-bottom: 20px;
}

.hero-meta-row {
	display: flex;
	gap: 12px;
	flex-wrap: wrap;
}

.hero-chip {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 4px 12px;
	background: var(--accent-subtle);
	border: 1px solid var(--border);
	border-radius: 6px;
	font-size: 12px;
	font-weight: 600;
	color: var(--text-main);
}

/* SECTION CARDS */
.doc-card {
	background: var(--surface);
	border: 1px solid var(--border);
	border-radius: 8px;
	padding: 32px 36px;
	margin-bottom: 32px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.doc-card h2 {
	font-size: 21px;
	font-weight: 700;
	color: var(--text-main);
	letter-spacing: -0.3px;
	margin-bottom: 12px;
	padding-bottom: 10px;
	border-bottom: 1px solid var(--border-light);
	display: flex;
	align-items: center;
	gap: 10px;
}

.doc-card h3 {
	font-size: 16px;
	font-weight: 600;
	color: var(--text-main);
	margin: 22px 0 8px;
}

.doc-card h4 {
	font-size: 14px;
	font-weight: 600;
	color: var(--text-main);
	margin: 16px 0 6px;
}

.doc-card p {
	margin-bottom: 14px;
	line-height: 1.65;
}

.doc-card ul, .doc-card ol {
	margin-left: 22px;
	margin-bottom: 16px;
}

.doc-card li {
	margin-bottom: 6px;
}

/* CALLOUTS */
.callout {
	background: #f8fafc;
	border: 1px solid var(--border);
	border-left: 4px solid var(--text-main);
	border-radius: 0 6px 6px 0;
	padding: 16px 20px;
	margin: 18px 0;
}

.callout-title {
	font-size: 13.5px;
	font-weight: 700;
	color: var(--text-main);
	margin-bottom: 4px;
}

/* TABLES */
table.clean-table {
	width: 100%;
	border-collapse: collapse;
	margin: 18px 0 22px;
	font-size: 13.5px;
}

table.clean-table th, table.clean-table td {
	padding: 10px 14px;
	border: 1px solid var(--border);
	text-align: left;
}

table.clean-table th {
	background: #f8fafc;
	font-weight: 600;
	color: var(--text-main);
}

table.clean-table tr:nth-child(even) td {
	background: #fafbfc;
}

/* CODE & BADGES */
code {
	background: var(--inline-code-bg);
	border: 1px solid var(--inline-code-border);
	padding: 2px 6px;
	border-radius: 4px;
	font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
	font-size: 12.5px;
	color: var(--inline-code-text);
}

pre {
	background: var(--code-bg);
	color: #f8fafc;
	padding: 18px 20px;
	border-radius: 6px;
	overflow-x: auto;
	font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
	font-size: 13px;
	line-height: 1.5;
	margin: 16px 0;
	border: 1px solid #1e293b;
	position: relative;
}

pre code {
	background: transparent;
	border: none;
	padding: 0;
	color: inherit;
}

.badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 4px;
	font-size: 11.5px;
	font-weight: 600;
}

.badge-green { background: var(--badge-green-bg); color: var(--badge-green-text); }
.badge-slate { background: var(--badge-blue-bg); color: var(--badge-blue-text); border: 1px solid var(--border); }
.badge-warn  { background: var(--badge-warn-bg); color: var(--badge-warn-text); }
.badge-err   { background: var(--badge-err-bg); color: var(--badge-err-text); }

/* FOOTER */
.doc-footer {
	text-align: center;
	padding: 36px 0 20px;
	font-size: 13px;
	color: var(--text-muted);
	border-top: 1px solid var(--border);
	margin-top: 48px;
}

.doc-footer a {
	color: var(--text-main);
	text-decoration: underline;
	font-weight: 600;
}

/* FLOATING TOP BUTTON */
.back-top {
	position: fixed;
	bottom: 24px;
	right: 24px;
	background: var(--text-main);
	color: #ffffff;
	width: 40px;
	height: 40px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	border: none;
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
	font-size: 16px;
	opacity: 0.85;
	transition: opacity 0.15s ease;
	text-decoration: none;
}

.back-top:hover {
	opacity: 1;
}

@media print {
	.sidebar, .back-top, .search-box { display: none !important; }
	.main-content { margin-left: 0 !important; padding: 0 !important; max-width: 100% !important; }
	.doc-card { box-shadow: none !important; border: 1px solid #ccc !important; page-break-inside: avoid; }
}

@media (max-width: 900px) {
	.sidebar { display: none; }
	.main-content { margin-left: 0; padding: 24px; }
}
"""

def format_md_to_html(md):
    def code_repl(m):
        code_body = m.group(2)
        code_body = code_body.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')
        return f'<pre><code>{code_body.strip()}</code></pre>'
    
    md = re.sub(r'```([a-zA-Z0-9_-]*)\n(.*?)```', code_repl, md, flags=re.DOTALL)
    md = re.sub(r'`([^`]+)`', r'<code>\1</code>', md)
    
    lines = md.split('\n')
    new_lines = []
    in_table = False
    table_rows = []
    
    for line in lines:
        if line.strip().startswith('|') and line.strip().endswith('|'):
            if not in_table:
                in_table = True
                table_rows = [line.strip()]
            else:
                table_rows.append(line.strip())
        else:
            if in_table:
                in_table = False
                th_row = table_rows[0]
                th_cells = [c.strip() for c in th_row.split('|')[1:-1]]
                tbl_html = '<table class="clean-table">\n<thead>\n<tr>'
                for c in th_cells:
                    tbl_html += f'<th>{c}</th>'
                tbl_html += '</tr>\n</thead>\n<tbody>\n'
                
                start_idx = 1
                if len(table_rows) > 1 and '---' in table_rows[1]:
                    start_idx = 2
                
                for r in table_rows[start_idx:]:
                    cells = [c.strip() for c in r.split('|')[1:-1]]
                    tbl_html += '<tr>'
                    for c in cells:
                        tbl_html += f'<td>{c}</td>'
                    tbl_html += '</tr>\n'
                tbl_html += '</tbody>\n</table>'
                new_lines.append(tbl_html)
                table_rows = []
            new_lines.append(line)
            
    if in_table and table_rows:
        th_row = table_rows[0]
        th_cells = [c.strip() for c in th_row.split('|')[1:-1]]
        tbl_html = '<table class="clean-table">\n<thead>\n<tr>'
        for c in th_cells:
            tbl_html += f'<th>{c}</th>'
        tbl_html += '</tr>\n</thead>\n<tbody>\n'
        start_idx = 1
        if len(table_rows) > 1 and '---' in table_rows[1]:
            start_idx = 2
        for r in table_rows[start_idx:]:
            cells = [c.strip() for c in r.split('|')[1:-1]]
            tbl_html += '<tr>'
            for c in cells:
                tbl_html += f'<td>{c}</td>'
            tbl_html += '</tr>\n'
        tbl_html += '</tbody>\n</table>'
        new_lines.append(tbl_html)

    text = '\n'.join(new_lines)
    text = re.sub(r'\*\*([^*]+)\*\*', r'<strong>\1</strong>', text)
    text = re.sub(r'\*([^*]+)\*', r'<em>\1</em>', text)
    text = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', r'<a href="\2" target="_blank">\1</a>', text)
    return text

formatted_body = format_md_to_html(md_text)
sections = re.split(r'\n(?=##\s+)', formatted_body)

header_section = sections[0]
card_sections = sections[1:]

sidebar_nav_items = [
    ("Overview & Core", [
        ("overview", "Plugin Philosophy"),
        ("module-index", "20-Module Directory"),
        ("state-persistence", "Data Persistence Architecture"),
        ("disk-permissions", "Dual-Engine File Access"),
    ]),
    ("Core SEO & Auditing", [
        ("asf-panel", "360° SEO Audit Dashboard"),
        ("asf-ai-assistant", "AI SEO Assistant & Copilot"),
        ("floating-widget", "Floating Copilot Widget"),
        ("asf-onpage", "On-Page Tag Checker"),
        ("asf-serp-preview", "Live SERP & Social Simulator"),
        ("asf-authority", "On-Page Authority & E-E-A-T"),
        ("asf-keyword-analyzer", "Keyword Density & N-Gram"),
    ]),
    ("Speed, Media & Builders", [
        ("asf-pagespeed", "Google PageSpeed Insights"),
        ("asf-lazy-load", "Lazy Load & LCP Guard"),
        ("asf-media", "Media Scanner & Alt-Text"),
        ("asf-performance", "Speed & Database Optimizer"),
        ("asf-builder-analyzer", "Page Builder & Elementor"),
    ]),
    ("Technical, GEO & Security", [
        ("asf-schema", "Schema JSON-LD Studio"),
        ("asf-geo", "GEO & AI Search Hub (/llms.txt)"),
        ("asf-redirects", "301 Redirects & 404 Monitor"),
        ("asf-link-cleaner", "Broken Link Cleaner"),
        ("asf-security", "Domain Security & Headers"),
        ("asf-error-doctor", "Console & Error Doctor"),
        ("asf-w3c-validator", "W3C HTML Validator"),
        ("asf-gsc-inspector", "Google Search Console"),
    ]),
    ("Tools & Settings", [
        ("asf-swiss-tools", "Swiss-Knife 19-in-1 Suite"),
        ("asf-settings", "Settings Hub (7 Tabs)"),
        ("toast-alerts", "Modern Toast System"),
        ("developer-api", "Developer API & Endpoints"),
    ])
]

sidebar_html = ""
for group_title, items in sidebar_nav_items:
    sidebar_html += f'<div class="nav-section-title">{group_title}</div>\n<ul class="nav-list">\n'
    for anchor, title in items:
        sidebar_html += f'<li><a href="#{anchor}">{title}</a></li>\n'
    sidebar_html += '</ul>\n'

def make_card(sec_text):
    sec_lines = sec_text.strip().split('\n')
    title_line = sec_lines[0]
    body_lines = sec_lines[1:]
    title_clean = re.sub(r'^##\s+', '', title_line).strip()
    anchor_id = title_clean.lower()
    
    if '360° seo audit' in anchor_id or 'asf-panel' in anchor_id:
        anchor_id = 'asf-panel'
    elif 'ai seo assistant' in anchor_id or 'asf-ai-assistant' in anchor_id:
        anchor_id = 'asf-ai-assistant'
    elif 'floating ai assistant' in anchor_id or 'floating' in anchor_id:
        anchor_id = 'floating-widget'
    elif 'on-page seo checker' in anchor_id or 'asf-onpage' in anchor_id:
        anchor_id = 'asf-onpage'
    elif 'pagespeed' in anchor_id or 'asf-pagespeed' in anchor_id:
        anchor_id = 'asf-pagespeed'
    elif 'lazy load' in anchor_id or 'asf-lazy-load' in anchor_id:
        anchor_id = 'asf-lazy-load'
    elif 'media library' in anchor_id or 'asf-media' in anchor_id:
        anchor_id = 'asf-media'
    elif 'speed & database' in anchor_id or 'asf-performance' in anchor_id:
        anchor_id = 'asf-performance'
    elif 'page builder' in anchor_id or 'asf-builder-analyzer' in anchor_id:
        anchor_id = 'asf-builder-analyzer'
    elif 'google search console' in anchor_id or 'asf-gsc-inspector' in anchor_id:
        anchor_id = 'asf-gsc-inspector'
    elif 'w3c nu html' in anchor_id or 'asf-w3c-validator' in anchor_id:
        anchor_id = 'asf-w3c-validator'
    elif 'keyword density' in anchor_id or 'asf-keyword-analyzer' in anchor_id:
        anchor_id = 'asf-keyword-analyzer'
    elif 'on-page seo health' in anchor_id or 'asf-authority' in anchor_id:
        anchor_id = 'asf-authority'
    elif 'live serp' in anchor_id or 'asf-serp-preview' in anchor_id:
        anchor_id = 'asf-serp-preview'
    elif 'domain security' in anchor_id or 'asf-security' in anchor_id:
        anchor_id = 'asf-security'
    elif 'console & error doctor' in anchor_id or 'asf-error-doctor' in anchor_id:
        anchor_id = 'asf-error-doctor'
    elif 'broken link' in anchor_id or 'asf-link-cleaner' in anchor_id:
        anchor_id = 'asf-link-cleaner'
    elif '301 canonical redirect' in anchor_id or 'asf-redirects' in anchor_id:
        anchor_id = 'asf-redirects'
    elif 'schema.org' in anchor_id or 'asf-schema' in anchor_id:
        anchor_id = 'asf-schema'
    elif 'geo & ai search' in anchor_id or 'asf-geo' in anchor_id:
        anchor_id = 'asf-geo'
    elif 'swiss-knife' in anchor_id or 'asf-swiss-tools' in anchor_id:
        anchor_id = 'asf-swiss-tools'
    elif 'settings hub' in anchor_id or 'asf-settings' in anchor_id:
        anchor_id = 'asf-settings'
    elif 'plugin overview' in anchor_id:
        anchor_id = 'overview'
    elif 'master module index' in anchor_id:
        anchor_id = 'module-index'
    elif 'data persistence' in anchor_id:
        anchor_id = 'state-persistence'
    elif 'file manager' in anchor_id:
        anchor_id = 'disk-permissions'
    elif 'toast notification' in anchor_id:
        anchor_id = 'toast-alerts'
    elif 'developer api' in anchor_id:
        anchor_id = 'developer-api'
    elif 'credits' in anchor_id:
        anchor_id = 'credits'
    else:
        anchor_id = re.sub(r'[^a-z0-9]+', '-', anchor_id).strip('-')

    content_html = ""
    in_ul = False
    in_ol = False
    
    for line in body_lines:
        line_s = line.strip()
        if not line_s:
            if in_ul: content_html += '</ul>\n'; in_ul = False
            if in_ol: content_html += '</ol>\n'; in_ol = False
            continue
            
        if line_s.startswith('### '):
            if in_ul: content_html += '</ul>\n'; in_ul = False
            if in_ol: content_html += '</ol>\n'; in_ol = False
            content_html += f'<h3>{line_s[4:]}</h3>\n'
        elif line_s.startswith('#### '):
            if in_ul: content_html += '</ul>\n'; in_ul = False
            if in_ol: content_html += '</ol>\n'; in_ol = False
            content_html += f'<h4>{line_s[5:]}</h4>\n'
        elif line_s.startswith('- '):
            if in_ol: content_html += '</ol>\n'; in_ol = False
            if not in_ul: content_html += '<ul>\n'; in_ul = True
            content_html += f'<li>{line_s[2:]}</li>\n'
        elif re.match(r'^\d+\.\s+', line_s):
            if in_ul: content_html += '</ul>\n'; in_ul = False
            if not in_ol: content_html += '<ol>\n'; in_ol = True
            li_text = re.sub(r'^\d+\.\s+', '', line_s)
            content_html += f'<li>{li_text}</li>\n'
        elif line_s.startswith('<table') or line_s.startswith('<pre') or line_s.startswith('</pre') or line_s.startswith('</table>') or line_s.startswith('<tr>') or line_s.startswith('<td>') or line_s.startswith('<th>') or line_s.startswith('<thead>') or line_s.startswith('<tbody>'):
            if in_ul: content_html += '</ul>\n'; in_ul = False
            if in_ol: content_html += '</ol>\n'; in_ol = False
            content_html += line + '\n'
        else:
            if in_ul: content_html += '</ul>\n'; in_ul = False
            if in_ol: content_html += '</ol>\n'; in_ol = False
            if not line_s.startswith('<'):
                content_html += f'<p>{line_s}</p>\n'
            else:
                content_html += line + '\n'
                
    if in_ul: content_html += '</ul>\n'
    if in_ol: content_html += '</ol>\n'

    return f'<section class="doc-card" id="{anchor_id}">\n<h2>{title_clean}</h2>\n{content_html}\n</section>\n'

cards_html = ""
for sec in card_sections:
    if sec.strip():
        cards_html += make_card(sec)

js_script = """
document.getElementById('doc-search').addEventListener('input', function(e) {
	var term = e.target.value.toLowerCase().trim();
	var links = document.querySelectorAll('.nav-list li');
	links.forEach(function(li) {
		var txt = li.textContent.toLowerCase();
		if (!term || txt.indexOf(term) !== -1) {
			li.style.display = '';
		} else {
			li.style.display = 'none';
		}
	});
});

document.querySelectorAll('.sidebar a').forEach(function(anchor) {
	anchor.addEventListener('click', function (e) {
		var targetId = this.getAttribute('href').substring(1);
		var targetElem = document.getElementById(targetId);
		if (targetElem) {
			e.preventDefault();
			targetElem.scrollIntoView({ behavior: 'smooth' });
			history.pushState(null, null, '#' + targetId);
		}
	});
});
"""

html_full = f"""<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>All-in-One SEO Fixer & Auditor — Master Technical Documentation & User Manual</title>
	<meta name="description" content="Complete enterprise-grade technical SEO, AI Copilot, performance acceleration, structured schema graphs, and site diagnostics for WordPress.">
	<style>
{css_styles}
	</style>
</head>
<body>

<div class="layout">

	<!-- SIDEBAR -->
	<aside class="sidebar">
		<div class="brand">
			<span class="brand-icon">🛡️</span> All-in-One SEO Fixer
		</div>
		<div class="brand-meta">
			<span class="brand-tag">v2.3.0</span>
			<span>Executive Documentation</span>
		</div>

		<div class="search-box">
			<input type="text" id="doc-search" class="search-input" placeholder="Quick search modules..." autocomplete="off" />
		</div>

		<div id="nav-container">
{sidebar_html}
		</div>
	</aside>

	<!-- MAIN -->
	<main class="main-content">

		<!-- HERO -->
		<div class="hero-card" id="top">
			<div class="hero-title">
				<span>🛡️</span> All-in-One SEO Fixer & Auditor
			</div>
			<div class="hero-desc">
				Official Master Technical Architecture &amp; Complete Page-by-Page User Manual for WordPress. Built for developers, digital agencies, and high-performance site owners.
			</div>
			<div class="hero-meta-row">
				<div class="hero-chip"><strong>Version:</strong> 2.3.0</div>
				<div class="hero-chip"><strong>Author:</strong> Abid Ali (<a href="https://abidalidev.com" target="_blank" style="color:inherit;text-decoration:underline;">abidalidev.com</a>)</div>
				<div class="hero-chip"><strong>License:</strong> GPL-2.0-or-later</div>
				<div class="hero-chip"><strong>Design:</strong> Clean Executive Slate</div>
			</div>
		</div>

		<!-- CONTENT CARDS -->
		<div id="cards-container">
{cards_html}
		</div>

		<footer class="doc-footer">
			All-in-One SEO Fixer v2.3.0 · Authored by <a href="https://abidalidev.com" target="_blank">Abid Ali</a> · Support: <a href="mailto:support@abidalidev.com">support@abidalidev.com</a>
		</footer>

	</main>

	<!-- BACK TO TOP BUTTON -->
	<a href="#top" class="back-top" title="Back to top">↑</a>

</div>

<script>
{js_script}
</script>

</body>
</html>
"""

with open(html_path, "w", encoding="utf-8") as f:
    f.write(html_full)

print(f"Clean executive documentation.html created! Size: {os.path.getsize(html_path)} bytes")
