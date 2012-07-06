<?php
error_reporting(E_ALL & ~E_NOTICE);

//Use a loader PSR-0 compatible

include('Loader.php');

Loader::setLibrariesPath(dirname(__DIR__));
Loader::register();


//Initialize stylecow

$css = Stylecow\Parser::parseFile(__DIR__.'/'.$_GET['styles']);

//Load the css file

$css->applyPlugins(array(
	'Color',
	'Grid',
	'IeFilters',
	'Matches',
	'Math',
	'NestedRules',
	'Rem',
	'Variables',
	'VendorPrefixes'
));

/*
Stylecow\Plugins\Color::apply($css);
Stylecow\Plugins\Grid::apply($css);
Stylecow\Plugins\IeFilters::apply($css);
Stylecow\Plugins\Matches::apply($css);
Stylecow\Plugins\Math::apply($css);
Stylecow\Plugins\NestedRules::apply($css);
Stylecow\Plugins\Rem::apply($css);
Stylecow\Plugins\Variables::apply($css);
Stylecow\Plugins\VendorPrefixes::apply($css);
*/

echo '<pre>';
//print_r($css->getParsedCode());
//print_r($css->toArray());
print_r($css->toString());
echo '</pre>';
?>