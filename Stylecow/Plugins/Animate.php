<?php
/**
* Animate plugin (version 0.1)
* for styleCow PHP library
*
* 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*
* The Animate plugin is based in the work of Dan Eden in http://daneden.me/animate
*
* LICENSED UNDER THE  MIT LICENSE (MIT)
* Copyright (c) 2011 Dan Eden
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Stylecow;

class Animate implements Plugins_interface {
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
		$code = $this->Css->resolve(file_get_contents(__DIR__.'/animate.css'), __DIR__.'/animate.css', __DIR__.'/animate.css');
		$code = $this->Css->parse($code);

		foreach ($code as $animation) {
			if ($animation['type'] === '@keyframes') {
				$animation['properties'][] = array(
						'name' => 'animation-name',
						'value' => array($animation['selector'][0]),
						'settings' => array()
				);

				$this->animations[$animation['selector'][0]] = $animation;
			}
		}
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
				if ($property['name'] !== '$animate') {
					continue;
				}

				unset($array_code[$k_code]['properties'][$k_property]);

				$animation_name = $property['value'][0];

				if (!$this->animations[$animation_name]) {
					continue;
				}

				if (!$this->animations[$animation_name]['used']) {
					$animation = $this->animations[$animation_name];
					$animation['properties'] = array();
					$array_code[] = $animation;

					$this->animations[$animation_name]['used'] = true;
				}

				foreach ($this->animations[$animation_name]['properties'] as $prop) {
					$this->Css->addProperty($array_code[$k_code]['properties'], $prop['name'], $prop['value']);
				}
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