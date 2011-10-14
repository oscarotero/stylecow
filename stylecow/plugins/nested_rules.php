<?php
/**
* styleCow php library (version 0.1)
*
* 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*
* styleCow is released under the GNU Affero GPL version 3.
* More information at http://www.gnu.org/licenses/agpl-3.0.html
*/

namespace stylecow;

class Nested_rules implements iPlugins {
	public $position = 2;

	private $Css;

	/**
	 * public function __construct (Stylecow $Css)
	 *
	 * return none
	 */
	public function __construct (Stylecow $Css) {
		$this->Css = $Css;
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
	 * private function _transform (array $array_code, [array $parent_selectors])
	 *
	 * return none
	 */
	private function _transform ($array_code, $parent_selectors = array()) {
		$new_array_code = array();

		foreach ($array_code as $k_code => $code) {
			if ($parent_selectors && $code['properties'] && $code['is_css']) {
				$selectors = $code['selector'];
				$code['selector'] = array();

				foreach ($selectors as $selector) {
					$selector = ($selector[0] == '&') ? substr($selector, 1) : ' '.$selector;

					foreach ($parent_selectors as $parent_selector) {
						$code['selector'][] = $parent_selector.$selector;
					}
				}
			}

			if ($code['content'] && !$code['type']) {
				$new_code = $this->_transform($code['content'], $code['selector']);
				$code['content'] = array();

				$new_array_code[] = $code;
				$new_array_code = array_merge($new_array_code, $new_code);

				$new_array_code[0]['content'] = array();

				continue;
			}

			if ($code['content']) {
				$code['content'] = $this->_transform($code['content']);
			}

			$new_array_code[] = $code;
		}

		return $new_array_code;
	}


	/**
	 * private function nested (array $array_code, array $parent_selectors)
	 *
	 * return none
	 */
	private function nested ($array_code, $parent_selectors) {
		foreach ($array_code as $k_code => $code) {
			foreach ($code['selector'] as $k_selector => $selector) {
				$selector = ($selector[0] == '&') ? substr($selector, 1) : ' '.$selector;

				foreach ($parent_selectors as $parent_selector) {
					$array_code[$k_code]['selector'][$k_selector] = $parent_selector.$selector;
				}

				if ($code['content']) {
					$array_code[$k_code]['content'] = $this->nested($code['content'], $array_code[$k_code]['selector']);
				}
			}
		}

		return $array_code;
	}
}