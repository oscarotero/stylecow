<?php
/**
 * Stylecow PHP library
 *
 * Vendor_prefixes plugin
 * Adds the vendor prefixes of all css properties, selectors and values in need.
 *
 * Example:
 * border-radius: 5px;
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Parser;
use Stylecow\Css;
use Stylecow\Property;

class VendorPrefixes {
	const POSITION = 3;

	static $properties = array(
		'animation' => array('moz', 'webkit', 'o', 'ms'),
		'animation-delay' => array('moz', 'webkit', 'o', 'ms'),
		'animation-direction' => array('moz', 'webkit', 'o', 'ms'),
		'animation-duration' => array('moz', 'webkit', 'o', 'ms'),
		'animation-fill-mode' => array('moz', 'webkit', 'o', 'ms'),
		'animation-iteration-count' => array('moz', 'webkit', 'o', 'ms'),
		'animation-name' => array('moz', 'webkit', 'o', 'ms'),
		'animation-play-state' => array('moz', 'webkit', 'o', 'ms'),
		'animation-timing-function' => array('moz', 'webkit', 'o', 'ms'),
		'appearance' => array('moz', 'webkit'),
		'backface-visibility' => array('moz', 'webkit', 'o', 'ms'),
		'background-clip' => array('moz', 'webkit'),
		'background-origin' => array('moz', 'webkit'),
		'background-size' => array('moz', 'webkit', 'o'),
		'border-after' => array('webkit'),
		'border-after-color' => array('webkit'),
		'border-after-style' => array('webkit'),
		'border-after-width' => array('webkit'),
		'border-before' => array('webkit'),
		'border-before-color' => array('webkit'),
		'border-before-style' => array('webkit'),
		'border-before-width' => array('webkit'),
		'border-bottom-image' => array('moz', 'webkit', 'o'),
		'border-bottom-left-image' => array('moz', 'webkit', 'o'),
		'border-bottom-left-radius' => array('webkit'),
		'border-bottom-right-image' => array('moz', 'webkit', 'o'),
		'border-bottom-right-radius' => array('webkit'),
		'border-corner-image' => array('moz', 'webkit', 'o'),
		'border-image' => array('moz', 'webkit', 'o'),
		'border-left-image' => array('moz', 'webkit', 'o'),
		'border-top-image' => array('moz', 'webkit', 'o'),
		'border-top-left-image' => array('moz', 'webkit', 'o'),
		'border-top-left-radius' => array('webkit'),
		'border-top-right-image' => array('moz', 'webkit', 'o'),
		'border-top-right-radius' => array('webkit'),
		'border-radius' => array('moz', 'webkit', 'o'),
		'border-right-image' => array('moz', 'webkit', 'o'),
		'box-align' => array('moz', 'webkit', 'ms'),
		'box-direction' => array('moz', 'webkit', 'ms'),
		'box-flex' => array('moz', 'webkit', 'ms'),
		'box-flex-group' => array('moz', 'webkit', 'ms'),
		'box-lines' => array('moz', 'webkit', 'ms'),
		'box-ordinal-group' => array('moz', 'webkit', 'ms'),
		'box-orient' => array('moz', 'webkit', 'ms'),
		'box-pack' => array('moz', 'webkit', 'ms'),
		'box-shadow' => array('moz', 'webkit', 'o'),
		'box-sizing' => array('moz', 'webkit'),
		'column-count' => array('moz', 'webkit'),
		'column-gap' => array('moz', 'webkit'),
		'column-rule' => array('moz', 'webkit'),
		'column-rule-color' => array('moz', 'webkit'),
		'column-rule-style' => array('moz', 'webkit'),
		'column-rule-width' => array('moz', 'webkit'),
		'column-span' => array('moz', 'webkit'),
		'column-width' => array('moz', 'webkit'),
		'columns' => array('moz', 'webkit'),
		'filter' => array('ms'),
		'grid-column' => array('ms'),
		'grid-column-align' => array('ms'),
		'grid-column-span' => array('ms'),
		'grid-columns' => array('ms'),
		'grid-layer' => array('ms'),
		'grid-row' => array('ms'),
		'grid-row-align' => array('ms'),
		'grid-row-span' => array('ms'),
		'grid-rows' => array('ms'),
		'hyphens' => array('moz', 'epub', 'webkit', 'ms'),
		'opacity' => array('moz', 'webkit'),
		'text-overflow' => array('o'),
		'text-size-adjust' => array('moz', 'webkit', 'ms'),
		'transform' => array('moz', 'webkit', 'o', 'ms'),
		'transform-origin' => array('moz', 'webkit', 'o', 'ms'),
		'transition' => array('moz', 'webkit', 'o'),
		'transition-delay' => array('moz', 'webkit', 'o'),
		'transition-duration' => array('moz', 'webkit', 'o'),
		'transition-property' => array('moz', 'webkit', 'o'),
		'transition-timing-function' => array('moz', 'webkit', 'o'),
		'user-select' => array('moz', 'webkit')
	);

	static $non_standard_properties = array(
		'border-top-left-radius' => array(
			'moz' => '-moz-border-radius-topleft'
		),
		'border-top-right-radius' => array(
			'moz' => '-moz-border-radius-topright'
		),
		'border-bottom-left-radius' => array(
			'moz' => '-moz-border-radius-bottomleft'
		),
		'border-bottom-right-radius' => array(
			'moz' => '-moz-border-radius-bottomright'
		)
	);

	static $values = array(
		'display' => array(
			'box' => array('moz', 'webkit'),
			'inline-block' => array('moz')
		),
		'background' => array(
			'linear-gradient' => array('moz', 'webkit', 'o')
		),
		'background-image' => array(
			'linear-gradient' => array('moz', 'webkit', 'o')
		)
	);

	static $non_standard_values = array(
		'background' => array(
			'linear-gradient' => array('webkit' => 'webkitLinearGradient')
		),
		'background-image' => array(
			'linear-gradient' => array('webkit' => 'webkitLinearGradient')
		)
	);

	static $selectors = array(
		'::selection' => array('moz' => '::-moz-selection'),
		'::input-placeholder' => array('moz' => ':-moz-placeholder', 'webkit' => '::-webkit-input-placeholder', 'ms' => '::-ms-input-placeholder')
	);

	static $types = array(
		'@keyframes' => array(
			'moz' => '@-moz-keyframes',
			'webkit' => '@-webkit-keyframes',
			'ms' => '@-ms-keyframes',
			'o' => '@-o-keyframes'
		),
		'@document' => array(
			'moz' => '@-moz-document',
		)
	);


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {

		//Properties names
		$css->executeRecursive(function ($code) {
			foreach ($code->getProperties() as $property) {
				if (isset(VendorPrefixes::$properties[$property->name])) {
					foreach (VendorPrefixes::$properties[$property->name] as $vendor) {
						$name = '-'.$vendor.'-'.$property->name;

						if (!$code->hasProperty($name)) {
							$newProperty = clone $property;
							$newProperty->name = $name;
							$newProperty->vendor = $vendor;

							$code->addProperty($newProperty, $property->getPositionInParent());
						}
					}
				}

				if (isset(VendorPrefixes::$non_standard_properties[$property->name])) {
					foreach (VendorPrefixes::$non_standard_properties[$property->name] as $vendor => $name) {
						if (!$code->hasProperty($name)) {
							$newProperty = clone $property;
							$newProperty->name = $name;
							$newProperty->vendor = $vendor;

							$code->addProperty($newProperty, $property->getPositionInParent());
						}
					}
				}
			}
		});

		//Properties values
		$css->executeRecursive(function ($code) {
			foreach ($code->getProperties() as $property) {
				if (isset(VendorPrefixes::$values[$property->name])) {
					foreach (VendorPrefixes::$values[$property->name] as $value => $vendors) {
						if (strpos($property->value, $value) !== false) {
							foreach ($vendors as $vendor) {
								$newValue = preg_replace('/(^|[^\w-])('.preg_quote($value, '/').')([^\w]|$)/', '\\1-'.$vendor.'-'.$value.'\\3', $property->value);

								if (!$code->hasProperty($property->name, $newValue)) {
									$newProperty = clone $property;
									$newProperty->value = $newValue;
									$newProperty->vendor = $vendor;

									$code->addProperty($newProperty, $property->getPositionInParent());
								}
							}
						}
					}
				}

				if (isset(VendorPrefixes::$non_standard_values[$property->name])) {
					foreach (VendorPrefixes::$non_standard_values[$property->name] as $value => $prefixes) {
						if (strpos($property->value, $value) !== false) {
							foreach ($prefixes as $vendor => $fn) {
								$newValue = call_user_func(__NAMESPACE__.'\\VendorPrefixes::'.$fn, $property->value);

								if (!$code->hasProperty($property->name, $newValue)) {
									$newProperty = clone $property;
									$newProperty->value = $newValue;
									$newProperty->vendor = $vendor;

									$code->addProperty($newProperty, $property->getPositionInParent());
								}
							}
						}
					}
				}
			}
		});


		//Selector
		$css->executeRecursive(function ($code) {
			foreach (VendorPrefixes::$selectors as $selector => $prefixes) {
				if (strpos((string)$code->selector, $selector) === false) {
					continue;
				}

				foreach ($prefixes as $vendor => $vendor_selector) {
					$newCode = clone $code;
					$newCode->selector->set(str_replace($selector, $vendor_selector, $code->selector->get()));
					$newCode->selector->vendor = $vendor;

					$code->parent->addChild($newCode, $code->getPositionInParent());
				}
			}
		});


		//Type
		$css->executeRecursive(function ($code) {
			if (!isset($code->selector->type) || !isset(VendorPrefixes::$types[$code->selector->type])) {
				return;
			}

			foreach (VendorPrefixes::$types[$code->selector->type] as $vendor => $type) {
				$newCode = clone $code;
				$newCode->selector->vendor = $vendor;
				$newCode->selector->type = $type;

				$code->parent->addChild($newCode, $code->getPositionInParent());
			}
		});

		//Resolve and simplify the vendors
		$css->resolveVendors();
	}



	/**
	 * Fix the different syntaxis for the linear-gradient
	 *
	 * @param string  $value  The value of the property
	 *
	 * @return array  The linear-gradient code
	 */
	static public function webkitLinearGradient ($value) {
		return Parser::executeFunctions($value, 'linear-gradient', function ($params) {
			$point = 'top';

			if (preg_match('/(top|bottom|left|right|deg)/', $params[0])) {
				$point = array_shift($params);
			}

			switch ($point) {
				case 'center top':
				case 'top':
				case 'to bottom':
					$start = 'left top';
					$end = 'left bottom';
					break;

				case 'center bottom':
				case 'bottom':
				case 'to top':
					$start = 'left bottom';
					$end = 'left top';
					break;

				case 'left top':
				case 'left':
				case 'to right':
					$start = 'left top';
					$end = 'right top';
					break;

				case 'right top':
				case 'right':
				case 'to left':
					$start = 'right top';
					$end = 'left top';
					break;

				default:
					if (preg_match('/^\ddeg$/', $point)) {
						$radius = intval($point);
					} else {
						$start = 'left top';
						$end = 'left bottom';
					}
			}

			$color_stops = array();
			$tk = count($params)-1;

			foreach ($params as $k => $param) {
				$param = Parser::explode(' ', trim($param));

				$color = $param[0];
				$stop = isset($param[1]) ? $param[1] : null;

			 	if ($k === 0) {
			 		$text = 'from';
				} else if ($k === $tk) {
					$text = 'to';
				} else {
					$text = 'color-stop';
				}

				if ($stop) {
					$color_stops[] = $text.'('.$stop.', '.$color.')';
				} else {
					$color_stops[] = $text.'('.$color.')';
				}
			}

			if (isset($radius)) {
				return '-webkit-gradient(linear, '.$radius.'deg, '.implode(', ', $color_stops).')';
			} else {
				return '-webkit-gradient(linear, '.$start.', '.$end.', '.implode(', ', $color_stops).')';
			}
		});
	}
}