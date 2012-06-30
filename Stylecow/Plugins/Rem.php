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
 * @version 1.0.0 (2012)
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
		if (!($keys = Stylecow::searchBySelectors($array_code, array(':root', 'html', 'body')))) {
			foreach ($keys as $key) {
				if (($value = Stylecow::getValue($array_code[$key]['properties'], 'font-size', 0)) !== false) {
					if (strpos($value, 'px') !== false) {
						$this->rem = intval($value);
					} else if (strpos($value, 'em') !== false) {
						$this->rem = floatval($value) * 16;
					} else if (strpos($value, 'pt') !== false) {
						$this->rem = floatval($value) * 14;
					}
				}
			}
		}

		$self = $this;

		return Stylecow::propertiesWalk($array_code, function ($properties) use ($self) {
			$new_properties = array();

			foreach ($properties as $property) {
				if (!preg_match('/([0-9\.]+)rem/', implode($property['value']))) {
					$new_properties[] = $property;

					continue;
				}

				$new_property = $property;

				foreach ($new_property['value'] as $k_value => $value) {
					if (strpos($value, 'rem') === false) {
						continue;
					}

					$new_property['value'][$k_value] = preg_replace_callback('/([0-9\.]+)rem/', array($self, 'remCallback'), $value);
				}

				$new_properties[] = $new_property;
				$new_properties[] = $property;
			}

			return $new_properties;
		});
	}



	/**
	 * The internal callback to replace the rem value by its value in pixels
	 *
	 * @param array  $matches  The matches found in the css code
	 *
	 * @return string  The rem value in pixels
	 */
	public function remCallback ($matches) {
		if ($matches[1][0] === '.') {
			$matches[1] = '0'.$matches[1];
		}

		return ($this->rem * $matches[1]).'px';
	}
}