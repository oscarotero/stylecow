<?php
/**
 * Stylecow PHP library
 *
 * Matches plugin
 * Resolve the css4 :matches() selector
 *
 * Examples:
 * article :matches(h1, h2, h3) a { }
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class Matches extends Plugin implements PluginsInterface {
	static protected $position = 2;


	/**
	 * Resolve all matches in the selectors
	 *
	 * @param array $array_code The piece of the parsed css code
	 *
	 * @return array The transformed code
	 */
	public function transform (array $array_code) {
		return Stylecow::selectorsWalk($array_code, function ($selectors) {

			while (strpos(implode($selectors), ':matches(') !== false) {
				foreach ($selectors as $k_selector => $selector) {
					if (preg_match('/:matches\(([^\)]*)\)/', $selector, $match)) {
						unset($selectors[$k_selector]);

						foreach (Stylecow::explodeTrim(',', $match[1]) as $sub_selector) {
							$selectors[] = str_replace($match[0], $sub_selector, $selector);
						}
					}
				}
			}

			return $selectors;
		});
	}
}