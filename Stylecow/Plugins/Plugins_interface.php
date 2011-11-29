<?php
/**
* Plugins interface class (version 0.1)
* for styleCow PHP library
*
* 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*/

namespace Stylecow;

interface Plugins_interface {
	public function __construct (Stylecow $Css);
	public function transform ();
}
?>