<?php
include('../stylecow/stylecow.php');

$styleCow = new stylecow\Stylecow;

$styleCow->load($_GET['styles']);

$styleCow->transform(array(
	'vendor_prefixes',
	'ie_filters',
	'grid',
	'matches',
	'nested_rules'
));

$styleCow->show();
?>