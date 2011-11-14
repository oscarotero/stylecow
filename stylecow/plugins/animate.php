<?php
/**
* styleCow php library (version 0.1)
*
* 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*
* styleCow is released under the GNU Affero GPL version 3.
* More information at http://www.gnu.org/licenses/agpl-3.0.html
*
* The animate plugins is based in the work of Dan Eden in http://daneden.me/animate
*
* LICENSED UNDER THE  MIT LICENSE (MIT)
* Copyright (c) 2011 Dan Eden
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace stylecow;

class Animate implements iPlugins {
	public $position = 1;

	private $animations = array();
	private $Css;

	/**
	 * public function __construct (Stylecow $Css)
	 *
	 * return none
	 */
	public function __construct (Stylecow $Css) {
		$this->Css = $Css;
		$this->animations = $this->Css->parse(file_get_contents(__DIR__.'/animate.css'));
		print_r($this->animations);
		die();
	}

	
	/**
	 * public function transform ()
	 *
	 * return none
	 */
	public function transform () {
		$this->Css->code = $this->_transform($this->Css->code);
	}


	/**
	 * private function _transform (array $array_code)
	 *
	 * return none
	 */
	private function _transform ($array_code) {
		foreach ($array_code as $k_code => $code) {
			foreach ($code['properties'] as $k_property => $property) {
				//if (!$property['name'] !== '$animate')
				continue;
				if ($grid = $this->grids[$property['name']]) {
					$options = array();
					$new_properties = array();

					foreach ($this->Css->explodeFunctions($property['value'][0]) as $function) {
						switch ($function[0]) {
							case 'cols':
							case 'right':
							case 'left':
							case 'in-cols':
								$options[$function[0]] = $function[1];
								break;

							case 'columns':
							case 'width':
							case 'gutter':
								$grid[$function[0]] = intval($function[1][0]);
								break;
						}
					}

					if ($options['cols']) {
						foreach ($this->cols($grid, $options) as $property_name => $property_value) {
							$this->Css->addProperty($array_code[$k_code]['properties'], $property_name, $property_value, 2);
						}

					}

					unset($array_code[$k_code]['properties'][$k_property]);
				}
			}

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content']);
			}
		}

		return $array_code;
	}


	/**
	 * private function cols (array $grid, int $options)
	 *
	 * return none
	 */
	private function cols ($grid, $options) {
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

		return array(
			'width' => floor(($width * $options['cols'][0]) + ($grid['gutter'] * ($options['cols'][0] - 1)) + $options['cols'][1]).'px',
			'float' => 'left',
			'display' => 'inline',
			'margin-right' => floor($right).'px',
			'margin-left' => floor($left).'px'
		);
	}
}