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
 * @version 0.1.2 (2012)
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
		foreach ($array_code as $k_code => $code) {
			if ($code['content']) {
				$code['content'] = $this->transform($code['content']);
			}

			if ($code['selector'] && $code['is_css']) {
				while (strpos(implode($code['selector']), ':matches(') !== false) {
					foreach ($code['selector'] as $k_selector => $selector) {
						if (preg_match('/:matches\(([^\)]*)\)/', $selector, $match)) {
							unset($code['selector'][$k_selector]);

							foreach (Stylecow::explodeTrim(',', $match[1]) as $sub_selector) {
								$code['selector'][] = str_replace($match[0], $sub_selector, $selector);
							}
						}
					}
				}
			}

			$array_code[$k_code] = $code;
		}

		return $array_code;
	}
}