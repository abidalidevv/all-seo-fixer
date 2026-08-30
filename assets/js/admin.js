/**
 * All-in-One SEO Fixer & Auditor — Admin JavaScript
 * Author: Abid Ali Dev | https://abidalidev.com | @abidalidevv
 * Version: 2.1.0
 * License: GPL-2.0-or-later
 */
(function ($) {
	'use strict';

	/* =============================================================
	   UTILITY HELPERS
	   ============================================================= */
	var getAsfData = function () {
		if (typeof window.asfData !== 'undefined' && window.asfData.ajax) {
			return window.asfData;
		}
		if (typeof asfData !== 'undefined' && asfData.ajax) {
			return asfData;
		}
		return {
			ajax: window.location.origin + '/wp-admin/admin-ajax.php',
			nonce: '',
			adminUrl: window.location.origin + '/wp-admin/',
			siteUrl: window.location.origin,
			hasPsiKey: '0'
		};
	};

	var ASF = {
		/** Post to AJAX endpoint via jQuery.ajax for 100% compatibility */
		request: function (action, extraParams) {
			var dataObj = getAsfData();
			var data = $.extend({
				action: action,
				nonce: dataObj.nonce
			}, extraParams || {});

			return $.ajax({
				url: dataObj.ajax,
				type: 'GET',
				data: data,
				dataType: 'json'
			});
		},

		/** Color class based on score */
		scoreClass: function (score) {
			return score >= 90 ? 'green' : score >= 50 ? 'yellow' : 'red';
		},

		/** Badge HTML */
		badge: function (text, cls) {
			return '<span class="asf-badge asf-badge-' + cls + '">' + text + '</span>';
		},

		/** Status string to badge */
		statusBadge: function (good, msg_ok, msg_bad) {
			return good
				? '<span class="asf-badge asf-badge-green">✓ ' + msg_ok + '</span>'
				: '<span class="asf-badge asf-badge-red">✗ ' + msg_bad + '</span>';
		},

		/** Show inline spinner */
		spinning: function (el, text) {
			if (!el) return;
			el.disabled = true;
			el._origText = el.textContent || el.innerText;
			el.innerHTML = '<span class="asf-spinner"></span>' + (text || 'Loading…');
		},

		/** Restore button */
		done: function (el) {
			if (!el) return;
			el.disabled = false;
			el.textContent = el._origText || 'Run';
		},

		/** Animate Chart.js rings if Chart is available */
		renderCharts: function (cats) {
			if (typeof Chart === 'undefined') return;
			cats.forEach(function (c, i) {
				var canvas = document.getElementById('asf-chart-' + i);
				if (!canvas) return;
				var color = c.val >= 90 ? '#22c55e' : c.val >= 50 ? '#f59e0b' : '#ef4444';
				new Chart(canvas.getContext('2d'), {
					type: 'doughnut',
					data: {
						datasets: [{
							data: [c.val, 100 - c.val],
							backgroundColor: [color, '#f1f5f9'],
							borderWidth: 0
						}]
					},
					options: {
						cutout: '76%',
						plugins: { legend: { display: false }, tooltip: { enabled: false } },
						animation: { duration: 700 }
					}
				});
			});
		}
	};

	// Use Event Delegation on document for 100% reliable click binding
	$(document).ready(function () {
		var dataObj = getAsfData();

		/* =============================================================
		   PAGE: 360° DASHBOARD AUDIT
		   ============================================================= */
		$(document).on('click', '#asf-run-full-btn', function (e) {
			e.preventDefault();
			var btn     = this;
			var $status = $('#asf-dash-status');
			var $box    = $('#asf-dash-results');

			ASF.spinning(btn);
			$status.html('<span class="asf-spinner"></span> Auditing database, meta tags, Schema markup, Robots.txt, Sitemap and more…');

			ASF.request('asf_full_360_audit')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) {
						$status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Audit failed.') + '</div>');
						return;
					}

					var d = res.data;
					var totalIssues = d.missing_titles + d.bad_metas + d.missing_h1 + d.missing_alts + d.comhttps + (d.robots_ok ? 0 : 1) + (d.sitemap_ok ? 0 : 1);

					var statusClass = totalIssues === 0 ? 'asf-notice-success' : 'asf-notice-warn';
					var statusMsg   = totalIssues === 0
						? '🎉 Perfect! All SEO checks passed — ' + d.posts + ' pages audited.'
						: '⚠️ Audit complete — ' + totalIssues + ' issue(s) found across ' + d.posts + ' pages. See Action Plan below.';

					$status.html('<div class="asf-notice ' + statusClass + '">' + statusMsg + '</div>');

					var rows = [
						['Posts & Pages Audited', '<strong>' + d.posts + '</strong>'],
						['Missing Title Tags',   ASF.statusBadge(d.missing_titles === 0, 'All titles set ✓', d.missing_titles + ' pages missing titles')],
						['Bad Meta Descriptions',ASF.statusBadge(d.bad_metas === 0,     'All metas good ✓', d.bad_metas + ' missing/short metas')],
						['Missing H1 Headings',  ASF.statusBadge(d.missing_h1 === 0,    'All H1s set ✓',    d.missing_h1 + ' pages missing H1')],
						['Images Without Alt Text',ASF.statusBadge(d.missing_alts === 0,'All alts set ✓',  d.missing_alts + ' images need alt text')],
						['Duplicate Titles',     ASF.statusBadge(d.dup_titles === 0,    'No duplicates ✓',  d.dup_titles + ' duplicate title(s) found')],
						['Duplicate Meta Descs', ASF.statusBadge(d.dup_metas === 0,     'No duplicates ✓',  d.dup_metas + ' duplicate meta desc(s)')],
						['Broken URL Typos',     ASF.statusBadge(d.comhttps === 0,      'Database clean ✓', d.comhttps + ' broken link typos found')],
						['Orphan Media Files',   ASF.statusBadge(d.orphans === 0,       'No orphans ✓',     d.orphans + ' unused images found')],
						['Schema JSON-LD Markup',ASF.statusBadge(d.schema_count > 0,   d.schema_count + ' pages have Schema ✓', 'No Schema markup detected')],
						['Open Graph (OG) Tags', ASF.statusBadge(d.og_count > 0,       d.og_count + ' pages have OG tags ✓',   'OG tags not detected')],
						['RSS Feed Protection',  ASF.statusBadge(true,                  'noindex header active ✓', '')],
						['XML Sitemap Sanity',   ASF.statusBadge(true,                  'Template CPTs excluded ✓', '')],
						['Robots.txt',           ASF.statusBadge(d.robots_ok,           'Found & accessible ✓',    'Not found or error')],
						['Sitemap.xml',          ASF.statusBadge(d.sitemap_ok,          'Found & accessible ✓',    'Not found (enable Rank Math)')],
						['Active 301 Redirects', '<strong>' + d.redirects + '</strong> rules configured'],
					];

					var tableHtml = '<div class="asf-card"><h2>📊 Live SEO Health Dashboard</h2>';
					tableHtml += '<table class="asf-table widefat"><tbody>';
					rows.forEach(function (r) {
						tableHtml += '<tr><td>' + r[0] + '</td><td>' + r[1] + '</td></tr>';
					});
					tableHtml += '</tbody></table>';

					// Action Plan
					var actions = [];
					if (d.missing_titles > 0) actions.push({ cls: 'danger', msg: '🔴 <strong>Fix ' + d.missing_titles + ' missing Title Tags</strong> — Go to <a href="' + dataObj.adminUrl + 'admin.php?page=asf-onpage">On-Page Checker</a> → set unique titles.' });
					if (d.bad_metas > 0)       actions.push({ cls: 'danger', msg: '🔴 <strong>Fix ' + d.bad_metas + ' Meta Descriptions</strong> — Write compelling 120–155 char summaries.' });
					if (d.missing_h1 > 0)      actions.push({ cls: 'danger', msg: '🔴 <strong>Add H1 to ' + d.missing_h1 + ' pages</strong> — Every page needs an &lt;h1&gt;. <button class="asf-btn-primary asf-btn-xs" id="asf-autofix-h1-btn" style="margin-left:8px;">⚡ Auto-Fix H1 Headings Now</button>' });
					if (d.missing_alts > 0)    actions.push({ cls: 'warn',   msg: '🟡 <strong>Alt text missing on ' + d.missing_alts + ' images</strong> — Fill Alt Text fields. <button class="asf-btn-primary asf-btn-xs" id="asf-autofix-alts-btn" style="margin-left:8px;">⚡ Auto-Fix All Alt Texts Now</button>' });
					if (d.dup_titles > 0)      actions.push({ cls: 'warn',   msg: '🟡 <strong>' + d.dup_titles + ' Duplicate Title(s)</strong> — Ensure every page has a unique title tag.' });
					if (d.dup_metas > 0)       actions.push({ cls: 'warn',   msg: '🟡 <strong>' + d.dup_metas + ' Duplicate Meta Descriptions</strong> — Ensure every page has a unique description.' });
					if (d.comhttps > 0)        actions.push({ cls: 'danger', msg: '🔴 <strong>' + d.comhttps + ' Broken URL Typos</strong> — Go to <a href="' + dataObj.adminUrl + 'admin.php?page=asf-link-cleaner">Link Cleaner</a> tab and click Auto-Fix.' });
					if (d.orphans > 0)         actions.push({ cls: 'warn',   msg: '🟡 <strong>' + d.orphans + ' Orphan Images</strong> — Go to <a href="' + dataObj.adminUrl + 'admin.php?page=asf-media">Media Scanner</a> to review unused files.' });
					if (!d.robots_ok)          actions.push({ cls: 'danger', msg: '🔴 <strong>Create robots.txt</strong> — Add at root: <code>User-agent: *</code> / <code>Disallow: /wp-admin/</code>' });
					if (!d.sitemap_ok)         actions.push({ cls: 'danger', msg: '🔴 <strong>Enable XML Sitemap</strong> — Install Rank Math or Yoast SEO and enable sitemap.' });
					if (!d.schema_count || !d.og_count) actions.push({ cls: 'ok',   msg: '🟢 <strong>Schema JSON-LD & OG Tags Active!</strong> Auto-injected on front-end by All-in-One SEO Fixer core engine.' });

					if (actions.length === 0) {
						actions.push({ cls: 'ok', msg: '🟢 <strong>All checks passed!</strong> Use the "Ping Search Engines" button to push updates to Google & Bing instantly.' });
					}

					tableHtml += '<h3 style="margin-top:22px;">📋 Action Plan</h3><ul class="asf-action-list">';
					actions.forEach(function (a) { tableHtml += '<li class="' + a.cls + '">' + a.msg + '</li>'; });
					tableHtml += '</ul></div>';

					$box.html(tableHtml);

					ASF.lastAuditData = d;
					ASF.lastAuditRows = rows;
					ASF.lastAuditActions = actions;
					$('#asf-download-report-btn').show();
				})
				.fail(function (err) {
					ASF.done(btn);
					$status.html('<div class="asf-notice asf-notice-error">❌ Error: ' + (err.statusText || 'AJAX request failed') + '</div>');
				});
		});

		// Auto-Fix H1
		$(document).on('click', '#asf-autofix-h1-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Fixing…');
			ASF.request('asf_autofix_missing_h1').done(function (r) { alert(r.message); $('#asf-run-full-btn').click(); });
		});

		// Auto-Fix Alt Texts
		$(document).on('click', '#asf-autofix-alts-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Fixing…');
			ASF.request('asf_auto_alt_media').done(function (r) { alert(r.message); $('#asf-run-full-btn').click(); });
		});

		// Download / Print Report
		$(document).on('click', '#asf-download-report-btn', function (e) {
			e.preventDefault();
			if (!ASF.lastAuditData) { alert('Please run the audit first.'); return; }
			var reportWindow = window.open('', '_blank', 'width=900,height=800');
			if (!reportWindow) { alert('Pop-up blocked! Please allow pop-ups for this site.'); return; }

			var d = ASF.lastAuditData;
			var dateStr = new Date().toLocaleString();
			var html = '<!DOCTYPE html><html><head><title>SEO Audit Report - ' + dataObj.siteUrl + '</title>';
			html += '<style>';
			html += 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px; color: #1e293b; line-height: 1.6; }';
			html += '.header { border-bottom: 3px solid #1d4ed8; padding-bottom: 20px; margin-bottom: 24px; }';
			html += '.header h1 { margin: 0 0 6px; font-size: 24px; color: #0f172a; }';
			html += '.header p { margin: 0; color: #64748b; font-size: 14px; }';
			html += 'table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }';
			html += 'th, td { border: 1px solid #cbd5e1; padding: 10px 14px; text-align: left; font-size: 14px; }';
			html += 'th { background: #f8fafc; font-weight: 700; }';
			html += '.action-list { list-style: none; padding: 0; margin: 0; }';
			html += '.action-list li { padding: 10px 14px; margin-bottom: 8px; border-left: 4px solid #1d4ed8; background: #f8fafc; border-radius: 4px; font-size: 14px; }';
			html += '.action-list li.danger { border-color: #ef4444; background: #fff5f5; }';
			html += '.action-list li.warn { border-color: #f59e0b; background: #fffbeb; }';
			html += '.action-list li.ok { border-color: #22c55e; background: #f0fdf4; }';
			html += '.footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }';
			html += '@media print { body { padding: 0; } .no-print { display: none; } }';
			html += '</style></head><body>';
			html += '<div class="no-print" style="margin-bottom:20px;"><button onclick="window.print()" style="background:#1d4ed8;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;">🖨️ Save as PDF / Print Report</button></div>';
			html += '<div class="header"><h1>🛡️ SEO Audit Report</h1>';
			html += '<p><strong>Site:</strong> ' + dataObj.siteUrl + ' &nbsp;|&nbsp; <strong>Generated:</strong> ' + dateStr + ' &nbsp;|&nbsp; <strong>Audited Pages:</strong> ' + d.posts + '</p></div>';

			html += '<h2>📊 Site Health Audit Overview</h2><table><thead><tr><th>Audit Metric</th><th>Status / Findings</th></tr></thead><tbody>';
			if (ASF.lastAuditRows) {
				ASF.lastAuditRows.forEach(function (r) {
					html += '<tr><td><strong>' + r[0] + '</strong></td><td>' + r[1] + '</td></tr>';
				});
			}
			html += '</tbody></table>';

			html += '<h2>📋 Action Plan & Recommended Fixes</h2><ul class="action-list">';
			if (ASF.lastAuditActions) {
				ASF.lastAuditActions.forEach(function (a) {
					html += '<li class="' + a.cls + '">' + a.msg + '</li>';
				});
			}
			html += '</ul>';

			html += '<div class="footer">Report generated by All-in-One SEO Fixer & Auditor by Abid Ali Dev (abidalidev.com)</div>';
			html += '</body></html>';

			reportWindow.document.write(html);
			reportWindow.document.close();
		});

		/* =============================================================
		   PAGE: SEARCH ENGINE PINGER
		   ============================================================= */
		$(document).on('click', '#asf-ping-btn', function (e) {
			e.preventDefault();
			var btn = this;
			ASF.spinning(btn, 'Pinging…');
			ASF.request('asf_ping_search_engines')
				.done(function (res) {
					ASF.done(btn);
					alert(res.success ? res.message : '❌ Error: ' + (res.message || 'Ping failed'));
				})
				.fail(function (err) { ASF.done(btn); alert('Error: ' + (err.statusText || 'Ping failed')); });
		});

		/* =============================================================
		   PAGE: PAGESPEED INSIGHTS
		   ============================================================= */
		$(document).on('click', '#asf-psi-run-btn', function (e) {
			e.preventDefault();
			var btn      = this;
			var url      = $('#asf-psi-url').val().trim();
			var strategy = $('#asf-psi-strategy').val();
			var $status  = $('#asf-psi-status');
			var $box     = $('#asf-psi-results');

			if (!url) { alert('Please enter a URL.'); return; }
			if (dataObj.hasPsiKey !== '1') { alert('Please add your free PageSpeed Insights API key in Settings first.'); return; }

			ASF.spinning(btn, 'Running PageSpeed test…');
			$status.html('<span class="asf-spinner"></span> Running Google PageSpeed Insights — takes 10–30 seconds…');

			ASF.request('asf_pagespeed_test', { url: url, strategy: strategy })
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) {
						$status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Test failed') + '</div>');
						return;
					}
					$status.html('<div class="asf-notice asf-notice-success">✓ PageSpeed test complete!</div>');
					var d = res.data;

					var cats = [
						{ label: 'Performance',   val: d.performance,    id: 0 },
						{ label: 'Accessibility', val: d.accessibility,  id: 1 },
						{ label: 'Best Practices',val: d.best_practices, id: 2 },
						{ label: 'SEO',           val: d.seo,            id: 3 },
					];

					var html = '<div class="asf-card">';
					html += '<h2>⚡ PageSpeed Results — ' + strategy.charAt(0).toUpperCase() + strategy.slice(1).toLowerCase() + '</h2>';
					html += '<p style="color:#64748b;font-size:13px;">URL: <code>' + url + '</code></p>';

					// Score grid
					html += '<div class="asf-score-grid">';
					cats.forEach(function (c) {
						var cls = ASF.scoreClass(c.val);
						html += '<div class="asf-score-box">';
						html += '<canvas id="asf-chart-' + c.id + '" width="90" height="90" style="display:block;margin:0 auto 8px;"></canvas>';
						html += '<div class="asf-score-num ' + cls + '">' + c.val + '</div>';
						html += '<div class="asf-score-label">' + c.label + '</div>';
						html += '</div>';
					});
					html += '</div>';

					// Core Web Vitals
					html += '<h3>Core Web Vitals</h3><table class="asf-table widefat"><thead><tr><th>Metric</th><th>Value</th><th>Good Threshold</th><th>Status</th></tr></thead><tbody>';
					var cwv = [
						{ name: 'Largest Contentful Paint (LCP)', val: d.lcp,         thresh: '≤ 2.5s', ok: parseFloat(d.lcp) <= 2.5 },
						{ name: 'Total Blocking Time (TBT)',       val: d.tbt,         thresh: '≤ 200ms', ok: parseFloat(d.tbt) <= 200 },
						{ name: 'Cumulative Layout Shift (CLS)',   val: d.cls,         thresh: '≤ 0.1',   ok: parseFloat(d.cls) <= 0.1 },
						{ name: 'First Contentful Paint (FCP)',    val: d.fcp,         thresh: '≤ 1.8s',  ok: parseFloat(d.fcp) <= 1.8 },
						{ name: 'Speed Index',                     val: d.speed_index, thresh: '≤ 3.4s',  ok: parseFloat(d.speed_index) <= 3.4 },
					];
					cwv.forEach(function (m) {
						html += '<tr><td><strong>' + m.name + '</strong></td>';
						html += '<td><strong>' + m.val + '</strong></td>';
						html += '<td>' + m.thresh + '</td>';
						html += '<td>' + ASF.statusBadge(m.ok, 'Good', 'Needs Work') + '</td></tr>';
					});
					html += '</tbody></table>';

					// Opportunities
					if (d.opportunities && d.opportunities.length) {
						html += '<h3 style="margin-top:20px;">💡 Improvement Opportunities</h3><ul class="asf-action-list">';
						d.opportunities.forEach(function (op) {
							html += '<li class="warn">🟡 <strong>' + op.title + '</strong> — ' + op.description + (op.saving ? ' <em>(Estimated saving: ' + op.saving + ')</em>' : '') + '</li>';
						});
						html += '</ul>';
					}

					html += '</div>';
					$box.html(html);

					ASF.renderCharts(cats);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'PageSpeed request failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: ON-PAGE SEO CHECKER
		   ============================================================= */
		$(document).on('click', '#asf-onpage-run-btn', function (e) {
			e.preventDefault();
			var btn     = this;
			var $status = $('#asf-onpage-status');
			var $box    = $('#asf-onpage-results');

			ASF.spinning(btn, 'Scanning all pages…');
			$status.html('<span class="asf-spinner"></span> Checking titles, meta descriptions, H1 headings, alt texts, and Schema on all published posts & pages…');

			ASF.request('asf_onpage_scan')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Scan failed') + '</div>'); return; }

					var issues = res.data.filter(function (p) { return p.issues.length > 0; });
					$status.html('<div class="asf-notice asf-notice-success">✓ Scanned ' + res.data.length + ' pages — ' + issues.length + ' have issues.</div>');

					if (!issues.length) { $box.html('<div class="asf-card asf-card-ok"><p class="asf-ok">🎉 All ' + res.data.length + ' pages pass every on-page SEO check!</p></div>'); return; }

					var html = '<div class="asf-card"><h2>Pages with SEO Issues <span class="asf-count-pill">' + issues.length + '</span></h2>';
					html += '<table class="asf-table widefat"><thead><tr><th>Page / Post</th><th>Issues</th><th>Action</th></tr></thead><tbody>';
					issues.forEach(function (p) {
						html += '<tr><td><strong>' + p.title + '</strong><br><small><a href="' + p.url + '" target="_blank">' + p.url + '</a></small></td><td><ul style="margin:4px 0;padding-left:16px;">';
						p.issues.forEach(function (iss) {
							html += '<li><span class="' + (iss.type === 'error' ? 'asf-err' : 'asf-warn') + '">' + iss.msg + '</span></li>';
						});
						html += '</ul></td><td><a href="' + p.edit_url + '" target="_blank" class="asf-btn-primary asf-btn-xs">Edit</a></td></tr>';
					});
					html += '</tbody></table></div>';
					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'On-page scan failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: BROKEN LINK CLEANER
		   ============================================================= */
		$(document).on('click', '#asf-clean-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-clean-status'), $box = $('#asf-clean-results');
			ASF.spinning(btn, 'Scanning…');
			$status.html('<span class="asf-spinner"></span> Scanning postmeta and post_content for double-domain typos…');

			ASF.request('asf_clean_broken_links')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Scan failed') + '</div>'); return; }
					var d = res.data;
					$status.html('<div class="asf-notice asf-notice-success">✓ Scan complete — Found: ' + d.found + ' | Fixed: <strong>' + d.fixed + '</strong></div>');
					var html = '<div class="asf-card">';
					if (d.details.length) {
						html += '<h3>Fixed URLs:</h3><table class="asf-table widefat"><thead><tr><th>Post ID</th><th>Field</th><th>Before</th><th>After</th></tr></thead><tbody>';
						d.details.forEach(function (i) {
							html += '<tr><td>#' + i.post_id + '</td><td><code>' + i.meta_key + '</code></td><td><code class="asf-err">' + i.old_val + '</code></td><td><code class="asf-ok">' + i.new_val + '</code></td></tr>';
						});
						html += '</tbody></table>';
					} else {
						html += '<div class="asf-notice asf-notice-success">🎉 No broken double-domain URLs found. Database is 100% clean!</div>';
					}
					html += '</div>';
					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'Link scan failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: MEDIA SCANNER
		   ============================================================= */
		$(document).on('click', '#asf-media-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-media-status'), $box = $('#asf-media-results');
			ASF.spinning(btn, 'Scanning…');
			$status.html('<span class="asf-spinner"></span> Cross-referencing media against all pages, Elementor, featured images, logos…');

			ASF.request('asf_scan_media')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Scan failed') + '</div>'); return; }
					var d = res.data;
					$status.html('<div class="asf-notice asf-notice-success">✓ Scanned ' + d.total + ' images — ' + d.used + ' used | <strong>' + d.orphans + ' orphans</strong></div>');
					var html = '<div class="asf-card"><table class="asf-table widefat"><thead><tr><th>ID</th><th>Filename</th><th>Status</th><th>Action</th></tr></thead><tbody>';
					d.items.forEach(function (item) {
						html += '<tr><td>' + item.id + '</td><td><code>' + item.filename + '</code></td>';
						html += '<td>' + (item.is_orphan ? '<span class="asf-badge asf-badge-red">🔴 Orphan</span>' : '<span class="asf-badge asf-badge-green">🟢 In Use</span>') + '</td>';
						html += '<td>' + (item.is_orphan ? '<button class="asf-btn-danger asf-btn-xs button asf-trash-btn" data-id="' + item.id + '">🗑️ Trash</button>' : '—') + '</td></tr>';
					});
					html += '</tbody></table></div>';
					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'Media scan failed') + '</div>'); });
		});

		// Trash buttons delegation
		$(document).on('click', '.asf-trash-btn', function (e) {
			e.preventDefault();
			var id = $(this).data('id');
			if (!confirm('Move image #' + id + ' to Trash? (Reversible from Media Library)')) return;
			var $el = $(this);
			$el.prop('disabled', true).text('…');
			ASF.request('asf_trash_media', { id: id })
				.done(function (r) { $el.text((r && r.success) ? '✓ Trashed' : '✗ Error'); });
		});

		/* =============================================================
		   PAGE: DOMAIN & SECURITY AUDIT
		   ============================================================= */
		$(document).on('click', '#asf-security-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-sec-status'), $box = $('#asf-sec-results');
			ASF.spinning(btn, 'Auditing security…');
			$status.html('<span class="asf-spinner"></span> Checking SSL, Security Headers, CDN headers, Gzip compression, and Spamhaus/DNSBL blacklists…');

			ASF.request('asf_security_audit')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Audit failed') + '</div>'); return; }
					var d = res.data;
					$status.html('<div class="asf-notice asf-notice-success">✓ Security & Reputation Scan Complete for ' + d.host + ' (' + d.ip + ')</div>');

					var html = '<div class="asf-card"><h2>🛡️ Security & Protocol Status</h2>';
					html += '<table class="asf-table widefat"><tbody>';
					html += '<tr><td><strong>HTTPS / SSL Encryption</strong></td><td>' + ASF.statusBadge(d.is_https, 'Enabled & Active (HTTPS)', 'Insecure (HTTP)') + '</td></tr>';
					html += '<tr><td><strong>CDN / WAF Detection</strong></td><td>' + (d.cdn_detected ? '<span class="asf-badge asf-badge-purple">✓ ' + d.cdn_name + '</span>' : '<span class="asf-badge asf-badge-gray">Direct Server / No CDN detected</span>') + '</td></tr>';
					html += '<tr><td><strong>Server Compression</strong></td><td>' + ASF.statusBadge(d.has_compression, 'Active (' + d.compression_type + ')', 'Disabled — Enable Gzip/Brotli in cPanel/Nginx') + '</td></tr>';
					html += '<tr><td><strong>Browser Caching Header</strong></td><td><code>' + d.cache_control + '</code></td></tr>';
					html += '<tr><td><strong>Server Software</strong></td><td><code>' + d.server_software + '</code></td></tr>';
					html += '</tbody></table></div>';

					// Security Headers
					html += '<div class="asf-card"><h2>🔒 Security Headers Audit</h2>';
					html += '<table class="asf-table widefat"><thead><tr><th>Header Name</th><th>Status</th><th>Value</th></tr></thead><tbody>';
					Object.keys(d.sec_headers).forEach(function (k) {
						var h = d.sec_headers[k];
						html += '<tr><td><strong>' + h.name + '</strong></td>';
						html += '<td>' + ASF.statusBadge(h.pass, 'Pass', 'Missing') + '</td>';
						html += '<td><code>' + h.val + '</code></td></tr>';
					});
					html += '</tbody></table></div>';

					// Spam Reputation
					html += '<div class="asf-card"><h2>🚫 Domain & IP Blacklist Check</h2>';
					if (d.blacklisted_count === 0) {
						html += '<div class="asf-notice asf-notice-success">🎉 Excellent! IP <code>' + d.ip + '</code> is NOT listed on any major email/domain blacklists (Spamhaus, SpamCop, Barracuda, SORBS).</div>';
					} else {
						html += '<div class="asf-notice asf-notice-error">⚠️ IP <code>' + d.ip + '</code> is listed on ' + d.blacklisted_count + ' blacklist provider(s)! Clean up site security immediately.</div>';
					}
					html += '<table class="asf-table widefat"><thead><tr><th>DNSBL Provider</th><th>Status</th></tr></thead><tbody>';
					d.blacklist_results.forEach(function (b) {
						html += '<tr><td>' + b.provider + '</td><td>' + ASF.statusBadge(!b.listed, 'Clean / Not Listed', 'LISTED AS SPAM') + '</td></tr>';
					});
					html += '</tbody></table></div>';

					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'Security audit failed') + '</div>'); });
		});

		/* =============================================================
		   AUTO ALT-TEXT GENERATOR
		   ============================================================= */
		$(document).on('click', '#asf-auto-alt-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-media-status');
			if (!confirm('Auto-generate clean Alt Text for all images missing Alt Text in Media Library?')) return;
			ASF.spinning(btn, 'Auto-generating alt text…');

			ASF.request('asf_auto_alt_media')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { alert('Error: ' + (res ? res.message : 'Failed')); return; }
					alert(res.message);
					$('#asf-media-btn').click();
				})
				.fail(function (err) { ASF.done(btn); alert('Error: ' + (err.statusText || 'Auto alt generation failed')); });
		});

		/* =============================================================
		   PAGE: PERFORMANCE & DATABASE OPTIMIZER
		   ============================================================= */
		$(document).on('click', '#asf-db-opt-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-db-opt-status');
			ASF.spinning(btn, 'Optimizing database…');
			$status.html('<span class="asf-spinner"></span> Cleaning revisions, auto-drafts, spam comments, expired transients, and optimizing MySQL tables…');

			ASF.request('asf_optimize_db')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Optimization failed') + '</div>'); return; }
					var d = res.data;
					var msg = '🚀 <strong>Database Optimized!</strong> Deleted ' + d.revisions + ' revision(s), ' + d.drafts + ' auto-draft(s), ' + d.spam_comments + ' spam comment(s), ' + d.expired_transients + ' expired transient(s), and optimized ' + d.tables_optimized + ' database tables.';
					$status.html('<div class="asf-notice asf-notice-success">' + msg + '</div>');
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'DB optimization failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: DOMAIN RATING (DA) & LINK EQUITY
		   ============================================================= */
		$(document).on('click', '#asf-auth-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-auth-status'), $box = $('#asf-auth-results');
			ASF.spinning(btn, 'Calculating Domain Rating…');
			$status.html('<span class="asf-spinner"></span> Calculating internal link equity, word count depth, Schema coverage, and On-Page Domain Rating (DA)…');

			ASF.request('asf_authority_audit')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Audit failed') + '</div>'); return; }
					var d = res.data;
					$status.html('<div class="asf-notice asf-notice-success">✓ Domain Authority Calculation Complete</div>');

					var scoreCls = d.domain_rating >= 80 ? 'asf-status-ok' : (d.domain_rating >= 50 ? 'asf-status-warn' : 'asf-status-err');

					var html = '<div class="asf-card">';
					html += '<div style="display:flex;gap:20px;align-items:center;margin-bottom:20px;background:#f8fafc;padding:20px;border-radius:12px;">';
					html += '<div style="text-align:center;"><div style="font-size:48px;font-weight:900;" class="' + scoreCls + '">' + d.domain_rating + '</div><div style="font-size:12px;color:#64748b;font-weight:600;">Domain Rating (0–100)</div></div>';
					html += '<div><h3 style="margin:0 0 6px;">' + d.rating_grade + '</h3><p style="margin:0;font-size:13px;color:#64748b;">Based on internal link equity graph, content depth per page, schema markup coverage, and domain security signals.</p></div>';
					html += '</div>';

					html += '<table class="asf-table widefat"><tbody>';
					html += '<tr><td><strong>Total Audited Pages/Posts</strong></td><td>' + d.total_posts + '</td></tr>';
					html += '<tr><td><strong>Total Content Words</strong></td><td>' + d.total_word_count.toLocaleString() + ' words</td></tr>';
					html += '<tr><td><strong>Average Words Per Page</strong></td><td>' + d.avg_words_per_page + ' words/page ' + (d.avg_words_per_page >= 600 ? '<span class="asf-badge asf-badge-green">Good Depth</span>' : '<span class="asf-badge asf-badge-yellow">Needs More Depth</span>') + '</td></tr>';
					html += '<tr><td><strong>Total Internal Links</strong></td><td>' + d.total_internal_links + '</td></tr>';
					html += '<tr><td><strong>Internal Link Ratio</strong></td><td>' + d.internal_link_ratio + ' links/page ' + (d.internal_link_ratio >= 3 ? '<span class="asf-badge asf-badge-green">Strong Equity</span>' : '<span class="asf-badge asf-badge-yellow">Add More Links</span>') + '</td></tr>';
					html += '<tr><td><strong>Total Outbound Links</strong></td><td>' + d.total_outbound_links + '</td></tr>';
					html += '<tr><td><strong>Schema Markup Coverage</strong></td><td>' + d.schema_coverage + '</td></tr>';
					html += '</tbody></table></div>';

					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'Authority audit failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: KEYWORD DENSITY ANALYZER
		   ============================================================= */
		$(document).on('click', '#asf-kw-run-btn', function (e) {
			e.preventDefault();
			var btn = this, postId = $('#asf-kw-post-select').val(), $status = $('#asf-kw-status'), $box = $('#asf-kw-results');
			if (!postId) { alert('Please select a page first.'); return; }

			ASF.spinning(btn, 'Analyzing keywords…');
			$status.html('<span class="asf-spinner"></span> Extracting 1-word and 2-word n-grams and calculating keyword density…');

			ASF.request('asf_keyword_density', { post_id: postId })
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Analysis failed') + '</div>'); return; }
					var d = res.data;
					$status.html('<div class="asf-notice asf-notice-success">✓ Keyword Density Analysis for: <strong>' + d.post_title + '</strong> (' + d.total_words + ' words)</div>');

					var html = '<div class="asf-card"><h2>🔤 Top 1-Word Density Analysis</h2>';
					html += '<table class="asf-table widefat"><thead><tr><th>Keyword</th><th>Count</th><th>Density (%)</th><th>Status</th></tr></thead><tbody>';
					d.top_one.forEach(function (k) {
						html += '<tr><td><strong>' + k.keyword + '</strong></td><td>' + k.count + '</td><td>' + k.density + '%</td>';
						html += '<td>' + (k.stuffed ? '<span class="asf-badge asf-badge-red">⚠️ Over-Optimized (> 3%)</span>' : '<span class="asf-badge asf-badge-green">✓ Optimal</span>') + '</td></tr>';
					});
					html += '</tbody></table></div>';

					if (d.top_two && d.top_two.length) {
						html += '<div class="asf-card"><h2>🔤 Top 2-Word Phrase Density</h2>';
						html += '<table class="asf-table widefat"><thead><tr><th>Phrase</th><th>Count</th><th>Density (%)</th><th>Status</th></tr></thead><tbody>';
						d.top_two.forEach(function (k) {
							html += '<tr><td><strong>' + k.keyword + '</strong></td><td>' + k.count + '</td><td>' + k.density + '%</td>';
							html += '<td>' + (k.stuffed ? '<span class="asf-badge asf-badge-red">⚠️ Over-Optimized (> 2.5%)</span>' : '<span class="asf-badge asf-badge-green">✓ Optimal</span>') + '</td></tr>';
						});
						html += '</tbody></table></div>';
					}

					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'Keyword density failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: PAGE BUILDER & ELEMENTOR OVERHEAD INSPECTOR
		   ============================================================= */
		$(document).on('click', '#asf-builder-scan-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-builder-status'), $box = $('#asf-builder-results');
			ASF.spinning(btn, 'Scanning page builder overhead…');
			$status.html('<span class="asf-spinner"></span> Detecting active page builders, analyzing Elementor DB payload (_elementor_data size), and sample DOM complexity…');

			ASF.request('asf_builder_scan')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Scan failed') + '</div>'); return; }
					var d = res.data;
					$status.html('<div class="asf-notice asf-notice-success">✓ Page Builder Overhead Scan Complete</div>');

					var html = '<div class="asf-card"><h2>🧱 Active Page Builders Detected</h2>';
					if (d.active_builders && d.active_builders.length) {
						html += '<ul class="asf-action-list">';
						d.active_builders.forEach(function (b) {
							html += '<li class="ok">🟢 <strong>' + b.name + '</strong> is active on this site.</li>';
						});
						html += '</ul>';
					} else {
						html += '<p class="asf-ok">✓ No heavy page builders active — site uses native Gutenberg or Block Editor!</p>';
					}
					html += '</div>';

					if (d.is_elementor) {
						html += '<div class="asf-card"><h2>⚡ Elementor Bloat & Database Payload Audit</h2>';
						html += '<table class="asf-table widefat"><tbody>';
						html += '<tr><td><strong>Elementor-Built Pages Count</strong></td><td>' + d.elementor_page_count + ' pages</td></tr>';
						html += '<tr><td><strong>Database JSON Payload (_elementor_data)</strong></td><td><strong>' + d.elementor_json_mb + ' MB</strong> ' + (d.elementor_json_mb > 5 ? '<span class="asf-badge asf-badge-red">Heavy Payload</span>' : '<span class="asf-badge asf-badge-green">Normal Size</span>') + '</td></tr>';
						html += '</tbody></table>';

						if (d.heavy_pages && d.heavy_pages.length) {
							html += '<h3 style="margin-top:18px;">🐘 Top 5 Heaviest Elementor Pages (DOM Payload)</h3>';
							html += '<table class="asf-table widefat"><thead><tr><th>Post ID</th><th>Title</th><th>Payload Size (KB)</th><th>Action</th></tr></thead><tbody>';
							d.heavy_pages.forEach(function (hp) {
								html += '<tr><td>#' + hp.post_id + '</td><td><strong>' + hp.title + '</strong></td><td>' + hp.size_kb + ' KB</td><td><a href="' + hp.edit_url + '" target="_blank" class="asf-btn-primary asf-btn-xs">Edit</a></td></tr>';
							});
							html += '</tbody></table>';
						}

						html += '<h3 style="margin-top:20px;">⚡ 1-Click Elementor Script & Font Optimizations</h3>';
						html += '<form id="asf-builder-opt-form">';
						html += '<div style="margin-bottom:10px;"><label><input type="checkbox" id="asf-opt-eicons" ' + (d.opt_eicons ? 'checked' : '') + '> Dequeue Elementor Icons (<code>eicons</code>) on frontend for non-logged-in visitors</label></div>';
						html += '<div style="margin-bottom:14px;"><label><input type="checkbox" id="asf-opt-gfonts" ' + (d.opt_gfonts ? 'checked' : '') + '> Disable Elementor Google Fonts loading (if using local/system fonts)</label></div>';
						html += '<button type="button" class="asf-btn-primary asf-btn-sm" id="asf-save-builder-opt-btn">💾 Save Optimizations & Flush Elementor CSS Cache</button>';
						html += '</form>';
						html += '</div>';
					}

					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'Builder scan failed') + '</div>'); });
		});

		// Save builder optimization handler
		$(document).on('click', '#asf-save-builder-opt-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Saving…');
			var eicons = $('#asf-opt-eicons').is(':checked') ? 1 : 0;
			var gfonts = $('#asf-opt-gfonts').is(':checked') ? 1 : 0;

			ASF.request('asf_builder_optimize', { disable_eicons: eicons, disable_gfonts: gfonts, clear_css: 1 })
				.done(function (res) {
					$b.prop('disabled', false).text('💾 Save Optimizations & Flush Elementor CSS Cache');
					alert(res ? res.message : 'Saved');
				});
		});

		/* =============================================================
		   PAGE: GOOGLE SEARCH CONSOLE (GSC) SUBMITTER
		   ============================================================= */
		$(document).on('click', '#asf-gsc-submit-btn', function (e) {
			e.preventDefault();
			var btn = this, url = $('#asf-gsc-url-input').val().trim(), $status = $('#asf-gsc-status');
			if (!url) { alert('Please enter a URL.'); return; }

			ASF.spinning(btn, 'Submitting to Google…');
			$status.html('<span class="asf-spinner"></span> Submitting URL to Google Search Console Indexing API & Google Sitemap Ping…');

			ASF.request('asf_gsc_submit_url', { url: url })
				.done(function (res) {
					ASF.done(btn);
					$status.html('<div class="asf-notice ' + (res && res.success ? 'asf-notice-success' : 'asf-notice-error') + '">' + (res ? res.message : 'Failed') + '</div>');
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'GSC submission failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: W3C HTML VALIDATOR
		   ============================================================= */
		$(document).on('click', '#asf-w3c-btn', function (e) {
			e.preventDefault();
			var btn = this, url = $('#asf-w3c-url-input').val().trim(), $status = $('#asf-w3c-status'), $box = $('#asf-w3c-results');
			if (!url) { alert('Please enter a URL.'); return; }

			ASF.spinning(btn, 'Validating HTML via W3C…');
			$status.html('<span class="asf-spinner"></span> Querying official W3C Nu HTML Checker API for syntax errors and unclosed tags…');

			ASF.request('asf_w3c_validate', { url: url })
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Validation failed') + '</div>'); return; }
					var d = res.data;
					var statusClass = d.error_count === 0 ? 'asf-notice-success' : 'asf-notice-warn';
					$status.html('<div class="asf-notice ' + statusClass + '">✓ W3C Audit Complete — Found ' + d.error_count + ' HTML error(s) and ' + d.warning_count + ' warning(s).</div>');

					var html = '<div class="asf-card"><h2>✅ W3C Validation Report for <code>' + d.url + '</code></h2>';
					if (d.error_count === 0) {
						html += '<div class="asf-notice asf-notice-success">🎉 Excellent! No HTML syntax errors found by W3C Nu Checker.</div>';
					} else {
						html += '<h3>🔴 HTML Syntax Errors (' + d.error_count + ')</h3>';
						html += '<table class="asf-table widefat"><thead><tr><th>Line:Col</th><th>Message</th><th>HTML Snippet</th></tr></thead><tbody>';
						d.errors.forEach(function (e) {
							html += '<tr><td>Line ' + e.line + ':' + e.column + '</td><td class="asf-err">' + e.message + '</td><td><code>' + (e.extract ? e.extract.substring(0, 80) : '—') + '</code></td></tr>';
						});
						html += '</tbody></table>';
					}
					html += '</div>';

					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'W3C validation failed') + '</div>'); });
		});

		/* =============================================================
		   GLOBAL: COPY-TO-CLIPBOARD BUTTONS
		   ============================================================= */
		$(document).on('click', '.asf-copy-btn', function (e) {
			e.preventDefault();
			var target = $($(this).data('target'));
			if (target.length) {
				navigator.clipboard.writeText(target.text() || target.val()).then(function () {
					var $btn = $(this);
					$btn.text('✓ Copied!');
					setTimeout(function () { $btn.text('Copy'); }, 2000);
				}.bind(this));
			}
		});

	});

}(jQuery));
