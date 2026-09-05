<?php
define('ABSPATH', __DIR__ . '/../');
function home_url($p = '') { return 'https://efix.ae/' . $p; }
function admin_url($p = '') { return 'https://efix.ae/wp-admin/' . $p; }
function esc_url($u) { return $u; }
function esc_html($h) { return htmlspecialchars($h, ENT_QUOTES); }
function esc_js($j) { return addslashes($j); }
function wp_create_nonce($n) { return 'mock_nonce_123'; }
require_once __DIR__ . '/../includes/class-asf-error-doctor.php';

// Simulate diagnose_locally
$reflector = new ReflectionClass('ASF_ErrorDoctor');
$method = $reflector->getMethod('diagnose_locally');
$method->setAccessible(true);

$test_cases = array(
	"Uncaught ReferenceError: devicemaster_ajax_var is not defined at https://efix.ae/wp-includes/js/jquery/jquery.min.js?ver=3.7.1:2",
	"ReferenceError: Can't find variable: M at https://efix.ae/wp-content/plugins/revslider/public/js/sr7.js?ver=6.7.18:27",
	"TypeError: $ is not a function at https://efix.ae/script.js:5"
);

foreach ($test_cases as $tc) {
	echo "========================================\n";
	echo "TEST ERROR: $tc\n";
	$res = $method->invoke(null, $tc, 'JavaScript Error');
	print_r($res);
}
