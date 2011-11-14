<?php
include('../stylecow/stylecow.php');

$styleCow = new stylecow\Stylecow;

$styleCow->load($_GET['styles']);

$styleCow->transform(array(
	'vendor_prefixes',
	'variables',
	'ie_filters',
	'grid',
	'matches',
	'nested_rules',
	//'animate'
));

$styleCow->show();
?>