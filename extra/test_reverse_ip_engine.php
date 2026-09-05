<?php
function test_reverse_ip($raw_input) {
    $raw_input = trim($raw_input);
    if (empty($raw_input)) return ['success' => false, 'message' => 'Invalid domain or IP.'];

    $domain = '';
    if (filter_var($raw_input, FILTER_VALIDATE_IP)) {
        $ip = $raw_input;
    } else {
        if (!preg_match('/^https?:\/\//i', $raw_input)) $raw_input = 'http://' . $raw_input;
        $domain = parse_url($raw_input, PHP_URL_HOST);
        $domain = $domain ? strtolower($domain) : $raw_input;
        $ip = gethostbyname($domain);
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return ['success' => false, 'message' => 'Could not resolve domain to an IP address.'];
    }

    // Get PTR hostname
    $ptr = @gethostbyaddr($ip);
    $ptr_host = ($ptr && $ptr !== $ip) ? $ptr : '';

    // HackerTarget reverse IP lookup
    $ch = curl_init("https://api.hackertarget.com/reverseiplookup/?q={$ip}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ASF-Bot/1.0)');
    $body = curl_exec($ch);
    curl_close($ch);

    $hosts = [];
    if ($body) {
        $lines = explode("\n", $body);
        foreach ($lines as $line) {
            $line = trim(strtolower($line));
            if (empty($line)) continue;
            // Ignore error lines, headers, or lines with spaces
            if (strpos($line, ' ') !== false) continue;
            if (strpos($line, 'error') !== false) continue;
            if (strpos($line, 'limit') !== false) continue;
            if (strpos($line, 'record') !== false) continue;
            if (preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i', $line)) {
                $hosts[] = $line;
            }
        }
    }

    // If PTR host is valid and not in list, and no other hosts found, or as PTR
    $hosts = array_values(array_unique($hosts));

    return [
        'success' => true,
        'domain' => $domain ?: $ip,
        'ip' => $ip,
        'ptr_host' => $ptr_host,
        'hosts' => $hosts,
        'count' => count($hosts)
    ];
}

echo "Testing efix.ae:\n";
print_r(test_reverse_ip('https://efix.ae/'));

echo "\nTesting 88.222.222.248 (shared hosting):\n";
$res_shared = test_reverse_ip('88.222.222.248');
echo "Count: " . $res_shared['count'] . "\n";
echo "PTR: " . $res_shared['ptr_host'] . "\n";
echo "Sample hosts: " . implode(', ', array_slice($res_shared['hosts'], 0, 5)) . "\n";
