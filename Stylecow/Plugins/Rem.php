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
 * @version 0.1.3 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class Rem extends Plugin implements PluginsInterface {
	static protected $position = 4;

	private $rem = 16;


	/**
	 * Search for all rem values and fix them
	 *
	 * @param array $array_code The piece of the parsed css code
	 *
	 * @return array The transformed code
	 */
	public function transform (array $array_code) {
		$key = Stylecow::searchSelector($array_code, 'body');

		if ($key !== false) {
			$key_property = Stylecow::searchProperty($array_code[$key]['properties'], 'font-size');

			if ($key_property !== false) {
				$this->rem = floatval($array_code[$key]['properties'][$key_property]['value'][0]) * 16;
			}
		}

		return Stylecow::propertyWalk($array_code, function ($property) {
			if (!preg_match('/([0-9\.]+)rem/', implode($property['value']))) {
				return $property;
			}

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

			$property['value'] = $new_values;

			return $property;
		});
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