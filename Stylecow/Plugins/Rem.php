<?php
/**
 * Stylecow PHP library
 *
 * Rem plugin
 * Allows use rem values for old browsers (ie8 and lower)
 *
 * Example:
 * font-size: 2rem;
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

class Rem {
	const POSITION = 4;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$rem = 16;

		foreach ($css->getChildren(array(':root', 'html', 'body')) as $child) {
			foreach ($child->getProperties('font-size') as $property) {
				$rem = Rem::rootPixels($property->value);
			}
		}

		$css->executeRecursive(function ($code) use ($rem) {
			foreach ($code->getProperties() as $property) {
				if (strpos($property->value, 'rem') === false) {
					continue;
				}

				$value = preg_replace_callback('/([0-9\.]+)rem/', function ($matches) use ($rem) {
					return Rem::toPixels($matches[1], $rem);
				}, $property->value);

				if ($property->value !== $value) {
					$code->addProperty(new Property($property->name, $value), $property->getPositionInParent());
				}
			}
		});
	}


	static public function rootPixels ($value) {
		if ($value[0] === '.') {
			$value = '0'.$value;
		}

		if (strpos($value, 'px') !== false) {
			return intval($value);
		}

		if (strpos($value, 'em') !== false) {
			return floatval($value) * 16;
		}

		if (strpos($value, 'pt') !== false) {
			return floatval($value) * 14;
		}

		return 16;
	}


	static public function toPixels ($value, $rootPixels) {
		if ($value[0] === '.') {
			$value = '0'.$value;
		}

		return ($rootPixels * $value).'px';
	}
}
