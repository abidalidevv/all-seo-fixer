<?php
/**
 * Admin Menu Registration + Asset Enqueueing
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @link    https://github.com/abidalidevv/all-seo-fixer
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ASF_Admin {

	/** Slug prefix for all plugin pages */
	const SLUG = 'asf-panel';

	public static function init() {
		add_action( 'admin_menu',            array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 1 );
		add_action( 'admin_head',            array( __CLASS__, 'print_inline_asf_data' ), 1 );
		add_action( 'admin_footer',          array( __CLASS__, 'render_floating_ai_widget' ) );
	}

	/** Prints window.asfData globally in <head> so JS can never miss it */
	public static function print_inline_asf_data() {
		$page = sanitize_text_field( $_GET['page'] ?? '' );
		if ( strpos( $page, 'asf' ) === false ) return;

		$data = array(
			'ajax'      => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'asf_nonce' ),
			'adminUrl'  => admin_url(),
			'siteUrl'   => home_url(),
			'hasPsiKey' => get_option( ASF_OPT_PSI_KEY, '' ) ? '1' : '0',
			'version'   => ASF_VERSION,
		);
		echo '<script type="text/javascript">window.asfData = ' . wp_json_encode( $data ) . ';</script>' . "\n";
	}

	/** Register all admin menu pages */
	public static function register_menu() {
		add_menu_page(
			__( 'All-in-One SEO Fixer', 'all-seo-fixer' ),
			__( 'All SEO Fixer', 'all-seo-fixer' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'dispatch' ),
			'dashicons-chart-bar',
			58
		);

		$pages = array(
			array( 'asf-panel',            '360° SEO Audit' ),
			array( 'asf-onpage',           'On-Page Checker' ),
			array( 'asf-pagespeed',        'PageSpeed Insights' ),
			array( 'asf-lazy-load',        'Lazy Load Images' ),
			array( 'asf-media',            'Media Scanner' ),
			array( 'asf-performance',      'Speed & DB Optimizer' ),
			array( 'asf-builder-analyzer', 'Page Builder Optimizer' ),
			array( 'asf-gsc-inspector',    'Google Search Console' ),
			array( 'asf-w3c-validator',    'W3C HTML Validator' ),
			array( 'asf-keyword-analyzer', 'Keyword Density' ),
			array( 'asf-authority',        'On-Page SEO Health' ),
			array( 'asf-serp-preview',     'SERP Simulator' ),
			array( 'asf-security',         'Security & Headers' ),
			array( 'asf-error-doctor',     'Console & Error Doctor' ),
			array( 'asf-link-cleaner',     'Broken Link Cleaner' ),
			array( 'asf-redirects',        '301 Redirects' ),
			array( 'asf-schema',           'Schema JSON-LD Studio' ),
			array( 'asf-geo',              'GEO & AI Search Hub' ),
			array( 'asf-ai-assistant',     '🤖 AI Assistant' ),
			array( 'asf-swiss-tools',      '🛠️ Swiss-Knife Tools' ),
			array( 'asf-settings',         'Settings' ),
		);

		foreach ( $pages as $page ) {
			add_submenu_page( self::SLUG, $page[1], $page[1], 'manage_options', $page[0], array( __CLASS__, 'dispatch' ) );
		}
	}

	/** Route current page slug to the correct view file */
	public static function dispatch() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$page = $_GET['page'] ?? self::SLUG;
		$map  = array(
			'asf-panel'            => 'dashboard',
			'asf-pagespeed'        => 'pagespeed',
			'asf-performance'      => 'performance',
			'asf-builder-analyzer' => 'builder-analyzer',
			'asf-onpage'           => 'onpage',
			'asf-gsc-inspector'    => 'gsc-inspector',
			'asf-w3c-validator'    => 'w3c-validator',
			'asf-keyword-analyzer' => 'keyword-analyzer',
			'asf-authority'        => 'authority',
			'asf-serp-preview'     => 'serp-preview',
			'asf-security'         => 'domain-security',
			'asf-error-doctor'     => 'error-doctor',
			'asf-link-cleaner'     => 'link-cleaner',
			'asf-media'            => 'media-scanner',
			'asf-lazy-load'        => 'lazy-load',
			'asf-redirects'        => 'redirects',
			'asf-schema'           => 'schema-studio',
			'asf-geo'              => 'geo-hub',
			'asf-ai-assistant'     => 'ai-assistant',
			'asf-swiss-tools'      => 'swiss-tools',
			'asf-settings'         => 'settings',
		);

		$view = $map[ $page ] ?? 'dashboard';
		$file = ASF_PLUGIN_DIR . 'admin/views/' . $view . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		} else {
			echo '<div class="wrap"><div class="asf-notice asf-notice-error">View file not found: ' . esc_html( $file ) . '</div></div>';
		}
	}

	/** Enqueue CSS + JS only on our plugin pages */
	public static function enqueue_assets( $hook ) {

		// Robust check: load assets if hook contains asf OR $_GET['page'] contains asf
		$page = sanitize_text_field( $_GET['page'] ?? '' );
		$is_our_page = ( strpos( (string) $hook, 'asf' ) !== false || strpos( $page, 'asf' ) !== false );

		if ( ! $is_our_page ) return;

		$ver = ASF_VERSION;

		// Ensure WordPress core Dashicons stylesheet is always loaded
		wp_enqueue_style( 'dashicons' );

		// Admin CSS
		wp_enqueue_style(
			'asf-admin',
			ASF_PLUGIN_URL . 'assets/css/admin.css',
			array( 'dashicons' ),
			$ver
		);

		// Chart.js from jsDelivr CDN
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Admin JS
		wp_enqueue_script(
			'asf-admin',
			ASF_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			$ver,
			true
		);

		$last_audit = get_option( 'asf_last_audit_data', false );

		// Localize all dynamic data for JS
		wp_localize_script( 'asf-admin', 'asfData', array(
			'ajax'      => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'asf_nonce' ),
			'adminUrl'  => admin_url(),
			'siteUrl'   => home_url(),
			'hasPsiKey' => get_option( ASF_OPT_PSI_KEY, '' ) ? '1' : '0',
			'version'   => ASF_VERSION,
			'lastAudit' => $last_audit ? $last_audit : null,
		) );
	}

	/** Render floating AI Assistant bubble circle & drawer on all ASF pages */
	public static function render_floating_ai_widget() {
		$page = sanitize_text_field( $_GET['page'] ?? '' );
		if ( strpos( $page, 'asf' ) === false ) return;
		?>
		<!-- FLOATING AI ASSISTANT BUBBLE (ALL ASF PAGES) -->
		<div id="asf-floating-ai-trigger" title="Chat with AI SEO Copilot (Groq AI)" aria-label="AI SEO Copilot">
			<span class="dashicons dashicons-rest-api"></span>
			<span class="asf-ai-badge-dot"></span>
		</div>

		<!-- FLOATING AI COPILOT DRAWER -->
		<div id="asf-floating-ai-drawer" style="display:none;">
			<div class="asf-floating-header">
				<div style="display:flex;align-items:center;gap:8px;">
					<span class="dashicons dashicons-rest-api" style="font-size:20px;color:#fff;"></span>
					<div>
						<strong style="font-size:14px;color:#fff;display:block;line-height:1.2;">AI SEO Copilot</strong>
						<span style="font-size:11px;color:#cbd5e1;"><span style="color:#4ade80;">●</span> Groq AI LLaMA 3.3</span>
					</div>
				</div>
				<div style="display:flex;align-items:center;gap:6px;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=asf-ai-assistant' ) ); ?>" title="Open Full Screen AI Assistant" style="color:#ffffff;text-decoration:none;display:inline-flex;align-items:center;padding:4px;" target="_blank">
						<span class="dashicons dashicons-external" style="font-size:16px;width:16px;height:16px;"></span>
					</a>
					<button type="button" id="asf-floating-ai-close" title="Minimize Drawer" style="background:none;border:none;color:#fff;cursor:pointer;font-size:18px;line-height:1;padding:4px;">✕</button>
				</div>
			</div>

			<div class="asf-floating-chips">
				<button type="button" class="asf-floating-chip" data-prompt="Analyze my site SEO Health and list top 3 priority fixes.">⚡ Priority Fixes</button>
				<button type="button" class="asf-floating-chip" data-prompt="How do I optimize meta descriptions to increase Google CTR?">📝 Meta Descs</button>
				<button type="button" class="asf-floating-chip" data-prompt="How to fix Largest Contentful Paint (LCP) and Total Blocking Time on WordPress?">🚀 Speed & CWV</button>
				<button type="button" class="asf-floating-chip" data-prompt="How to generate schema JSON-LD for a local business WordPress website?">📐 Local Schema</button>
			</div>

			<div id="asf-floating-chat-box">
				<div class="asf-ai-msg asf-ai-msg-bot">
					👋 <strong>Hello! I am your AI SEO Copilot</strong> powered by ultra-fast Groq LLaMA 3.3. How can I assist you with ranking, indexing, technical fixes, or content optimization today?
				</div>
			</div>

			<div class="asf-floating-footer">
				<input type="text" id="asf-floating-ai-input" placeholder="Ask AI Copilot anything..." />
				<button type="button" id="asf-floating-ai-send" class="button button-primary">Send</button>
				<button type="button" id="asf-floating-ai-clear" title="Clear conversation" class="button">🗑</button>
			</div>
		</div>
		<?php
	}
}
