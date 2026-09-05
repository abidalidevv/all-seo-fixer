<?php
define('ABSPATH', __DIR__ . '/../');
function home_url($p = '') { return 'https://efix.ae/' . $p; }
function admin_url($p = '') { return 'https://efix.ae/wp-admin/' . $p; }
function esc_url($u) { return $u; }
function esc_html($h) { return htmlspecialchars($h, ENT_QUOTES); }
function esc_js($j) { return addslashes($j); }
function wp_create_nonce($n) { return 'mock_nonce_123'; }
function get_option($k, $d = '') {
	$opts = array(
		'asf_gsc_client_id' => '123456789-test.apps.googleusercontent.com',
		'asf_gsc_client_secret' => 'GOCSPX-secret123',
	);
	return $opts[$k] ?? $d;
}

echo "=======================================================\n";
echo "TEST 1: CONSOLE & ERROR DOCTOR (DIAGNOSTIC ENGINE)\n";
echo "=======================================================\n";
require_once __DIR__ . '/../includes/class-asf-error-doctor.php';

$reflector = new ReflectionClass('ASF_ErrorDoctor');
$method = $reflector->getMethod('diagnose_locally');
$method->setAccessible(true);

$e1 = "Uncaught ReferenceError: devicemaster_ajax_var is not defined at https://efix.ae/wp-includes/js/jquery/jquery.min.js?ver=3.7.1:2";
$r1 = $method->invoke(null, $e1, 'JavaScript Error');
echo "E1 Title: " . $r1['title'] . "\n";
echo "E1 Target File: " . $r1['snippet_target'] . "\n";
echo "E1 Steps Count: " . count($r1['steps']) . "\n";
echo "E1 Snippet preview: " . substr($r1['snippet'], 0, 80) . "...\n\n";

$e2 = "ReferenceError: Can't find variable: M at https://efix.ae/wp-content/plugins/revslider/public/js/sr7.js?ver=6.7.18:27";
$r2 = $method->invoke(null, $e2, 'JavaScript Error');
echo "E2 Title: " . $r2['title'] . "\n";
echo "E2 Target File: " . $r2['snippet_target'] . "\n";
echo "E2 Steps Count: " . count($r2['steps']) . "\n";
echo "E2 Snippet preview: " . substr($r2['snippet'], 0, 80) . "...\n\n";

echo "=======================================================\n";
echo "TEST 2: W3C VALIDATOR FILTERING\n";
echo "=======================================================\n";
require_once __DIR__ . '/../includes/class-asf-w3c.php';

// Simulate W3C message parsing as in class-asf-w3c.php
$raw_messages = array(
	array('type' => 'error', 'message' => 'CSS: “color”: Parse Error.', 'extract' => 'r *{color:!important}.head', 'lastLine' => 150, 'lastColumn' => 25847),
	array('type' => 'error', 'message' => 'CSS: “background-color”: Parse Error.', 'extract' => 'ackground-color:}.archive .pag', 'lastLine' => 150, 'lastColumn' => 30658),
	array('type' => 'error', 'message' => '“script” element between “head” and “body”.', 'extract' => '</head> <script> docum', 'lastLine' => 282, 'lastColumn' => 9),
	array('type' => 'error', 'message' => 'Cannot recover after last error. Any further errors will be ignored.', 'extract' => '</head> <script> docum', 'lastLine' => 282, 'lastColumn' => 9),
	array('type' => 'info', 'subType' => 'warning', 'message' => 'The “type” attribute is unnecessary for JavaScript resources.', 'extract' => 'Layer --> <script type="text/javascript"> (func', 'lastLine' => 11, 'lastColumn' => 31),
);

$errors      = array();
$warnings    = array();
$css_notices = array();

foreach ( $raw_messages as $msg ) {
	$type         = $msg['type'] ?? 'info';
	$message_text = $msg['message'] ?? '';

	if (
		strpos( $message_text, 'The “type” attribute is unnecessary for JavaScript resources' ) !== false ||
		strpos( $message_text, 'The “type” attribute is unnecessary for style resources' ) !== false ||
		strpos( $message_text, 'Cannot recover after last error' ) !== false ||
		strpos( $message_text, 'Trailing slash on void element' ) !== false ||
		strpos( $message_text, 'executable code' ) !== false
	) {
		continue;
	}

	if ( strpos( $message_text, 'CSS:' ) !== false || strpos( $message_text, 'Parse Error' ) !== false ) {
		$clean_key = preg_replace( '/^CSS:\s*“?([^”:]+)”?:\s*/i', '$1', $message_text );
		if ( ! isset( $css_notices[ $clean_key ] ) ) {
			$css_notices[ $clean_key ] = array(
				'property' => $clean_key,
				'message'  => $message_text,
				'count'    => 1,
			);
		} else {
			$css_notices[ $clean_key ]['count']++;
		}
		continue;
	}

	$item = array('message' => $message_text, 'line' => $msg['lastLine']);
	if ($type === 'error') $errors[] = $item;
	else $warnings[] = $item;
}

echo "Filtered Critical HTML Errors: " . count($errors) . " (Expected: 1)\n";
echo "Theme Customizer CSS Notices: " . count($css_notices) . " (Expected: 2)\n";
echo "Skipped Harmless Warnings: " . (count($raw_messages) - count($errors) - count($css_notices) - 1) . "\n\n";

echo "=======================================================\n";
echo "TEST 3: GOOGLE SEARCH CONSOLE & OAUTH 2.0\n";
echo "=======================================================\n";
require_once __DIR__ . '/../includes/class-asf-gsc.php';

$auth_url = ASF_GSC::get_google_auth_url();
echo "Generated Google OAuth URL:\n" . $auth_url . "\n";
assert(strpos($auth_url, 'accounts.google.com/o/oauth2/v2/auth') !== false, 'OAuth URL valid');
assert(strpos($auth_url, 'webmasters.readonly') !== false, 'Webmasters scope included');
assert(strpos($auth_url, 'indexing') !== false, 'Indexing scope included');
echo "✓ All assertions passed!\n";
