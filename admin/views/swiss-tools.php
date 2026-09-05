<?php
/**
 * View: Swiss-Knife Multi-Tools Hub
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$tabs = array(
	'dns'       => 'DNS Lookup',
	'whois'     => 'WHOIS',
	'ssl'       => 'SSL Check',
	'ip'        => 'IP Lookup',
	'rev-ip'    => 'Reverse IP',
	'redirect'  => 'Redirects',
	'server'    => 'Server Status',
	'broken'    => 'Broken Links',
	'emails'    => 'Email Finder',
	'source'    => 'Page Source',
	'class-c'   => 'Class C IP',
	'blacklist' => 'Blacklist',
	'keywords'  => 'Keyword Ideas',
	'pagesize'  => 'Page Size',
	'md5'       => 'MD5/Hash',
	'url-code'  => 'URL Encode',
	'utm'       => 'UTM Builder',
	'qr'        => 'QR Code',
	'pw'        => 'Password',
);
?>
<div class="wrap asf-wrap">
<div class="asf-header">
<div class="asf-header-title">
<h1>&#128736; Swiss-Knife Multi-Tools Hub</h1>
<p class="asf-header-desc">19 instant-run web utilities: DNS, WHOIS, SSL, Redirects, Keyword Research, Blacklist, QR Code, Password Generator and more.</p>
</div></div>
<div class="asf-card" style="padding:0;">
<div id="asf-swiss-tabs" style="display:flex;flex-wrap:wrap;border-bottom:2px solid #e2e8f0;">
<?php
$first = true;
foreach ( $tabs as $slug => $label ) {
	$style = $first ? 'padding:10px 14px;border:none;background:none;cursor:pointer;font-size:12px;border-bottom:3px solid #2271b1;color:#2271b1;font-weight:600;' : 'padding:10px 14px;border:none;background:none;cursor:pointer;font-size:12px;border-bottom:3px solid transparent;';
	echo '<button type="button" class="asf-tab-btn" data-tab="' . esc_attr( $slug ) . '" style="' . $style . '">' . esc_html( $label ) . '</button>';
	$first = false;
}
?>
</div></div>
<div id="asf-swiss-panels" style="margin-top:0;">
<?php include ASF_PLUGIN_DIR . 'admin/views/partials/swiss-panels.php'; ?>
</div>
<div class="asf-footer">Swiss-Knife Multi-Tools &mdash; All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?></div>
</div>
