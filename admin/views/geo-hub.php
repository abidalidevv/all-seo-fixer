<?php
/**
 * View: Generative Engine Optimization (GEO) & AI Search Hub
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

$max_snip        = get_option( 'asf_geo_max_snippets', '1' );
$bot_gptbot      = get_option( 'asf_bot_gptbot', '1' );
$bot_perplexity  = get_option( 'asf_bot_perplexity', '1' );
$bot_claudebot   = get_option( 'asf_bot_claudebot', '1' );
$bot_gemini      = get_option( 'asf_bot_gemini', '1' );

$llms_enable     = get_option( 'asf_llms_enable', '1' );
$default_llms    = "# " . get_bloginfo('name') . "\n\n> " . ( get_bloginfo('description') ?: 'Professional Services' ) . "\n\n## Website Overview\n- URL: " . home_url('/') . "\n\n## Directives for AI Search Crawlers\n- User-agent: GPTBot\n  Allow: /\n- User-agent: PerplexityBot\n  Allow: /\n- User-agent: ClaudeBot\n  Allow: /\n- User-agent: Google-Extended\n  Allow: /\n";
$llms_content    = get_option( 'asf_llms_txt_content', $default_llms );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Generative Engine Optimization (GEO) &amp; AI Search Hub</h1>
			<p class="asf-header-desc">Optimize your content, entities, and citations to be recommended as primary sources by ChatGPT Search, Perplexity AI, Google Gemini, and Claude.</p>
		</div>
		<div class="asf-header-actions">
			<a href="<?php echo esc_url( home_url('/llms.txt') ); ?>" target="_blank" class="button button-secondary">
				<span class="dashicons dashicons-external" style="vertical-align:text-top;font-size:16px;"></span> View Live /llms.txt
			</a>
		</div>
	</div>

	<!-- CARD 1: AI CITABILITY AUDIT -->
	<div class="asf-card">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
			<div>
				<h2 style="margin:0;">📊 AI Citability Audit &amp; Readiness Score</h2>
				<p class="description" style="margin:4px 0 0 0;">Evaluates your site's readiness for AI conversational citations and answers.</p>
			</div>
			<button type="button" class="button button-primary" id="asf-geo-audit-btn">🔍 Run AI Readiness Audit</button>
		</div>

		<div id="asf-geo-audit-status" style="margin-top:10px;"></div>
		<div id="asf-geo-audit-results" style="margin-top:14px;"></div>
	</div>

	<!-- CARD 2: AI SEARCH BOT CRAWLER DIRECTIVES -->
	<div class="asf-card">
		<h2>🤖 AI Crawler Permissions &amp; Snippet Policy</h2>
		<p>Manage bot access for generative AI engines seeking to index your content for instant answers:</p>

		<form id="asf-geo-bots-form">
			<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:14px;margin:16px 0;">
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
					<label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
						<input type="checkbox" name="asf_bot_gptbot" value="1" <?php checked($bot_gptbot, '1'); ?> />
						OpenAI / SearchGPT (<code>GPTBot</code>)
					</label>
					<p style="font-size:12px;color:#64748b;margin:6px 0 0 24px;">Powers ChatGPT real-time web search and citation cards.</p>
				</div>

				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
					<label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
						<input type="checkbox" name="asf_bot_perplexity" value="1" <?php checked($bot_perplexity, '1'); ?> />
						Perplexity AI (<code>PerplexityBot</code>)
					</label>
					<p style="font-size:12px;color:#64748b;margin:6px 0 0 24px;">Powers Perplexity Pro search and source citation pills.</p>
				</div>

				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
					<label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
						<input type="checkbox" name="asf_bot_claudebot" value="1" <?php checked($bot_claudebot, '1'); ?> />
						Anthropic Claude (<code>ClaudeBot</code>)
					</label>
					<p style="font-size:12px;color:#64748b;margin:6px 0 0 24px;">Permits Anthropic knowledge retrieval and citations.</p>
				</div>

				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
					<label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
						<input type="checkbox" name="asf_bot_gemini" value="1" <?php checked($bot_gemini, '1'); ?> />
						Google Gemini (<code>Google-Extended</code>)
					</label>
					<p style="font-size:12px;color:#64748b;margin:6px 0 0 24px;">Powers Google AI Overviews &amp; Gemini responses.</p>
				</div>
			</div>

			<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;margin-bottom:16px;">
				<label style="display:flex;align-items:center;gap:8px;font-weight:700;cursor:pointer;color:#1e40af;">
					<input type="checkbox" name="asf_geo_max_snippets" value="1" <?php checked($max_snip, '1'); ?> />
					Permit Unrestricted AI Answer Snippets (<code>max-snippet:-1</code>)
				</label>
				<p style="font-size:12px;color:#3b82f6;margin:6px 0 0 24px;">Instructs search engines to extract rich, full-length answer quotes from your site rather than truncating excerpts.</p>
			</div>

			<button type="button" class="button button-primary" id="asf-save-geo-bots-btn">💾 Save AI Crawler Directives</button>
			<span id="asf-geo-bots-msg" style="margin-left:10px;font-size:13px;font-weight:600;"></span>
		</form>
	</div>

	<!-- CARD 3: LLMS.TXT SITE INDEX PUBLISHER -->
	<div class="asf-card">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
			<div>
				<h2 style="margin:0;">🧠 AI Knowledge Map (<code>/llms.txt</code>)</h2>
				<p class="description" style="margin:4px 0 0 0;">The official 2026 standard for helping LLMs navigate and understand your site structure (<a href="https://llmstxt.org" target="_blank">llmstxt.org</a>).</p>
			</div>
			<div style="display:flex;gap:8px;">
				<button type="button" class="button button-primary" id="asf-regen-llms-btn">🔄 1-Click Auto-Regenerate /llms.txt</button>
			</div>
		</div>

		<div id="asf-llms-status" style="margin-bottom:10px;"></div>

		<div style="position:relative;">
			<div style="position:absolute;top:10px;right:10px;z-index:2;">
				<button type="button" class="button button-small asf-copy-btn" data-target="#asf-llms-code">📋 Copy Markdown</button>
			</div>
			<textarea id="asf-llms-code" class="asf-input" style="width:100%;height:220px;font-family:Consolas,Monaco,monospace;font-size:12px;line-height:1.5;background:#0f172a;color:#f8fafc;padding:16px;border-radius:8px;box-sizing:border-box;"><?php echo esc_textarea($llms_content); ?></textarea>
		</div>
		<p class="description" style="margin-top:8px;">Served dynamically with <code>Content-Type: text/markdown</code> at <code><?php echo esc_html(home_url('/llms.txt')); ?></code>.</p>
	</div>

	<!-- CARD 4: GEO BEST PRACTICES FOR 2026 -->
	<div class="asf-card">
		<h2>💡 Top Generative Engine Optimization (GEO) Best Practices</h2>
		<ul style="margin:10px 0;padding-left:20px;line-height:1.8;color:#334155;font-size:13px;">
			<li><strong>Direct Answers in Top 100 Words:</strong> AI engines prioritize articles that provide concise, authoritative definitions right under the main H1 heading.</li>
			<li><strong>Numbered &amp; Bulleted Step Lists:</strong> LLMs extract structured step-by-step guides significantly more frequently than dense text paragraphs.</li>
			<li><strong>Conversational Q&amp;A Headers:</strong> Frame H2 and H3 headings as exact search queries (e.g. <em>"How much does MacBook repair cost in Dubai?"</em>).</li>
			<li><strong>Entity Consistency:</strong> Ensure your business name, address, and phone number (NAP) in Schema JSON-LD match across Google Maps, Apple Maps, and directory citations.</li>
		</ul>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
