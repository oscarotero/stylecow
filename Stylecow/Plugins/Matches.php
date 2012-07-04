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

use Stylecow\Parser;
use Stylecow\Css;

class Matches {
	const POSITION = 2;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			while (strpos((string)$code->selector, ':matches(') !== false) {
				foreach ($code->selector->get() as $key => $selector) {
					if (preg_match('/:matches\(([^\)]*)\)/', $selector, $match)) {
						$code->selector->delete($key);

						foreach (Parser::explodeTrim(',', $match[1]) as $matchSelector) {
							$code->selector->add(str_replace($match[0], $matchSelector, $selector));
						}
					}
				}
			}
		});
	}
}