<?php
/**
 * Stylecow PHP library
 *
 * Variables plugin
 * Allows use predefined variables in the css
 *
 * Example:
 * color: $my-color;
 * color: var(my-color);
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class Variables extends Plugin {
	static protected $position = 1;

	private $variables = array();



	/**
	 * Search for variables and replace with the value
	 *
	 * @param array $array_code The piece of the parsed css code
	 *
	 * @return array The transformed code
	 */
	public function transform (array $array_code) {
		$rootVariables = isset($this->settings['variables']) ? $this->settings['variables'] : array();

		if (($keys = Stylecow::searchBySelectors($array_code, array(':root', 'html', 'body')))) {
			foreach ($keys as $key) {
				$rootVariables = array_replace($rootVariables, self::getVariables($array_code[$key]['properties']));
			}
		}

		return Stylecow::walk($array_code, function ($code, $depth) use ($rootVariables) {
			$depthVariables = $rootVariables;

			return Stylecow::propertiesWalk($code, function ($properties) use ($depthVariables) {
				$propertiesVariables = array_replace($depthVariables, Variables::getVariables($properties));

				return Stylecow::valueWalk($properties, function ($value) use ($propertiesVariables) {
					$value = preg_replace_callback('/\$([\w-]+)/', function ($matches) use ($propertiesVariables) {
						return isset($propertiesVariables[$matches[1]]) ? $propertiesVariables[$matches[1]] : $matches[0];
					}, $value);

					return Stylecow::executeFunctions($value, 'var', function ($params) use ($propertiesVariables) {
						if (!isset($params[0])) {
							return 'var()';
						}

						if (isset($propertiesVariables[$params[0]])) {
							return $propertiesVariables[$params[0]];
						}

						return isset($params[1]) ? $params[1] : 'var('.$params[0].')';
					});
				});
			});
		});

		return $array_code;
	}


	
	/**
	 * Search and return the css variables in an array. Removes also the css property
	 *
	 * @param array $properties The piece of the parsed css code with the properties
	 *
	 * @return array The transformed code
	 */
	static public function getVariables (array &$properties) {
		$variables = array();

		foreach ($properties as $k => $property) {
			if (strpos($property['name'], 'var-') === 0) {
				$variables[substr($property['name'], 4)] = $property['value'][0];

				unset($properties[$k]);
			}
		}

		return $variables;
	}
}