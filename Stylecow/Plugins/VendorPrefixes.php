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

use Stylecow\Stylecow;

class VendorPrefixes extends Plugin implements PluginsInterface {
	static protected $position = 3;

	static $property_prefixes = array(
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

	static $property_fn_prefixes = array(
		'border-top-left-radius' => 'borderRadius',
		'border-top-right-radius' => 'borderRadius',
		'border-bottom-left-radius' => 'borderRadius',
		'border-bottom-right-radius' => 'borderRadius'
	);

	static $value_prefixes = array(
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

	static $value_fn_prefixes = array(
		'background' => array(
			'linear-gradient' => 'linearGradient'
		),
		'background-image' => array(
			'linear-gradient' => 'linearGradient'
		)
	);

	static $selector_prefixes = array(
		'::selection' => array('moz' => '::-moz-selection')
	);

	static $type_prefixes = array(
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
	 * Transform the parsed css code
	 */
	public function transform (array $array_code) {
		$array_code = $this->transformType($array_code);
		$array_code = $this->transformSelector($array_code);
		$array_code = $this->transformProperties($array_code);
		return $this->transformValues($array_code);
	}



	/**
	 * Private function to add the type prefixes
	 *
	 * @param array  $array_code    The piece of the parsed css code
	 * @param array  $prefix_scope  The browser prefix
	 *
	 * @return array  The transformed code
	 */
	private function transformType ($array_code, $prefix_scope = '') {
		$new_array_code = array();

		foreach ($array_code as $code) {
			if ($code['type'] && isset(VendorPrefixes::$type_prefixes[$code['type']])) {
				foreach (VendorPrefixes::$type_prefixes[$code['type']] as $prefix => $new_type_prefix) {
					if ((isset($code['browser']) && $code['browser'] !== $prefix) || ($prefix_scope && $prefix !== $prefix_scope)) {
						continue;
					}

					$new_code = $code;
					$new_code['type'] = $new_type_prefix;
					$new_code['browser'] = $prefix;

					if ($new_code['content']) {
						$new_code['content'] = $this->transformType($new_code['content'], $prefix);
					}

					$new_array_code[] = $new_code;
				}
			} else if ($code['content']) {
				$code['content'] = $this->transformType($code['content'], isset($code['browser']) ? $code['browser'] : null);
			}

			$new_array_code[] = $code;
		}



		return $new_array_code;
	}


	
	/**
	 * Private function to add the selector prefixes
	 *
	 * @param array  $array_code    The piece of the parsed css code
	 * @param array  $prefix_scope  The browser prefix
	 *
	 * @return array  The transformed code
	 */
	private function transformSelector ($array_code, $prefix_scope = '') {
		$new_array_code = array();

		foreach ($array_code as $code) {
			if ($code['content']) {
				$code['content'] = $this->transformSelector($code['content'], isset($code['browser']) ? $code['browser'] : null);
			}

			$new_array_code[] = $code;

			if ($code['is_css']) {
				foreach ($code['selector'] as $selector) {
					foreach (VendorPrefixes::$selector_prefixes as $selector_prefix => $prefixes) {
						if (strpos($selector, $selector_prefix) !== false) {
							foreach ($prefixes as $prefix => $new_selector_prefix) {
								if ((isset($code['browser']) && $code['browser'] !== $prefix) || ($prefix_scope && $prefix !== $prefix_scope)) {
									continue;
								}

								$new_code = $code;
								$new_code['selector'] = array(str_replace($selector_prefix, $new_selector_prefix, $selector));
								$new_code['browser'] = $prefix;

								if ($new_code['content']) {
									$new_code['content'] = $this->transformSelector($new_code['content'], $prefix);
								}

								$new_array_code[] = $new_code;
							}
						}
					}
				}
			}
		}

		return $new_array_code;
	}



	/**
	 * Private function to add the properties prefixes
	 *
	 * @param array  $array_code    The piece of the parsed css code
	 * @param array  $prefix_scope  The browser prefix
	 *
	 * @return array  The transformed code
	 */
	private function transformProperties ($array_code, $prefix_scope = '') {
		$new_array_code = array();

		foreach ($array_code as $code) {
			if ($code['content']) {
				$code['content'] = $this->transformProperties($code['content'], $code['browser']);
			}

			$new_code = $code;

			if (is_array($code['properties'])) {
				$new_code['properties'] = array();

				foreach ($code['properties'] as $property) {
					$new_code['properties'][] = $property;

					if (isset(VendorPrefixes::$property_fn_prefixes[$property['name']]) && ($fn = VendorPrefixes::$property_fn_prefixes[$property['name']])) {
						$v = $this->$fn($property['name'], $property['value']);

						Stylecow::addProperty($new_code['properties'], $v['name'], $v['value'], Stylecow::PROPERTY_IF_UNDEFINED, $v['browser']);
					}

					if (isset(VendorPrefixes::$property_prefixes[$property['name']])) {
						foreach (VendorPrefixes::$property_prefixes[$property['name']] as $prefix) {
							if ((isset($code['browser']) && $code['browser'] !== $prefix) || ($prefix_scope && $prefix !== $prefix_scope)) {
								continue;
							}

							Stylecow::addProperty($new_code['properties'], '-'.$prefix.'-'.$property['name'], $property['value'], Stylecow::PROPERTY_IF_UNDEFINED, $prefix);
						}
					}
				}
			}

			$new_array_code[] = $new_code;
		}

		return $new_array_code;
	}



	/**
	 * Private function to add the value prefixes
	 *
	 * @param array  $array_code    The piece of the parsed css code
	 * @param array  $prefix_scope  The browser prefix
	 *
	 * @return array  The transformed code
	 */
	private function transformValues ($array_code, $prefix_scope = '') {
		$new_array_code = array();

		foreach ($array_code as $code) {
			if ($code['content']) {
				$code['content'] = $this->transformValues($code['content'], $code['browser']);
			}

			$new_code = $code;

			if (is_array($code['properties'])) {
				$new_code['properties'] = array();

				foreach ($code['properties'] as $property) {
					$new_code['properties'][] = $property;

					if (isset(VendorPrefixes::$value_fn_prefixes[$property['name']])) {
						foreach (VendorPrefixes::$value_fn_prefixes[$property['name']] as $property_value => $fn) {
							if (preg_match('/(^|[^-])'.preg_quote($property_value, '/').'([^\w]|$)?/', implode($property['value']))) {
								$v = $this->$fn($property['name'], $property['value']);

								Stylecow::addProperty($new_code['properties'], $v['name'], $v['value'], Stylecow::PROPERTY_ADD, $v['browser']);
							}
						}
					}

					if (isset(VendorPrefixes::$value_prefixes[$property['name']])) {
						foreach (VendorPrefixes::$value_prefixes[$property['name']] as $property_value => $prefixes) {
							if (preg_match('/(^|[^-])'.preg_quote($property_value, '/').'([^\w]|$)?/', implode($property['value']))) {
								foreach ($prefixes as $prefix) {
									if ((isset($code['browser']) && $code['browser'] !== $prefix) || ($prefix_scope && $prefix !== $prefix_scope)) {
										continue;
									}

									$new_values = array();

									foreach ($property['value'] as $v) {
										$new_values[] = str_replace($property_value, '-'.$prefix.'-'.$property_value, $v);
									}

									Stylecow::addProperty($new_code['properties'], $property['name'], $new_values, Stylecow::PROPERTY_ADD, $prefix);
								}
							}
						}
					}
				}
			}

			$new_array_code[] = $new_code;
		}

		return $new_array_code;
	}



	/**
	 * Fix the different syntaxis for the border-radius
	 *
	 * @param array   $code    The piece of the parsed css code
	 * @param string  $name    The name of the border-radius property
	 * @param array   $values  The values of the property
	 *
	 * @return array  The border-radius code
	 */
	public function borderRadius ($name, $values) {
		switch ($name) {
			case 'border-top-right-radius':
				return array(
					'name' => '-moz-border-radius-topright',
					'value' => $values,
					'browser' => 'moz'
				);

			case 'border-top-left-radius':
				return array(
					'name' => '-moz-border-radius-topleft',
					'value' => $values,
					'browser' => 'moz'
				);

			case 'border-bottom-right-radius':
				return array(
					'name' => '-moz-border-radius-bottomright',
					'value' => $values,
					'browser' => 'moz'
				);

			case 'border-bottom-left-radius':
				return array(
					'name' => '-moz-border-radius-bottomleft',
					'value' => $values,
					'browser' => 'moz'
				);
		}
	}



	/**
	 * Fix the different syntaxis for the linear-gradient
	 *
	 * @param string  $name    The name of the linear-gradient property
	 * @param array   $values  The values of the property
	 *
	 * @return array  The linear-gradient code
	 */
	public function linearGradient ($name, $values) {
		foreach ($values as &$value) {
			$value = Stylecow::executeFunctions($value, 'linear-gradient', function ($params) {
				$point = 'top';

				if (preg_match('/(top|bottom|left|right|deg)/', $params[0])) {
					$point = array_shift($params);
				}

				switch ($point) {
					case 'center top':
					case 'top':
					case '90deg':
						$start = 'left top';
						$end = 'left bottom';
						break;

					case 'center bottom':
					case 'bottom':
					case '-90deg':
						$start = 'left bottom';
						$end = 'left top';
						break;

					case 'left top':
					case 'left':
					case '180deg':
					case '-180deg':
						$start = 'left top';
						$end = 'right top';
						break;

					case 'right top':
					case 'right':
					case '0deg':
					case '360deg':
						$start = 'right top';
						$end = 'left top';
						break;
				}

				$color_stops = array();
				$tk = count($params)-1;

				foreach ($params as $k => $param) {
					$param = Stylecow::explode(' ', trim($param));

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
						if (preg_match('/%$/', $stop)) {
							$stop = intval($top) / 100;
						}

						$color_stops[] = $text.'('.$stop.', '.$color.')';
					} else {
						$color_stops[] = $text.'('.$color.')';
					}
				}

				return '-webkit-gradient(linear, '.$start.', '.$end.', '.implode(', ', $color_stops).')';
			});
		}

		return array(
			'name' => $name,
			'value' => $values,
			'browser' => 'webkit'
		);
	}
}