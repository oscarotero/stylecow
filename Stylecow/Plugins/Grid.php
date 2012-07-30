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

use Stylecow\Css;
use Stylecow\Property;

class Grid {
	const POSITION = 1;

	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code, &$contextGrid) {
			$contextGrid = array_replace($contextGrid, Grid::getGrid($code));

			$arguments = array();
			$grid = $contextGrid;

			foreach ($code->getProperties('$grid') as $property) {
				$property->executeAllFunctions(function ($params, $name) use (&$grid, &$arguments) {
					switch ($name) {
						case 'cols':
						case 'right':
						case 'cols-width':
						case 'left':
						case 'in-cols':
						case 'background':
							$arguments[$name] = $params;
							break;

						case 'columns':
						case 'width':
						case 'gutter':
							$grid = intval($params[0]);
							break;
					}
				});

				if ($arguments) {
					foreach (Grid::getStyles($grid, $arguments) as $name => $value) {
						if (!$code->hasProperty($name)) {
							$code->addProperty(new Property($name, $value));
						}
					}
				}
			}
		}, array());
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
		$options['cols'][1] = isset($options['cols'][1]) ? intval($options['cols'][1]) : 0;

		if (isset($options['in-cols'])) {
			$options['cols'][1] += ($options['cols'][0] / floatval($options['in-cols'][0])) * intval($options['in-cols'][1]);
		}

		if (isset($options['right'])) {
			$right = (($width + $grid['gutter']) * floatval($options['right'][0])) + intval($options['right'][1]) + $grid['gutter'];
		} else {
			$right = $grid['gutter'];
		}

		if (isset($options['left'])) {
			$left = (($width + $grid['gutter']) * floatval($options['left'][0])) + intval($options['left'][1]);
		} else {
			$left = '0';
		}

		$width = floor(($width * $options['cols'][0]) + ($grid['gutter'] * ($options['cols'][0] - 1)) + $options['cols'][1]);

		return array($width, floor($left), floor($right));
	}


	/**
	 * Search and return the css variables in an array. Removes also the css property
	 *
	 * @param array $properties The piece of the parsed css code with the properties
	 *
	 * @return array The transformed code
	 */
	static public function getGrid (Css $css) {
		$grid = array();
		$gridChild = $css->getChildren('$grid');

		if (isset($gridChild[0])) {
			$gridChild[0]->removeFromParent();

			foreach ($gridChild[0]->getProperties(array('width', 'columns', 'gutter')) as $property) {
				$grid[$property->name] = $property->value;
			}
		}

		return $grid;
	}
}