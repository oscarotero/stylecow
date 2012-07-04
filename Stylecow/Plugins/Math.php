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
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Css;

class Math {
	const POSITION = 4;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			foreach ($code->getProperties() as $property) {
				$property->executeFunction('math', function ($parameters) {
					$units = '';
					$operations = $parameters[0];

					if (strpos($operations, 'px')) {
						$units = 'px';
						$operations = str_replace('px', '', $operations);
					} else if (strpos($operations, '%')) {
						$units = '%';
						$operations = str_replace('%', '', $operations);
					} else if (strpos($operations, 'em')) {
						$units = 'em';
						$operations = str_replace('em', '', $operations);
					} else if (strpos($operations, 'rem')) {
						$units = 'rem';
						$operations = str_replace('rem', '', $operations);
					} else if (strpos($operations, 'pt')) {
						$units = 'pt';
						$operations = str_replace('pt', '', $operations);
					}

					if (preg_match('/^[\+\*\/\.\(\)0-9- ]*$/', $operations)) {
						$calculate = create_function('', 'return('.$operations.');');

						return round($calculate(), 2).$units;
					}
				});
			}
		});
	}
}