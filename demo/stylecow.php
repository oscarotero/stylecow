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

Stylecow\Plugins\Math::apply($Css);
Stylecow\Plugins\Color::apply($Css);
Stylecow\Plugins\IeFilters::apply($Css);
Stylecow\Plugins\Matches::apply($Css);
Stylecow\Plugins\NestedRules::apply($Css);
Stylecow\Plugins\Rem::apply($Css);
Stylecow\Plugins\Variables::apply($Css);


echo '<pre>';
//print_r($sc->getParsedCode());
print_r($sc->getParsedCode()->toString());
//print_r($sc->getParsedCode()->toArray());
echo '</pre>';
die();
//Show the result code
$sc->show();
?>