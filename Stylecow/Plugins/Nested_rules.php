<?php
/**
 * Stylecow PHP library
 *
 * Nested_rules plugin
 * Allows write css in a tree way
 *
 * Examples:
 * article.post {
 *   header {
 *   }
 * }
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1.2 (2012)
 */

namespace Stylecow;

class Nested_rules implements Plugins_interface {
	public $position = 2;

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
	 * @param array  $array_code        The piece of the parsed css code
	 * @param array  $parent_selectors  The parent selectors to joint
	 *
	 * @return array  The transformed code
	 */
	private function _transform ($array_code, $parent_selectors = array()) {
		$new_array_code = array();

		foreach ($array_code as $k_code => $code) {
			if ($parent_selectors && $code['is_css']) {
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
}