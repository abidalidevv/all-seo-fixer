<?php
// Mock test for media scan logic
$filename = 'samsung-galaxy-s21-screen-repair-dubai.jpg';
$clean_alt = preg_replace( '/[_\-\s]+/', ' ', pathinfo($filename, PATHINFO_FILENAME) );
$clean_alt = ucwords( trim( $clean_alt ) );
echo "Clean Alt: $clean_alt\n";

$num_file = '89274982374.png';
$clean_num = preg_replace( '/[_\-\s]+/', ' ', pathinfo($num_file, PATHINFO_FILENAME) );
echo "Numeric: " . (preg_match('/^[0-9_\-\s]+$/', $clean_num) ? 'YES' : 'NO') . "\n";
