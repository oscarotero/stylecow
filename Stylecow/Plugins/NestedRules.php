<?php
/**
 * Stylecow PHP library
 *
 * Nested_rules plugin
 * Allows write css in a tree way
 *
 * Examples:
 * article.post {
 *   header {
 *   }
 * }
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class NestedRules extends Plugin implements PluginsInterface {
	static protected $position = 2;


	/**
	 * Search for color() function and execute it
	 *
	 * @param array $array_code The piece of the parsed css code
	 *
	 * @return array The transformed code
	 */
	public function transform (array $array_code) {
		return $this->transformRecursively($array_code);
	}


	/**
	 * Private function to transform recursively the parsed css code
	 *
	 * @param array  $array_code        The piece of the parsed css code
	 * @param array  $parent_selectors  The parent selectors to joint
	 *
	 * @return array  The transformed code
	 */
	private function transformRecursively ($array_code, $parent_selectors = array()) {
		$new_array_code = array();

		foreach ($array_code as $k_code => $code) {
			if ($parent_selectors && $code['is_css']) {
				$selectors = $code['selector'];
				$code['selector'] = array();

				foreach ($selectors as $selector) {
					$selector = ($selector[0] == '&') ? substr($selector, 1) : ' '.$selector;

					foreach ($parent_selectors as $parent_selector) {
						$code['selector'][] = $parent_selector.$selector;
					}
				}
			}

			if ($code['content'] && !$code['type']) {
				$new_code = $this->transformRecursively($code['content'], $code['selector']);
				$code['content'] = array();

				$new_array_code[] = $code;
				$new_array_code = array_merge($new_array_code, $new_code);

				$new_array_code[0]['content'] = array();

				continue;
			}

			if ($code['content']) {
				$code['content'] = $this->transformRecursively($code['content']);
			}

			$new_array_code[] = $code;
		}

		return $new_array_code;
	}
}