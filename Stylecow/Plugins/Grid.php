<?php
/**
 * Stylecow PHP library
 *
 * Grid plugin
 * Provides a grid system to build websites with columns:
 *
 * Examples:
 * $grid: cols(3);
 * $grid: cols(2) left(1);
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class Grid extends Plugin implements PluginsInterface {
	static protected $position = 1;

	private $grids = array();


	
	/**
	 * Finds the $grid properties and resolve them
	 *
	 * @param array  $array_code  The piece of the parsed css code
	 *
	 * @return array  The transformed code
	 */
	public function transform (array $array_code) {
		return Stylecow::walk($array_code, function ($code) {
			$grids = array();

			foreach ($code as $key => $subcode) {
				if ($subcode['type'] === '$grid' || strpos($subcode['type'], '$grid-') === 0) {
					$grids[$subcode['type']][$property['name']] = array();

					foreach ($subcode['properties'] as $property) {
						$grids[$subcode['type']][$property['name']] = intval(current($property['value']));
					}

					unset($code[$key]);
				}
			}

			return Stylecow::propertiesWalk($code, function ($properties) use ($grids) {
				foreach ($properties as $k => $property) {
					if (($grid = $grids[$property['name']])) {

						$options = array();

						Stylecow::executeFunctions($property['value'][0], null, function ($params, $name) use (&$grid, &$options) {
							switch ($name) {
								case 'cols':
								case 'right':
								case 'cols-width':
								case 'left':
								case 'in-cols':
								case 'background':
									$options[$name] = $params;
									break;

								case 'columns':
								case 'width':
								case 'gutter':
									$grid = intval($params[0]);
									break;
							}
						});

						if ($options) {
							foreach (Grid::getStyles($grid, $options) as $name => $value) {
								Stylecow::addProperty($properties, $name, $value, Stylecow::PROPERTY_IF_FAMILY_UNDEFINED);
							}
						}

						unset($properties[$k]);
					}
				}

				return $properties;
			});
		});
	}


	
	/**
	 * Get the styles for a grid function
	 *
	 * @param array  $grid     The grid configuration
	 * @param array  $options  The used functions of the grid (cols, cols-width, etc)
	 *
	 * @return array  The css styles
	 */
	static public function getStyles ($grid, $options) {
		$styles = array();

		if (array_key_exists('cols', $options)) {
			list($width, $left, $right) = self::calculate($grid, $options);

			$styles += array(
				'width' => $width.'px',
				'float' => 'left',
				'display' => 'inline',
				'margin-right' => $right.'px',
				'margin-left' => $left.'px'
			);
		} else if (array_key_exists('cols-width', $options)) {
			$options['cols'] = $options['cols-width'];
			list($width, $left, $right) = self::calculate($grid, $options);
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
	static private function calculate ($grid, $options) {
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