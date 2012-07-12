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

use Stylecow\Parser;
use Stylecow\Css;
use Stylecow\Property;

class Variables {
	const POSITION = 1;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 * @param array $rootVariables Optional variables not defined in css code
	 */
	static public function apply (Css $css, array $rootVariables = array()) {
		foreach ($css->getChildren(array(':root', 'html', 'body')) as $child) {
			$rootVariables = self::getVariables($child, $rootVariables);
		}

		$css->executeRecursive(function ($code, &$contextVariables) {
			$contextVariables = Variables::getVariables($code, $contextVariables);

			foreach ($code->properties as $property) {
				$property->value = Variables::replaceVariables($property->value, $contextVariables);
			}

		}, $rootVariables);
	}


	
	/**
	 * Search and return the css variables in an array. Removes also the css property
	 *
	 * @param array $css The piece of the parsed css code with the properties
	 * @param array $variables An array with the existing variables
	 *
	 * @return array The variables found appended to existing variables
	 */
	static public function getVariables (Css $css, array $variables = array()) {
		foreach ($css->properties as $k => $property) {
			if (strpos($property->name, 'var-') === 0) {
				$variables[substr($property->name, 4)] = self::replaceVariables($property->value, $variables);

				unset($css->properties[$k]);
			}
		}

		return $variables;
	}

	/**
	 * Search and replace the css variables with the real value
	 *
	 * @param string $value The property value where the variable is placed
	 * @param array $variables The defined variables
	 */
	static public function replaceVariables ($value, array $variables) {
		if (strpos($value, '$') !== false) {
			$value = preg_replace_callback('/\$([\w-]+)/', function ($matches) use ($variables) {
				return isset($variables[$matches[1]]) ? $variables[$matches[1]] : $matches[0];
			}, $value);
		}

		$value = Parser::executeFunctions($value, 'var', function ($params) use ($variables) {
			if (!isset($params[0])) {
				return 'var()';
			}

			if (isset($variables[$params[0]])) {
				return $variables[$params[0]];
			}

			return isset($params[1]) ? $params[1] : 'var('.$params[0].')';
		});

		return $value;
	}
}