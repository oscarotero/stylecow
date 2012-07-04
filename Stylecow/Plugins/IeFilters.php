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
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Css;
use Stylecow\Property;
use Stylecow\Plugins\Color;

class IeFilters {
	const POSITION = 5;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			$filter = array('opacity', 'transform', 'background', 'background-image');

			foreach ($code->getProperties($filter) as $property) {
				switch ($property->name) {
					case 'opacity':
						IeFilters::addFilter($property, IeFilters::getOpacityFilter($property->value));
						break;

					case 'transform':
						$property->executeFunction(null, function ($params, $name, $property) {
							switch ($name) {
								case 'rotate':
									IeFilters::addFilter($property, IeFilters::getRotateFilter($params));
									break;
									
								case 'scaleX':
									if ($params[0] == '-1') {
										IeFilters::addFilter($property, 'flipH');
									}
									break;
								
								case 'scaleY':
									if ($params[0] == '-1') {
										IeFilters::addFilter($property, 'flipV');
									}
									break;

								case 'scale':
									if ($params[0] == '-1' && $params[1] == '-1') {
										IeFilters::addFilter($property, 'flipH');
										IeFilters::addFilter($property, 'flipV');
									}
									break;
								}
						});

						break;

					case 'background':
					case 'background-image':
						$property->executeFunction('rgba', function ($params, $name, $property) {
							IeFilters::addFilter($property, IeFilters::getRGBAFilter($params));
						});

						$property->executeFunction('hsla', function ($params, $name, $property) {
							IeFilters::addFilter($property, IeFilters::getRGBAFilter(Color::HSLA_RGBA($params)));
						});

						$property->executeFunction('linear-gradient', function ($params, $name, $property) {
							IeFilters::addFilter($property, IeFilters::getLinearGradientFilter($params));
						});
						break;
				}
			}
		});
	}


	/**
	 * Add an ie filter to the parsed code
	 *
	 * @param array   &$array_code  The piece of the parsed code
	 * @param string  $params       The ie filter code to insert
	 */
	static public function addFilter ($property, $filter) {
		if (($filterProperty = $property->parent->getProperties('filter'))) {
			$filterProperty[0]->addValue($filter);
		} else {
			$property->parent->addProperty(new Property('filter', $filter));
		}
	}


	
	/**
	 * Generate the Ie filter to emulate the opacity of an element
	 *
	 * @param array  $params  The opacity parameter
	 */
	static public function getOpacityFilter ($opacity) {
		return 'alpha(opacity='.($opacity * 100).')';
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


	/**
	 * Generate the Ie filter to emulate the rotation of an element: background: linear-gradient(top, #333, #999);
	 *
	 * @param array  &$code   The piece of the parsed code
	 * @param array  $params  The linear gradient parameters
	 */
	static public function getLinearGradientFilter ($params) {
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

		if ($direction && count($colors) === 2) {
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


	/**
	 * Generate the Ie filter to emulate the background rgba color of an element: background: rgba(0, 0, 0, 0.5);
	 *
	 * @param array  $params  The rgba parameters
	 */
	static public function getRGBAFilter ($params) {
		$color = '#'.dechex(round(255*floatval($params[3]))).Color::RGBA_HEX($params);

		return 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$color.'\', endColorStr=\''.$color.'\')';
	}
}