<?php
/**
 * Stylecow PHP library
 *
 * Math plugin
 * To execute math operations.
 *
 * Examples:
 * font-size: math(3 + 8)em
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1.0 (2012)
 */

namespace Stylecow;

class Math implements Plugins_interface {
	public $position = 4;

	private $Css;


	/**
	 * Constructor
	 *
	 * @param Stylecow  $Css       The Stylecow instance
	 * @param array     $settings  The settings for this plugin
	 */
	public function __construct (Stylecow $Css, array $settings) {
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
			if ($code['properties']) {
				foreach ($code['properties'] as $k_property => $property) {
					foreach ($property['value'] as $k_value => $value) {
						if (strpos($value, 'math(') === false) {
							continue;
						}

						$array_code[$k_code]['properties'][$k_property]['value'][$k_value] = preg_replace_callback('/math\(([^\)]+)\)/', array($this, 'mathCallback'), $value);
					}
				}
			}

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content']);
			}
		}

		return $array_code;
	}



	/**
	 * The internal callback to replace each math() function with the result
	 *
	 * @param array  $matches  The matches found in the preg_replace_callback
	 *
	 * @return array  The result of the operation
	 */
	private function mathCallback ($matches) {
		if (preg_match('/^[\+\*\/%\.0-9- ]*$/', $matches[1])) {
			$calculate = create_function('', 'return('.$matches[1].');');

			return round($calculate(), 2);
		}

		return $matches[0];
	}
}