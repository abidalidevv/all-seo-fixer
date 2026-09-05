<?php
// Test harness for Swiss Knife Multi-tools fixes

define('ABSPATH', dirname(__DIR__) . '/');

$sent_json = null;
function wp_send_json($data) {
    global $sent_json;
    $sent_json = $data;
    // We don't exit in test
}

function asf_check_nonce() { return true; }
function asf_cap_check() { return true; }
function add_action($tag, $callback) {}
function sanitize_text_field($str) { return trim(strip_tags((string)$str)); }

function wp_remote_get($url, $args = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout'] ?? 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $args['user-agent'] ?? 'Mozilla/5.0');
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body' => $body, 'response' => ['code' => $code]];
}

function wp_remote_retrieve_body($response) {
    return is_array($response) ? ($response['body'] ?? '') : '';
}

function is_wp_error($thing) { return false; }
function esc_attr($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function esc_html($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function esc_textarea($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function home_url($path = '') { return 'https://testsite.com' . $path; }

require_once dirname(__DIR__) . '/includes/class-asf-swiss-tools.php';

echo "=== SWISS-KNIFE MULTI-TOOLS TESTS ===\n\n";

// TEST 1: IP Geolocation Lookup for efix.ae
echo "[TEST 1] Testing IP Geo Location for https://efix.ae/ ...\n";
$_REQUEST['ip'] = 'https://efix.ae/';
ASF_SwissTools::tool_ip_lookup();
print_r($sent_json);
assert(!empty($sent_json['success']));
assert($sent_json['country'] !== 'N/A');
assert($sent_json['city'] !== 'N/A');
assert($sent_json['isp'] !== 'N/A');
echo "  ✓ IP Geo Location successfully resolved without N/A values!\n";

// TEST 2: WHOIS Lookup for efix.ae
echo "\n[TEST 2] Testing WHOIS for efix.ae ...\n";
$_REQUEST['domain'] = 'https://efix.ae/';
ASF_SwissTools::tool_whois();
print_r($sent_json);
assert(!empty($sent_json['success']));
assert($sent_json['domain'] === 'efix.ae');
assert($sent_json['registrar'] !== 'N/A');
assert($sent_json['domain_age'] !== 'N/A');
echo "  ✓ WHOIS for efix.ae successfully resolved with real registrar and active status!\n";

// TEST 3: Reverse IP Lookup for efix.ae
echo "\n[TEST 3] Testing Reverse IP for efix.ae ...\n";
$_REQUEST['domain'] = 'https://efix.ae/';
ASF_SwissTools::tool_reverse_ip();
print_r($sent_json);
assert(!empty($sent_json['success']));
// Count must not include "No DNS A records found"
assert(!in_array('no dns a records found', $sent_json['hosts']));
assert(!in_array('No DNS A records found', $sent_json['hosts']));
echo "  ✓ Reverse IP cleanly handles zero/dedicated servers without false error hostnames!\n";

// TEST 4: View pre-filling verification
echo "\n[TEST 4] Testing Pre-filled values in swiss-panels.php ...\n";
ob_start();
include dirname(__DIR__) . '/admin/views/partials/swiss-panels.php';
$html = ob_get_clean();

assert(strpos($html, 'id="asf-whois-domain" class="asf-input" style="flex:1;" placeholder="example.com" value="testsite.com"') !== false);
assert(strpos($html, 'id="asf-dns-domain" class="asf-input" style="flex:1;" placeholder="example.com" value="testsite.com"') !== false);
assert(strpos($html, 'id="asf-ip-input" class="asf-input" style="flex:1;" placeholder="8.8.8.8 or example.com" value="testsite.com"') !== false);
assert(strpos($html, 'id="asf-revip-domain" class="asf-input" style="flex:1;" placeholder="example.com" value="testsite.com"') !== false);
echo "  ✓ All Swiss Tool inputs correctly pre-filled by default with active domain/URL!\n";

echo "\n🎉 ALL TESTS PASSED WITH 100% SUCCESS!\n";
