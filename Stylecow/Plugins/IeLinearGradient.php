<?php
/**
 * Stylecow PHP library
 *
 * IeLinearGradient plugin
 * Generate ie specific code to emulate background linear-gradient
 *
 * Examples:
 * background: linear-gradient(top, red, black);
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
use Stylecow\Plugins\Color;

class IeLinearGradient {
	const POSITION = 5;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			if (($property = $code->getLastProperty(array('background', 'background-image')))) {

				$property->executeFunction('linear-gradient', function ($params, $name, $property) {
					if (($filter = IeLinearGradient::getLinearGradientFilter($params)) === null) {
						return null;
					}

					if (($filterProperty = $property->parent->getLastProperty('filter'))) {
						$filterProperty->addValue($filter);
						$filterProperty->vendor = 'ms';
					} else {
						$property->parent->addProperty(new Property('filter', $filter))->vendor = 'ms';
					}
				});
			}
		});
	}



	/**
	 * Generate the Ie filter to emulate the rotation of an element: background: linear-gradient(top, #333, #999);
	 *
	 * @param array  &$code   The piece of the parsed code
	 * @param array  $params  The linear gradient parameters
	 */
	static public function getLinearGradientFilter ($params) {
		$direction = null;
		$point = array_shift($params);

		switch ($point) {
			case 'to bottom':
			case '90deg':
				$direction = 'vertical';
				$reverse = false;
				break;

			case 'to top':
			case '-90deg':
				$direction = 'vertical';
				$reverse = true;
				break;

			case 'to right':
			case '180deg':
			case '-180deg':
				$direction = 'horizontal';
				$reverse = false;
				break;

			case 'to left':
			case '0deg':
			case '360deg':
				$direction = 'vertical';
				$reverse = true;
				break;

			default:
				return null;
		}

		$colors = $params;

		if (isset($direction) && count($colors) === 2) {
			$colors[0] = Color::RGBA_HEX(Color::toRGBA($colors[0]));
			$colors[1] = Color::RGBA_HEX(Color::toRGBA($colors[1]));

			if ($reverse) {
				$colors = array_reverse($colors);
			}

			if ($direction === 'horizontal') {
				return 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\'#'.$colors[0].'\', endColorStr=\'#'.$colors[1].'\', GradientType=1)';
			} else {
				return 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\'#'.$colors[0].'\', endColorStr=\'#'.$colors[1].'\')';
			}
		}
	}
}
