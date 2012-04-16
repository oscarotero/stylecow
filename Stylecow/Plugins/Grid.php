<?php
/**
 * Stylecow PHP library
 *
 * Grid plugin
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1.1 (2011)
 */

namespace Stylecow;

class Grid implements Plugins_interface {
	public $position = 1;

	private $grids = array();
	private $Css;


	/**
	 * Constructor
	 *
	 * @param Stylecow  $Css  The Stylecow instance
	 */
	public function __construct (Stylecow $Css) {
		$this->Css = $Css;
	}

	
	/**
	 * Transform the parsed css code
	 */
	public function transform () {
		$this->Css->code = $this->_transform($this->Css->code);
	}


	/**
	 * Private function to transform recursively the parsed css code
	 *
	 * @param array  $array_code  The piece of the parsed css code
	 *
	 * @return array  The transformed code
	 */
	private function _transform ($array_code) {
		foreach ($array_code as $k_code => $code) {
			if ($code['type'] == '$grid' || strpos($code['type'], '$grid-') === 0) {
				$grid = array();

				foreach ($code['properties'] as $property) {
					$grid[$property['name']] = intval(current($property['value']));
				}

				if (!$this->grids[$code['type']]) {
					$this->grids[$code['type']] = $grid;
				} else {
					$this->grids[$code['type']] = array_merge($this->grids[$code['type']], $grid);
				}

				unset($array_code[$k_code]);

				continue;
			}

			if (!$code['is_css']) {
				continue;
			}

			if ($code['properties']) {
				foreach ($code['properties'] as $k_property => $property) {
					if ($grid = $this->grids[$property['name']]) {
						$options = array();
						$new_properties = array();

						foreach ($this->Css->explodeFunctions($property['value'][0]) as $function) {
							switch ($function[0]) {
								case 'cols':
								case 'right':
								case 'cols-width':
								case 'left':
								case 'in-cols':
								case 'background':
									$options[$function[0]] = $function[1];
									break;

								case 'columns':
								case 'width':
								case 'gutter':
									$grid[$function[0]] = intval($function[1][0]);
									break;
							}
						}

						if ($options) {
							foreach ($this->getStyles($grid, $options) as $property_name => $property_value) {
								$this->Css->addProperty($array_code[$k_code]['properties'], $property_name, $property_value, 2);
							}

						}

						unset($array_code[$k_code]['properties'][$k_property]);
					}
				}
			}

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content']);
			}
		}

		return $array_code;
	}


	
	/**
	 * Get the styles for a grid function
	 *
	 * @param array  $grid     The grid configuration
	 * @param array  $options  The used functions of the grid (cols, cols-width, etc)
	 *
	 * @return array  The css styles
	 */
	private function getStyles ($grid, $options) {
		$styles = array();

		if (array_key_exists('cols', $options)) {
			list($width, $left, $right) = $this->calculate($grid, $options);

			$styles += array(
				'width' => $width.'px',
				'float' => 'left',
				'display' => 'inline',
				'margin-right' => $right.'px',
				'margin-left' => $left.'px'
			);
		} else if (array_key_exists('cols-width', $options)) {
			$options['cols'] = $options['cols-width'];
			list($width, $left, $right) = $this->calculate($grid, $options);
			$styles['width'] = $width.'px';
		}

		if (array_key_exists('background', $options)) {
			$url = 'http://griddle.it/'.$grid['width'].'-'.$grid['columns'].'-'.$grid['gutter'];

			if ($options['background']) {
				$params = array();

				foreach ($options['background'] as $param) {
					list($name, $value) = explode(':', $param, 2);
					$params[$name] = $value;
				}

				$url .= '?'.http_build_query($params);
			}

			$styles['background'] = "url('$url') !important";
		}
		
		return $styles;
	}


	/**
	 * Calculate the width, left and right for a grid element
	 *
	 * @param array  $grid     The grid configuration
	 * @param array  $options  The used functions of the grid (cols, cols-width, etc)
	 *
	 * @return array  An array with three elements: width, left and right values
	 */
	private function calculate ($grid, $options) {
		$width = ($grid['width'] - ($grid['gutter'] * ($grid['columns'] - 1))) / $grid['columns'];

		$options['cols'][0] = floatval($options['cols'][0]);
		$options['cols'][1] = intval($options['cols'][1]);

		if ($options['in-cols']) {
			$options['cols'][1] += ($options['cols'][0] / floatval($options['in-cols'][0])) * intval($options['in-cols'][1]);
		}

		if ($options['right']) {
			$right = (($width + $grid['gutter']) * floatval($options['right'][0])) + intval($options['right'][1]) + $grid['gutter'];
		} else {
			$right = $grid['gutter'];
		}

		if ($options['left']) {
			$left = (($width + $grid['gutter']) * floatval($options['left'][0])) + intval($options['left'][1]);
		} else {
			$left = '0';
		}

		$width = floor(($width * $options['cols'][0]) + ($grid['gutter'] * ($options['cols'][0] - 1)) + $options['cols'][1]);

		return array($width, floor($left), floor($right));
	}
}