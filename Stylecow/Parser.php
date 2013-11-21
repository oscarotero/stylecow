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
 * @version 2.0.1 (2013)
 */

namespace Stylecow;


class Parser {

	/**
	 * Loads a css file gets its content and parse it
	 *
	 * @param string $file The path to file to load
	 *
	 * @return Stylecow\Css The css object with the code parsed or false if file doesn't exist
	 */
	static public function parseFile ($file, $contextFile = '') {
		$file = stream_resolve_include_path($file);

		if (!is_file($file)) {
			return false;
		}

		$code = file_get_contents($file);

		return self::parse($code, $file, $contextFile);
	}


	/**
	 * Parse a string
	 *
	 * @param string $string The string to parse
	 *
	 * @return Stylecow\Css The css object
	 */
	static public function parseString ($code, $contextFile = '') {
		return self::parse($code, '', $contextFile);
	}


	/**
	 * Resolves all relative url() requests
	 *
	 * @param string $code The css code to resolve
	 *
	 * @return string The resolved code
	 */
	static private function parseImport ($code, $filename, $contextFile) {
		if (!preg_match('/\@import([^;]*)/', $code, $matches)) {
			return $code;
		}

		$file = trim(str_replace(array('\'', '"', 'url(', ')'), '', $matches[1]));

		if (($file[0] === '/') || parse_url($file, PHP_URL_SCHEME)) {
			return $matches[0];
		}

		$importedFilename = ($filename ? dirname($filename) : '').'/'.$file;

		if (($relFile = dirname($file)) !== '.') {
			$contextFile .= $relFile.'/';
		}

		$Css = self::parseFile($importedFilename, $contextFile);

		if (!empty($contextFile)) {
			$Css->applyPlugins(array('BaseUrl' => $contextFile));
		}

		return $Css;
	}


	/**
	 * Parses the css code converting to a Css object with all selectors, properties and values.
	 *
	 * @param string $string_code The css code to parse
	 * @param string $filename The original filename (used to import relative files)
	 *
	 * @return Stylecow\Css The parsed css code
	 */
	static private function parse ($string_code, $filename = null, $contextFile = null) {
		if ($filename) {
			$relativePath = ($contextFile ? substr($filename, strlen($contextFile)) : pathinfo($filename, PATHINFO_BASENAME));
		} else {
			$relativePath = '';
		}

		$Css = $Child = new Css();

		$status = array('selector');
		$buffer = '';

		$code = explode("\n", str_replace("\n\r", "\n", $string_code));
		array_unshift($code, '');

		foreach ($code as $line => $string_line) {
			if (empty($string_line)) {
				continue;
			}

			$col = 0;
			$length = strlen($string_line);
			$char = $previousChar = null;
			$nextChar = $string_line[$col];

			while ($col < $length) {
				$previousChar = $char;
				$char = $nextChar;
				$col ++;
				$nextChar = ($col === $length) ? null : $string_line[$col];

				switch ($char) {
					case '"':
						switch ($status[0]) {
							case 'doubleQuote':
								$buffer .= $char;

								if ($previousChar !== '\\') {
									array_shift($status);
								}
								break;

							case 'simpleQuote':
								$buffer .= $char;
								break;

							case 'selector':
							case 'properties':
								$buffer .= $char;
								array_unshift($status, 'doubleQuote');
						}
						break;

					case "'":
						switch ($status[0]) {
							case 'simpleQuote':
								$buffer .= $char;

								if ($previousChar !== '\\') {
									array_shift($status);
								}
								break;

							case 'doubleQuote':
								$buffer .= $char;
								break;

							case 'selector':
							case 'properties':
								$buffer .= $char;
								array_unshift($status, 'simpleQuote');
						}
						break;

					case '{':
						switch ($status[0]) {
							case 'selector':
							case 'properties':
								$Child = $Child->addChild(new Css(Selector::createFromString($buffer)))->setSourceMap($line, $col, $relativePath);
								array_unshift($status, 'properties');
								$buffer = '';
								break;
						}
						break;

					case '}':
						switch ($status[0]) {
							case 'properties':
								if (trim($buffer)) {
									$Child->addProperty(Property::createFromString($buffer))->setSourceMap($line, $col, $relativePath);
								}

								$buffer = '';
								array_shift($status);
								$Child = $Child->parent;

								break;
						}
						break;

					case ';':
						switch ($status[0]) {
							case 'selector':
								if ((strpos($buffer, '@import') === false) || !is_object($Children = self::parseImport($buffer, $filename, $contextFile))) {
									$Child->addChild(new Css(Selector::createFromString($buffer)))->setSourceMap($line, $col, $relativePath);
								} else {
									foreach ($Children->getChildren() as $Each) {
										$Child->addChild($Each);
									}
								}

								$buffer = '';
								break;

							case 'properties':
								$Child->addProperty(Property::createFromString($buffer))->setSourceMap($line, $col, $relativePath);
								$buffer = '';
								break;
						}
						break;

					case '/':
						if ($status[0] !== 'comment') {
							if ($nextChar === '*') {
								array_unshift($status, 'comment');
								$col++;
                                
                                $nextNextChar = ($col === $length) ? null : $string_line[$col];
                                if ($nextNextChar === '/') {
                                    $col++;
                                }
							} else {
								$buffer .= $char;
							}
						} else if ($previousChar === '*') {
							array_shift($status);
						}
						break;

					default:
						if (!isset($status[0])) {
							$status = array('selector');
						}
						
						if ($status[0] !== 'comment') {
							$buffer .= $char;
						}
				}
			}
		}

		return $Css;
	}



	/**
	 * Explode a string in an array using a delimiter. Ignore the delimiter placed between parenthesis or other characters
	 *
	 * @param string $delimiter The delimiter used.
	 * @param string $string The string to explode
	 * @param int $limit The limit of the explode
	 * @param array $str_in The characters to start to ignore the delimiter. By default "("
	 * @param array $str_out The characters to end to ignore the delimiter. By default ")"
	 *
	 * @return array The exploded array.
	 */
	static public function explode ($delimiter, $string, $limit = null, array $strs_in = array('(', '"', "'"), array $strs_out = array(')', '"', "'")) {
		$exists = false;

		foreach ($strs_in as $str_in) {
			if (strpos($string, $str_in) !== false) {
				$exists = true;
				break;
			}
		}

		if (!$exists) {
			return is_null($limit) ? explode($delimiter, $string) : explode($delimiter, $string, $limit);
		}

		$array = array();
		$delimiter_length = strlen($delimiter);
		$str_out = array();

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

				if (isset($str_out[0]) && ($l === $str_out[0]) && $in) {
					array_shift($str_out);
					$in--;
					continue;
				}

				if (($k = array_search($l, $strs_in, true)) !== false) {
					array_unshift($str_out, $strs_out[$k]);
					$in++;
					continue;
				}

				if (($in === 0) && ($l === $delimiter || $n === $length || (isset($str_out[0]) && $l === $str_out[0]) || ($delimiter_length > 1 && strpos(substr($string, $n), $delimiter) === 0))) {
					$array[] = trim(substr($string, 0, $n));
					$string = trim(substr($string, $n + $delimiter_length));

					if (isset($str_out[0]) && ($l === $str_out[0])) {
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
	 * @param string $string The string to explode
	 * @param int $limit The limit of th explode
	 * @param array $str_in The characters to start to ignore the delimiter. By default "("
	 * @param array $str_out The characters to end to ignore the delimiter. By default ")"
	 *
	 * @return array  The exploded array
	 */
	static public function explodeTrim ($delimiter, $string) {
		$return = array();

		foreach (call_user_func_array(array('Stylecow\\Parser', 'explode'), func_get_args()) as $text_value) {
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

			if (isset($function)) {
				if (preg_match('/(^|[^\w-])('.preg_quote($function).')$/', substr($string, 0, $index), $matches) !== 1) {
					$index++;
					continue;
				}
				$name = $matches[2];
			} else {
				$name = preg_match('/([\w-]+)$/', substr($string, 0, $index), $matches);
				$name = $matches[1];
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
