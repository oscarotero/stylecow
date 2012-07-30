<?php
/**
 * Stylecow PHP library
 *
 * IeOpacity plugin
 * Generate ie specific code to support opacity property
 *
 * Examples:
 * opacity: 0.2;
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

class IeOpacity {
	const POSITION = 5;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			if (($property = $code->getLastProperty('opacity'))) {
				$filter = 'alpha(opacity='.($property->value * 100).')';

				if (($filterProperty = $code->getLastProperty('filter'))) {
					$filterProperty->addValue($filter);
					$filterProperty->vendor = 'ms';
				} else {
					$code->addProperty(new Property('filter', $filter))->vendor = 'ms';
				}
			}
		});
	}
}