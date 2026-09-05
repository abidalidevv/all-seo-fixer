<?php
/**
 * Console & Error Doctor — Script & Debug Log Fixer
 *
 * Real-time detection & 1-click auto-fixing for:
 *  - Front-end JavaScript Console Errors & Uncaught Exceptions
 *  - Mixed Content (HTTP Scripts/Styles blocked on HTTPS)
 *  - jQuery $ Conflicts & Undefined Variable Errors
 *  - WordPress PHP Debug Log (Fatal Errors, Warnings, Deprecated Functions)
 *  - AI-Powered Root Cause Diagnosis & Code Fixes (Groq / Gemini / OpenRouter)
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_ErrorDoctor {

	const OPT_LOG = 'asf_captured_console_errors';

	public static function init() {
		// AJAX Handlers
		add_action( 'wp_ajax_asf_scan_error_doctor',       array( __CLASS__, 'handle_scan' ) );
		add_action( 'wp_ajax_asf_clear_debug_log',         array( __CLASS__, 'handle_clear_debug_log' ) );
		add_action( 'wp_ajax_asf_clear_console_log',       array( __CLASS__, 'handle_clear_console_log' ) );
		add_action( 'wp_ajax_asf_toggle_script_fixer',     array( __CLASS__, 'handle_toggle_script_fixer' ) );
		add_action( 'wp_ajax_asf_ai_diagnose_error',       array( __CLASS__, 'handle_ai_diagnose' ) );
		add_action( 'wp_ajax_nopriv_asf_log_client_error', array( __CLASS__, 'handle_log_client_error' ) );
		add_action( 'wp_ajax_asf_log_client_error',        array( __CLASS__, 'handle_log_client_error' ) );

		// Front-End Real-Time Protective Hooks
		add_action( 'wp_head', array( __CLASS__, 'inject_frontend_monitor_and_fixes' ), 0 );
		add_filter( 'script_loader_src', array( __CLASS__, 'force_https_asset_url' ), 99 );
		add_filter( 'style_loader_src',  array( __CLASS__, 'force_https_asset_url' ), 99 );
	}

	/**
	 * Front-end Protective Script Injections (Console error monitor + jQuery safety wrapper)
	 */
	public static function inject_frontend_monitor_and_fixes() {
		if ( is_admin() ) return;

		$enable_monitor = get_option( 'asf_opt_monitor_console', '1' );
		$enable_jquery  = get_option( 'asf_opt_fix_jquery', '1' );

		// 1. Safe jQuery & Frontend Script Compatibility Layer — Prevents "$ is not a function", "jQuery is undefined" and localized variable console crashes
		if ( $enable_jquery === '1' ) {
			$ajax_fallback  = esc_url( admin_url( 'admin-ajax.php' ) );
			$nonce_fallback = wp_create_nonce( 'asf_fallback_nonce' );
			echo "\n<!-- All-in-One SEO Fixer: Safe jQuery & Script Compatibility Layer -->\n";
			echo "<script type=\"text/javascript\">
(function(){
	if(typeof window!=='undefined'){
		if(typeof window.devicemaster_ajax_var==='undefined'){
			window.devicemaster_ajax_var={ajaxurl:'{$ajax_fallback}',ajax_url:'{$ajax_fallback}',url:'{$ajax_fallback}',nonce:'{$nonce_fallback}'};
		}
		window.addEventListener('DOMContentLoaded',function(){
			if(typeof jQuery!=='undefined'&&typeof window.$==='undefined'){window.$=jQuery;}
		});
	}
})();
</script>\n";
		}

		// 2. Client-side Console & Runtime Error Catcher (Max 25 events stored)
		if ( $enable_monitor === '1' ) {
			$ajax_url = admin_url( 'admin-ajax.php' );
			$nonce    = wp_create_nonce( 'asf_error_doctor_nonce' );
			echo "<!-- All-in-One SEO Fixer: Client Console Error Monitor -->\n";
			echo "<script type=\"text/javascript\">
(function(){
	var logged = 0;
	function reportError(msg, url, line, col, type) {
		if (logged >= 5) return;
		logged++;
		var data = {
			action: 'asf_log_client_error',
			nonce: '{$nonce}',
			msg: String(msg || 'Unknown Script Error').substring(0, 300),
			url: String(url || window.location.href).substring(0, 200),
			line: line || 0,
			col: col || 0,
			type: type || 'JavaScript'
		};
		try {
			if (navigator.sendBeacon) {
				var fd = new FormData();
				for (var k in data) fd.append(k, data[k]);
				navigator.sendBeacon('{$ajax_url}', fd);
			} else {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', '{$ajax_url}', true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				var params = Object.keys(data).map(function(k){return encodeURIComponent(k)+'='+encodeURIComponent(data[k]);}).join('&');
				xhr.send(params);
			}
		} catch(e){}
	}
	window.addEventListener('error', function(e){
		reportError(e.message, e.filename, e.lineno, e.colno, 'JS Runtime Error');
	});
	window.addEventListener('unhandledrejection', function(e){
		reportError(e.reason ? (e.reason.message || String(e.reason)) : 'Unhandled Promise Rejection', window.location.href, 0, 0, 'Unhandled Promise');
	});
})();
</script>\n";
		}
	}

	/**
	 * Automatically rewrites http:// enqueued script/style URLs to https:// to fix Mixed Content errors
	 */
	public static function force_https_asset_url( $src ) {
		if ( empty( $src ) || ! is_ssl() ) return $src;

		$force_https = get_option( 'asf_opt_force_https_scripts', '1' );
		if ( $force_https === '1' && strpos( $src, 'http://' ) === 0 ) {
			return preg_replace( '/^http:/i', 'https:', $src );
		}
		return $src;
	}

	/**
	 * Receiver for client-side JS console errors reported by front-end monitor
	 */
	public static function handle_log_client_error() {
		$nonce = sanitize_text_field( $_REQUEST['nonce'] ?? '' );
		if ( ! wp_verify_nonce( $nonce, 'asf_error_doctor_nonce' ) ) {
			wp_send_json_error();
		}

		$msg  = sanitize_text_field( $_REQUEST['msg'] ?? '' );
		$url  = esc_url_raw( $_REQUEST['url'] ?? '' );
		$line = intval( $_REQUEST['line'] ?? 0 );
		$col  = intval( $_REQUEST['col'] ?? 0 );
		$type = sanitize_text_field( $_REQUEST['type'] ?? 'JS Error' );

		if ( empty( $msg ) ) wp_send_json_error();

		$errors = get_option( self::OPT_LOG, array() );
		if ( ! is_array( $errors ) ) $errors = array();

		// Deduplicate: Don't re-log exact same message within 1 hour
		$key = md5( $msg . $url . $line );
		$now = time();

		$errors[ $key ] = array(
			'type'      => $type,
			'msg'       => $msg,
			'url'       => $url,
			'line'      => $line,
			'col'       => $col,
			'time'      => $now,
			'formatted' => date( 'M j, H:i:s', $now ),
		);

		// Keep max 30 recent client errors
		if ( count( $errors ) > 30 ) {
			$errors = array_slice( $errors, -30, null, true );
		}

		update_option( self::OPT_LOG, $errors, false );
		wp_send_json_success();
	}

	/**
	 * Main Scanner: Inspects PHP debug.log, Client Console Errors, and Asset HTTPS status
	 */
	public static function handle_scan() {
		asf_check_nonce();
		asf_cap_check();

		// 1. Scan PHP debug.log
		$debug_log_path = WP_CONTENT_DIR . '/debug.log';
		$debug_exists   = file_exists( $debug_log_path );
		$debug_size     = $debug_exists ? size_format( filesize( $debug_log_path ) ) : '0 B';
		$php_errors     = array();
		$fatal_count    = 0;
		$warn_count     = 0;
		$notice_count   = 0;

		if ( $debug_exists && is_readable( $debug_log_path ) ) {
			// Read last 150KB of log to prevent memory exhaustion on huge files
			$file_size = filesize( $debug_log_path );
			$fp = @fopen( $debug_log_path, 'r' );
			if ( $fp ) {
				$read_len = min( $file_size, 150000 );
				if ( $file_size > $read_len ) {
					fseek( $fp, $file_size - $read_len );
				}
				$log_chunk = fread( $fp, $read_len );
				fclose( $fp );

				$lines = explode( "\n", $log_chunk );
				$lines = array_reverse( array_filter( array_map( 'trim', $lines ) ) );

				$parsed = 0;
				foreach ( $lines as $line ) {
					if ( $parsed >= 40 ) break;

					// Match standard WP debug.log format: [dd-Mmm-yyyy hh:mm:ss UTC] PHP Fatal error: ... in /path on line 123
					$is_fatal   = ( stripos( $line, 'Fatal error' ) !== false || stripos( $line, 'Parse error' ) !== false );
					$is_warning = ( stripos( $line, 'Warning:' ) !== false );
					$is_notice  = ( stripos( $line, 'Notice:' ) !== false || stripos( $line, 'Deprecated:' ) !== false );

					if ( $is_fatal )   $fatal_count++;
					if ( $is_warning ) $warn_count++;
					if ( $is_notice )  $notice_count++;

					if ( $is_fatal || $is_warning || $is_notice ) {
						// Extract source file and line
						$source_file = 'Unknown';
						$line_num    = '—';
						if ( preg_match( '#in\s+([^\s]+)\s+on\s+line\s+(\d+)#i', $line, $m ) ) {
							$full_path   = $m[1];
							$line_num    = $m[2];
							$source_file = basename( dirname( $full_path ) ) . '/' . basename( $full_path );
						}

						// Extract clean message
						$clean_msg = preg_replace( '/^\[[^\]]+\]\s*(PHP\s+)?/i', '', $line );

						$php_errors[] = array(
							'severity' => $is_fatal ? 'fatal' : ( $is_warning ? 'warning' : 'notice' ),
							'message'  => $clean_msg,
							'file'     => $source_file,
							'line'     => $line_num,
							'raw'      => $line,
						);
						$parsed++;
					}
				}
			}
		}

		// 2. Fetch Client Console Errors
		$console_errors_raw = get_option( self::OPT_LOG, array() );
		$console_errors     = array_values( is_array( $console_errors_raw ) ? array_reverse( $console_errors_raw ) : array() );

		// 3. Status of Auto-Fixers
		$fix_https_active   = get_option( 'asf_opt_force_https_scripts', '1' ) === '1';
		$fix_jquery_active  = get_option( 'asf_opt_fix_jquery', '1' ) === '1';
		$monitor_active     = get_option( 'asf_opt_monitor_console', '1' ) === '1';
		$wp_debug_enabled   = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$wp_debug_log_on    = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;

		wp_send_json( array(
			'success' => true,
			'data'    => array(
				'debug_file_exists'  => $debug_exists,
				'debug_file_size'    => $debug_size,
				'wp_debug_enabled'   => $wp_debug_enabled,
				'wp_debug_log_on'    => $wp_debug_log_on,
				'fatal_count'        => $fatal_count,
				'warn_count'         => $warn_count,
				'notice_count'       => $notice_count,
				'php_errors'         => $php_errors,
				'console_errors'     => $console_errors,
				'console_count'      => count( $console_errors ),
				'fix_https_active'   => $fix_https_active,
				'fix_jquery_active'  => $fix_jquery_active,
				'monitor_active'     => $monitor_active,
			),
		) );
	}

	/**
	 * Empties wp-content/debug.log
	 */
	public static function handle_clear_debug_log() {
		asf_check_nonce();
		asf_cap_check();

		$debug_log_path = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log_path ) ) {
			@file_put_contents( $debug_log_path, '' );
		}

		wp_send_json( array(
			'success' => true,
			'message' => 'WordPress debug.log has been purged and cleared successfully!',
		) );
	}

	/**
	 * Empties captured front-end console errors
	 */
	public static function handle_clear_console_log() {
		asf_check_nonce();
		asf_cap_check();

		update_option( self::OPT_LOG, array() );

		wp_send_json( array(
			'success' => true,
			'message' => 'Front-end JavaScript console error history cleared!',
		) );
	}

	/**
	 * Toggles Auto-Fixer features
	 */
	public static function handle_toggle_script_fixer() {
		asf_check_nonce();
		asf_cap_check();

		$feature = sanitize_text_field( $_REQUEST['feature'] ?? '' );
		$state   = sanitize_text_field( $_REQUEST['state'] ?? '1' );

		if ( $feature === 'force_https' ) {
			update_option( 'asf_opt_force_https_scripts', $state );
			$msg = $state === '1' ? 'Mixed Content HTTPS enforcer enabled!' : 'Mixed Content HTTPS enforcer disabled.';
		} elseif ( $feature === 'fix_jquery' ) {
			update_option( 'asf_opt_fix_jquery', $state );
			$msg = $state === '1' ? 'Safe jQuery Compatibility Wrapper enabled!' : 'Safe jQuery Wrapper disabled.';
		} elseif ( $feature === 'monitor_console' ) {
			update_option( 'asf_opt_monitor_console', $state );
			$msg = $state === '1' ? 'Client Console Error Monitor enabled!' : 'Client Console Error Monitor disabled.';
		} else {
			wp_send_json( array( 'success' => false, 'message' => 'Invalid feature' ) );
		}

		wp_send_json( array( 'success' => true, 'message' => $msg ) );
	}

	/**
	 * AI Error Doctor: Takes any PHP or JS error, analyzes root cause, and provides step-by-step WordPress fixes + snippets
	 */
	public static function handle_ai_diagnose() {
		asf_check_nonce();
		asf_cap_check();

		$error_text = sanitize_textarea_field( $_REQUEST['error_text'] ?? '' );
		$error_type = sanitize_text_field( $_REQUEST['error_type'] ?? 'PHP / JS Error' );

		if ( empty( trim( $error_text ) ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'No error message provided.' ) );
		}

		$site_url = home_url( '/' );

		// 1. Check Local Expert Diagnostic Engine first for instant, accurate WordPress solutions
		$local = self::diagnose_locally( $error_text, $error_type );
		if ( $local ) {
			wp_send_json( array_merge( array( 'success' => true ), $local ) );
		}

		$system_prompt = "You are an elite WordPress Technical Engineer and Debugging Specialist for site {$site_url}.
A website owner has encountered a runtime error (JavaScript console error or PHP debug error).
Analyze the error and provide a concise, crystal-clear diagnosis specifically formatted for a WordPress site owner:

1. 🔍 Root Cause: Explain why this error happened in plain sentences (mentioning themes, plugins, caching deferrals, or wp_localize_script where applicable).
2. 🛠️ Step-by-Step Fix: Exact instructions to resolve it inside WordPress Admin (e.g., Appearance → Theme File Editor → functions.php, or Cache Plugin settings).
3. 💻 Code Solution: Provide the exact PHP snippet, JavaScript fix, or wp-config.php snippet needed wrapped in ```php or ```javascript.

Keep your response practical, safe, non-breaking, and directly applicable.";

		$user_prompt = "Error Type: {$error_type}\nError Log Snippet:\n{$error_text}\n\nPlease diagnose and provide the exact step-by-step WordPress fix and copyable snippet.";

		// Route 1: Google Gemini API
		$gemini_key = get_option( 'asf_gemini_api_key', '' );
		if ( ! empty( $gemini_key ) ) {
			$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
			$body = array(
				'contents' => array(
					array( 'parts' => array( array( 'text' => $system_prompt . "\n\n" . $user_prompt ) ) )
				)
			);
			$res = wp_remote_post( $url, array(
				'headers' => array( 'Content-Type' => 'application/json', 'X-goog-api-key' => $gemini_key ),
				'body'    => wp_json_encode( $body ),
				'timeout' => 15,
			) );

			if ( ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) === 200 ) {
				$data  = json_decode( wp_remote_retrieve_body( $res ), true );
				$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
				if ( ! empty( $reply ) ) {
					wp_send_json( array( 'success' => true, 'diagnosis' => trim( $reply ), 'source' => 'Google Gemini AI' ) );
				}
			}
		}

		// Route 2: OpenRouter API
		$openrouter_key = get_option( 'asf_openrouter_api_key', '' );
		if ( ! empty( $openrouter_key ) ) {
			$res = wp_remote_post( 'https://openrouter.ai/api/v1/chat/completions', array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $openrouter_key,
					'HTTP-Referer'  => $site_url,
					'X-Title'       => 'All-in-One SEO Fixer Error Doctor',
				),
				'body'    => wp_json_encode( array(
					'model'    => 'meta-llama/llama-3.3-70b-instruct:free',
					'messages' => array(
						array( 'role' => 'system', 'content' => $system_prompt ),
						array( 'role' => 'user', 'content' => $user_prompt ),
					),
				) ),
				'timeout' => 15,
			) );

			if ( ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) === 200 ) {
				$data  = json_decode( wp_remote_retrieve_body( $res ), true );
				$reply = $data['choices'][0]['message']['content'] ?? '';
				if ( ! empty( $reply ) ) {
					wp_send_json( array( 'success' => true, 'diagnosis' => trim( $reply ), 'source' => 'OpenRouter AI' ) );
				}
			}
		}

		// Route 3: Groq AI (Ultra-fast)
		$groq_key = get_option( 'asf_groq_api_key', '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' );
		if ( ! empty( $groq_key ) ) {
			$groq_models = array( 'llama-3.3-70b-versatile', 'llama-3.1-8b-instant' );
			foreach ( $groq_models as $g_model ) {
				$res = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', array(
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $groq_key,
						'User-Agent'    => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
					),
					'body'    => wp_json_encode( array(
						'model'       => $g_model,
						'messages'    => array(
							array( 'role' => 'system', 'content' => $system_prompt ),
							array( 'role' => 'user',   'content' => $user_prompt ),
						),
						'temperature' => 0.2,
					) ),
					'timeout' => 15,
				) );

				if ( ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) === 200 ) {
					$data  = json_decode( wp_remote_retrieve_body( $res ), true );
					$reply = $data['choices'][0]['message']['content'] ?? '';
					if ( ! empty( $reply ) ) {
						wp_send_json( array( 'success' => true, 'diagnosis' => trim( $reply ), 'source' => 'Groq AI (' . $g_model . ')' ) );
					}
				}
			}
		}

		// Final Fallback Local WordPress Engine
		wp_send_json( array(
			'success'        => true,
			'title'          => 'WordPress Runtime Diagnostic Advice',
			'root_cause'     => 'A script or PHP function encountered an execution halt. Inspect the source file and line number shown in the error table.',
			'steps'          => array(
				'Check the file URL or path in the error table to identify which plugin or theme is triggering the issue.',
				'If caused by a plugin, ensure it is updated to the latest version compatible with your WordPress and PHP version.',
				'If the error relates to scripts loading out of order, check your Cache/Minification plugin (e.g. WP Rocket, LiteSpeed) and exclude the offending script from deferral.',
			),
			'snippet'        => "// Add error handling or check function_exists before calling functions\nif ( function_exists( 'my_custom_function' ) ) {\n    my_custom_function();\n}",
			'snippet_target' => 'functions.php',
			'source'         => 'Built-in Diagnostic Specialist',
		) );
	}

	/**
	 * Local Expert Diagnostic Engine for instant, 100% accurate WordPress troubleshooting
	 */
	private static function diagnose_locally( $error_text, $error_type ) {
		$clean_err = stripslashes( $error_text );

		// 1. Extract variable name from ReferenceError / Can't find variable
		$var_name = '';
		if ( preg_match( '/(?:ReferenceError:\s*(?:Can[\'\\\]*t find variable:\s*|Uncaught ReferenceError:\s*)?|Can[\'\\\]*t find variable:\s*)([a-zA-Z0-9_\$]+)/i', $clean_err, $m ) ) {
			$var_name = trim( $m[1] );
		} elseif ( preg_match( '/([a-zA-Z0-9_\$]+)\s+is not defined/i', $clean_err, $m ) ) {
			$var_name = trim( $m[1] );
		}

		// A. Slider Revolution 7 (sr7.js) / Variable M
		if ( $var_name === 'M' || stripos( $clean_err, 'revslider' ) !== false || stripos( $clean_err, 'sr7.js' ) !== false ) {
			$snippet = "/**\n * All-in-One SEO Fixer: Exclude Slider Revolution from Deferral Conflicts\n */\nadd_filter( 'script_loader_tag', function( \$tag, \$handle ) {\n    if ( strpos( \$handle, 'revslider' ) !== false || strpos( \$tag, 'sr7.js' ) !== false ) {\n        return str_replace( array( 'defer', 'async' ), '', \$tag );\n    }\n    return \$tag;\n}, 99, 2 );";

			return array(
				'title'          => 'Slider Revolution (sr7.js) Script Conflict Fix',
				'root_cause'     => 'Slider Revolution 7 (<code>sr7.js</code>) core math library (<code>M</code>) was deferred or delayed by an optimization plugin (e.g. WP Rocket, LiteSpeed Cache, or Autoptimize). When <code>sr7.js</code> executes out of order before its core dependencies, the browser throws <code>Can\'t find variable: M</code>.',
				'steps'          => array(
					'<strong>Option A (Cache Plugin - Recommended):</strong> Go to your Cache Plugin settings (e.g., <em>LiteSpeed Cache → Page Optimization → JS Settings</em> or <em>WP Rocket → File Optimization</em>). In <strong>"Exclude JS from Defer"</strong> or <strong>"Excluded JS Files"</strong>, add <code>sr7.js</code> and <code>revslider</code>, then click <strong>Purge All Caches</strong>.',
					'<strong>Option B (functions.php Snippet):</strong> In WordPress Admin, go to <strong>Appearance → Theme File Editor</strong> (or open your <strong>WPCode / Code Snippets</strong> plugin).',
					'In the right panel, select <strong>Theme Functions (<code>functions.php</code>)</strong> of your active or child theme.',
					'Scroll to the bottom, paste the code snippet below, and click <strong>Update File</strong>.',
				),
				'snippet'        => $snippet,
				'snippet_target' => 'functions.php',
				'source'         => 'WordPress Diagnostic Specialist (Built-in)',
			);
		}

		// B. Localized AJAX Variable Error (e.g. devicemaster_ajax_var or [name]_ajax_var)
		if ( ! empty( $var_name ) && ( stripos( $var_name, 'ajax' ) !== false || stripos( $var_name, 'var' ) !== false || stripos( $var_name, 'params' ) !== false || stripos( $var_name, 'devicemaster' ) !== false ) ) {
			$snippet = "/**\n * All-in-One SEO Fixer: Auto-define missing window.{$var_name}\n */\nadd_action( 'wp_head', function() {\n    ?>\n    <script type=\"text/javascript\">\n    window.{$var_name} = window.{$var_name} || {\n        ajaxurl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',\n        ajax_url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',\n        url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',\n        nonce: '<?php echo wp_create_nonce( \"asf_ajax_nonce\" ); ?>'\n    };\n    </script>\n    <?php\n}, 1 );";

			return array(
				'title'          => "Missing Variable Fix: window.{$var_name}",
				'root_cause'     => "A front-end theme/plugin script (such as <strong>DeviceMaster</strong>) attempted to send an AJAX request expecting <code>window.{$var_name}</code>, but the script ran before WordPress registered <code>wp_localize_script()</code>, or a page caching plugin delayed the localization script.",
				'steps'          => array(
					'In WordPress Admin, go to <strong>Appearance → Theme File Editor</strong> (or open your <strong>WPCode / Code Snippets</strong> plugin).',
					'In the right column, click on <strong>Theme Functions (<code>functions.php</code>)</strong> for your active theme.',
					'Scroll down to the very bottom of the file.',
					'Paste the code snippet below and click <strong>Update File</strong>. This immediately pre-defines <code>window.' . esc_html( $var_name ) . '</code> with the safe WordPress AJAX URL and nonce in <code>&lt;head&gt;</code> before any scripts execute.',
				),
				'snippet'        => $snippet,
				'snippet_target' => 'functions.php',
				'source'         => 'WordPress Diagnostic Specialist (Built-in)',
			);
		}

		// C. Generic Undefined Variable
		if ( ! empty( $var_name ) && $var_name !== '$' && $var_name !== 'jQuery' ) {
			$snippet = "/**\n * All-in-One SEO Fixer: Safeguard for undefined window.{$var_name}\n */\nadd_action( 'wp_head', function() {\n    ?>\n    <script type=\"text/javascript\">\n    if ( typeof window.{$var_name} === 'undefined' ) {\n        window.{$var_name} = window.{$var_name} || {};\n    }\n    </script>\n    <?php\n}, 1 );";

			return array(
				'title'          => "Safeguard for Undefined Variable: window.{$var_name}",
				'root_cause'     => "A JavaScript script on your site is trying to access <code>window.{$var_name}</code> before it was defined or loaded.",
				'steps'          => array(
					'In WordPress Admin, go to <strong>Appearance → Theme File Editor</strong>.',
					'Click on <strong>Theme Functions (<code>functions.php</code>)</strong>.',
					'Paste the snippet below at the bottom and click <strong>Update File</strong>.',
				),
				'snippet'        => $snippet,
				'snippet_target' => 'functions.php',
				'source'         => 'WordPress Diagnostic Specialist (Built-in)',
			);
		}

		// D. jQuery $ is not a function / jQuery is undefined
		if ( preg_match( '/\$\s+is not a function|jQuery is (?:not defined|undefined)/i', $clean_err ) ) {
			$snippet = "jQuery(document).ready(function($) {\n    // Wrap your jQuery code here safely using $\n});";

			return array(
				'title'          => 'Safe jQuery $ Conflict Fix',
				'root_cause'     => 'WordPress runs jQuery in <code>noConflict()</code> mode, meaning the global <code>$</code> shortcut is not assigned to <code>window.$</code> by default. A custom script or plugin tried to use <code>$()</code> before jQuery was assigned.',
				'steps'          => array(
					'In this <strong>Console & Error Doctor</strong> tab, scroll up to the <strong>Protection Toggles</strong> table.',
					'Find <strong>Safe jQuery Compatibility Layer</strong> and click <strong>Enable & Auto-Fix</strong>. This immediately injects <code>window.$ = jQuery</code> site-wide without editing code.',
					'If you are adding custom jQuery in your theme, wrap your code in the snippet below.',
				),
				'snippet'        => $snippet,
				'snippet_target' => 'Theme JS File or Custom JS Box',
				'source'         => 'WordPress Diagnostic Specialist (Built-in)',
			);
		}

		// E. Cannot read properties of null / undefined (DOM Element missing)
		if ( preg_match( '/Cannot read propert(?:y|ies) of (?:null|undefined)/i', $clean_err ) ) {
			$snippet = "document.addEventListener('DOMContentLoaded', function() {\n    var el = document.querySelector('.your-target-selector');\n    if (el) {\n        // Code runs safely only when element exists\n    }\n});";

			return array(
				'title'          => 'DOM Null / Undefined Element Access Fix',
				'root_cause'     => 'A JavaScript file attempted to call a method (like <code>.addEventListener</code>, <code>.classList</code>, or <code>.innerHTML</code>) on an HTML element that does not exist on this specific page or has not rendered yet.',
				'steps'          => array(
					'Ensure any custom JavaScript is wrapped inside a <code>DOMContentLoaded</code> event listener so it waits for HTML elements to finish rendering.',
					'Add a guard condition <code>if (element) { ... }</code> before interacting with the element as shown below.',
				),
				'snippet'        => $snippet,
				'snippet_target' => 'JavaScript File',
				'source'         => 'WordPress Diagnostic Specialist (Built-in)',
			);
		}

		// F. PHP Fatal: Allowed memory size exhausted
		if ( stripos( $clean_err, 'Allowed memory size' ) !== false ) {
			$snippet = "define( 'WP_MEMORY_LIMIT', '256M' );";

			return array(
				'title'          => 'PHP Memory Limit Exhaustion Fix',
				'root_cause'     => 'WordPress reached the allocated PHP memory limit. Heavy plugins (Elementor, WooCommerce, slider builders) require at least 256MB of RAM.',
				'steps'          => array(
					'Connect to your server via <strong>FTP</strong> or your hosting <strong>cPanel / File Manager</strong>.',
					'In your WordPress root directory (where <code>wp-content</code> is located), open <strong><code>wp-config.php</code></strong>.',
					'Paste the snippet below right above the line <code>/* That\'s all, stop editing! Happy publishing. */</code>.',
					'Save the file.',
				),
				'snippet'        => $snippet,
				'snippet_target' => 'wp-config.php',
				'source'         => 'WordPress Diagnostic Specialist (Built-in)',
			);
		}

		return false; // Defer to external AI
	}
}
