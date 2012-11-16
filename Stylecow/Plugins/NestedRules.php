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
 * @version 1.0.2 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Css;

class NestedRules {
	const POSITION = 2;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			if (isset($code->parent->parent) && empty($code->parent->selector->type) && ($parentSelectors = $code->parent->selector->get())) {
				$selectors = $code->selector->get();
				$code->selector->delete();

				foreach ($selectors as $selector) {
					$selector = ($selector[0] == '&') ? substr($selector, 1) : ' '.$selector;

					foreach ($parentSelectors as $parentSelector) {
						$code->selector->add($parentSelector.$selector);
					}
				}
			}

			$firstParent = NestedRules::getRootParent($code);

			if ($firstParent) {
				$firstParent->addChild($code);
			}
		});
	}


	/**
	 * Returns first valid parent where move the nested rules
	 *
	 * @param Stylecow\Css The css object
	 * 
	 * @return Stylecow\Css The parent css object or null
	 */
	static public function getRootParent (Css $css) {
		if ($css->parent === null) {
			return null;
		}

		$parent = $css->parent;

		while (($parent->parent !== null) && empty($parent->selector->type)) {
			$parent = $parent->parent;
		}

		return $parent;
	}
}
