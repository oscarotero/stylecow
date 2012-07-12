<?php
/**
 * Stylecow PHP library
 *
 * MediaQuery plugin
 * Filter the styles for a specifi media-query
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

class MediaQuery {
	const POSITION = 10;

	static private $types = array('braille', 'embossed', 'handheld', 'print', 'projection', 'screen', 'speech', 'tty', 'tv');
	static private $expressions = array(
		'color' => array(
			'min-max' => true,
			'value' => 'int'
		),
		'color-index' => array(
			'min-max' => true,
			'value' => 'int'
		),
		'aspect-ratio' => array(
			'min-max' => true,
			'value' => 'ratio'
		),
		'device-aspect-ratio' => array(
			'min-max' => true,
			'value' => 'ratio'
		),
		'device-height' => array(
			'min-max' => true,
			'value' => 'valuePx'
		),
		'device-width' => array(
			'min-max' => true,
			'value' => 'valuePx'
		),
		'grid' => array(
			'min-max' => false,
			'value' => 'bool'
		),
		'height' => array(
			'min-max' => true,
			'value' => 'valuePx'
		),
		'monochrome' => array(
			'min-max' => true,
			'value' => 'int'
		),
		'orientation' => array(
			'min-max' => false,
			'value' => array('landscape', 'portrait')
		),
		'resolution' => array(
			'min-max' => true,
			'value' => 'resolution'
		),
		'scan' => array(
			'min-max' => false,
			'value' => array('progressive', 'interlace')
		),
		'width' => array(
			'min-max' => true,
			'value' => 'valuePx'
		)
	);


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 * @param int $rem The default value for rem. This value can be overwritten for the font-face property in :root, html or body
	 */
	static public function apply (Css $css, array $browser = null) {
		if (!$browser) {
			return;
		}

		foreach ($css->getArrayCopy() as $child) {
			if ($child->selector->type !== '@media') {
				continue;
			}

			if (self::checkMediaQueries($child->selector->get(), $browser)) {
				foreach ($child->getChildren() as $k => $grandChild) {
					$child->parent->addChild($grandChild, $child->getPositionInParent() + $k);
				}
			}

			$child->removeFromParent();
		}
	}


	static public function checkMediaQueries (array $selectors, array $browser) {
		$selectors = self::parseMediaQueries($selectors);

		foreach ($selectors as $selector) {
			if (isset($browser['type']) && isset($selector['type']) && $browser['type'] !== 'all' && $selector['type'] !== 'all') {
				if (($browser['type'] !== $selector['type']) && !isset($selector['not'])) {
					continue;
				}
			}

			foreach (self::$expressions as $expression => $settings) {
				if (!isset($browser[$expression])) {
					continue;
				}

				$browser[$expression] = self::$settings['value']($browser[$expression]);

				if (isset($selector[$expression]) && ($browser[$expression] !== $selector[$expression])) {
					continue 2;
				}

				if ($settings['min-max']) {
					if (isset($selector['max-'.$expression]) && ($browser[$expression] > $selector['max-'.$expression])) {
						continue 2;
					}

					if (isset($selector['min-'.$expression]) && ($browser[$expression] < $selector['min-'.$expression])) {
						continue 2;
					}
				}
			}

			return true;
		}

		return false;
	}


	static public function parseMediaQueries (array $selectors) {
		$parsed = array();

		foreach ($selectors as $k => $selector) {
			$parsed[$k] = array();

			foreach (Parser::explodeTrim(' and ', strtolower($selector)) as $rule) {
				$expressions = Parser::explodeTrim(' ', $rule);

				while ($expressions) {
					$expression = array_shift($expressions);

					if (($expression[0] === '(') && (substr($expression, -1) === ')')) {
						$expression = trim(substr($expression, 1, -1));
					}

					if ($expression === 'not') {
						$parsed[$k]['not'] = true;
						continue;
					}

					if ($expression === 'only') {
						$parsed[$k]['only'] = true;
						continue;
					}

					if ($expression === 'all') {
						continue;
					}

					if (in_array($expression, self::$types)) {
						$parsed[$k]['type'] = $expression;
						continue;
					}

					$expression = Parser::explodeTrim(':', $expression, 2);
					$name = $expression[0];
					$value = isset($expression[1]) ? $expression[1] : true;

					if (isset(self::$expressions[$name])) {
						$fn = self::$expressions[$name]['value'];
						$parsed[$k][$name] = self::$fn($value);
						continue;
					}

					if (((strpos($name, 'min-') === 0) || (strpos($name, 'max-') === 0)) && ($subname = substr($name, 4)) && isset(self::$expressions[$subname]) && (self::$expressions[$subname]['min-max'] === true)) {
						$fn = self::$expressions[$subname]['value'];
						$parsed[$k][$name] = self::$fn($value);
						continue;
					}
				}
			}
		}

		return $parsed;
	}

	private static function valuePx ($value) {
		return intval($value);
	}
}