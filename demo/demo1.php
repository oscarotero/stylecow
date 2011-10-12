<?php

include('../stylecow/stylecow.php');

$styleCow = new stylecow\Stylecow;

$styleCow->load('styles1.css');

$styleCow->transform(array(
	'vendor_prefixes',
	'ie_filters',
));

$styleCow->show();
?>