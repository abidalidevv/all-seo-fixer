<?php
$samples = array(
	"Uncaught ReferenceError: devicemaster_ajax_var is not defined at https://efix.ae/wp-includes/js/jquery/jquery.min.js?ver=3.7.1:2",
	"ReferenceError: Can't find variable: M at https://efix.ae/wp-content/plugins/revslider/public/js/sr7.js?ver=6.7.18:27",
	"ReferenceError: Can't find variable: devicemaster_ajax_var at https://efix.ae/wp-includes/js/jquery/jquery.min.js?ver=3.7.1:2",
	"TypeError: $ is not a function at https://efix.ae/wp-content/themes/mytheme/script.js:10",
	"Fatal error: Allowed memory size of 134217728 bytes exhausted",
);

foreach ($samples as $error_text) {
	echo "----------------------------------------\n";
	echo "TESTING: $error_text\n";
	$var_name = '';
	if ( preg_match( '/(?:ReferenceError:\s*(?:Can[\'\\\]*t find variable:\s*|Uncaught ReferenceError:\s*)?|Can[\'\\\]*t find variable:\s*)([a-zA-Z0-9_\$]+)/i', $error_text, $m ) ) {
		$var_name = trim( $m[1] );
	} elseif ( preg_match( '/([a-zA-Z0-9_\$]+)\s+is not defined/i', $error_text, $m ) ) {
		$var_name = trim( $m[1] );
	}
	echo "DETECTED VAR: '$var_name'\n";
}
