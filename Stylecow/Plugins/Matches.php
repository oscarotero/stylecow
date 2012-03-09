<?php
/**
 * Matches plugin (version 0.1)
 * for styleCow PHP library
 *
 * 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
 */

namespace Stylecow;

class Matches implements Plugins_interface {
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
	 * private function _transform ($array_code)
	 *
	 * return none
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