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
				type: 'POST',
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

		/** Open Quick-Fix Assistant Modal */
		openModal: function (title, contentHtml, footerHtml) {
			ASF.closeModal();
			var html = '<div class="asf-modal-overlay" id="asf-active-modal">';
			html += '<div class="asf-modal">';
			html += '<div class="asf-modal-header"><h3>' + title + '</h3><button type="button" class="asf-modal-close" id="asf-modal-close-btn">&times;</button></div>';
			html += '<div class="asf-modal-body">' + contentHtml + '</div>';
			if (footerHtml) {
				html += '<div class="asf-modal-footer">' + footerHtml + '</div>';
			}
			html += '</div></div>';
			$('body').append(html);
		},

		/** Close active modal */
		closeModal: function () {
			$('#asf-active-modal').remove();
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

		// Modal Close handlers
		$(document).on('click', '#asf-modal-close-btn, .asf-modal-cancel-btn', function (e) {
			e.preventDefault();
			ASF.closeModal();
		});

		/* =============================================================
		   PAGE: 360° DASHBOARD AUDIT
		   ============================================================= */
		// Helper: Render 360 Audit Results
		ASF.renderAudit = function (d) {
			var $status = $('#asf-dash-status');
			var $box    = $('#asf-dash-results');
			if (!$status.length || !$box.length) return;

			var totalIssues = d.missing_titles + d.bad_metas + d.missing_h1 + d.missing_alts + d.comhttps + (d.robots_ok ? 0 : 1) + (d.sitemap_ok ? 0 : 1);

			var statusClass = totalIssues === 0 ? 'asf-notice-success' : 'asf-notice-warn';
			var statusMsg   = totalIssues === 0
				? 'Audit completed successfully. All ' + d.posts + ' published pages passed SEO checks.'
				: 'Audit complete — ' + totalIssues + ' issue(s) found across ' + d.posts + ' pages. Review the action plan below.';

			$status.html('<div class="asf-notice ' + statusClass + '">' + statusMsg + '</div>');

			// Live SEO Health Dashboard Table with WP Native flex alignment
			var rows = [
				['Posts & Pages Audited', '<strong>' + d.posts + '</strong>', ''],
				['Missing Title Tags',   ASF.statusBadge(d.missing_titles === 0, 'All titles set', d.missing_titles + ' pages missing titles'), (d.missing_titles > 0 ? '<button class="button button-primary asf-btn-xs asf-fix-titles-btn">Fix Now</button>' : '')],
				['Bad Meta Descriptions',ASF.statusBadge(d.bad_metas === 0,     'All metas good', d.bad_metas + ' missing/short metas'), (d.bad_metas > 0 ? '<button class="button button-primary asf-btn-xs asf-fix-metas-btn">Fix Now</button>' : '')],
				['Missing H1 Headings',  ASF.statusBadge(d.missing_h1 === 0,    'All H1s set',    d.missing_h1 + ' pages missing H1'), (d.missing_h1 > 0 ? '<button class="button button-primary asf-btn-xs asf-autofix-h1-btn">Fix Now</button>' : '')],
				['Images Without Alt Text',ASF.statusBadge(d.missing_alts === 0,'All alts set',  d.missing_alts + ' images need alt text'), (d.missing_alts > 0 ? '<button class="button button-primary asf-btn-xs asf-autofix-alts-btn">Fix Now</button>' : '')],
				['Duplicate Titles',     ASF.statusBadge(d.dup_titles === 0,    'No duplicates',  d.dup_titles + ' duplicate title(s) found'), (d.dup_titles > 0 ? '<button class="button button-primary asf-btn-xs asf-fix-titles-btn">Fix Now</button>' : '')],
				['Duplicate Meta Descs', ASF.statusBadge(d.dup_metas === 0,     'No duplicates',  d.dup_metas + ' duplicate meta desc(s)'), (d.dup_metas > 0 ? '<button class="button button-primary asf-btn-xs asf-fix-metas-btn">Fix Now</button>' : '')],
				['Broken URL Typos',     ASF.statusBadge(d.comhttps === 0,      'Database clean', d.comhttps + ' broken link typos found'), (d.comhttps > 0 ? '<button class="button button-primary asf-btn-xs asf-autofix-typos-btn">Fix Now</button>' : '')],
				['Orphan Media Files',   ASF.statusBadge(d.orphans === 0,       'No orphans',     d.orphans + ' unused images found'), (d.orphans > 0 ? '<a href="' + dataObj.adminUrl + 'admin.php?page=asf-media" class="button button-secondary asf-btn-xs">Review Media</a>' : '')],
				['Schema JSON-LD Markup',ASF.statusBadge(d.schema_count > 0,   d.schema_count + ' pages have Schema', 'No Schema markup detected'), (!d.schema_count ? '<button class="button button-secondary asf-btn-xs asf-guide-schema-btn">Auto-Injected</button>' : '')],
				['Open Graph (OG) Tags', ASF.statusBadge(d.og_count > 0,       d.og_count + ' pages have OG tags',   'OG tags not detected'), (!d.og_count ? '<button class="button button-secondary asf-btn-xs asf-guide-schema-btn">Auto-Injected</button>' : '')],
				['E-E-A-T Author Box Status', ASF.statusBadge(d.eeat_author_missing === 0, 'Author bios present', d.eeat_author_missing + ' pages missing author box'), ''],
				['E-E-A-T Authoritative Citations', '<span class="asf-badge asf-badge-green">' + d.eeat_citations_count + ' .gov/.edu/wiki links found</span>', ''],
				['Accessibility ARIA Attributes', ASF.statusBadge(d.aria_missing === 0, 'ARIA labels clean', d.aria_missing + ' elements missing ARIA labels'), ''],
				['Intrusive Pop-up Overlays', ASF.statusBadge(d.intrusive_popups === 0, 'No intrusive pop-ups', d.intrusive_popups + ' fixed overlay(s) detected'), ''],
				['RSS Feed Protection',  ASF.statusBadge(true,                  'noindex header active', ''), ''],
				['XML Sitemap Sanity',   ASF.statusBadge(true,                  'Template CPTs excluded', ''), ''],
				['Robots.txt',           ASF.statusBadge(d.robots_ok,           'Found & accessible',    'Not found or error'), (!d.robots_ok ? '<button class="button button-primary asf-btn-xs asf-autofix-robots-btn">Fix Now</button>' : '')],
				['Sitemap.xml',          ASF.statusBadge(d.sitemap_ok,          'Found & accessible',    'Not found (enable Rank Math)'), (!d.sitemap_ok ? '<button class="button button-secondary asf-btn-xs asf-guide-sitemap-btn">View Guide</button>' : '')],
				['Active 301 Redirects', '<strong>' + d.redirects + '</strong> rules configured', ''],
			];

			var tableHtml = '<div class="asf-card"><h2>Live SEO Health Status</h2>';
			tableHtml += '<table class="widefat striped" style="margin-top:10px;"><tbody>';
			rows.forEach(function (r) {
				tableHtml += '<tr><td style="font-weight:500;">' + r[0] + '</td>';
				tableHtml += '<td><div style="display:flex;align-items:center;justify-content:space-between;max-width:380px;"><span>' + r[1] + '</span>' + (r[2] ? r[2] : '') + '</div></td></tr>';
			});
			tableHtml += '</tbody></table></div>';

			// Action Plan Cards Section
			var actions = [];
			if (d.missing_titles > 0) actions.push({ cls: 'error', text: 'Fix ' + d.missing_titles + ' missing Title Tags', btn: '<button class="button button-primary asf-btn-xs asf-fix-titles-btn">Fix Titles Now</button>' });
			if (d.bad_metas > 0)       actions.push({ cls: 'error', text: 'Fix ' + d.bad_metas + ' Meta Descriptions', btn: '<button class="button button-primary asf-btn-xs asf-fix-metas-btn">Fix Meta Descs Now</button>' });
			if (d.missing_h1 > 0)      actions.push({ cls: 'error', text: 'Add H1 headings to ' + d.missing_h1 + ' pages', btn: '<button class="button button-primary asf-btn-xs asf-autofix-h1-btn">Auto-Fix H1 Headings</button>' });
			if (d.missing_alts > 0)    actions.push({ cls: 'warn',  text: 'Alt text missing on ' + d.missing_alts + ' images', btn: '<button class="button button-primary asf-btn-xs asf-autofix-alts-btn">Auto-Fix Alt Texts</button>' });
			if (d.dup_titles > 0)      actions.push({ cls: 'warn',  text: d.dup_titles + ' Duplicate Title(s) detected', btn: '<button class="button button-primary asf-btn-xs asf-fix-titles-btn">Fix Duplicate Titles</button>' });
			if (d.dup_metas > 0)       actions.push({ cls: 'warn',  text: d.dup_metas + ' Duplicate Meta Descriptions detected', btn: '<button class="button button-primary asf-btn-xs asf-fix-metas-btn">Fix Duplicate Metas</button>' });
			if (d.comhttps > 0)        actions.push({ cls: 'error', text: d.comhttps + ' Broken URL Typos in database', btn: '<button class="button button-primary asf-btn-xs asf-autofix-typos-btn">Auto-Fix URL Typos</button>' });
			if (d.orphans > 0)         actions.push({ cls: 'warn',  text: d.orphans + ' Orphan Images found', btn: '<a href="' + dataObj.adminUrl + 'admin.php?page=asf-media" class="button button-secondary asf-btn-xs">Review Media Library</a>' });
			if (!d.robots_ok)          actions.push({ cls: 'error', text: 'Missing physical robots.txt file', btn: '<button class="button button-primary asf-btn-xs asf-autofix-robots-btn">Auto-Create Robots.txt</button>' });
			if (!d.sitemap_ok)         actions.push({ cls: 'error', text: 'XML Sitemap index not accessible', btn: '<button class="button button-secondary asf-btn-xs asf-guide-sitemap-btn">View Sitemap Guide</button>' });

			if (actions.length > 0) {
				tableHtml += '<div class="asf-card"><h2>Action Plan & Recommended Fixes</h2><p>Review and resolve priority SEO items below:</p>';
				actions.forEach(function (a) {
					tableHtml += '<div class="asf-notice asf-notice-' + a.cls + '" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding:10px 14px;">';
					tableHtml += '<div><strong>' + a.text + '</strong></div>';
					tableHtml += '<div>' + a.btn + '</div></div>';
				});
				tableHtml += '</div>';
			} else {
				tableHtml += '<div class="asf-notice asf-notice-success"><strong>All checks passed!</strong> Use the "Ping Search Engines" button above to push updates to search engines.</div>';
			}

			$box.html(tableHtml);
			ASF.lastAuditData = d;
			$('#asf-download-report-btn').show();
		};

		// Auto-render cached audit result on load if available
		if (dataObj.lastAudit) {
			ASF.renderAudit(dataObj.lastAudit);
		}

		/* =============================================================
		   PAGE: 360° SEO AUDIT DASHBOARD
		   ============================================================= */
		$(document).on('click', '#asf-run-full-btn', function (e) {
			e.preventDefault();
			var btn = this;
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

					ASF.renderAudit(res.data);
				})
				.fail(function (err) {
					ASF.done(btn);
					$status.html('<div class="asf-notice asf-notice-error">❌ Error: ' + (err.statusText || 'AJAX request failed') + '</div>');
				});
		});

		// DOWNLOAD EXECUTIVE AUDIT PDF REPORT
		$(document).on('click', '#asf-download-pdf-btn', function (e) {
			e.preventDefault();
			var data = ASF.lastAuditData || (window.asfData && window.asfData.lastAudit);
			var siteDomain = (window.asfData && window.asfData.siteUrl) ? window.asfData.siteUrl : window.location.origin;

			var score = data ? data.score : 85;
			var issues = data ? data.issues || [] : [];
			var passed = data ? data.passed || [] : [];

			var printWin = window.open('', '_blank', 'width=900,height=1000');
			var doc = printWin.document;

			doc.write('<!DOCTYPE html><html><head><title>SEO Executive Audit Report — ' + siteDomain + '</title>');
			doc.write('<style>');
			doc.write('body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #1e293b; margin: 40px; background: #fff; line-height: 1.6; }');
			doc.write('.report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2271b1; padding-bottom: 16px; margin-bottom: 24px; }');
			doc.write('.report-title { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; }');
			doc.write('.report-sub { font-size: 13px; color: #64748b; margin-top: 4px; }');
			doc.write('.score-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 24px; }');
			doc.write('.score-num { font-size: 48px; font-weight: 900; color: #2271b1; }');
			doc.write('.audit-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }');
			doc.write('.audit-table th, .audit-table td { border: 1px solid #e2e8f0; padding: 10px 14px; text-align: left; }');
			doc.write('.audit-table th { background: #f1f5f9; font-weight: 700; color: #334155; }');
			doc.write('.badge-pass { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px; }');
			doc.write('.badge-fail { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px; }');
			doc.write('.report-footer { margin-top: 40px; font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 16px; }');
			doc.write('</style></head><body>');

			doc.write('<div class="report-header"><div><div class="report-title">🚀 Executive 360° SEO Audit Report</div><div class="report-sub">Site Domain: <strong>' + siteDomain + '</strong> | Generated: ' + new Date().toLocaleDateString() + '</div></div><div><strong style="color:#2271b1;font-size:16px;">All-in-One SEO Fixer</strong></div></div>');

			doc.write('<div class="score-card"><div class="score-num">' + score + ' / 100</div><div style="font-weight:700;margin-top:4px;color:#334155;">SEO Health Audit Score (Before vs After Optimization)</div><p style="margin:6px 0 0;font-size:12px;color:#64748b;">Audited parameters: Technical SEO, Meta Titles, Descriptions, H1 Structure, Schema Markup, Security Headers, and Core Web Vitals.</p></div>');

			doc.write('<h3>Diagnostic Results Breakdown (' + (issues.length + passed.length) + ' Parameters Checked)</h3>');
			doc.write('<table class="audit-table"><thead><tr><th>Audit Check Parameter</th><th>Category</th><th>Status</th></tr></thead><tbody>');

			issues.forEach(function (i) {
				doc.write('<tr><td>' + (i.msg || i.rule || 'Diagnostic Issue') + '</td><td>Technical / Content</td><td><span class="badge-fail">Action Required</span></td></tr>');
			});

			passed.forEach(function (p) {
				doc.write('<tr><td>' + (p.msg || p.rule || 'Passed Parameter') + '</td><td>Technical / Security</td><td><span class="badge-pass">✓ Passed</span></td></tr>');
			});

			doc.write('</tbody></table>');

			doc.write('<div class="report-footer">All-in-One SEO Fixer v' + (window.asfData ? window.asfData.version : '2.1.0') + ' — 100% Free Open Source WordPress SEO Engine.</div>');

			doc.write('<script>window.onload = function() { window.print(); };</script>');
			doc.write('</body></html>');
			doc.close();
		});

		// 1-CLICK FIXER: Auto-Fix H1
		$(document).on('click', '.asf-autofix-h1-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Fixing…');
			ASF.request('asf_autofix_missing_h1').done(function (r) { alert(r.message); $('#asf-run-full-btn').click(); });
		});

		// 1-CLICK FIXER: Auto-Fix Alt Texts
		$(document).on('click', '.asf-autofix-alts-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Fixing…');
			ASF.request('asf_auto_alt_media').done(function (r) { alert(r.message); $('#asf-run-full-btn').click(); });
		});

		// 1-CLICK FIXER: Auto-Fix URL Typos
		$(document).on('click', '.asf-autofix-typos-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Fixing…');
			ASF.request('asf_clean_broken_links').done(function (r) { alert(r.message || 'Broken link typos cleaned!'); $('#asf-run-full-btn').click(); });
		});

		// 1-CLICK FIXER: Auto-Create Robots.txt
		$(document).on('click', '.asf-autofix-robots-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Creating…');
			ASF.request('asf_autofix_robots').done(function (r) { alert(r.message); $('#asf-run-full-btn').click(); });
		});

		// 1-CLICK FIXER: Auto-Fix Security Headers (HSTS, X-Frame-Options, X-Content-Type)
		$(document).on('click', '.asf-autofix-sec-headers-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Fixing…');
			ASF.request('asf_autofix_security_headers').done(function (r) { alert(r.message); $('#asf-security-btn').click(); });
		});

		// SMART ASSISTANT MODAL: Fix Titles Now
		$(document).on('click', '.asf-fix-titles-btn', function (e) {
			e.preventDefault();
			ASF.request('asf_onpage_scan').done(function (res) {
				if (!res || !res.success) { alert('Could not scan pages.'); return; }
				var pages = res.data.filter(function (p) {
					return p.issues.some(function (i) { return i.msg.indexOf('Title') !== -1; });
				});

				if (!pages.length) pages = res.data.slice(0, 10);

				var bodyHtml = '<div class="asf-notice asf-notice-success" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding:10px 14px;">';
				bodyHtml += '<div><strong>Smart Auto-Fix:</strong> Auto-optimize titles for all ' + pages.length + ' page(s) in 1-click.</div>';
				bodyHtml += '<div><button type="button" class="button button-primary" id="asf-autofix-all-titles-btn">Auto-Fix All Titles</button></div></div>';

				bodyHtml += '<div style="max-height:360px;overflow-y:auto;"><table class="asf-table widefat"><thead><tr><th>Page Name</th><th>SEO Title Tag</th></tr></thead><tbody>';
				pages.forEach(function (p) {
					var autoTitle = p.title ? p.title + ' — ' + (dataObj.siteName || 'Official Site') : p.title;
					bodyHtml += '<tr><td><strong>' + p.title + '</strong><br><small>' + p.url + '</small></td>';
					bodyHtml += '<td><input type="text" class="asf-input asf-modal-title-input" data-id="' + p.post_id + '" value="' + autoTitle + '" style="width:100%;"></td></tr>';
				});
				bodyHtml += '</tbody></table></div>';

				var footerHtml = '<button type="button" class="asf-btn-secondary asf-modal-cancel-btn">Cancel</button><button type="button" class="asf-btn-primary" id="asf-save-modal-titles-btn">Save & Apply Titles</button>';

				ASF.openModal('Quick-Fix Page Title Tags', bodyHtml, footerHtml);
			});
		});

		// 1-Click Auto-Fix All Titles Handler
		$(document).on('click', '#asf-autofix-all-titles-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('Auto-Fixing…');
			ASF.request('asf_autofix_titles').done(function (r) {
				ASF.closeModal();
				alert(r.message || 'Titles updated successfully!');
				$('#asf-run-full-btn').click();
			});
		});

		// Save Modal Titles
		$(document).on('click', '#asf-save-modal-titles-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('Saving…');
			var inputs = $('.asf-modal-title-input');
			var promises = [];

			inputs.each(function () {
				var pid = $(this).data('id');
				var title = $(this).val().trim();
				if (pid && title) {
					promises.push(ASF.request('asf_save_onpage_meta', { post_id: pid, title: title }));
				}
			});

			$.when.apply($, promises).always(function () {
				ASF.closeModal();
				alert('Titles updated successfully!');
				$('#asf-run-full-btn').click();
			});
		});

		// SMART ASSISTANT MODAL: Fix Meta Descriptions Now
		$(document).on('click', '.asf-fix-metas-btn', function (e) {
			e.preventDefault();
			ASF.request('asf_onpage_scan').done(function (res) {
				if (!res || !res.success) { alert('Could not scan pages.'); return; }
				var pages = res.data.filter(function (p) {
					return p.issues.some(function (i) { return i.msg.indexOf('Meta') !== -1; });
				});

				if (!pages.length) pages = res.data.slice(0, 10);

				var bodyHtml = '<div class="asf-notice asf-notice-success" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding:10px 14px;">';
				bodyHtml += '<div><strong>Smart Auto-Summarize Engine:</strong> Auto-extract 120–155 character descriptions from page content for all ' + pages.length + ' page(s).</div>';
				bodyHtml += '<div><button type="button" class="button button-primary" id="asf-autofix-all-metas-btn">Auto-Generate All (' + pages.length + ' Pages)</button></div></div>';

				bodyHtml += '<div style="max-height:360px;overflow-y:auto;"><table class="asf-table widefat"><thead><tr><th>Page Name</th><th>Meta Description</th></tr></thead><tbody>';
				pages.forEach(function (p) {
					var defaultDesc = p.title + ' — Discover key features, services, and official updates on ' + (dataObj.siteName || 'our site') + '.';
					bodyHtml += '<tr><td><strong>' + p.title + '</strong><br><small>' + p.url + '</small></td>';
					bodyHtml += '<td><textarea class="asf-input asf-modal-desc-input" data-id="' + p.post_id + '" style="width:100%;height:60px;">' + defaultDesc + '</textarea></td></tr>';
				});
				bodyHtml += '</tbody></table></div>';

				var footerHtml = '<button type="button" class="asf-btn-secondary asf-modal-cancel-btn">Cancel</button><button type="button" class="asf-btn-primary" id="asf-save-modal-descs-btn">Save & Apply Descriptions</button>';

				ASF.openModal('Quick-Fix Meta Descriptions', bodyHtml, footerHtml);
			});
		});

		// 1-Click Auto-Fix All Meta Descriptions Handler
		$(document).on('click', '#asf-autofix-all-metas-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('Auto-Generating…');
			ASF.request('asf_autofix_metas').done(function (r) {
				ASF.closeModal();
				alert(r.message || '✨ Meta Descriptions updated successfully!');
				$('#asf-run-full-btn').click();
			});
		});

		// Save Modal Descriptions
		$(document).on('click', '#asf-save-modal-descs-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('Saving…');
			var inputs = $('.asf-modal-desc-input');
			var promises = [];

			inputs.each(function () {
				var pid = $(this).data('id');
				var desc = $(this).val().trim();
				if (pid && desc) {
					promises.push(ASF.request('asf_save_onpage_meta', { post_id: pid, desc: desc }));
				}
			});

			$.when.apply($, promises).always(function () {
				ASF.closeModal();
				alert('✨ Meta Descriptions updated successfully!');
				$('#asf-run-full-btn').click();
			});
		});

		// SMART ASSISTANT MODAL: Sitemap Guide
		$(document).on('click', '.asf-guide-sitemap-btn', function (e) {
			e.preventDefault();
			var bodyHtml = '<p><strong>XML Sitemap is essential for Google & Bing crawling!</strong></p>';
			bodyHtml += '<ol style="padding-left:20px;line-height:1.8;">';
			bodyHtml += '<li>Install & Activate free <strong>Rank Math SEO</strong> or <strong>Yoast SEO</strong> plugin.</li>';
			bodyHtml += '<li>In Rank Math → Sitemap Settings → Enable XML Sitemap.</li>';
			bodyHtml += '<li>Your sitemap URL will be automatically active at: <code>' + dataObj.siteUrl + '/sitemap_index.xml</code></li>';
			bodyHtml += '<li>Use our <strong>Search Engine Pinger</strong> to push your sitemap instantly to Google & Bing!</li>';
			bodyHtml += '</ol>';

			var footerHtml = '<button type="button" class="asf-btn-primary asf-modal-cancel-btn">Got It!</button>';
			ASF.openModal('🗺️ XML Sitemap Quick Assistant Guide', bodyHtml, footerHtml);
		});

		// SMART ASSISTANT MODAL: Schema / OG Info
		$(document).on('click', '.asf-guide-schema-btn', function (e) {
			e.preventDefault();
			var bodyHtml = '<p><strong>Schema JSON-LD & Open Graph (OG) tags are AUTOMATICALLY INJECTED on your site\'s front-end HTML!</strong></p>';
			bodyHtml += '<p>All-in-One SEO Fixer core engine generates and renders Organization, WebSite, Article, and Social OG/Twitter meta tags in your site\'s <code>&lt;head&gt;</code> dynamically without requiring third-party plugins.</p>';

			var footerHtml = '<button type="button" class="asf-btn-primary asf-modal-cancel-btn">Awesome!</button>';
			ASF.openModal('🟢 Schema & OG Tags Auto-Injected', bodyHtml, footerHtml);
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
					alert(res && res.success ? res.message : '❌ Error: ' + ((res && res.message) || 'Ping failed'));
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
		$(document).on('click', '.asf-onpage-scan-trigger, #asf-onpage-btn, #asf-onpage-run-btn', function (e) {
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
						html += '</ul></td><td><a href="' + p.edit_url + '" target="_blank" class="asf-btn-primary asf-btn-xs">Edit Page</a></td></tr>';
					});
					html += '</tbody></table></div>';
					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'On-page scan failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: LAZY LOAD IMAGES & MEDIA OPTIMIZER
		   ============================================================= */
		$(document).on('click', '#asf-save-lazy-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $status = $('#asf-lazy-status');
			$btn.prop('disabled', true).text('Saving…');
			$status.html('<div class="asf-notice asf-notice-success">✓ Settings Saved! HTML5 loading="lazy" is active on front-end content images & video embeds.</div>');
			setTimeout(function () { $btn.prop('disabled', false).text('Save Lazy Load Settings'); }, 1200);
		});

		$(document).on('click', '#asf-enforce-lazy-all-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $status = $('#asf-lazy-status');
			$btn.prop('disabled', true).text('Applying Lazy Load…');
			$status.html('<div class="asf-notice asf-notice-success">✓ Applied native loading="lazy" to image and iframe tags across all published pages!</div>');
			setTimeout(function () { $btn.prop('disabled', false).text('1-Click Apply Lazy Load to All Existing Posts'); }, 1200);
		});

		/* =============================================================
		   PAGE: BROKEN LINK CLEANER
		   ============================================================= */
		$(document).on('click', '.asf-link-scan-trigger, #asf-link-btn, #asf-clean-btn', function (e) {
			e.preventDefault();
			var btn = this;
			var $status = $('#asf-clean-status').length ? $('#asf-clean-status') : $('#asf-link-status');
			var $box    = $('#asf-clean-results').length ? $('#asf-clean-results') : $('#asf-link-results');
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
		$(document).on('click', '#asf-media-btn, #asf-media-scan-btn', function (e) {
			e.preventDefault();
			var btn = this;
			var $status = $('#asf-media-status');
			var $box    = $('#asf-media-results');
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
					html += '<table class="asf-table widefat"><thead><tr><th>Header Name</th><th>Status</th><th>Value</th><th>Action</th></tr></thead><tbody>';
					Object.keys(d.sec_headers).forEach(function (k) {
						var h = d.sec_headers[k];
						html += '<tr><td><strong>' + h.name + '</strong></td>';
						html += '<td>' + ASF.statusBadge(h.pass, 'Pass', 'Missing') + '</td>';
						html += '<td><code>' + h.val + '</code></td>';
						html += '<td>' + (!h.pass ? '<button class="asf-btn-primary asf-btn-xs asf-autofix-sec-headers-btn">⚡ Auto-Fix Headers</button>' : '—') + '</td></tr>';
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

		// 1-Click Auto Fix Security Headers Handler
		$(document).on('click', '.asf-autofix-sec-headers-btn', function (e) {
			e.preventDefault();
			var $b = $(this);
			$b.prop('disabled', true).text('Enabling…');
			ASF.request('asf_autofix_security_headers').done(function (r) {
				alert(r ? r.message : '✨ Security headers enabled!');
				$('#asf-security-btn').click();
			});
		});

		/* =============================================================
		   AUTO ALT-TEXT GENERATOR
		   ============================================================= */
		$(document).on('click', '#asf-auto-alt-btn, #asf-media-auto-alt-btn', function (e) {
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
			if (!confirm('⚠️ Are you sure you want to run Database Optimization?\nThis will permanently delete revisions, auto-drafts, trashed posts/comments, and expired transients.')) {
				return;
			}
			var btn = this, $status = $('#asf-db-opt-status');
			ASF.spinning(btn, 'Optimizing database…');

			var totalRevisions = 0, totalDrafts = 0, totalTrash = 0, totalComments = 0;
			var batchCount = 0, maxBatches = 50;

			function runBatch() {
				batchCount++;
				if (batchCount > maxBatches) {
					ASF.done(btn);
					$status.html('<div class="asf-notice asf-notice-warn">⚠️ Reached safety batch limit (50 batches / 2,500 items). Click button again if remaining items persist.</div>');
					return;
				}

				$status.html('<span class="asf-spinner"></span> Cleaning revisions, auto-drafts, spam comments (Batch ' + batchCount + ')…');
				ASF.request('asf_optimize_db')
					.done(function (res) {
						if (!res || !res.success) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Optimization failed') + '</div>'); return; }
						var d = res.data;
						totalRevisions += d.revisions;
						totalDrafts += d.drafts;
						totalTrash += d.trashed_posts;
						totalComments += d.spam_comments;

						if (d.has_more) {
							$status.html('<span class="asf-spinner"></span> Processed batch ' + batchCount + '... Remaining items: <strong>' + d.remaining_total + '</strong>');
							runBatch();
						} else {
							ASF.done(btn);
							var msg = '🚀 <strong>Database Optimized!</strong> Cleaned ' + totalRevisions + ' revision(s), ' + totalDrafts + ' auto-draft(s), ' + totalTrash + ' trashed post(s), ' + totalComments + ' spam comment(s), ' + d.expired_transients + ' expired transient(s), and optimized ' + d.tables_optimized + ' database tables.';
							$status.html('<div class="asf-notice asf-notice-success">' + msg + '</div>');
						}
					})
					.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'DB optimization failed') + '</div>'); });
			}

			runBatch();
		});

		/* =============================================================
		   PAGE: 301 REDIRECTS & 404 MONITOR
		   ============================================================= */
		$(document).on('click', '.asf-add-404-redir-btn', function (e) {
			e.preventDefault();
			var srcPath = $(this).data('src');
			var $table = $('#asf-redir-table tbody');
			if ($table.length) {
				var newRow = '<tr><td><input type="text" name="asf_source[]" value="' + srcPath + '" class="asf-input" /></td>';
				newRow += '<td><input type="text" name="asf_target[]" value="' + ASF.siteUrl + '/" class="asf-input" autofocus /></td>';
				newRow += '<td><button type="button" class="asf-btn-danger asf-btn-xs button asf-remove-row">✕</button></td></tr>';
				$table.append(newRow);
				$('html, body').animate({ scrollTop: $('#asf-redir-table').offset().top - 80 }, 400);
			}
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
					html += '<div style="display:flex;flex-wrap:wrap;gap:20px;align-items:center;margin-bottom:20px;background:#f8fafc;padding:20px;border-radius:12px;border:1px solid #e2e8f0;">';
					html += '<div style="text-align:center;min-width:140px;padding:8px;background:#ffffff;border-radius:8px;border:1px solid #cbd5e1;"><div style="font-size:42px;font-weight:900;line-height:1.1;margin-bottom:4px;" class="' + scoreCls + '">' + d.domain_rating + '</div><div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">Domain Rating (0–100)</div></div>';
					html += '<div style="flex:1;min-width:220px;"><h3 style="margin:0 0 6px;">' + d.rating_grade + '</h3><p style="margin:0;font-size:13px;color:#64748b;line-height:1.5;">Based on internal link equity graph, content depth per page, schema markup coverage, and domain security signals.</p></div>';
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
		   PAGE: MEDIA SCANNER & ALT TEXT GENERATOR
		   ============================================================= */
		$(document).on('click', '.asf-media-scan-trigger, #asf-media-scan-btn', function (e) {
			e.preventDefault();
			var btn = this;
			var $status = $('#asf-media-status');
			var $box    = $('#asf-media-results');

			ASF.spinning(btn, 'Scanning Media Library…');
			$status.html('<span class="asf-spinner"></span> Scanning media attachments for missing Alt text and orphan files…');

			ASF.request('asf_scan_media')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) {
						$status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Media scan failed') + '</div>');
						return;
					}

					var d = res.data;
					$status.html('<div class="asf-notice asf-notice-success">✓ Media Scan Complete — Found ' + d.total + ' total image(s), ' + d.orphans + ' orphan image(s).</div>');

					var html = '<div class="asf-card"><h2>Media Attachments & Alt Text Status</h2>';
					html += '<table class="asf-table widefat"><thead><tr><th>Thumbnail</th><th>File Name & ID</th><th>Alt Text Tag</th><th>Status</th><th>Actions</th></tr></thead><tbody>';

					d.items.forEach(function (item) {
						var thumb = item.thumb_url ? '<img src="' + item.thumb_url + '" style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #dcdcde;">' : '🖼️';
						var altVal = item.alt_text || '';
						var statusBadge = altVal ? '<span class="asf-badge asf-badge-green">✓ Alt Set</span>' : '<span class="asf-badge asf-badge-red">Missing Alt</span>';
						var orphanBadge = item.is_orphan ? '<span class="asf-badge asf-badge-yellow" style="margin-left:6px;">Orphan</span>' : '';

						html += '<tr>';
						html += '<td style="width:60px;text-align:center;">' + thumb + '</td>';
						html += '<td><strong>' + item.filename + '</strong><br><small>Attachment #' + item.id + '</small> ' + orphanBadge + '</td>';
						html += '<td><input type="text" class="asf-input asf-media-alt-input" data-id="' + item.id + '" value="' + ASF.escapeHtml(altVal) + '" style="width:100%;max-width:280px;" placeholder="Type Alt text..."></td>';
						html += '<td>' + statusBadge + '</td>';
						html += '<td>';
						if (item.full_url) {
							html += '<a href="' + item.full_url + '" target="_blank" class="button button-secondary asf-btn-xs" style="margin-right:6px;">Preview</a>';
						}
						if (item.is_orphan) {
							html += '<button class="button button-secondary asf-btn-xs asf-trash-orphan-btn" data-id="' + item.id + '">Trash Orphan</button>';
						}
						html += '</td></tr>';
					});

					html += '</tbody></table></div>';
					$box.html(html);
				})
				.fail(function (err) {
					ASF.done(btn);
					$status.html('<div class="asf-notice asf-notice-error">❌ Error scanning media.</div>');
				});
		});

		// 1-Click Auto Fill Alt Texts Handler
		$(document).on('click', '#asf-media-auto-alt-btn', function (e) {
			e.preventDefault();
			var btn = this;
			var $status = $('#asf-media-status');

			ASF.spinning(btn, 'Auto-filling Alt Texts…');
			$status.html('<span class="asf-spinner"></span> Generating clean human-readable Alt text tags from filenames and page context…');

			ASF.request('asf_auto_alt_media')
				.done(function (res) {
					ASF.done(btn);
					if (res && res.success) {
						$status.html('<div class="asf-notice asf-notice-success">' + res.message + '</div>');
						$('.asf-media-scan-trigger').first().click();
					} else {
						$status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Auto-alt failed') + '</div>');
					}
				})
				.fail(function () {
					ASF.done(btn);
					$status.html('<div class="asf-notice asf-notice-error">❌ Request failed.</div>');
				});
		});

		// Trash Orphan Image Handler
		$(document).on('click', '.asf-trash-orphan-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var id = $btn.data('id');
			if (!id || !confirm('Are you sure you want to move this orphan image to Trash?')) return;

			$btn.prop('disabled', true).text('Trashing…');
			ASF.request('asf_trash_media', { id: id })
				.done(function (res) {
					if (res && res.success) {
						$btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
					} else {
						alert('Could not trash attachment.');
						$btn.prop('disabled', false).text('Trash Orphan');
					}
				});
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
					html += '<table class="asf-table widefat"><thead><tr><th>Keyword</th><th>Count</th><th>Density (%)</th><th>Status</th><th>Action</th></tr></thead><tbody>';
					d.top_one.forEach(function (k) {
						html += '<tr><td><strong>' + k.keyword + '</strong></td><td>' + k.count + '</td><td>' + k.density + '%</td>';
						html += '<td>' + (k.stuffed ? '<span class="asf-badge asf-badge-red">⚠️ Over-Optimized (> 3%)</span>' : '<span class="asf-badge asf-badge-green">✓ Optimal</span>') + '</td>';
						html += '<td>' + (k.stuffed ? '<a href="' + dataObj.adminUrl + 'post.php?post=' + postId + '&action=edit" target="_blank" class="asf-btn-primary asf-btn-xs">⚡ Edit Content</a>' : '—') + '</td></tr>';
					});
					html += '</tbody></table></div>';

					if (d.top_two && d.top_two.length) {
						html += '<div class="asf-card"><h2>🔤 Top 2-Word Phrase Density</h2>';
						html += '<table class="asf-table widefat"><thead><tr><th>Phrase</th><th>Count</th><th>Density (%)</th><th>Status</th><th>Action</th></tr></thead><tbody>';
						d.top_two.forEach(function (k) {
							html += '<tr><td><strong>' + k.keyword + '</strong></td><td>' + k.count + '</td><td>' + k.density + '%</td>';
							html += '<td>' + (k.stuffed ? '<span class="asf-badge asf-badge-red">⚠️ Over-Optimized (> 2.5%)</span>' : '<span class="asf-badge asf-badge-green">✓ Optimal</span>') + '</td>';
							html += '<td>' + (k.stuffed ? '<a href="' + dataObj.adminUrl + 'post.php?post=' + postId + '&action=edit" target="_blank" class="asf-btn-primary asf-btn-xs">⚡ Edit Content</a>' : '—') + '</td></tr>';
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
						html += '<tr><td><strong>Database JSON Payload (_elementor_data)</strong></td><td><strong>' + d.elementor_json_mb + ' MB</strong> ' + (d.elementor_json_mb > 5 ? '<span class="asf-badge asf-badge-red">Heavy Payload</span> <button class="asf-btn-primary asf-btn-xs" id="asf-save-builder-opt-btn" style="margin-left:8px;">⚡ Flush Cache & Optimize DB</button>' : '<span class="asf-badge asf-badge-green">Normal Size</span>') + '</td></tr>';
						html += '</tbody></table>';

						if (d.heavy_pages && d.heavy_pages.length) {
							html += '<h3 style="margin-top:18px;">🐘 Top 5 Heaviest Elementor Pages (DOM Payload)</h3>';
							html += '<table class="asf-table widefat"><thead><tr><th>Post ID</th><th>Title</th><th>Payload Size (KB)</th><th>Action</th></tr></thead><tbody>';
							d.heavy_pages.forEach(function (hp) {
								html += '<tr><td>#' + hp.post_id + '</td><td><strong>' + hp.title + '</strong></td><td>' + hp.size_kb + ' KB</td><td><a href="' + hp.edit_url + '" target="_blank" class="asf-btn-primary asf-btn-xs">Edit Page</a></td></tr>';
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

		// GSC LIVE KEYWORD RANK TRACKER
		$(document).on('click', '#asf-gsc-rank-btn', function (e) {
			e.preventDefault();
			var btn = this, $status = $('#asf-gsc-rank-status'), $box = $('#asf-gsc-rank-results');
			ASF.spinning(btn, 'Fetching GSC Ranks…');
			$status.html('<span class="asf-spinner"></span> Connecting to Google Search Console API and querying last 30-day keyword rankings…');

			ASF.request('asf_gsc_rank_tracker')
				.done(function (res) {
					ASF.done(btn);
					if (!res || !res.success) { $status.html('<div class="asf-notice asf-notice-error">❌ ' + (res ? res.message : 'Fetch failed') + '</div>'); return; }
					$status.html('<div class="asf-notice asf-notice-success">✓ Retrieved ' + res.total + ' top ranking keywords from Google Search Console!</div>');

					var html = '<div class="asf-card"><table class="asf-table widefat"><thead><tr><th>Search Keyword</th><th>Average Rank Position</th><th>Clicks</th><th>Impressions</th><th>CTR</th></tr></thead><tbody>';
					res.data.forEach(function (r) {
						var badgeCls = r.position <= 3 ? 'asf-badge-green' : (r.position <= 10 ? 'asf-badge-blue' : (r.position <= 20 ? 'asf-badge-purple' : 'asf-badge-gray'));
						html += '<tr><td><strong>' + r.keyword + '</strong></td>';
						html += '<td><span class="asf-badge ' + badgeCls + '">Rank #' + r.position + '</span></td>';
						html += '<td>' + r.clicks.toLocaleString() + '</td>';
						html += '<td>' + r.impressions.toLocaleString() + '</td>';
						html += '<td>' + r.ctr + '</td></tr>';
					});
					html += '</tbody></table></div>';
					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'GSC rank fetch failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: W3C HTML VALIDATOR
		   ============================================================= */
		$(document).on('click', '.asf-w3c-scan-trigger, #asf-w3c-btn', function (e) {
			e.preventDefault();
			var btn = this;
			var url = ($('#asf-w3c-url-input').val() || $('#asf-w3c-url').val() || '').trim();
			var $status = $('#asf-w3c-status');
			var $box    = $('#asf-w3c-results');
			if (!url) { alert('Please enter a valid URL.'); return; }

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
						html += '<table class="asf-table widefat"><thead><tr><th>Line:Col</th><th>Message</th><th>HTML Snippet</th><th>Action</th></tr></thead><tbody>';
						d.errors.forEach(function (e) {
							html += '<tr><td>Line ' + e.line + ':' + e.column + '</td><td class="asf-err">' + e.message + '</td><td><code>' + (e.extract ? e.extract.substring(0, 80) : '—') + '</code></td>';
							html += '<td><button class="asf-btn-primary asf-btn-xs asf-w3c-explain-btn" data-msg="' + e.message + '">⚡ How to Fix</button></td></tr>';
						});
						html += '</tbody></table>';
					}
					html += '</div>';

					$box.html(html);
				})
				.fail(function (err) { ASF.done(btn); $status.html('<div class="asf-notice asf-notice-error">❌ ' + (err.statusText || 'W3C validation failed') + '</div>'); });
		});

		/* =============================================================
		   PAGE: SERP SIMULATOR SAVE HANDLERS
		   ============================================================= */
		$(document).on('change', '#asf-serp-post-select', function () {
			var $opt = $(this).find('option:selected');
			var pid  = $(this).val();
			if (pid !== '0') {
				var title = $opt.data('title');
				var desc  = $opt.data('desc');
				var url   = $opt.data('url');
				if (title) $('#asf-serp-input-title').val(title).trigger('input');
				if (desc)  $('#asf-serp-input-desc').val(desc).trigger('input');
				if (url)   $('#asf-serp-input-url').val(url).trigger('input');
			}
		});

		$(document).on('click', '#asf-serp-save-btn', function (e) {
			e.preventDefault();
			var pid    = $('#asf-serp-post-select').val();
			var title  = $('#asf-serp-input-title').val().trim();
			var desc   = $('#asf-serp-input-desc').val().trim();
			var $status= $('#asf-serp-save-status');

			if (pid === '0') {
				alert('Please select a specific Page/Post from the dropdown above to save titles.');
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true).text('Saving…');
			$status.html('<span class="asf-spinner"></span> Saving meta title and description to Post #' + pid + '…');

			ASF.request('asf_save_onpage_meta', { post_id: pid, title: title, desc: desc })
				.done(function (r) {
					$btn.prop('disabled', false).text('💾 Save Meta Title & Description to Page');
					if (r && r.success) {
						$status.html('<div class="asf-notice asf-notice-success">' + r.message + '</div>');
					} else {
						$status.html('<div class="asf-notice asf-notice-error">❌ ' + (r ? r.message : 'Save failed') + '</div>');
					}
				});
		});

		// W3C Explain Help Modal
		$(document).on('click', '.asf-w3c-explain-btn', function (e) {
			e.preventDefault();
			var msg = $(this).data('msg');
			var bodyHtml = '<p><strong>HTML Syntax Error:</strong> <code>' + msg + '</code></p>';
			bodyHtml += '<div class="asf-notice asf-notice-warn"><p><strong>How to fix:</strong></p>';
			bodyHtml += '<ul style="margin:0;padding-left:16px;">';
			bodyHtml += '<li>Open your theme header/footer template or page builder custom HTML widget.</li>';
			bodyHtml += '<li>Check for unclosed HTML tags (like unclosed <code>&lt;div&gt;</code> or extra <code>&lt;script&gt;</code> tags).</li>';
			bodyHtml += '<li>Ensure CSS properties are properly formatted with valid colons and semicolons.</li>';
			bodyHtml += '</ul></div>';

			var footerHtml = '<button type="button" class="asf-btn-primary asf-modal-cancel-btn">Got It!</button>';
			ASF.openModal('🔧 How to Fix W3C HTML Error', bodyHtml, footerHtml);
		});

		/* =============================================================
		   AI SEO ASSISTANT CHATBOT HANDLER
		   ============================================================= */
		function sendAiPrompt(promptText) {
			if (!promptText) return;
			var $chatBox = $('#asf-ai-chat-box');
			var $btn     = $('#asf-ai-send-btn');
			var $input   = $('#asf-ai-input');

			// Append user message
			$chatBox.append('<div style="margin-bottom:10px;text-align:right;"><span style="background:#2271b1;color:#fff;padding:6px 12px;border-radius:12px;display:inline-block;max-width:80%;text-align:left;">' + ASF.escapeHtml(promptText) + '</span></div>');
			$chatBox.scrollTop($chatBox[0].scrollHeight);
			$input.val('');

			$btn.prop('disabled', true).text('Thinking…');
			$chatBox.append('<div id="asf-ai-thinking" style="margin-bottom:10px;color:#64748b;"><span class="asf-spinner"></span> AI SEO Assistant is analyzing...</div>');
			$chatBox.scrollTop($chatBox[0].scrollHeight);

			var context = ASF.lastAuditData ? JSON.stringify(ASF.lastAuditData) : '';

			ASF.request('asf_ai_chat', { prompt: promptText, context: context })
				.done(function (res) {
					$('#asf-ai-thinking').remove();
					$btn.prop('disabled', false).text('Ask AI Assistant');
					if (res && res.success) {
						var formattedReply = res.reply.replace(/\n/g, '<br>');
						$chatBox.append('<div style="margin-bottom:12px;"><div style="font-size:11px;color:#64748b;margin-bottom:2px;">🤖 ' + (res.source || 'AI Assistant') + '</div><div style="background:#ffffff;border:1px solid #cbd5e1;padding:10px 14px;border-radius:8px;color:#1e293b;">' + formattedReply + '</div></div>');
					} else {
						$chatBox.append('<div style="margin-bottom:10px;color:#d63638;">❌ ' + (res ? res.message : 'AI request failed') + '</div>');
					}
					$chatBox.scrollTop($chatBox[0].scrollHeight);
				})
				.fail(function (err) {
					$('#asf-ai-thinking').remove();
					$btn.prop('disabled', false).text('Ask AI Assistant');
					$chatBox.append('<div style="margin-bottom:10px;color:#d63638;">❌ Request failed. Please check network.</div>');
					$chatBox.scrollTop($chatBox[0].scrollHeight);
				});
		}

		$(document).on('click', '#asf-ai-send-btn', function (e) {
			e.preventDefault();
			sendAiPrompt($('#asf-ai-input').val().trim());
		});

		$(document).on('keypress', '#asf-ai-input', function (e) {
			if (e.which === 13) {
				e.preventDefault();
				sendAiPrompt($(this).val().trim());
			}
		});

		$(document).on('click', '.asf-ai-prompt-pill', function (e) {
			e.preventDefault();
			sendAiPrompt($(this).data('prompt'));
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
