<?php
/**
 * Stylecow PHP library
 *
 * IeMinHeight plugin
 * Generate ie specific code to emulate min-height css property in IE 6
 *
 * Examples:
 * min-height: 200px;
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

class IeMinHeight {
	const POSITION = 5;

	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code, $settings) {
			if (($property = $code->getLastProperty('min-height')) && !$code->hasProperty(array('_height', '*height', 'height'))) {
				$code->addProperty(new Property('_height', $property->value))->vendor = 'ms';
			}
		});
	}
}