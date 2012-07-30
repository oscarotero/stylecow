<?php
/**
 * Stylecow PHP library
 *
 * IeClip plugin
 * Fix the comma-separated property clip in Ie 6-7
 *
 * Examples:
 * clip: 1, 1;
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

class IeClip {
	const POSITION = 5;

	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			if (($property = $code->getLastProperty('clip'))) {
				$value = str_replace(',', ' ', str_replace(' ', '', $property->value));

				if (!$code->hasProperty(array('clip', '*clip'), $value)) {
					$code->addProperty(new Property('*clip', $value))->vendor = 'ms';
				}
			}
		});
	}
}