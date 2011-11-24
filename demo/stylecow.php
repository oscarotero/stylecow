<?php
include('../stylecow/stylecow.php');

$styleCow = new stylecow\Stylecow;

$styleCow->load($_GET['styles']);

$styleCow->transform(array(
	'Vendor_prefixes',
	'Variables',
	'Ie_filters',
	'Grid',
	'Matches',
	'Nested_rules',
	//'animate'
));

$styleCow->show();
?>