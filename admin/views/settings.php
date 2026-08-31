<?php
/**
 * View: Plugin Settings
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

// Handle save
if ( isset($_POST['asf_save_settings']) && check_admin_referer('asf_save_settings','asf_settings_nonce') ) {
	update_option( ASF_OPT_PSI_KEY, sanitize_text_field( $_POST['asf_psi_key'] ?? '' ) );
	update_option( 'asf_groq_api_key', sanitize_text_field( $_POST['asf_groq_api_key'] ?? '' ) );
	update_option( 'asf_gemini_api_key', sanitize_text_field( $_POST['asf_gemini_api_key'] ?? '' ) );
	update_option( 'asf_openrouter_api_key', sanitize_text_field( $_POST['asf_openrouter_api_key'] ?? '' ) );
	update_option( 'asf_gsc_service_account_json', trim( $_POST['asf_gsc_json'] ?? '' ) );
	update_option( 'asf_local_biz_name', sanitize_text_field( $_POST['asf_local_biz_name'] ?? '' ) );
	update_option( 'asf_local_biz_phone', sanitize_text_field( $_POST['asf_local_biz_phone'] ?? '' ) );
	update_option( 'asf_local_biz_address', sanitize_text_field( $_POST['asf_local_biz_address'] ?? '' ) );
	echo '<div class="asf-notice asf-notice-success"><strong>Settings saved successfully.</strong></div>';
}

$psi_key        = get_option( ASF_OPT_PSI_KEY, '' );
$groq_key       = get_option( 'asf_groq_api_key', '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' );
$gemini_key     = get_option( 'asf_gemini_api_key', '' );
$openrouter_key = get_option( 'asf_openrouter_api_key', '' );
$gsc_json       = get_option( 'asf_gsc_service_account_json', '' );
$biz_name       = get_option( 'asf_local_biz_name', get_bloginfo('name') );
$biz_phone      = get_option( 'asf_local_biz_phone', '' );
$biz_address    = get_option( 'asf_local_biz_address', '' );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Plugin Settings & Integrations</h1>
			<p class="asf-header-desc">Configure API credentials for Groq AI, Google Gemini, OpenRouter, PageSpeed Insights, Google Search Console, and Local Business Schema.</p>
		</div>
		<div class="asf-header-actions">
			<input type="submit" form="asf-settings-form" name="asf_save_settings" class="button button-primary" value="Save Settings" />
		</div>
	</div>

	<form method="post" id="asf-settings-form">
		<?php wp_nonce_field('asf_save_settings','asf_settings_nonce'); ?>

		<!-- AI PROVIDERS CONFIGURATION -->
		<div class="asf-card">
			<h2>🤖 AI Copilot API Keys (Groq, Gemini, OpenRouter)</h2>
			<p>Customize or swap AI model provider API keys powering the <strong>AI SEO Assistant & Copilot Chatbot</strong>:</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="asf_groq_api_key">Groq AI API Key (Default)</label></th>
						<td>
							<input id="asf_groq_api_key" type="password" name="asf_groq_api_key" value="<?php echo esc_attr($groq_key); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="gsk_..." />
							<p class="description">Ultra-fast Groq Llama/GPT-120B model key from <a href="https://console.groq.com/keys" target="_blank">Groq Console</a> (Pre-configured out-of-the-box).</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="asf_gemini_api_key">Google Gemini API Key</label></th>
						<td>
							<input id="asf_gemini_api_key" type="password" name="asf_gemini_api_key" value="<?php echo esc_attr($gemini_key); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="AIzaSy... / AQ..." />
							<p class="description">Free Gemini API key from <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="asf_openrouter_api_key">OpenRouter API Key</label></th>
						<td>
							<input id="asf_openrouter_api_key" type="password" name="asf_openrouter_api_key" value="<?php echo esc_attr($openrouter_key); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="sk-or-v1-..." />
							<p class="description">OpenRouter API key from <a href="https://openrouter.ai/keys" target="_blank">OpenRouter.ai</a>.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- PAGESPEED INSIGHTS API -->
		<div class="asf-card">
			<h2>Google PageSpeed Insights API Key</h2>
			<p>Required to fetch Core Web Vitals (LCP, CLS, TBT) and Lighthouse scores (0–100):</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="asf_psi_key">API Key</label></th>
						<td>
							<input id="asf_psi_key" type="text" name="asf_psi_key" value="<?php echo esc_attr($psi_key); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="AIzaSy..." />
							<p class="description">Free key from <a href="https://console.developers.google.com/apis/credentials" target="_blank">Google Cloud Console</a> (25,000 req/day quota).</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- GSC SERVICE ACCOUNT JSON -->
		<div class="asf-card">
			<h2>Google Search Console Service Account JSON</h2>
			<p>Required for instant Google indexing submissions in Search Console Inspector:</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="asf_gsc_json">Service Account JSON</label></th>
						<td>
							<textarea id="asf_gsc_json" name="asf_gsc_json" class="asf-input" style="width:100%;max-width:600px;height:120px;font-family:monospace;font-size:12px;"
								placeholder='{"type": "service_account", "project_id": "...", "private_key": "-----BEGIN PRIVATE KEY-----\n..."}'><?php echo esc_textarea($gsc_json); ?></textarea>
							<p class="description">Paste your downloaded Google Cloud Service Account JSON key contents.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- LOCAL SEO & NAP -->
		<div class="asf-card">
			<h2>Local SEO & LocalBusiness Schema (NAP)</h2>
			<p>Configure business Name, Address, and Phone number to generate <code>LocalBusiness</code> JSON-LD Schema on your homepage:</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="asf_local_biz_name">Business Name</label></th>
						<td><input id="asf_local_biz_name" type="text" name="asf_local_biz_name" value="<?php echo esc_attr($biz_name); ?>" class="asf-input" style="width:100%;max-width:400px;" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="asf_local_biz_phone">Phone Number</label></th>
						<td><input id="asf_local_biz_phone" type="text" name="asf_local_biz_phone" value="<?php echo esc_attr($biz_phone); ?>" class="asf-input" style="width:100%;max-width:400px;" placeholder="+1-555-0199" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="asf_local_biz_address">Physical Address</label></th>
						<td><input id="asf_local_biz_address" type="text" name="asf_local_biz_address" value="<?php echo esc_attr($biz_address); ?>" class="asf-input" style="width:100%;max-width:500px;" placeholder="123 Main St, Suite 400, New York, NY 10001" /></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="asf-action-bar">
			<input type="submit" name="asf_save_settings" class="button button-primary" value="Save Settings" />
		</div>
	</form>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
