<?php
/**
 * Stylecow PHP library
 *
 * IeFloat plugin
 * Fixes the double margin bug in floated elements in IE6
 *
 * Examples:
 * float: left;
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Css;
use Stylecow\Property;

class IeFloat {
	const POSITION = 5;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			if (($property = $code->getLastProperty('float', array('right', 'left'))) && !$code->hasProperty(array('display', '_display', '*display'), 'inline')) {
				$code->addProperty(new Property('_display', 'inline'))->vendor = 'ms';
			}
		});
	}
}