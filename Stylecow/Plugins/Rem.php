<?php
/**
* Rem plugin (version 0.1.1)
* for styleCow PHP library
*
* 2012. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*/

namespace Stylecow;

class Rem implements Plugins_interface {
	public $position = 4;

	private $Css;
	private $rem = 16;


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
	 * private function _transform (array $array_code)
	 *
	 * return none
	 */
	private function _transform ($array_code) {
		foreach ($array_code as $k_code => $code) {
			if ($code['is_css'] && $code['properties'] && $code['selector'] && in_array('body', $code['selector'])) {
				foreach ($code['properties'] as $property) {
					if ($property['name'] === 'font-size') {
						$this->rem = floatval($property['value'][0]) * 16;
					}
				}
			}

			if ($code['properties']) {
				$new_properties = array();

				foreach ($code['properties'] as $k_property => $property) {
					if (preg_match('/([0-9\.]+)rem/', implode($property['value']), $match)) {
						$new_values = array();

						foreach ($property['value'] as $k_value => $value) {
							if (strpos($value, 'rem') === false) {
								$new_values[] = $value;
							}

							$new_value = preg_replace_callback('/([0-9\.]+)rem/', array($this, 'remCallback'), $value);

							if ($new_value !== $value) {
								$new_values[] = $new_value;
							}
						}

						$new_properties[] = array('name' => $property['name'], 'value' => $new_values);
					}

					$new_properties[] = $property;
				}

				$array_code[$k_code]['properties'] = $new_properties;
			}
		}
		

		return $array_code;
	}



	/**
	 * private function remCallback (array $matches)
	 *
	 * return none
	 */
	private function remCallback ($matches) {
		if ($matches[1][0] === '.') {
			$matches[1] = '0'.$matches[1];
		}

		return ($this->rem * $matches[1]).'px';
	}
}