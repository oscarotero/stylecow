<?php
/**
 * Stylecow PHP library
 *
 * IeTransform plugin
 * Generate ie specific code to emulate some css 2d transform properties
 *
 * Examples:
 * transform: rotate(3d);
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

class IeTransform {
	const POSITION = 5;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			if (($property = $code->getLastProperty('transform'))) {
				$property->executeFunction(null, function ($params, $name, $property) {
					switch ($name) {
						case 'rotate':
							$filter = IeTransform::getRotateFilter($params);
							break;

						case 'scaleX':
							if ($params[0] == '-1') {
								$filter = 'flipH';
							} else {
								return null;
							}
							break;

						case 'scaleY':
							if ($params[0] == '-1') {
								$filter = 'flipV';
							} else {
								return null;
							}
							break;

						case 'scale':
							if ($params[0] == '-1' && $params[1] == '-1') {
								$filter = array('flipH', 'flipV');
							} else {
								return null;
							}
							break;

						default:
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
	 * Generate the Ie filter to emulate the rotation of an element: tranform: rotate(4deg);
	 *
	 * @param array  &$code   The piece of the parsed code
	 * @param array  $params  The rotation parameters
	 */
	static public function getRotateFilter ($params) {
		$value = intval($params[0]);

		if ($value < 0) {
			$value += 360;
		}

		switch ($value) {
			case 90:
				return 'progid:DXImageTransform.Microsoft.BasicImage(rotation=1)';

			case 180:
				return 'progid:DXImageTransform.Microsoft.BasicImage(rotation=2)';

			case 270:
				return 'progid:DXImageTransform.Microsoft.BasicImage(rotation=3)';

			case 360:
				return 'progid:DXImageTransform.Microsoft.BasicImage(rotation=4)';

			default:
				$rad = ($value * pi() * 2) / 360;
				$cos = cos($rad);
				$sin = sin($rad);

				return 'progid:DXImageTransform.Microsoft.Matrix(sizingMethod="auto expand", M11 = '.$cos.', M12 = '.(-$sin).', M21 = '.$sin.', M22 = '.$cos.')';
		}
	}
}