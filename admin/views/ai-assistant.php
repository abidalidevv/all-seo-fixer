<?php
/**
 * View: AI SEO Assistant & Copilot
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$groq_configured = get_option( 'asf_groq_api_key', '' ) ? true : false;
$gemini_configured = get_option( 'asf_gemini_api_key', '' ) ? true : false;
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1><span class="dashicons dashicons-rest-api" style="font-size:28px;vertical-align:middle;color:#2271b1;margin-right:6px;"></span> AI SEO Assistant &amp; Copilot</h1>
			<p class="asf-header-desc">Real-time technical SEO intelligence powered primarily by ultra-fast <strong>Groq LLaMA-3.3-70B AI</strong> (with Gemini Flash failover). Ask custom ranking questions, generate rich schema JSON-LD, audit meta tags, and import external AI optimizations.</p>
		</div>
		<div class="asf-header-actions">
			<button type="button" class="button button-secondary" id="asf-export-prompt-btn"><span class="dashicons dashicons-download" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Download Master AI Prompt (JSON)</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=asf-settings#tab-ai' ) ); ?>" class="button button-secondary"><span class="dashicons dashicons-admin-generic" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Configure AI Keys</a>
		</div>
	</div>

	<!-- AI ENGINE STATUS BAR -->
	<div class="asf-card" style="padding:14px 20px;margin-bottom:18px;background:#f8fafc;border-left:4px solid #2271b1;">
		<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
			<div style="display:flex;align-items:center;gap:12px;">
				<span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:8px;background:#dbeafe;color:#1d4ed8;">
					<span class="dashicons dashicons-superhero-alt" style="font-size:22px;width:22px;height:22px;"></span>
				</span>
				<div>
					<strong style="font-size:14px;color:#0f172a;display:block;">Primary Engine: Groq Ultra-Fast AI (LLaMA-3.3-70B-Versatile)</strong>
					<span style="font-size:12px;color:#475569;">Sub-second latency (~0.4s) · Zero hallucination mode · Live 360° site audit context injected automatically.</span>
				</div>
			</div>
			<div style="display:flex;align-items:center;gap:10px;">
				<span class="asf-badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">
					<span style="color:#10b981;">●</span> Groq AI Active
				</span>
				<?php if ( $gemini_configured ) : ?>
					<span class="asf-badge" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;padding:4px 10px;border-radius:12px;font-size:12px;">
						Gemini Failover Ready
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- AI CHATBOT INTERACTIVE CONSOLE -->
	<div class="asf-card" style="margin-bottom:20px;">
		<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
			<h2 style="margin:0;font-size:16px;"><span class="dashicons dashicons-format-chat" style="font-size:20px;vertical-align:middle;color:#2271b1;"></span> Live Interactive SEO Chat Session</h2>
			<button type="button" class="button button-link-delete" id="asf-ai-clear-btn" style="text-decoration:none;font-size:12px;"><span class="dashicons dashicons-trash" style="font-size:14px;vertical-align:middle;"></span> Clear Chat History</button>
		</div>

		<!-- QUICK PROMPT PILLS -->
		<div id="asf-ai-pills" style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="Analyze my site SEO Health and list the top 3 priority fixes with exact steps.">&#x1F4A1; Audit Priority Analysis</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How do I optimize meta descriptions to increase Google search CTR?">&#x270F; Meta Description Tips</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How to fix Largest Contentful Paint (LCP) and Total Blocking Time (TBT) on WordPress?">&#x26A1; Speed &amp; CWV Guide</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How to generate Schema.org LocalBusiness JSON-LD for my WordPress site?">&#x1F4CD; Local SEO Schema</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How do I resolve duplicate titles and duplicate meta descriptions across my WordPress posts?">&#x1F504; Duplicate Metadata Fix</button>
			<button type="button" class="button button-secondary asf-ai-prompt-pill" data-prompt="How should I structure my robots.txt and sitemap.xml for optimal Googlebot crawl budget?">&#x1F916; Crawl Budget &amp; Robots.txt</button>
		</div>

		<!-- CHAT STREAM BOX -->
		<div id="asf-ai-chat-box" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;min-height:300px;max-height:480px;overflow-y:auto;margin-bottom:14px;font-size:13px;line-height:1.65;">
			<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;color:#334155;">
				👋 <strong>Hello! I am your AI SEO Assistant &amp; Copilot.</strong> Powered by ultra-fast Groq LLaMA 3.3. I have live access to your site's SEO Health scores, missing titles, unoptimized meta descriptions, missing image alt tags, and server performance. Click any prompt above or type a question below!
			</div>
		</div>

		<!-- CHAT INPUT ROW -->
		<div style="display:flex;gap:10px;align-items:center;">
			<input type="text" id="asf-ai-input" class="asf-input" style="flex:1;height:40px;font-size:13px;padding:0 12px;" placeholder="Ask AI SEO Copilot anything (e.g. How to fix my LCP score or create FAQ schema?)..." />
			<button type="button" class="button button-primary" id="asf-ai-send-btn" style="height:40px;padding:0 18px;font-weight:600;"><span class="dashicons dashicons-controls-play" style="font-size:16px;vertical-align:middle;margin-right:2px;"></span> Ask AI</button>
		</div>
	</div>

	<!-- IMPORT EXTERNAL AI JSON CONFIG CARD (MOVED FROM DASHBOARD) -->
	<div class="asf-card" style="margin-bottom:20px;">
		<div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:14px;">
			<div>
				<h2 style="margin:0;"><span class="dashicons dashicons-upload" style="font-size:22px;vertical-align:middle;color:#2271b1;margin-right:6px;"></span> Import &amp; Apply External AI JSON Config</h2>
				<p class="description" style="margin:4px 0 0 0;">Paste the optimized JSON response returned by <strong>ChatGPT Plus (GPT-4o), Claude 3.5 Sonnet, or Google Gemini Pro</strong> (using the Master AI Prompt) to automatically bulk-apply title tags, meta descriptions, and security settings across your website.</p>
			</div>
			<button type="button" class="button button-secondary" id="asf-export-prompt-btn-2"><span class="dashicons dashicons-download" style="font-size:16px;vertical-align:middle;margin-right:4px;"></span> Download Master Prompt (JSON)</button>
		</div>

		<div style="margin-bottom:14px;">
			<textarea id="asf-import-json-input" class="asf-input" style="width:100%;height:140px;font-family:monospace;font-size:12px;line-height:1.5;" placeholder='{
  "posts_titles": {
    "1": "Optimized Clean Title Tag - 55 Characters",
    "12": "High CTR Service Page Title - Brand Name"
  },
  "posts_descriptions": {
    "1": "High-converting 145 character meta description packed with focus keywords and compelling search snippet call-to-action.",
    "12": "Professional local service page meta description tailored for maximum organic search clicks."
  },
  "enable_hsts": "1",
  "enable_lazy": "1"
}'></textarea>
		</div>
		<div style="display:flex;gap:12px;align-items:center;">
			<button type="button" class="button button-primary" id="asf-import-json-btn" style="height:38px;padding:0 18px;font-weight:600;">⚡ Apply AI JSON Settings to Site</button>
			<span style="font-size:12px;color:#64748b;">Updates your WordPress database posts, custom fields, and security options safely with instant rollback.</span>
		</div>
		<div id="asf-import-json-status" style="margin-top:14px;"></div>
	</div>

	<!-- HOW IT WORKS: EXTERNAL LLM WORKFLOW GUIDE -->
	<div class="asf-card">
		<h2><span class="dashicons dashicons-book" style="font-size:20px;vertical-align:middle;color:#2271b1;margin-right:6px;"></span> How to Mass-Optimize Your Site with External AI Models</h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-top:14px;">
			<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
				<div style="font-size:24px;font-weight:700;color:#2271b1;margin-bottom:6px;">1</div>
				<strong style="font-size:13px;color:#1e293b;">Export Full Site Snapshot</strong>
				<p style="font-size:12px;color:#64748b;margin:6px 0 0;">Click <strong>Download Master AI Prompt (JSON)</strong>. It extracts your entire site's technical audit, public post inventory, current meta descriptions, word counts, and missing tags.</p>
			</div>

			<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
				<div style="font-size:24px;font-weight:700;color:#2271b1;margin-bottom:6px;">2</div>
				<strong style="font-size:13px;color:#1e293b;">Provide to ChatGPT or Claude</strong>
				<p style="font-size:12px;color:#64748b;margin:6px 0 0;">Upload or paste the JSON into ChatGPT Plus, Claude 3.5 Sonnet, or Gemini 1.5 Pro. The prompt already contains strict SEO rules and pre-defined output instructions.</p>
			</div>

			<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
				<div style="font-size:24px;font-weight:700;color:#2271b1;margin-bottom:6px;">3</div>
				<strong style="font-size:13px;color:#1e293b;">Copy Returned JSON Response</strong>
				<p style="font-size:12px;color:#64748b;margin:6px 0 0;">Copy the structured JSON output provided by the AI containing optimized titles (50–60 chars) and unique descriptions (120–155 chars).</p>
			</div>

			<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
				<div style="font-size:24px;font-weight:700;color:#2271b1;margin-bottom:6px;">4</div>
				<strong style="font-size:13px;color:#1e293b;">Paste &amp; Apply Instantly</strong>
				<p style="font-size:12px;color:#64748b;margin:6px 0 0;">Paste into the box above and click <strong>⚡ Apply AI JSON Settings to Site</strong>. All pages and posts will be updated in your database in under 2 seconds!</p>
			</div>
		</div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · Open Source GPL-2.0
	</div>
</div>
