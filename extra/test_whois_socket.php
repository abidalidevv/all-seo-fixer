<?php
function query_whois($server, $query) {
    $fp = @fsockopen($server, 43, $errno, $errstr, 5);
    if (!$fp) return false;
    fwrite($fp, $query . "\r\n");
    $out = '';
    while (!feof($fp)) { $out .= fgets($fp, 2048); }
    fclose($fp);
    return $out;
}

$domain = 'efix.ae';
echo "Querying IANA for {$domain}...\n";
$iana = query_whois('whois.iana.org', $domain);
if (preg_match('/(?:whois|refer):\s*([^\s]+)/i', $iana, $m)) {
    $target_server = trim($m[1]);
    echo "Found target server: {$target_server}\n";
    $res = query_whois($target_server, $domain);
    echo "WHOIS Response:\n" . substr($res, 0, 500) . "\n";
} else {
    echo "No refer server found in IANA response.\n";
}
