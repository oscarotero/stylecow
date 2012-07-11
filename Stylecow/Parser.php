<?php
/**
 * Stylecow PHP library
 *
 * Parser class. To convert any css string code to css objects/selectors/properties
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow;

class Parser {
	static private $basePath;
	static private $baseUrl;



	/**
	 * Loads a css file gets its content and parse it
	 *
	 * @param string $file The path to file to load
	 *
	 * @return Stylecow\Css The css object with the code parsed
	 */
	static public function parseFile ($file) {
		self::$basePath = (strpos($file, '/') === false) ? '' : dirname($file);
		self::$baseUrl = '';

		if (is_file($file)) {
			$css = self::parseString(file_get_contents($file));
		}

		self::$basePath = self::$baseUrl = null;

		return $css;
	}


	/**
	 * Parse a string
	 *
	 * @param string $string The string to parse
	 *
	 * @return Stylecow\Css The css object
	 */
	static public function parseString ($string) {
		$string = self::resolve($string);

		//Remove comments
		$string = preg_replace('|/\*.*\*/|Us', '', $string);

		return self::parse($string);
	}



	/**
	 * Resolves all url() and @import requests and removes the comments
	 *
	 * @param string $code The css code to resolve
	 *
	 * @return string The resolved code
	 */
	static private function resolve ($code) {
		//Resolve imported images
		if (strpos($code, 'url(') !== false) {
			$code = preg_replace_callback('#url\(["\']?([^\)\'"]*)["\']?\)#', __NAMESPACE__.'\\Parser::urlCallback', $code);
		}

		//Resolve importes styles
		if (!empty(self::$basePath) && (strpos($code, '@import') !== false)) {
			$code = preg_replace_callback('/\@import([^;]*);/', __NAMESPACE__.'\\Parser::importCallback', $code);
		}

		return $code;
	}



	/**
	 * The callback used in the function resolve() to fix the urls in the url() functions.
	 *
	 * @param string $matches The matches of the preg_replace_callback
	 *
	 * @return string The new code
	 */
	static private function urlCallback ($matches) {
		$url = $matches[1];

		if (empty(self::$baseUrl) || parse_url($url, PHP_URL_SCHEME) || $url[0] === '/') {
			return 'url(\''.$url.'\')';
		}

		return 'url(\''.self::fixPath(self::$baseUrl.'/'.$url).'\')';
	}



	/**
	 * The callback used in the function resolve() to replace the @import for the imported file code.
	 * If the url file is absolute (start by http:// or by "/") doesn't replace anything
	 *
	 * @param string $matches The matches of the preg_replace_callback
	 *
	 * @return string The new code
	 */
	static private function importCallback ($matches) {
		$file = trim(str_replace(array('\'', '"', 'url(', ')'), '', $matches[1]));

		if (($file[0] === '/') || parse_url($file, PHP_URL_SCHEME)) {
			return $matches[0];
		}

		$filePath = self::$basePath ? self::$basePath.'/'.$file : $file;
		$fileUrl = self::$baseUrl ? self::$baseUrl.'/'.$file : $file;

		if (is_file($filePath)) {
			$basePath = self::$basePath;
			$baseUrl = self::$baseUrl;

			self::$basePath = (strpos($filePath, '/') === false) ? '' : dirname($filePath);
			self::$baseUrl = (strpos($fileUrl, '/') === false) ? '' : dirname($fileUrl);

			$code = self::$resolve(file_get_contents($filePath));

			self::$basePath = $basePath;
			self::$baseUrl = $baseUrl;

			return $code;
		}

		return $matches[0];
	}


	

	/**
	 * Parses the css code converting to a Css object with all selectors, properties and values.
	 *
	 * @param string $string_code The css code to parse
	 *
	 * @return Stylecow\Css The parsed css code
	 */
	static private function parse ($string_code) {
		$Css = new Css();

		while ($string_code) {
			$pos = strpos($string_code, '{');
			$pos2 = strpos($string_code, ';');

			if (($pos2 !== false) && $pos2 < $pos) {
				list($selector, $type) = self::parseSelector(trim(substr($string_code, 0, $pos2)));

				$Child = $Css->addChild(new Css($selector, $type));

				$string_code = trim(substr($string_code, $pos2+1));
				continue;
			}

			if ($pos === false) {
				break;
			}

			list($selector, $type) = self::parseSelector(trim(substr($string_code, 0, $pos)));

			$string_code = trim(substr($string_code, $pos + 1));
			$length = strlen($string_code);
			$in = 1;

			for ($n = 0; $n <= $length; $n++) {
				$letter = $string_code[$n];

				if ($letter === '{') {
					$in++;
					continue;
				}

				if (($letter !== '}') || (--$in)) {
					continue;
				}

				$Child = $Css->addChild(new Css($selector, $type));

				$string_piece = ($n === 0) ? '' : trim(substr($string_code, 0, $n-1));
				$string_code = trim(substr($string_code, $n+1));
				$pos = strpos($string_piece, '{');

				if ($pos === false) {
					$properties_string = $string_piece;
					$content_string = '';
				} else {
					$pos = strrpos(substr($string_piece, 0, $pos), ';');

					if ($pos !== false) {
						$properties_string = trim(substr($string_piece, 0, $pos + 1));
						$content_string = trim(substr($string_piece, $pos + 1));
					} else {
						$properties_string = '';
						$content_string = $string_piece;
					}
				}

				if ($properties_string) {
					foreach (self::explodeTrim(';', $properties_string) as $property) {
						list($name, $value) = self::explodeTrim(':', $property, 2);

						$Child->addProperty(new Property($name, $value));
					}
				}

				if ($content_string) {
					foreach (self::parse($content_string) as $child) {
						$Child->addChild($child);
					}
				}

				break;
			}
		}

		return $Css;
	}


	/**
	 * Parses the css code of a selector
	 *
	 * @param string $selector The css code to parse
	 *
	 * @return array The parsed css code
	 */
	static private function parseSelector ($selector) {
		$type = '';

		if ($selector[0] === '@') {
			$selector = self::explodeTrim(' ', $selector, 2);

			$type = $selector[0];
			$selector = isset($selector[1]) ? self::explodeTrim(',', $selector[1]) : array();
		} else {
			$selector = self::explodeTrim(',', $selector);
		}

		return array($selector, $type);
	}



	/**
	 * Resolve '//' or '/./' or '/foo/../' in a path
	 *
	 * @param string $path The path to fix
	 *
	 * @return string The fixed path
	 */
	static private function fixPath ($path) {
		$replace = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');

		do {
			$path = preg_replace($replace, '/', $path, -1, $n);
		} while ($n > 0);

		return $path;
	}



	/**
	 * Explode a string in an array using a delimiter. Ignore the delimiter placed between parenthesis or other characters
	 *
	 * @param string $delimiter The delimiter used.
	 * @param string $string The string to explode
	 * @param int $limit The limit of the explode
	 * @param string $str_in The character to start to ignore the delimiter. By default "("
	 * @param string $str_out The character to end to ignore the delimiter. By default ")"
	 *
	 * @return array The exploded array.
	 */
	static public function explode ($delimiter, $string, $limit = null, $str_in = '(', $str_out = ')') {
		if (strpos($string, $str_in) === false) {
			return is_null($limit) ? explode($delimiter, $string) : explode($delimiter, $string, $limit);
		}

		$array = array();
		$delimiter_length = strlen($delimiter);

		while ($string) {
			if (isset($limit) && count($array) === ($limit - 1)) {
				$array[] = $string;

				break;
			}

			if (strpos($string,$delimiter) === false) {
				$array[] = trim($string);
				break;
			}

			for ($n = 0, $in = 0, $length = strlen($string); $n <= $length; $n++) {
				$l = isset($string[$n]) ? $string[$n] : '';

				if ($l === $str_in) {
					$in++;
					continue;
				}

				if ($l === $str_out && $in) {
					$in--;
					continue;
				}

				if (($l === $delimiter || $l === $str_out || $n === $length || ($delimiter_length > 1 && strpos(substr($string, $n), $delimiter) === 0)) && !$in) {
					$array[] = trim(substr($string, 0, $n));
					$string = trim(substr($string, $n + $delimiter_length));

					if ($l === $str_out) {
						break;
					}

					continue 2;
				}
			}

			break;
		}

		return $array;
	}



	/**
	 * Explode a string into an array and trim its value. All empty values will be ignored
	 *
	 * @param string $delimiter The delimiter used.
	 * @param string $text The string to explode
	 * @param int $limit The limit of th explode
	 * @param string $str_in The character to start to ignore the delimiter. By default "("
	 * @param string $str_out The character to end to ignore the delimiter. By default ")"
	 *
	 * @return array  The exploded array
	 */
	static public function explodeTrim ($delimiter, $text, $limit = null, $str_in = '(', $str_out = ')') {
		$return = array();

		foreach (self::explode($delimiter, $text, $limit, $str_in, $str_out) as $text_value) {
			$text_value = trim($text_value);

			if ($text_value !== '') {
				$return[] = $text_value;
			}
		}

		return $return;
	}



	/**
	 * Search for all the css functions in a css code, for example scale(1, 1.2) and execute a callback
	 *
	 * @param string $string The css code to parse
	 * @param string $function If it's defined, only apply the callback to the function specified
	 * @param callable $callback The function to execute. Will have three arguments: The css function parameters, the name and an optional argument passed
	 * @param mixed $argument An optional third argument to pass to the callback
	 *
	 * @return array  List of all functions found. Each function is an array with the name and all parameters.
	 */
	static public function executeFunctions ($string, $function, $callback, $argument = null) {
		if ((strpos($string, '(') === false) || (isset($function) && strpos($string, $function.'(') === false)) {
			return $string;
		}

		$length = strlen($string);
		$index = 0;

		while ($index < $length) {
			if (($index = strpos($string, '(', $index)) === false) {
				break;
			}

			$name = preg_match('/([\w-]+)$/', substr($string, 0, $index), $matches);
			$name = $matches[1];

			if (isset($function) && ($name !== $function)) {
				$index++;
				continue;
			}

			$start = $index - strlen($name);

			for ($end = $index, $in = 0; $end <= $length; $end++) {
				$l = isset($string[$end]) ? $string[$end] : '';

				if ($l === '(') {
					$in++;
					continue;
				}

				if ($l === ')' && $in) {
					$in--;
					
					if (!$in) {
						break;
					}
				}
			}

			$parameters = substr($string, $index + 1, $end - $index - 1);
			$result = $callback(empty($parameters) ? array() : self::explodeTrim(',', $parameters), $name, $argument);

			if (isset($result)) {
				$string = substr_replace($string, $result, $start, ($end - $start + 1));
				$length = strlen($string);

				if (strpos($result, '(') === false) {
					$index = $start + strlen($result);
				} else {
					$index = $start + strpos($result, '(');
				}
			}

			$index++;
		}

		return $string;
	}
}
?>