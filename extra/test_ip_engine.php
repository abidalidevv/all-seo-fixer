<?php
function test_ip_lookup($raw_input) {
    $raw_input = trim($raw_input);
    if (empty($raw_input)) return ['success' => false, 'message' => 'No input provided.'];

    // If input is a URL or domain, extract host
    if (!filter_var($raw_input, FILTER_VALIDATE_IP)) {
        if (!preg_match('/^https?:\/\//i', $raw_input)) $raw_input = 'http://' . $raw_input;
        $host = parse_url($raw_input, PHP_URL_HOST);
        $host = $host ? strtolower($host) : $raw_input;
        $ip = gethostbyname($host);
    } else {
        $ip = $raw_input;
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return ['success' => false, 'message' => 'Could not resolve domain to an IP address.'];
    }

    $city = $region = $country = $isp = $asn = $timezone = 'N/A';
    $lat = $lon = '';

    // Primary: ip-api.com (reliable, fast, rich data)
    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,regionName,city,zip,lat,lon,timezone,isp,org,as,query");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200 && $body) {
        $data = json_decode($body, true);
        if (!empty($data['status']) && $data['status'] === 'success') {
            $city = $data['city'] ?? 'N/A';
            $region = $data['regionName'] ?? 'N/A';
            $country = $data['country'] ?? 'N/A';
            $isp = $data['isp'] ?? ($data['org'] ?? 'N/A');
            $asn = $data['as'] ?? 'N/A';
            $timezone = $data['timezone'] ?? 'N/A';
            $lat = $data['lat'] ?? '';
            $lon = $data['lon'] ?? '';
        }
    }

    // Fallback: ipwho.is
    if ($country === 'N/A' || $city === 'N/A') {
        $ch2 = curl_init("https://ipwho.is/{$ip}");
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $body2 = curl_exec($ch2);
        curl_close($ch2);
        if ($body2) {
            $data2 = json_decode($body2, true);
            if (!empty($data2['success'])) {
                if ($country === 'N/A') $country = $data2['country'] ?? 'N/A';
                if ($region === 'N/A')  $region = $data2['region'] ?? 'N/A';
                if ($city === 'N/A')    $city = $data2['city'] ?? 'N/A';
                if ($isp === 'N/A')     $isp = $data2['connection']['isp'] ?? ($data2['connection']['org'] ?? 'N/A');
                if ($asn === 'N/A')     $asn = $data2['connection']['asn'] ? ('AS' . $data2['connection']['asn'] . ' ' . ($data2['connection']['org'] ?? '')) : 'N/A';
                if ($timezone === 'N/A')$timezone = $data2['timezone']['id'] ?? 'N/A';
                if (!$lat) $lat = $data2['latitude'] ?? '';
                if (!$lon) $lon = $data2['longitude'] ?? '';
            }
        }
    }

    return [
        'success' => true,
        'ip' => $ip,
        'city' => $city,
        'region' => $region,
        'country' => $country,
        'isp' => $isp,
        'asn' => $asn,
        'timezone' => $timezone,
        'lat' => $lat,
        'lon' => $lon
    ];
}

echo "Testing https://efix.ae/ ...\n";
print_r(test_ip_lookup('https://efix.ae/'));

echo "\nTesting 88.222.222.248 ...\n";
print_r(test_ip_lookup('88.222.222.248'));
