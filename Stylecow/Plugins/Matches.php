<?php
/**
 * Stylecow PHP library
 *
 * Matches plugin
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1 (2011)
 */

namespace Stylecow;

class Matches implements Plugins_interface {
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
	 * @param array  $array_code  The piece of the parsed css code
	 *
	 * @return array  The transformed code
	 */
	private function _transform ($array_code) {
		foreach ($array_code as $k_code => $code) {
			if ($code['content']) {
				$code['content'] = $this->_transform($code['content']);
			}

			if ($code['selector'] && $code['is_css']) {
				while (strpos(implode($code['selector']), ':matches(') !== false) {
					foreach ($code['selector'] as $k_selector => $selector) {
						if (preg_match('/:matches\(([^\)]*)\)/', $selector, $match)) {
							unset($code['selector'][$k_selector]);

							foreach ($this->Css->explodeTrim(',', $match[1]) as $sub_selector) {
								$code['selector'][] = str_replace($match[0], $sub_selector, $selector);
							}
						}
					}
				}
			}

			$array_code[$k_code] = $code;
		}

		return $array_code;
	}
}