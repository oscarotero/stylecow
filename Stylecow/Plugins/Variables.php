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

use Stylecow\Css;
use Stylecow\Property;

class Variables {
	const POSITION = 1;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$rootVariables = array();

		foreach ($css->getChildren(array(':root', 'html', 'body')) as $child) {
			$rootVariables = array_replace($rootVariables, Variables::getVariables($child));
		}

		$css->executeRecursive(function ($code, &$contextVariables) {
			$contextVariables = array_replace($contextVariables, Variables::getVariables($code));

			foreach ($code->properties as $property) {
				if (strpos($property->value, '$')) {
					$property->value = preg_replace_callback('/\$([\w-]+)/', function ($matches) use ($contextVariables) {
						return isset($contextVariables[$matches[1]]) ? $contextVariables[$matches[1]] : $matches[0];
					}, $property->value);
				}

				$property->executeFunction('var', function ($params) use ($contextVariables) {
					if (!isset($params[0])) {
						return 'var()';
					}

					if (isset($contextVariables[$params[0]])) {
						return $contextVariables[$params[0]];
					}

					return isset($params[1]) ? $params[1] : 'var('.$params[0].')';
				});
			}

		}, $rootVariables);
	}


	
	/**
	 * Search and return the css variables in an array. Removes also the css property
	 *
	 * @param array $properties The piece of the parsed css code with the properties
	 *
	 * @return array The transformed code
	 */
	static public function getVariables (Css $css) {
		$variables = array();

		foreach ($css->properties as $k => $property) {
			if (strpos($property->name, 'var-') === 0) {
				$variables[substr($property->name, 4)] = $property->value;

				unset($css->properties[$k]);
			}
		}

		return $variables;
	}
}