<?php
include('../Stylecow/Stylecow.php');

$styleCow = new Stylecow\Stylecow;

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