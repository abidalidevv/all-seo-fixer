<?php
/**
 * Swiss-Knife Multi-Tools Hub — 14+ AJAX-powered Web Utilities
 *
 * @package All_SEO_Fixer
 * @author  Abid Ali Dev <https://abidalidev.com>
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==============================================================
   SWISS-KNIFE MULTI-TOOLS HUB
   ============================================================== */
class ASF_SwissTools {

	public static function init() {
		add_action( 'wp_ajax_asf_tool_dns',         array( __CLASS__, 'tool_dns' ) );
		add_action( 'wp_ajax_asf_tool_whois',       array( __CLASS__, 'tool_whois' ) );
		add_action( 'wp_ajax_asf_tool_ip_lookup',   array( __CLASS__, 'tool_ip_lookup' ) );
		add_action( 'wp_ajax_asf_tool_reverse_ip',  array( __CLASS__, 'tool_reverse_ip' ) );
		add_action( 'wp_ajax_asf_tool_redirect',    array( __CLASS__, 'tool_redirect' ) );
		add_action( 'wp_ajax_asf_tool_server_stat', array( __CLASS__, 'tool_server_status' ) );
		add_action( 'wp_ajax_asf_tool_broken',      array( __CLASS__, 'tool_broken_links' ) );
		add_action( 'wp_ajax_asf_tool_emails',      array( __CLASS__, 'tool_email_extractor' ) );
		add_action( 'wp_ajax_asf_tool_source',      array( __CLASS__, 'tool_page_source' ) );
		add_action( 'wp_ajax_asf_tool_class_c',     array( __CLASS__, 'tool_class_c_ip' ) );
		add_action( 'wp_ajax_asf_tool_blacklist',   array( __CLASS__, 'tool_blacklist' ) );
		add_action( 'wp_ajax_asf_tool_kw_suggest',  array( __CLASS__, 'tool_keyword_suggest' ) );
		add_action( 'wp_ajax_asf_tool_ssl_check',   array( __CLASS__, 'tool_ssl_check' ) );
		add_action( 'wp_ajax_asf_tool_page_size',   array( __CLASS__, 'tool_page_size' ) );
	}

	private static function auth() {
		asf_check_nonce();
		asf_cap_check();
	}

	private static function clean_domain( $raw ) {
		$raw = trim( (string) $raw );
		if ( empty( $raw ) ) return false;
		if ( ! preg_match( '/^https?:\/\//i', $raw ) ) $raw = 'http://' . $raw;
		$host = parse_url( $raw, PHP_URL_HOST );
		if ( ! $host ) {
			$host = preg_replace( '/^https?:\/\//i', '', $raw );
			$host = strtok( $host, '/?#' );
		}
		return $host ? strtolower( trim( $host ) ) : false;
	}

	private static function query_whois_socket( $server, $query ) {
		if ( ! function_exists( 'fsockopen' ) ) return false;
		$fp = @fsockopen( $server, 43, $errno, $errstr, 4 );
		if ( ! $fp ) return false;
		stream_set_timeout( $fp, 4 );
		fwrite( $fp, $query . "\r\n" );
		$out = '';
		while ( ! feof( $fp ) ) {
			$chunk = fgets( $fp, 2048 );
			if ( $chunk === false ) break;
			$out .= $chunk;
		}
		fclose( $fp );
		return $out;
	}

	private static function fetch_url( $url, $timeout = 8 ) {
		$resp = wp_remote_get( $url, array(
			'timeout'    => $timeout,
			'sslverify'  => false,
			'user-agent' => 'Mozilla/5.0 (compatible; ASF-Bot/1.0)',
		) );
		if ( is_wp_error( $resp ) ) return false;
		return wp_remote_retrieve_body( $resp );
	}

	/* DNS Records */
	public static function tool_dns() {
		self::auth();
		$domain = self::clean_domain( sanitize_text_field( $_REQUEST['domain'] ?? '' ) );
		if ( ! $domain ) wp_send_json( array( 'success' => false, 'message' => 'Invalid domain.' ) );

		$types      = array( DNS_A => 'A', DNS_AAAA => 'AAAA', DNS_CNAME => 'CNAME', DNS_MX => 'MX', DNS_NS => 'NS', DNS_TXT => 'TXT', DNS_SOA => 'SOA' );
		$records    = array();
		foreach ( $types as $type => $name ) {
			$res = @dns_get_record( $domain, $type );
			if ( ! empty( $res ) ) {
				foreach ( $res as $r ) { $r['_type'] = $name; $records[] = $r; }
			}
		}
		wp_send_json( array( 'success' => true, 'domain' => $domain, 'records' => $records, 'count' => count( $records ) ) );
	}

	/* Whois + Domain Age with Multi-layered Failover (RDAP + whoisjs + Socket WHOIS) */
	public static function tool_whois() {
		self::auth();
		$domain = self::clean_domain( sanitize_text_field( $_REQUEST['domain'] ?? '' ) );
		if ( ! $domain ) wp_send_json( array( 'success' => false, 'message' => 'Invalid domain.' ) );

		$created = $updated = $expires = $registrar = $status = 'N/A';
		$raw_text = '';
		$nameservers = array();

		// Layer 1: Try RDAP (standard for gTLDs)
		$body = self::fetch_url( "https://rdap.org/domain/{$domain}", 6 );
		$data = $body ? json_decode( $body, true ) : null;
		if ( ! empty( $data ) && is_array( $data ) ) {
			if ( ! empty( $data['events'] ) ) {
				foreach ( $data['events'] as $ev ) {
					if ( ( $ev['eventAction'] ?? '' ) === 'registration' ) $created = $ev['eventDate'] ?? 'N/A';
					if ( ( $ev['eventAction'] ?? '' ) === 'last changed' )  $updated = $ev['eventDate'] ?? 'N/A';
					if ( ( $ev['eventAction'] ?? '' ) === 'expiration' )    $expires = $ev['eventDate'] ?? 'N/A';
				}
			}
			if ( ! empty( $data['entities'] ) ) {
				foreach ( $data['entities'] as $ent ) {
					if ( in_array( 'registrar', (array) ( $ent['roles'] ?? array() ) ) ) {
						$registrar = $ent['vcardArray'][1][1][3] ?? ( $ent['handle'] ?? 'N/A' );
						break;
					}
				}
			}
			if ( ! empty( $data['status'] ) ) {
				$status = is_array( $data['status'] ) ? implode( ', ', $data['status'] ) : $data['status'];
			}
			if ( ! empty( $data['nameservers'] ) ) {
				foreach ( $data['nameservers'] as $ns ) {
					if ( ! empty( $ns['ldhName'] ) ) $nameservers[] = $ns['ldhName'];
				}
			}
		}

		// Layer 2: Fast WHOIS REST API (for ccTLDs like .ae, .me, etc.)
		if ( $created === 'N/A' || $registrar === 'N/A' ) {
			$body2 = self::fetch_url( "https://whoisjs.com/api/v1/{$domain}", 5 );
			if ( $body2 ) {
				$data2 = json_decode( $body2, true );
				if ( ! empty( $data2['registrar']['name'] ) && $registrar === 'N/A' ) {
					$registrar = strtoupper( $data2['registrar']['name'] );
				}
				if ( ! empty( $data2['raw'] ) ) {
					$raw_text .= "\n" . $data2['raw'];
				}
			}
		}

		// Layer 3: Direct Socket WHOIS via IANA -> ccTLD WHOIS server
		if ( $created === 'N/A' || $registrar === 'N/A' ) {
			$iana = self::query_whois_socket( 'whois.iana.org', $domain );
			$target_server = '';
			if ( $iana && preg_match( '/(?:whois|refer):\s*([^\s]+)/i', $iana, $m ) ) {
				$target_server = trim( $m[1] );
			}
			if ( $target_server ) {
				$socket_res = self::query_whois_socket( $target_server, $domain );
				if ( $socket_res ) {
					$raw_text .= "\n" . $socket_res;
				}
			}
		}

		// Parse raw text for registrar, dates, status, organization
		if ( $raw_text ) {
			if ( $registrar === 'N/A' && preg_match( '/(?:Registrar Name|Registrar|Sponsoring Registrar|registrar-name):\s*([^\r\n]+)/i', $raw_text, $m ) ) {
				$registrar = trim( $m[1] );
			}
			if ( $created === 'N/A' && preg_match( '/(?:Creation Date|Created On|Registration Date|Registered on|created|Domain Name Commencement Date):\s*([^\r\n]+)/i', $raw_text, $m ) ) {
				$created = trim( $m[1] );
			}
			if ( $expires === 'N/A' && preg_match( '/(?:Registry Expiry Date|Expir\w+ Date|Expiration Date|Expires on|paid-till|validity):\s*([^\r\n]+)/i', $raw_text, $m ) ) {
				$expires = trim( $m[1] );
			}
			if ( $updated === 'N/A' && preg_match( '/(?:Updated Date|Last Updated On|Last Modified|changed):\s*([^\r\n]+)/i', $raw_text, $m ) ) {
				$updated = trim( $m[1] );
			}
			if ( $status === 'N/A' && preg_match( '/(?:Domain Status|Status):\s*([^\r\n]+)/i', $raw_text, $m ) ) {
				$status = trim( $m[1] );
			}
			if ( preg_match( '/(?:Registrant Organisation|Registrant Organization|Registrant Contact Organisation):\s*([^\r\n]+)/i', $raw_text, $m ) ) {
				$org = trim( $m[1] );
				if ( $registrar !== 'N/A' && stripos( $registrar, $org ) === false ) {
					$registrar .= " (" . $org . ")";
				} elseif ( $registrar === 'N/A' ) {
					$registrar = $org;
				}
			}
			if ( empty( $nameservers ) && preg_match_all( '/(?:Name Server|nserver):\s*([^\r\n\s]+)/i', $raw_text, $ns_m ) ) {
				$nameservers = array_values( array_unique( $ns_m[1] ) );
			}
		}

		// Calculate domain age
		$domain_age = 'N/A';
		if ( $created !== 'N/A' ) {
			$ts = strtotime( $created );
			if ( $ts ) {
				$diff = time() - $ts;
				if ( $diff > 0 ) {
					$years      = floor( $diff / 31536000 );
					$months     = floor( ( $diff % 31536000 ) / 2592000 );
					$domain_age = "{$years} years, {$months} months";
				}
			}
		} elseif ( $status !== 'N/A' && ( stripos( $status, 'ok' ) !== false || stripos( $status, 'active' ) !== false ) ) {
			$domain_age = 'Active (Protected by Registry)';
		}

		// Normalize dates if ISO strings
		if ( $created !== 'N/A' && strtotime( $created ) ) {
			$created = date( 'Y-m-d', strtotime( $created ) );
		}
		if ( $expires !== 'N/A' && strtotime( $expires ) ) {
			$expires = date( 'Y-m-d', strtotime( $expires ) );
		}

		wp_send_json( array(
			'success'     => true,
			'domain'      => $domain,
			'created'     => $created,
			'updated'     => $updated,
			'expires'     => $expires,
			'registrar'   => $registrar,
			'status'      => $status,
			'nameservers' => $nameservers,
			'domain_age'  => $domain_age,
		) );
	}

	/* IP Geolocation Lookup (Multi-source: ip-api.com + ipwho.is fallback) */
	public static function tool_ip_lookup() {
		self::auth();
		$raw_input = sanitize_text_field( $_REQUEST['ip'] ?? '' );
		if ( empty( $raw_input ) ) wp_send_json( array( 'success' => false, 'message' => 'No input provided.' ) );

		if ( filter_var( $raw_input, FILTER_VALIDATE_IP ) ) {
			$ip = $raw_input;
		} else {
			$host = self::clean_domain( $raw_input ) ?: $raw_input;
			$ip   = gethostbyname( $host );
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Could not resolve host to an IP address.' ) );
		}

		$city = $region = $country = $isp = $asn = $timezone = 'N/A';
		$lat = $lon = '';

		// Primary Source: ip-api.com (free, reliable, high-precision)
		$body = self::fetch_url( "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,zip,lat,lon,timezone,isp,org,as,query", 5 );
		if ( $body ) {
			$data = json_decode( $body, true );
			if ( ! empty( $data['status'] ) && $data['status'] === 'success' ) {
				$city     = $data['city'] ?? 'N/A';
				$region   = $data['regionName'] ?? 'N/A';
				$country  = $data['country'] ?? 'N/A';
				$isp      = $data['isp'] ?? ( $data['org'] ?? 'N/A' );
				$asn      = $data['as'] ?? 'N/A';
				$timezone = $data['timezone'] ?? 'N/A';
				$lat      = $data['lat'] ?? '';
				$lon      = $data['lon'] ?? '';
			}
		}

		// Fallback Source: ipwho.is
		if ( $country === 'N/A' || $city === 'N/A' ) {
			$body2 = self::fetch_url( "https://ipwho.is/{$ip}", 4 );
			if ( $body2 ) {
				$data2 = json_decode( $body2, true );
				if ( ! empty( $data2['success'] ) ) {
					if ( $country === 'N/A' )  $country  = $data2['country'] ?? 'N/A';
					if ( $region === 'N/A' )   $region   = $data2['region'] ?? 'N/A';
					if ( $city === 'N/A' )     $city     = $data2['city'] ?? 'N/A';
					if ( $isp === 'N/A' )      $isp      = $data2['connection']['isp'] ?? ( $data2['connection']['org'] ?? 'N/A' );
					if ( $asn === 'N/A' )      $asn      = ! empty( $data2['connection']['asn'] ) ? ( 'AS' . $data2['connection']['asn'] . ' ' . ( $data2['connection']['org'] ?? '' ) ) : 'N/A';
					if ( $timezone === 'N/A' ) $timezone = $data2['timezone']['id'] ?? 'N/A';
					if ( ! $lat )              $lat      = $data2['latitude'] ?? '';
					if ( ! $lon )              $lon      = $data2['longitude'] ?? '';
				}
			}
		}

		wp_send_json( array(
			'success'  => true,
			'ip'       => $ip,
			'city'     => $city,
			'region'   => $region,
			'country'  => $country,
			'isp'      => $isp,
			'lat'      => $lat,
			'lon'      => $lon,
			'timezone' => $timezone,
			'asn'      => $asn,
		) );
	}

	/* Reverse IP (With Domain Name Validation + PTR Reverse DNS) */
	public static function tool_reverse_ip() {
		self::auth();
		$raw_input = sanitize_text_field( $_REQUEST['domain'] ?? '' );
		if ( empty( $raw_input ) ) wp_send_json( array( 'success' => false, 'message' => 'Invalid domain or IP.' ) );

		$domain = '';
		if ( filter_var( $raw_input, FILTER_VALIDATE_IP ) ) {
			$ip = $raw_input;
		} else {
			$domain = self::clean_domain( $raw_input );
			if ( ! $domain ) wp_send_json( array( 'success' => false, 'message' => 'Invalid domain name.' ) );
			$ip = gethostbyname( $domain );
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Could not resolve domain to an IP address.' ) );
		}

		// Reverse DNS PTR Hostname
		$ptr = @gethostbyaddr( $ip );
		$ptr_host = ( $ptr && $ptr !== $ip ) ? $ptr : '';

		// HackerTarget Reverse IP Lookup
		$body  = self::fetch_url( "https://api.hackertarget.com/reverseiplookup/?q={$ip}", 6 );
		$hosts = array();
		if ( $body ) {
			$lines = explode( "\n", $body );
			foreach ( $lines as $line ) {
				$line = trim( strtolower( $line ) );
				if ( empty( $line ) ) continue;
				// Filter out status messages, errors, and text with spaces
				if ( strpos( $line, ' ' ) !== false ) continue;
				if ( strpos( $line, 'error' ) !== false ) continue;
				if ( strpos( $line, 'limit' ) !== false ) continue;
				if ( strpos( $line, 'record' ) !== false ) continue;
				if ( preg_match( '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i', $line ) ) {
					$hosts[] = $line;
				}
			}
		}

		$hosts = array_values( array_unique( $hosts ) );

		wp_send_json( array(
			'success'  => true,
			'domain'   => $domain ?: $ip,
			'ip'       => $ip,
			'ptr_host' => $ptr_host,
			'hosts'    => $hosts,
			'count'    => count( $hosts ),
		) );
	}

	/* Redirect Chain Checker */
	public static function tool_redirect() {
		self::auth();
		$url = sanitize_text_field( $_REQUEST['url'] ?? '' );
		if ( ! preg_match( '/^https?:\/\//i', $url ) ) $url = 'http://' . ltrim( $url, '/' );
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) wp_send_json( array( 'success' => false, 'message' => 'Invalid URL.' ) );

		$chain   = array();
		$current = $url;
		for ( $i = 0; $i < 10; $i++ ) {
			$resp     = wp_remote_head( $current, array( 'timeout' => 6, 'redirection' => 0, 'sslverify' => false ) );
			if ( is_wp_error( $resp ) ) break;
			$code     = wp_remote_retrieve_response_code( $resp );
			$location = wp_remote_retrieve_header( $resp, 'location' );
			$chain[]  = array( 'url' => $current, 'code' => $code );
			if ( $code >= 300 && $code < 400 && ! empty( $location ) ) {
				if ( ! preg_match( '/^https?:\/\//i', $location ) ) {
					$p = parse_url( $current );
					$location = $p['scheme'] . '://' . $p['host'] . $location;
				}
				$current = $location;
			} else { break; }
		}

		wp_send_json( array( 'success' => true, 'chain' => $chain, 'hops' => count( $chain ) ) );
	}

	/* Server Status Checker */
	public static function tool_server_status() {
		self::auth();
		$raw_urls = sanitize_textarea_field( $_REQUEST['urls'] ?? '' );
		$url_list = array_slice( array_filter( array_map( 'trim', explode( "\n", $raw_urls ) ) ), 0, 20 );
		$results  = array();
		foreach ( $url_list as $u ) {
			if ( ! preg_match( '/^https?:\/\//i', $u ) ) $u = 'http://' . ltrim( $u, '/' );
			$start = microtime(true);
			$resp  = wp_remote_head( $u, array( 'timeout' => 6, 'sslverify' => false, 'redirection' => 5 ) );
			$time  = round( ( microtime(true) - $start ) * 1000 );
			$code  = is_wp_error($resp) ? 0 : wp_remote_retrieve_response_code($resp);
			$results[] = array( 'url' => $u, 'status' => ($code >= 200 && $code < 400) ? 'Online' : ($code === 0 ? 'Offline' : 'Error'), 'code' => $code, 'ms' => $time );
		}
		wp_send_json( array( 'success' => true, 'results' => $results ) );
	}

	/* Broken Links Finder */
	public static function tool_broken_links() {
		self::auth();
		$url = sanitize_text_field( $_REQUEST['url'] ?? '' );
		if ( ! preg_match( '/^https?:\/\//i', $url ) ) $url = 'http://' . ltrim( $url, '/' );
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) wp_send_json( array( 'success' => false, 'message' => 'Invalid URL.' ) );

		$body = self::fetch_url( $url, 12 );
		if ( ! $body ) wp_send_json( array( 'success' => false, 'message' => 'Could not fetch page.' ) );

		preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $body, $matches );
		$links  = array_unique( $matches[1] ?? array() );
		$parsed = parse_url( $url );
		$base   = $parsed['scheme'] . '://' . $parsed['host'];
		$broken = array();
		$ok     = 0;

		foreach ( array_slice( $links, 0, 50 ) as $link ) {
			if ( substr( $link, 0, 1 ) === '#' ) continue;
			if ( substr( $link, 0, 2 ) === '//' ) $link = 'https:' . $link;
			if ( substr( $link, 0, 1 ) === '/' )  $link = $base . $link;
			if ( ! preg_match( '/^https?:\/\//i', $link ) ) continue;
			$resp = wp_remote_head( $link, array( 'timeout' => 5, 'sslverify' => false, 'redirection' => 3 ) );
			$code = is_wp_error($resp) ? 0 : wp_remote_retrieve_response_code($resp);
			if ( $code === 0 || $code >= 400 ) $broken[] = array( 'url' => $link, 'code' => $code );
			else $ok++;
		}

		wp_send_json( array( 'success' => true, 'broken' => $broken, 'ok_count' => $ok ) );
	}

	/* Email Extractor */
	public static function tool_email_extractor() {
		self::auth();
		$url = sanitize_text_field( $_REQUEST['url'] ?? '' );
		if ( ! preg_match( '/^https?:\/\//i', $url ) ) $url = 'http://' . ltrim( $url, '/' );
		$body = self::fetch_url( $url );
		if ( ! $body ) wp_send_json( array( 'success' => false, 'message' => 'Could not fetch page.' ) );
		preg_match_all( '/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,6})/', $body, $m );
		$emails = array_values( array_unique( $m[1] ?? array() ) );
		wp_send_json( array( 'success' => true, 'emails' => $emails, 'count' => count($emails) ) );
	}

	/* Page Source Viewer */
	public static function tool_page_source() {
		self::auth();
		$url = sanitize_text_field( $_REQUEST['url'] ?? '' );
		if ( ! preg_match( '/^https?:\/\//i', $url ) ) $url = 'http://' . ltrim( $url, '/' );
		$body = self::fetch_url( $url );
		if ( ! $body ) wp_send_json( array( 'success' => false, 'message' => 'Could not fetch source.' ) );
		wp_send_json( array( 'success' => true, 'source' => mb_substr( $body, 0, 30000 ), 'size' => strlen($body) ) );
	}

	/* Class C IP Checker */
	public static function tool_class_c_ip() {
		self::auth();
		$raw     = sanitize_textarea_field( $_REQUEST['domains'] ?? '' );
		$domains = array_slice( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ), 0, 20 );
		$results = array();
		foreach ( $domains as $d ) {
			$host  = self::clean_domain( $d );
			if ( ! $host ) continue;
			$ip    = gethostbyname( $host );
			$parts = explode( '.', $ip );
			$results[] = array( 'domain' => $host, 'ip' => $ip, 'class_c' => count($parts) === 4 ? $parts[0].'.'.$parts[1].'.'.$parts[2] : 'N/A' );
		}
		wp_send_json( array( 'success' => true, 'results' => $results ) );
	}

	/* Blacklist DNSBL Lookup */
	public static function tool_blacklist() {
		self::auth();
		$domain = self::clean_domain( sanitize_text_field( $_REQUEST['domain'] ?? '' ) );
		if ( ! $domain ) wp_send_json( array( 'success' => false, 'message' => 'Invalid domain.' ) );

		$ip      = gethostbyname( $domain );
		$rev_ip  = implode( '.', array_reverse( explode( '.', $ip ) ) );
		$dnsbl   = array( 'zen.spamhaus.org', 'b.barracudacentral.org', 'bl.spamcop.net', 'dnsbl.sorbs.net', 'spam.dnsbl.sorbs.net' );
		$results = array();
		$listed  = 0;

		foreach ( $dnsbl as $bl ) {
			$q     = $rev_ip . '.' . $bl;
			$found = ( @gethostbyname($q) !== $q );
			if ($found) $listed++;
			$results[] = array( 'list' => $bl, 'listed' => $found );
		}

		wp_send_json( array( 'success' => true, 'domain' => $domain, 'ip' => $ip, 'listed' => $listed, 'clean' => $listed === 0, 'results' => $results ) );
	}

	/* Keyword Suggestion (Google Suggest A-Z) */
	public static function tool_keyword_suggest() {
		self::auth();
		$keyword = sanitize_text_field( $_REQUEST['keyword'] ?? '' );
		if ( empty($keyword) ) wp_send_json( array( 'success' => false, 'message' => 'No keyword provided.' ) );

		$suggestions = array();
		$enc         = urlencode( $keyword );
		$body        = self::fetch_url( "https://suggestqueries.google.com/complete/search?client=firefox&q={$enc}" );
		if ( $body ) {
			$data = json_decode( $body, true );
			if ( ! empty($data[1]) ) $suggestions = array_merge( $suggestions, $data[1] );
		}

		foreach ( array_slice( str_split('abcdefghijklmnopqrstuvwxyz'), 0, 8 ) as $letter ) {
			$sugg_body = self::fetch_url( "https://suggestqueries.google.com/complete/search?client=firefox&q=" . urlencode($keyword.' '.$letter), 4 );
			if ( $sugg_body ) {
				$sd = json_decode( $sugg_body, true );
				if ( ! empty($sd[1]) ) $suggestions = array_merge( $suggestions, $sd[1] );
			}
		}

		$suggestions = array_slice( array_values( array_unique( $suggestions ) ), 0, 50 );
		sort( $suggestions );
		wp_send_json( array( 'success' => true, 'keyword' => $keyword, 'suggestions' => $suggestions, 'count' => count($suggestions) ) );
	}

	/* SSL Certificate Checker */
	public static function tool_ssl_check() {
		self::auth();
		$domain = self::clean_domain( sanitize_text_field( $_REQUEST['domain'] ?? '' ) );
		if ( ! $domain ) wp_send_json( array( 'success' => false, 'message' => 'Invalid domain.' ) );

		$ctx    = stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false ) ) );
		$socket = @stream_socket_client( "ssl://{$domain}:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx );

		if ( ! $socket ) wp_send_json( array( 'success' => false, 'domain' => $domain, 'ssl' => false, 'message' => $errstr ) );

		$cert_info = stream_context_get_params( $socket );
		$cert      = $cert_info['options']['ssl']['peer_certificate'] ?? null;
		fclose( $socket );

		if ( $cert ) {
			$cd        = openssl_x509_parse( $cert );
			$expires   = $cd['validTo_time_t'] ?? 0;
			$days_left = $expires ? intval( ($expires - time()) / 86400 ) : 'N/A';
			wp_send_json( array(
				'success'    => true, 'domain' => $domain, 'ssl' => true,
				'subject'    => $cd['subject']['CN'] ?? $domain,
				'issuer'     => $cd['issuer']['O'] ?? 'Unknown',
				'valid_from' => date('Y-m-d', $cd['validFrom_time_t'] ?? 0),
				'valid_to'   => date('Y-m-d', $expires),
				'days_left'  => $days_left,
				'expired'    => is_int($days_left) && $days_left < 0,
				'expiring'   => is_int($days_left) && $days_left < 30,
			) );
		}

		wp_send_json( array( 'success' => false, 'domain' => $domain, 'ssl' => false, 'message' => 'Could not parse certificate.' ) );
	}

	/* Page Size Checker */
	public static function tool_page_size() {
		self::auth();
		$url = sanitize_text_field( $_REQUEST['url'] ?? '' );
		if ( ! preg_match( '/^https?:\/\//i', $url ) ) $url = 'http://' . ltrim( $url, '/' );
		$resp = wp_remote_get( $url, array( 'timeout' => 10, 'sslverify' => false ) );
		if ( is_wp_error($resp) ) wp_send_json( array( 'success' => false, 'message' => 'Could not reach URL.' ) );

		$body    = wp_remote_retrieve_body( $resp );
		$size_kb = round( strlen($body) / 1024, 2 );
		wp_send_json( array(
			'success' => true, 'url' => $url,
			'http_code' => wp_remote_retrieve_response_code($resp),
			'size_kb'   => $size_kb,
			'size_mb'   => round( strlen($body) / 1048576, 3 ),
			'status'    => $size_kb > 1000 ? 'Critical' : ($size_kb > 500 ? 'Large' : 'Good'),
		) );
	}
}

ASF_SwissTools::init();
