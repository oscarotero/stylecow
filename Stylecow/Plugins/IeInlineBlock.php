<?php
/**
 * Stylecow PHP library
 *
 * IeInlineBlock plugin
 * Generate ie specific code to emulate display inline-block in IE 6-7
 *
 * Examples:
 * display: inline-block;
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

class IeInlineBlock {
	const POSITION = 5;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			if (($property = $code->getLastProperty('display', 'inline-block'))) {
				if (!$code->hasProperty(array('zoom', '*zoom'))) {
					$code->addProperty(new Property('*zoom', 1))->vendor = 'ms';
				}
				if (!$code->hasProperty('*display')) {
					$code->addProperty(new Property('*display', 'inline'))->vendor = 'ms';
				}
			}
		});
	}
}