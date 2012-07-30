<?php
/**
 * Stylecow PHP library
 *
 * IeBackgroundAlpha plugin
 * Generate Internet Explorer specific code to emulate rgba/hsla colors as background
 *
 * Examples:
 * background: rgba(0, 0, 0, 0.5);
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

class IeBackgroundAlpha {
	const POSITION = 5;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code, $settings) {
			if (($property = $code->getLastProperty(array('background', 'background-color')))) {
				$property->executeFunction('rgba', function ($params, $name, $property) {
					$filter = IeBackgroundAlpha::getRGBAFilter($params);

					if (($filterProperty = $property->parent->getLastProperty('filter'))) {
						$filterProperty->addValue($filter);
						$filterProperty->vendor = 'ms';
					} else {
						$property->parent->addProperty(new Property('filter', $filter))->vendor = 'ms';
					}
				});

				$property->executeFunction('hsla', function ($params, $name, $property) {
					$filter = IeBackgroundAlpha::getHSLAFilter($params);

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
	 * Generate the Ie filter to emulate the background rgba color of an element: background: rgba(0, 0, 0, 0.5);
	 *
	 * @param array  $params  The rgba parameters
	 */
	static public function getRGBAFilter ($params) {
		$color = '#'.dechex(round(255*floatval($params[3]))).Color::RGBA_HEX($params);

		return 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$color.'\', endColorStr=\''.$color.'\')';
	}


	/**
	 * Generate the Ie filter to emulate the background hsla color of an element: background: hsla(0, 0, 0, 0.5);
	 *
	 * @param array  $params  The rgba parameters
	 */
	static public function getHSLAFilter ($params) {
		return self::getRGBAFilter(Color::HSLA_RGBA($params));
	}
}