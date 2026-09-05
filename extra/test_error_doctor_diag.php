<?php
// Test diagnostic function
function diagnose_locally( $error_text, $error_type ) {
	$clean_err = stripslashes( $error_text );

	$var_name = '';
	if ( preg_match( '/(?:ReferenceError:\s*(?:Can[\'\\\]*t find variable:\s*|Uncaught ReferenceError:\s*)?|Can[\'\\\]*t find variable:\s*)([a-zA-Z0-9_\$]+)/i', $clean_err, $m ) ) {
		$var_name = trim( $m[1] );
	} elseif ( preg_match( '/([a-zA-Z0-9_\$]+)\s+is not defined/i', $clean_err, $m ) ) {
		$var_name = trim( $m[1] );
	}

	if ( $var_name === 'M' || stripos( $clean_err, 'revslider' ) !== false || stripos( $clean_err, 'sr7.js' ) !== false ) {
		return array(
			'title' => 'Slider Revolution (sr7.js) Script Conflict Fix',
			'var'   => 'M',
			'type'  => 'revslider'
		);
	}

	if ( ! empty( $var_name ) && ( stripos( $var_name, 'ajax' ) !== false || stripos( $var_name, 'var' ) !== false || stripos( $var_name, 'devicemaster' ) !== false ) ) {
		return array(
			'title' => "Missing Variable Fix: window.{$var_name}",
			'var'   => $var_name,
			'type'  => 'ajax_var'
		);
	}

	return false;
}

$tests = array(
	"Uncaught ReferenceError: devicemaster_ajax_var is not defined at https://efix.ae/wp-includes/js/jquery/jquery.min.js?ver=3.7.1:2",
	"ReferenceError: Can\'t find variable: M at https://efix.ae/wp-content/plugins/revslider/public/js/sr7.js?ver=6.7.18:27",
	"ReferenceError: Can\'t find variable: devicemaster_ajax_var at https://efix.ae/wp-includes/js/jquery/jquery.min.js?ver=3.7.1:2",
);

foreach ($tests as $t) {
	print_r(diagnose_locally($t, 'JS Error'));
}
