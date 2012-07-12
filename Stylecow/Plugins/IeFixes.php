<?php
/**
 * Stylecow PHP library
 *
 * IeFixes plugin
 * Generate ie specific code to emulate some css properties no supported
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

class IeFixes {
	const POSITION = 5;

	static private $propertiesSupport = array(
		'opacity' => 9, //Support for opacity
		'transform' => 9, //Suport for some 2D transforms
		'background-alpha' => 9, //Support for background rgba/hsla colors
		'background-gradient' => 10, //Support for linear gradients
		'inline-block' => 8, //Support for display:inline-block
		'min-height' => 7, //Support for min-height property
		'float' => 7, //Fix the double-margin bug in floated elements
		'clip' => 8 //Fix the comma separated properties
	);


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css, array $settings = array('ie-min-version' => 8)) {
		$css->executeRecursive(function ($code, $settings) {
			foreach ($code->getProperties(array('opacity', 'transform', 'background', 'background-image', 'display', 'min-height', 'float', 'overflow', 'clip')) as $property) {
				switch ($property->name) {
					case 'clip':
						$value = str_replace(',', ' ', $property->value);

						if (!$code->hasProperty(array('clip', '*clip'), $value)) {
							$code->addProperty(new Property('*clip', $value));
						}
						break;

					case 'float':
						if (($property->value !== 'none') && IeFixes::mustFixed('float', $settings) && !$code->hasProperty(array('display', '_display', '*display'), 'inline')) {
							$code->addProperty(new Property('_display', 'inline'))->vendor = 'ms';
						}
						break;

					case 'display':
						if (($property->value === 'inline-block') && IeFixes::mustFixed('inline-block', $settings)) {
							if (!$code->hasProperty(array('zoom', '*zoom'))) {
								$code->addProperty(new Property('*zoom', 1))->vendor = 'ms';
							}
							if (!$code->hasProperty('*display')) {
								$code->addProperty(new Property('*display', 'inline'))->vendor = 'ms';
							}
						}
						break;

					case 'min-height':
						if (IeFixes::mustFixed('min-height', $settings)) {
							if (!$code->hasProperty(array('_height', '*height', 'height'))) {
								$code->addProperty(new Property('_height', $property->value))->vendor = 'ms';
							}
						}
						break;

					case 'opacity':
						if (IeFixes::mustFixed('opacity', $settings)) {
							IeFixes::addFilter($property, IeFixes::getOpacityFilter($property->value));
						}
						break;

					case 'transform':
						if (IeFixes::mustFixed('transform', $settings)) {
							$property->executeFunction(null, function ($params, $name, $property) {
								switch ($name) {
									case 'rotate':
										IeFixes::addFilter($property, IeFixes::getRotateFilter($params));
										break;

									case 'scaleX':
										if ($params[0] == '-1') {
											IeFixes::addFilter($property, 'flipH');
										}
										break;

									case 'scaleY':
										if ($params[0] == '-1') {
											IeFixes::addFilter($property, 'flipV');
										}
										break;

									case 'scale':
										if ($params[0] == '-1' && $params[1] == '-1') {
											IeFixes::addFilter($property, 'flipH');
											IeFixes::addFilter($property, 'flipV');
										}
										break;
									}
							});
						}
						break;

					case 'background':
					case 'background-image':
						if (IeFixes::mustFixed('background-alpha', $settings)) {
							$property->executeFunction('rgba', function ($params, $name, $property) {
								IeFixes::addFilter($property, IeFixes::getRGBAFilter($params));
							});

							$property->executeFunction('hsla', function ($params, $name, $property) {
								IeFixes::addFilter($property, IeFixes::getRGBAFilter(Color::HSLA_RGBA($params)));
							});
						}
						if (IeFixes::mustFixed('background-gradient', $settings)) {
							$property->executeFunction('linear-gradient', function ($params, $name, $property) {
								IeFixes::addFilter($property, IeFixes::getLinearGradientFilter($params));
							});
						}
						break;
				}
			}
		}, $settings);
	}


	/**
	 * Check if the bug must fixed
	 *
	 * @param string $property The property to fix
	 * @param array $settings The settings
	 */
	static public function mustFixed ($property, array $settings = null) {
		if (!isset($settings)) {
			return false;
		}

		if (isset($settings[$property])) {
			return $settings[$property];
		}

		if (isset($settings['ie-min-version']) && $settings['ie-min-version'] >= self::$propertiesSupport[$property]) {
			return false;
		}

		return true;
	}


	/**
	 * Add an ie filter to the parsed code
	 *
	 * @param Stylecow\Property $property The property element
	 * @param string $params The ie filter code to insert
	 */
	static public function addFilter (Property $property, $filter) {
		if (($filterProperty = $property->parent->getProperties('filter'))) {
			$filterProperty[0]->addValue($filter);
			$filterProperty[0]->vendor = 'ms';
		} else {
			$property->parent->addProperty(new Property('filter', $filter))->vendor = 'ms';
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
		$direction = null;

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