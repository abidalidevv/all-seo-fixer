<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php
// Helper
$asf_active_host = parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';
$asf_active_url  = home_url( '/' );

function asf_sw_panel_open( $id, $show = false ) {
	$d = $show ? '' : 'display:none;';
	echo '<div class="asf-swiss-panel asf-card" data-panel="' . esc_attr( $id ) . '" style="' . $d . '">';
}
function asf_sw_panel_close() { echo '</div>'; }
function asf_sw_input_row( $id, $label, $placeholder, $btn_id, $btn_label, $default_val = '' ) {
	echo '<p>' . esc_html( $label ) . '</p>';
	echo '<div style="display:flex;gap:10px;margin-bottom:12px;">';
	echo '<input type="text" id="' . esc_attr($id) . '" class="asf-input" style="flex:1;" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($default_val) . '">';
	echo '<button type="button" class="button button-primary" id="' . esc_attr($btn_id) . '">' . esc_html($btn_label) . '</button>';
	echo '</div>';
}
?>

<?php asf_sw_panel_open('dns', true); ?>
<h2>DNS Records Lookup</h2>
<?php asf_sw_input_row('asf-dns-domain','Fetch A, AAAA, CNAME, MX, NS, TXT, SOA records for any domain.','example.com','asf-dns-btn','Lookup DNS', $asf_active_host); ?>
<div id="asf-dns-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('whois'); ?>
<h2>WHOIS &amp; Domain Age</h2>
<?php asf_sw_input_row('asf-whois-domain','Domain registration date, expiry, registrar, and age via RDAP.','example.com','asf-whois-btn','Run WHOIS', $asf_active_host); ?>
<div id="asf-whois-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('ssl'); ?>
<h2>SSL Certificate Checker</h2>
<?php asf_sw_input_row('asf-ssl-domain','Verify SSL validity, expiry date, days remaining, and certificate issuer.','example.com','asf-ssl-btn','Check SSL', $asf_active_host); ?>
<div id="asf-ssl-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('ip'); ?>
<h2>IP Geo Location Lookup</h2>
<?php asf_sw_input_row('asf-ip-input','ISP, city, country, ASN, and coordinates for any IP or domain.','8.8.8.8 or example.com','asf-ip-btn','Lookup IP', $asf_active_host); ?>
<div id="asf-ip-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('rev-ip'); ?>
<h2>Reverse IP Lookup</h2>
<?php asf_sw_input_row('asf-revip-domain','Find all domains hosted on the same server IP address.','example.com','asf-revip-btn','Reverse Lookup', $asf_active_host); ?>
<div id="asf-revip-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('redirect'); ?>
<h2>Redirect Chain Checker</h2>
<?php asf_sw_input_row('asf-redirect-url','Trace the full 301/302 redirect chain with HTTP status codes.','https://example.com/old-page','asf-redirect-btn','Trace Redirects', $asf_active_url); ?>
<div id="asf-redirect-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('server'); ?>
<h2>Server Status Checker</h2>
<p>Check if multiple websites are online or offline. One URL per line (max 20).</p>
<textarea id="asf-server-urls" class="asf-input" style="width:100%;height:90px;font-family:monospace;" placeholder="https://example.com"><?php echo esc_textarea( $asf_active_url ); ?></textarea>
<div style="margin-top:10px;"><button type="button" class="button button-primary" id="asf-server-btn">Check All Servers</button></div>
<div id="asf-server-result" style="margin-top:12px;"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('broken'); ?>
<h2>Broken Links Finder</h2>
<?php asf_sw_input_row('asf-broken-url','Crawl a URL and check all outgoing links for 404 errors (up to 50 links).','https://example.com','asf-broken-btn','Find Broken Links', $asf_active_url); ?>
<div id="asf-broken-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('emails'); ?>
<h2>Email Extractor</h2>
<?php asf_sw_input_row('asf-email-url','Scan a webpage and extract all email addresses from the page source.','https://example.com/contact','asf-email-btn','Extract Emails', $asf_active_url); ?>
<div id="asf-email-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('source'); ?>
<h2>Page Source Code Viewer</h2>
<?php asf_sw_input_row('asf-source-url','Fetch and display the raw HTML source of any public URL.','https://example.com','asf-source-btn','Get Source', $asf_active_url); ?>
<div id="asf-source-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('class-c'); ?>
<h2>Class C IP Subnet Checker</h2>
<p>Find IP addresses and Class C subnets for multiple domains. One per line.</p>
<textarea id="asf-classc-domains" class="asf-input" style="width:100%;height:90px;font-family:monospace;" placeholder="example.com"><?php echo esc_textarea( $asf_active_host ); ?></textarea>
<div style="margin-top:10px;"><button type="button" class="button button-primary" id="asf-classc-btn">Check IPs</button></div>
<div id="asf-classc-result" style="margin-top:12px;"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('blacklist'); ?>
<h2>Domain Blacklist / DNSBL Checker</h2>
<?php asf_sw_input_row('asf-blacklist-domain','Check if a domain IP is on major spam blacklists (Spamhaus, Barracuda, SpamCop, SORBS).','example.com','asf-blacklist-btn','Check Blacklists', $asf_active_host); ?>
<div id="asf-blacklist-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('keywords'); ?>
<h2>Keyword Suggestion Tool</h2>
<?php asf_sw_input_row('asf-kw-input','Get 50+ long-tail ideas from Google Suggest (A-Z Alphabet Soup method).','wordpress seo plugin','asf-kw-btn','Get Keyword Ideas'); ?>
<div id="asf-kw-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('pagesize'); ?>
<h2>Page Size Checker</h2>
<?php asf_sw_input_row('asf-pagesize-url','Check raw HTML page size in KB. Pages over 500 KB are flagged as heavy.','https://example.com','asf-pagesize-btn','Check Size', $asf_active_url); ?>
<div id="asf-pagesize-result"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('md5'); ?>
<h2>MD5 / SHA-256 Hash Generator</h2>
<p>Generate hashes of any text, instantly in your browser (no server needed).</p>
<textarea id="asf-md5-input" class="asf-input" style="width:100%;height:70px;" placeholder="Enter text to hash..."></textarea>
<div style="margin-top:10px;"><button type="button" class="button button-primary" id="asf-md5-btn">Generate Hashes</button></div>
<div id="asf-md5-result" style="margin-top:12px;"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('url-code'); ?>
<h2>URL Encoder / Decoder</h2>
<p>Encode or decode URL strings, Base64, and HTML entities instantly.</p>
<textarea id="asf-urlcode-input" class="asf-input" style="width:100%;height:70px;" placeholder="Enter URL or text..."></textarea>
<div style="display:flex;gap:8px;margin-top:10px;">
<button type="button" class="button button-primary" id="asf-url-encode-btn">URL Encode</button>
<button type="button" class="button" id="asf-url-decode-btn">URL Decode</button>
<button type="button" class="button" id="asf-b64-encode-btn">Base64 Encode</button>
<button type="button" class="button" id="asf-b64-decode-btn">Base64 Decode</button>
</div>
<div id="asf-urlcode-result" style="margin-top:12px;"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('utm'); ?>
<h2>Google Analytics UTM Builder</h2>
<p>Build tracking URLs with UTM parameters for Google Analytics campaigns.</p>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
<div><label style="font-weight:600;font-size:12px;display:block;margin-bottom:3px;">Website URL *</label><input type="text" id="utm-url" class="asf-input" style="width:100%;" placeholder="https://example.com/landing" value="<?php echo esc_attr( $asf_active_url ); ?>"></div>
<div><label style="font-weight:600;font-size:12px;display:block;margin-bottom:3px;">Source * (utm_source)</label><input type="text" id="utm-source" class="asf-input" style="width:100%;" placeholder="google, newsletter"></div>
<div><label style="font-weight:600;font-size:12px;display:block;margin-bottom:3px;">Medium (utm_medium)</label><input type="text" id="utm-medium" class="asf-input" style="width:100%;" placeholder="cpc, email, social"></div>
<div><label style="font-weight:600;font-size:12px;display:block;margin-bottom:3px;">Campaign (utm_campaign)</label><input type="text" id="utm-campaign" class="asf-input" style="width:100%;" placeholder="spring_sale_2026"></div>
</div>
<button type="button" class="button button-primary" id="asf-utm-btn">Build UTM URL</button>
<div id="asf-utm-result" style="margin-top:12px;"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('qr'); ?>
<h2>QR Code Generator</h2>
<?php asf_sw_input_row('asf-qr-input','Generate a QR code for any URL or text. Download as PNG.','https://yoursite.com','asf-qr-btn','Generate QR', $asf_active_url); ?>
<div id="asf-qr-result" style="text-align:center;margin-top:12px;"></div>
<?php asf_sw_panel_close(); ?>

<?php asf_sw_panel_open('pw'); ?>
<h2>Strong Password Generator</h2>
<p>Generate cryptographically secure random passwords with custom rules.</p>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
<div><label style="font-weight:600;font-size:12px;display:block;margin-bottom:4px;">Password Length</label>
<input type="range" id="asf-pw-length" min="8" max="64" value="20" style="width:100%;" oninput="document.getElementById('asf-pw-len').textContent=this.value">
<span style="font-size:12px;">Length: <strong id="asf-pw-len">20</strong></span></div>
<div style="display:flex;flex-direction:column;gap:5px;justify-content:center;">
<label style="font-size:12px;"><input type="checkbox" id="asf-pw-upper" checked> Uppercase (A-Z)</label>
<label style="font-size:12px;"><input type="checkbox" id="asf-pw-lower" checked> Lowercase (a-z)</label>
<label style="font-size:12px;"><input type="checkbox" id="asf-pw-numbers" checked> Numbers (0-9)</label>
<label style="font-size:12px;"><input type="checkbox" id="asf-pw-symbols" checked> Symbols (!@#$)</label>
</div></div>
<button type="button" class="button button-primary" id="asf-pw-btn">Generate Password</button>
<div id="asf-pw-result" style="margin-top:12px;"></div>
<?php asf_sw_panel_close(); ?>
