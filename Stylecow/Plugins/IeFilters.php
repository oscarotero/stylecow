<?php
/**
 * Stylecow PHP library
 *
 * Ie_filters plugin
 * Generate ie filters code to emulate some css3 properties no supported
 *
 * Examples:
 * opacity: 0.2;
 * background: linear-gradient(top, red, black);
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1.3 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class IeFilters extends Plugin implements PluginsInterface {
	static protected $position = 5;


	/**
	 * Search for properties than can be emulated in Ie and insert the new code
	 *
	 * @param array $array_code The piece of the parsed css code
	 *
	 * @return array The transformed code
	 */
	public function transform (array $array_code) {
		return Stylecow::propertiesWalk($array_code, function ($properties) {
			foreach ($properties as $property) {
				switch ($property['name']) {
					case 'opacity':
						$this->addFilter($properties, 'alpha(opacity='.($property['value'][0] * 100).')');
						break;
					
					case 'transform':
						Stylecow::executeFunctions($property['value'][0], null, function ($params, $name) use (&$properties) {
							switch ($name) {
								case 'rotate':
									$this->addFilter($properties, $this->getRotateFilter($params));
									break;
								
								case 'scaleX':
									if ($params[0] == '-1') {
										$this->addFilter($properties, 'flipH');
									}
									break;
								
								case 'scaleY':
									if ($params[0] == '-1') {
										$this->addFilter($properties, 'flipV');
									}
									break;

								case 'scale':
									if ($params[0] == '-1' && $params[1] == '-1') {
										$this->addFilter($properties, 'flipH');
										$this->addFilter($properties, 'flipV');
									}
									break;
							}
						});
						break;

					case 'background':
					case 'background-image':
						Stylecow::executeFunctions($property['value'][0], 'rgba', function ($params) use (&$properties) {
							$this->addFilter($properties, $this->getRGBAFilter($params));
						});

						Stylecow::executeFunctions($property['value'][0], 'linear-gradient', function ($params) use (&$properties) {
							$this->addFilter($properties, $this->getLinearGradientFilter($params));
						});

						break;
				}
			}

			return $properties;
		});
	}


	/**
	 * Generate the Ie filter to emulate the rotation of an element: tranform: rotate(4deg);
	 *
	 * @param array  &$code   The piece of the parsed code
	 * @param array  $params  The rotation parameters
	 */
	private function getRotateFilter ($params) {
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


	/**
	 * Generate the Ie filter to emulate the rotation of an element: background: linear-gradient(top, #333, #999);
	 *
	 * @param array  &$code   The piece of the parsed code
	 * @param array  $params  The linear gradient parameters
	 */
	private function getLinearGradientFilter ($params) {
		$point = 'top';

		if (preg_match('/(top|bottom|left|right|deg)/', $params[0])) {
			$point = array_shift($params);
		}

		switch ($point) {
			case 'top':
			case '90deg':
				$direction = 'vertical';
				$reverse = false;
				break;

			case 'bottom':
			case '-90deg':
				$direction = 'vertical';
				$reverse = true;
				break;

			case 'left':
			case '180deg':
			case '-180deg':
				$direction = 'horizontal';
				$reverse = false;
				break;

			case 'right':
			case '0deg':
			case '360deg':
				$direction = 'vertical';
				$reverse = true;
				break;
		}

		$colors = $params;

		if ($direction && count($colors) == 2 && $colors[0][0] == '#' && $colors[1][0] == '#') {
			if (strlen($colors[0]) == 4) {
				$colors[0] = $colors[0][0].$colors[0][1].$colors[0][1].$colors[0][2].$colors[0][2].$colors[0][3].$colors[0][3];
			}
			if (strlen($colors[1]) == 4) {
				$colors[1] = $colors[1][0].$colors[1][1].$colors[1][1].$colors[1][2].$colors[1][2].$colors[1][3].$colors[1][3];
			}

			if ($reverse) {
				$colors = array_reverse($colors);
			}

			if ($direction == 'horizontal') {
				return 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$colors[0].'\', endColorStr=\''.$colors[1].'\', GradientType=1)';
			} else {
				return 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$colors[0].'\', endColorStr=\''.$colors[1].'\')';
			}
		}
	}


	/**
	 * Generate the Ie filter to emulate the background rgba color of an element: background: rgba(0, 0, 0, 0.5);
	 *
	 * @param array  &$code   The piece of the parsed code
	 * @param array  $params  The rgba parameters
	 */
	private function getRGBAFilter ($params) {
		$r = dechex($params[0]);
		$g = dechex($params[1]);
		$b = dechex($params[2]);

		if (strlen($r) == 1) {
			$r = str_repeat($r, 2);
		}
		if (strlen($g) == 1) {
			$g = str_repeat($g, 2);
		}
		if (strlen($b) == 1) {
			$b = str_repeat($b, 2);
		}

		$a = dechex(round(255*floatval($params[3])));

		$color = '#'.$a.$r.$g.$b;

		return 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$color.'\', endColorStr=\''.$color.'\')';
	}


	
	/**
	 * Add an ie filter to the parsed code
	 *
	 * @param array   &$array_code  The piece of the parsed code
	 * @param string  $params       The ie filter code to insert
	 */
	private function addFilter (&$properties, $filter) {
		Stylecow::addProperty($properties, 'filter', $filter, Stylecow::PROPERTY_APPEND, 'ms');
	}
}