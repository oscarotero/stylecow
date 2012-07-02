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
/*
$sc->transform(array(
	//'Stylecow\\Plugins\\Color',
	//'Stylecow\\Plugins\\NestedRules',
	//'Stylecow\\Plugins\\Matches',
	//'Stylecow\\Plugins\\Math',
	//'Stylecow\\Plugins\\IeFilters',
	//'Stylecow\\Plugins\\Rem',
	//'Stylecow\\Plugins\\VendorPrefixes',
	//'Stylecow\\Plugins\\Variables',
	//'Stylecow\\Plugins\\Grid',
	//'Stylecow\\Plugins\\Snippets'
));
*/
$Css = $sc->getParsedCode();

$Math = new Stylecow\Plugins\Math();
$Math->transform($Css);

$Color = new Stylecow\Plugins\Color();
$Color->transform($Css);

$IeFilters = new Stylecow\Plugins\IeFilters();
$IeFilters->transform($Css);


echo '<pre>';
print_r($sc->getParsedCode());
echo '</pre>';
die();
//Show the result code
$sc->show();
?>