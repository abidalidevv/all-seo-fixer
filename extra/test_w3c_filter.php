<?php
$raw_messages = array(
	array('type' => 'error', 'message' => 'CSS: “color”: Parse Error.', 'extract' => 'r *{color:!important}.head', 'lastLine' => 150, 'lastColumn' => 25847),
	array('type' => 'error', 'message' => 'CSS: “color”: Parse Error.', 'extract' => 'r *{color:!important}.head', 'lastLine' => 150, 'lastColumn' => 26300),
	array('type' => 'error', 'message' => 'CSS: “background-color”: Parse Error.', 'extract' => 'ackground-color:}.archive .pag', 'lastLine' => 150, 'lastColumn' => 30658),
	array('type' => 'error', 'message' => 'CSS: “background-color”: Parse Error.', 'extract' => 'ackground-color:}.single-post', 'lastLine' => 150, 'lastColumn' => 31609),
	array('type' => 'error', 'message' => '“script” element between “head” and “body”.', 'extract' => '</head> <script> docum', 'lastLine' => 282, 'lastColumn' => 9),
	array('type' => 'error', 'message' => 'Cannot recover after last error. Any further errors will be ignored.', 'extract' => '</head> <script> docum', 'lastLine' => 282, 'lastColumn' => 9),
	array('type' => 'info', 'subType' => 'warning', 'message' => 'The “type” attribute is unnecessary for JavaScript resources.', 'extract' => 'Layer --> <script type="text/javascript"> (func', 'lastLine' => 11, 'lastColumn' => 31),
	array('type' => 'info', 'subType' => 'warning', 'message' => 'The “type” attribute is unnecessary for JavaScript resources.', 'extract' => 'nitor --> <script type="text/javascript"> (func', 'lastLine' => 21, 'lastColumn' => 31),
);

$errors = array();
$css_notices = array();
$warnings = array();

foreach ($raw_messages as $msg) {
	$type = $msg['type'];
	$msg_text = $msg['message'];

	// 1. Skip benign WordPress quirks
	if (
		strpos( $msg_text, 'The “type” attribute is unnecessary for JavaScript resources' ) !== false ||
		strpos( $msg_text, 'The “type” attribute is unnecessary for style resources' ) !== false ||
		strpos( $msg_text, 'Cannot recover after last error' ) !== false ||
		strpos( $msg_text, 'Trailing slash on void element' ) !== false ||
		strpos( $msg_text, 'executable code' ) !== false
	) {
		continue;
	}

	// 2. Separate CSS parse errors from structural HTML errors
	if ( strpos( $msg_text, 'CSS:' ) !== false || strpos( $msg_text, 'Parse Error' ) !== false ) {
		$key = $msg_text;
		if ( ! isset( $css_notices[ $key ] ) ) {
			$css_notices[ $key ] = array(
				'message' => $msg_text,
				'count'   => 1,
				'extract' => $msg['extract'],
				'guide'   => 'Empty color/background setting in Theme Customizer (Appearance → Customize → Colors / Layout). Harmless to layout; will not break your site.'
			);
		} else {
			$css_notices[ $key ]['count']++;
		}
		continue;
	}

	// 3. True structural HTML issues
	$guide = 'Check recent template or widget changes.';
	if ( strpos( $msg_text, 'between “head” and “body”' ) !== false ) {
		$guide = 'Tracking script placed between </head> and <body>. In WPCode or header.php, move the script inside <head> or inside <body> to prevent HTML validation warning.';
	}

	$item = array(
		'message' => $msg_text,
		'line'    => $msg['lastLine'],
		'col'     => $msg['lastColumn'],
		'extract' => $msg['extract'],
		'guide'   => $guide,
	);

	if ( $type === 'error' ) {
		$errors[] = $item;
	} else {
		$warnings[] = $item;
	}
}

echo "CRITICAL HTML ERRORS: " . count($errors) . "\n";
print_r($errors);
echo "\nCSS THEME NOTICES: " . count($css_notices) . "\n";
print_r($css_notices);
echo "\nWARNINGS: " . count($warnings) . "\n";
print_r($warnings);
