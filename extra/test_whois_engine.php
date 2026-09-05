<?php
function test_clean_domain($raw) {
    $raw = trim($raw);
    if (!preg_match('/^https?:\/\//i', $raw)) $raw = 'http://' . $raw;
    $host = parse_url($raw, PHP_URL_HOST);
    return $host ? strtolower($host) : false;
}

function query_whois_socket($server, $query) {
    if (!function_exists('fsockopen')) return false;
    $fp = @fsockopen($server, 43, $errno, $errstr, 4);
    if (!$fp) return false;
    stream_set_timeout($fp, 4);
    fwrite($fp, $query . "\r\n");
    $out = '';
    while (!feof($fp)) {
        $chunk = fgets($fp, 2048);
        if ($chunk === false) break;
        $out .= $chunk;
    }
    fclose($fp);
    return $out;
}

function resolve_whois($raw_domain) {
    $domain = test_clean_domain($raw_domain);
    if (!$domain) return ['success' => false, 'message' => 'Invalid domain'];

    $created = $updated = $expires = $registrar = $status = 'N/A';
    $raw_text = '';

    // Step 1: Try RDAP first (best for gTLDs)
    $ch = curl_init("https://rdap.org/domain/{$domain}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ASF-Bot/1.0)');
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $body) {
        $data = json_decode($body, true);
        if (!empty($data['events'])) {
            foreach ($data['events'] as $ev) {
                if ($ev['eventAction'] === 'registration') $created = $ev['eventDate'] ?? 'N/A';
                if ($ev['eventAction'] === 'last changed')  $updated = $ev['eventDate'] ?? 'N/A';
                if ($ev['eventAction'] === 'expiration')    $expires = $ev['eventDate'] ?? 'N/A';
            }
        }
        if (!empty($data['entities'])) {
            foreach ($data['entities'] as $ent) {
                if (in_array('registrar', (array)($ent['roles'] ?? []))) {
                    $registrar = $ent['vcardArray'][1][1][3] ?? ($ent['handle'] ?? 'N/A');
                    break;
                }
            }
        }
        if (!empty($data['status'])) {
            $status = is_array($data['status']) ? implode(', ', $data['status']) : $data['status'];
        }
    }

    // Step 2: If RDAP didn't get registrar or created, try whoisjs.com JSON API
    if ($created === 'N/A' || $registrar === 'N/A') {
        $ch2 = curl_init("https://whoisjs.com/api/v1/{$domain}");
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ASF-Bot/1.0)');
        $body2 = curl_exec($ch2);
        curl_close($ch2);
        if ($body2) {
            $data2 = json_decode($body2, true);
            if (!empty($data2['registrar']['name']) && $registrar === 'N/A') {
                $registrar = strtoupper($data2['registrar']['name']);
            }
            if (!empty($data2['raw'])) {
                $raw_text = $data2['raw'];
            }
        }
    }

    // Step 3: Direct Socket WHOIS query via IANA -> TLD WHOIS server
    if ($created === 'N/A' || $registrar === 'N/A') {
        $tld = substr(strrchr($domain, '.'), 1);
        $iana = query_whois_socket('whois.iana.org', $domain);
        $target_server = '';
        if ($iana && preg_match('/(?:whois|refer):\s*([^\s]+)/i', $iana, $m)) {
            $target_server = trim($m[1]);
        }
        if ($target_server) {
            $socket_res = query_whois_socket($target_server, $domain);
            if ($socket_res) {
                $raw_text .= "\n" . $socket_res;
            }
        }
    }

    // Parse any raw text found
    if ($raw_text) {
        if ($registrar === 'N/A' && preg_match('/(?:Registrar Name|Registrar|Sponsoring Registrar):\s*([^\r\n]+)/i', $raw_text, $m)) {
            $registrar = trim($m[1]);
        }
        if ($created === 'N/A' && preg_match('/(?:Creation Date|Created On|Registration Date|Registered on|created|Commencement Date):\s*([^\r\n]+)/i', $raw_text, $m)) {
            $created = trim($m[1]);
        }
        if ($expires === 'N/A' && preg_match('/(?:Registry Expiry Date|Expir\w+ Date|Expiration Date|Expires on|paid-till|validity):\s*([^\r\n]+)/i', $raw_text, $m)) {
            $expires = trim($m[1]);
        }
        if ($updated === 'N/A' && preg_match('/(?:Updated Date|Last Updated On|Last Modified|changed):\s*([^\r\n]+)/i', $raw_text, $m)) {
            $updated = trim($m[1]);
        }
        if ($status === 'N/A' && preg_match('/(?:Domain Status|Status):\s*([^\r\n]+)/i', $raw_text, $m)) {
            $status = trim($m[1]);
        }
        if (preg_match('/(?:Registrant Organisation|Registrant Organization|Registrant Contact Organisation):\s*([^\r\n]+)/i', $raw_text, $m)) {
            $org = trim($m[1]);
            if ($registrar !== 'N/A') {
                $registrar .= " (" . $org . ")";
            } else {
                $registrar = $org;
            }
        }
    }

    // Domain age calculation
    $domain_age = 'N/A';
    if ($created !== 'N/A') {
        $ts = strtotime($created);
        if ($ts) {
            $diff = time() - $ts;
            if ($diff > 0) {
                $years = floor($diff / 31536000);
                $months = floor(($diff % 31536000) / 2592000);
                $domain_age = "{$years} years, {$months} months";
            }
        }
    } elseif ($status !== 'N/A' && stripos($status, 'ok') !== false) {
        $domain_age = 'Active (Protected by Registry)';
    }

    return [
        'success' => true,
        'domain' => $domain,
        'registrar' => $registrar,
        'created' => $created,
        'updated' => $updated,
        'expires' => $expires,
        'status' => $status,
        'domain_age' => $domain_age
    ];
}

echo "Testing efix.ae...\n";
print_r(resolve_whois('https://efix.ae/'));

echo "\nTesting google.com...\n";
print_r(resolve_whois('google.com'));
