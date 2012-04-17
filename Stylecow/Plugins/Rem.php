<?php
/**
 * Stylecow PHP library
 *
 * Rem plugin
 * Allows use rem values for old browsers (ie8 and lower)
 *
 * Example:
 * font-size: 2rem;
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1.2 (2012)
 */

namespace Stylecow;

class Rem implements Plugins_interface {
	public $position = 4;

	private $Css;
	private $rem = 16;


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
	 *
	 * @return array  The transformed code
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
	 * The internal callback to replace the rem value by its value in pixels
	 *
	 * @param array  $matches  The matches found in the css code
	 *
	 * @return string  The rem value in pixels
	 */
	private function remCallback ($matches) {
		if ($matches[1][0] === '.') {
			$matches[1] = '0'.$matches[1];
		}

		return ($this->rem * $matches[1]).'px';
	}
}