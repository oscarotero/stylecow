<?php
error_reporting(E_ALL & ~E_NOTICE);

//Use a loader PSR-0 compatible

include('Loader.php');

Loader::setLibrariesPath(dirname(__DIR__));
Loader::register();


//Initialize stylecow

$sc = new Stylecow\Stylecow;

//Load the css file

$sc->load(__DIR__.'/'.$_GET['styles']);

//Execute the plugins

$sc->transform(array(
	//'Stylecow\\Plugins\\Color',
	//'Stylecow\\Plugins\\NestedRules',
	//'Stylecow\\Plugins\\Matches',
	//'Stylecow\\Plugins\\Math',
	//'Stylecow\\Plugins\\IeFilters',
	//'Stylecow\\Plugins\\Rem',
	'Stylecow\\Plugins\\VendorPrefixes',
	//'Stylecow\\Plugins\\Variables',
	//'Stylecow\\Plugins\\Grid',
	'Stylecow\\Plugins\\Snippets'
));

//Show the result code
$sc->show();
?>