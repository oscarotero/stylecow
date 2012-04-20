<?php
/**
 * Stylecow PHP library
 *
 * Animate plugin
 * Implement predefined animations easily
 * 
 * Example:
 * $animate: flash;
 *
 * Based in the work of Dan Eden in http://daneden.me/animate
 * LICENSED UNDER THE  MIT LICENSE (MIT)
 * Copyright (c) 2011 Dan Eden
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1.4 (2012)
 */

namespace Stylecow;

class Animate implements Plugins_interface {
	public $position = 1;

	private $animations = array();
	private $Css;


	/**
	 * Constructor
	 *
	 * @param Stylecow  $Css       The Stylecow instance
	 * @param array     $settings  The settings for this plugin
	 */
	public function __construct (Stylecow $Css, array $settings) {
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
	 * Transform the parsed css code
	 */
	public function transform () {
		$this->Css->code = $this->_transform($this->Css->code, $animation_code);

		if ($animation_code) {
			$this->Css->code = array_merge($this->Css->code, $animation_code);
		}
	}



	/**
	 * Private function to transform recursively the parsed css code
	 *
	 * @param array  $array_code  The piece of the parsed css code
	 * @param array  &$animation_code  The extra code for the animations will be stored here
	 *
	 * @return array  The transformed code
	 */
	private function _transform ($array_code, &$animation_code) {
		foreach ($array_code as $k_code => $code) {
			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content'], $animation_code);
			}

			if ($code['properties']) {
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
						$animation_code[] = $animation;

						$this->animations[$animation_name]['used'] = true;
					}

					foreach ($this->animations[$animation_name]['properties'] as $prop) {
						$this->Css->addProperty($array_code[$k_code]['properties'], $prop['name'], $prop['value']);
					}
				}
			}
		}

		return $array_code;
	}
}