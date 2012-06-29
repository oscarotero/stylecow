<?php
error_reporting(E_ALL & ~E_NOTICE);

include('../Stylecow/Stylecow.php');
include('../Stylecow/Plugins/Color.php');

$styleCow = new Stylecow\Stylecow;
$colorPlugin = new Stylecow\Plugins\Color();

$styleCow->load(__DIR__.'/'.$_GET['styles']);

$new_code = $colorPlugin->_transform($styleCow->getParsedCode());

print_r($new_code);

die();

$styleCow->transform(array(
	'Vendor_prefixes',
	'Variables',
	'Ie_filters',
	'Grid',
	'Matches',
	'Nested_rules',
	'Animate',
	'Color',
	'Rem',
	'Math'
));

$styleCow->show();
?>