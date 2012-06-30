<?php
/**
 * Stylecow PHP library
 *
 * Core class
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow;

class Stylecow {
	const PROPERTY_ADD = 0;
	const PROPERTY_REPLACE = 1;
	const PROPERTY_IF_FAMILY_UNDEFINED = 2;
	const PROPERTY_IF_UNDEFINED = 3;
	const PROPERTY_APPEND = 4;

	private $code = array();
	private $basePath;
	private $baseUrl;



	/**
	 * Loads a css file and resolves its included css files
	 *
	 * @param string $file The file to load
	 *
	 * @return $this
	 */
	public function load ($file) {
		$this->basePath = (strpos($file, '/') === false) ? '' : dirname($file);
		$this->baseUrl = '';
		$this->code = array();

		if (!is_file($file)) {
			$this->code[] = array('comment' => "Stylecow error: '$file' does not exists");

			return $this;
		}

		$this->code = $this->resolve(file_get_contents($file));

		return $this;
	}



	/**
	 * Resolves all url() and @import requests and removes the comments
	 *
	 * @param string $code The css code to resolve
	 *
	 * @return string The resolved code
	 */
	private function resolve ($code) {

		//Remove comments
		$code = preg_replace('|/\*.*\*/|Us', '', $code);

		//Resolve imported images
		if (strpos($code, 'url(') !== false) {
			$code = preg_replace_callback('#url\(["\']?([^\)\'"]*)["\']?\)#', array($this, 'urlCallback'), $code);
		}

		//Resolve importes styles
		if (strpos($code, '@import') !== false) {
			$code = preg_replace_callback('/\@import([^;]*);/', array($this, 'importCallback'), $code);
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
	private function urlCallback ($matches) {
		$url = $matches[1];

		if (empty($this->baseUrl) || parse_url($url, PHP_URL_SCHEME) || $url[0] === '/') {
			return 'url(\''.$url.'\')';
		}

		return 'url(\''.self::fixPath($this->baseUrl.'/'.$url).'\')';
	}



	/**
	 * The callback used in the function resolve() to replace the @import for the imported file code.
	 * If the url file is absolute (start by http://) doesn't replace anything
	 *
	 * @param string  $matches  The matches of the preg_replace_callback
	 *
	 * @return string  The new code
	 */
	private function importCallback ($matches) {
		$file = trim(str_replace(array('\'', '"', 'url(', ')'), '', $matches[1]));

		if (($file[0] === '/') || parse_url($file, PHP_URL_SCHEME)) {
			return $matches[0];
		}

		$filePath = $this->basePath ? $this->basePath.'/'.$file : $file;
		$fileUrl = $this->baseUrl ? $this->baseUrl.'/'.$file : $file;

		if (is_file($filePath)) {
			$basePath = $this->basePath;
			$baseUrl = $this->baseUrl;

			$this->basePath = (strpos($filePath, '/') === false) ? '' : dirname($filePath);
			$this->baseUrl = (strpos($fileUrl, '/') === false) ? '' : dirname($fileUrl);

			$code = $this->resolve(file_get_contents($filePath));

			$this->basePath = $basePath;
			$this->baseUrl = $baseUrl;

			return $code;
		}

		return $matches[0];
	}


	
	/**
	 * Returns the array with the code parsed
	 *
	 * @return array The parsed code
	 */
	public function getParsedCode () {
		if (is_string($this->code)) {
			$this->code = $this->parse($this->code);
		}

		return $this->code;
	}



	/**
	 * Set new parsed code
	 *
	 * @param array $code The new parsed code
	 * 
	 * @return array The parsed code
	 */
	public function setParsedCode (array $code) {
		$this->code = $code;
	}



	/**
	 * Transform the css code using the plugins
	 *
	 * @param array  $plugins  The list of the plugins to execute
	 *
	 * @return $this
	 */
	public function transform ($plugins) {
		$plugins_objects = array();
		$plugins_positions = array();

		foreach ((array)$plugins as $plugin => $settings) {
			if (is_int($plugin)) {
				$plugin = $settings;
				$settings = array();
			}

			if (!class_exists($plugin)) {
				echo "'$plugin' does not exists!";
				die();
			}

			$plugins_objects[$plugin] = new $plugin($settings);
			$plugins_positions[$plugin] = $plugins_objects[$plugin]->getPosition();
		}

		asort($plugins_positions);

		$code = $this->getParsedCode();

		foreach ($plugins_positions as $plugin => $pos) {
			$result = $plugins_objects[$plugin]->transform($code);

			if (isset($result)) {
				$code = $result;
			}
		}

		$this->setParsedCode($code);

		return $this;
	}




	/**
	 * Send the content-type header and output the css
	 *
	 * @param array  $options  Options to export (minify, browser filter, etc)
	 */
	public function show ($options = null) {
		header('Content-type: text/css');

		echo $this->toString($options);

		die();
	}



	/**
	 * Convert the parsed and transformed code to css code and returns it.
	 *
	 * @param boolean  $options  Options to the css code (filter by vendor prefixes and minify)
	 *
	 * @return string  The css code
	 */
	public function toString (array $options = null) {
		if (is_string($this->code)) {
			$this->code = $this->parse($this->code);
		}

		$current_options = array(
			'browser' => null,
			'minify' => null
		);

		if (isset($options)) {
			$current_options = array_replace($current_options, $options);
		}

		return $this->_toString($this->code, $current_options['minify'] ? null : 0, $current_options['browser'], '');
	}



	
	/**
	 * Private function executed recursively that converts the parsed code into a css code
	 *
	 * @param array   $array_code      The piece of parsed code to convert to string
	 * @param int     $tabs            The number of tabulations. Null to minify the css
	 * @param string  $browser         The browser filter
	 * @param string  $parent_browser  The parent browser filter
	 *
	 * @return string  The css code
	 */
	private function _toString ($array_code, $tabs = 0, $browser, $parent_browser) {
		$text = '';

		if (isset($tabs)) {
			$comments = true;
			$tab_selector = str_repeat("\t", $tabs);
			$tab_property = str_repeat("\t", $tabs + 1);
			$type_separator = ",\n".$tab_selector;
			$property_start = ": ";
			$property_end = ";\n";
			$property_separator = ', ';
			$selector_start = " {\n";
			$selector_end = "}\n";
		} else {
			$comments = false;
			$tab_selector = '';
			$tab_property = '';
			$type_separator = ',';
			$property_start = ':';
			$property_end = ';';
			$property_separator = ',';
			$selector_start = '{';
			$selector_end = '}';
		}
		
		foreach ($array_code as $code) {
			if (isset($code['comment']) && $comments) {
				$text .= $tab_selector.'/* '.$code['comment'].' */';
			}

			if (!$code['is_css'] || ($browser === '' && isset($code['browser']) && $code['browser'])) {
				continue;
			}

			if ($code['type']) {
				$selector = trim($code['type'].' '.implode($type_separator, $code['selector']));
			} else {
				$selector = implode($type_separator, $code['selector']);
			}

			if (isset($code['properties'])) {
				$text_properties = '';

				foreach ($code['properties'] as $property) {
					if ($browser && (isset($code['browser']) && $code['browser'] !== $browser) && (isset($property['browser']) && $property['browser'] !== $browser) && ($parent_browser !== $browser)) {
						continue;
					} else if ($browser === '' && isset($property['browser']) && $property['browser']) {
						continue;
					}

					$text_properties .= $tab_property.$property['name'].$property_start.implode($property_separator, $property['value']).$property_end;
				}

				if ($code['content']) {
					$text_properties .= $this->_toString($code['content'], isset($tabs) ? ($tabs + 1) : null, $browser, isset($code['browser']) ? $code['browser'] : null);
				}

				if ($text_properties) {
					$text .= $tab_selector.$selector.$selector_start.$text_properties.$tab_selector.$selector_end;
				}
			} else if (!$browser || (isset($code['browser']) && $code['browser'] === $browser) || ($parent_browser === $browser)) {
				$text .= $tab_selector.$selector.$property_end;
			}
		}

		return $text;
	}



	/**
	 * Utils: Parses the css code into an multidimensional array with all selectors, properties and values.
	 *
	 * @param string  $string_code  The css code to parse
	 *
	 * @return array  The parsed css code
	 */
	static public function parse ($string_code) {
		$array_code = array();

		while ($string_code) {
			$pos = strpos($string_code, '{');
			$pos2 = strpos($string_code, ';');

			if (($pos2 !== false) && $pos2 < $pos) {
				$selector = trim(substr($string_code, 0, $pos2));
				$type = '';

				if ($selector[0] == '@' || $selector[0] == '$') {
					$selector = self::explodeTrim(' ', $selector, 2);
				
					$type = $selector[0];
					$selector = isset($selector[1]) ? $selector[1] : '';
				}

				$array_code[] = array(
					'selector' => array($selector),
					'type' => $type,
					'is_css' => ($type[0] === '$') ? false : true,
					'content' => array()
				);

				$string_code = trim(substr($string_code, $pos2+1));
				continue;
			}

			if ($pos === false) {
				break;
			}

			$selector = trim(substr($string_code, 0, $pos));
			$type = '';

			if ($selector[0] === '@' || $selector[0] === '$') {
				$selector = self::explodeTrim(' ', $selector, 2);
				
				$type = $selector[0];
				$selector = isset($selector[1]) ? $selector[1] : '';
			}

			if ($selector !== '' && $selector[0] === '\\') {
				$selector = substr($selector, 1);
			}

			$selector = self::explodeTrim(',', $selector);

			$string_code = trim(substr($string_code, $pos + 1));
			$length = strlen($string_code);
			$in = 1;

			for ($n = 0; $n <= $length; $n++) {
				$letter = $string_code[$n];

				if ($letter === '{') {
					$in++;
					continue;
				}

				if ($letter !== '}') {
					continue;
				}

				$in--;

				if ($in) {
					continue;
				}

				$string_piece = $n ? trim(substr($string_code, 0, $n-1)) : '';
				$string_code = trim(substr($string_code, $n+1));

				$code = array(
					'selector' => $selector,
					'type' => $type,
					'is_css' => true,
					'properties' => array(),
					'content' => array()
				);

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
						list($n, $v) = self::explodeTrim(':', $property, 2);

						$code['properties'][] = array(
							'name' => $n,
							'value' => $v === '' ? array() : array($v),
							'settings' => $settings
						);
					}

					if ($code['type'] && $code['type'][0] === '$') {
						$code['is_css'] = false;
					}
				}

				if ($content_string) {
					$code['content'] = self::parse($content_string);
				}

				$array_code[] = $code;

				break;
			}
		}

		return $array_code;
	}



	/**
	 * Utils: resolve '//' or '/./' or '/foo/../' in a path
	 *
	 * @var string $path The path to fix
	 *
	 * @return string The fixed path
	 */
	static public function fixPath ($path) {
		$replace = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');

		do {
			$path = preg_replace($replace, '/', $path, -1, $n);
		} while ($n > 0);

		return $path;
	}



	/**
	 * Utils: Search a property name in an array of properties and returns its key.
	 *
	 * @param array $properties The list of properties. Each property is a subarray with 'name' and 'values' keys.
	 * @param string $name The name of the property to search
	 *
	 * @return int/false The key of the property or false if it's not found
	 */
	static public function searchProperty ($properties, $name) {
		foreach ($properties as $k => $property) {
			if ($property['name'] === $name) {
				return $k;
			}
		}

		return false;
	}



	/**
	 * Utils: Search for a specific selector and returns an array with the keys of the code.
	 *
	 * @param array $array_code The parsed code
	 * @param array $selectors The exact selectors to search
	 *
	 * @return int/false The key of the code or false if the selector had not been found
	 */
	static public function searchBySelectors ($array_code, array $selectors) {
		$keys = array();

		foreach ($array_code as $key => $code) {
			if ($code['selector']) {
				foreach ($code['selector'] as $selector) {
					if (in_array($selector, $selectors)) {
						$keys[] = $key;

						continue 2;
					}
				}
			}
		}

		return $keys;
	}



	/**
	 * Utils: Returns the values of a property.
	 *
	 * @param array $properties The list of properties. Each property is a subarray with 'name' and 'values' keys.
	 * @param string $name The name of the property to search
	 * @param int $key If it's defined, returns just this value, otherwise returns all values.
	 *
	 * @return array/string/false The value of the property, an array of all values or false if the property is not found
	 */
	static public function getValue ($properties, $name, $key = null) {
		$k = self::searchProperty($properties, $name);

		if ($k === false) {
			return false;
		}

		return isset($key) ? $properties[$k]['value'][$key] : $properties[$k]['value'];
	}



	/**
	 * Utils: Adds a new property to a list of properties.
	 *
	 * @param array &$properties The list of properties. Each property is a subarray with 'name' and 'values' keys.
	 * @param string $name The name of the property to add
	 * @param int $value The value of the property
	 * @param int $replace_mode The type of the replace mode
	 * @param string $browser Optional browser value
	 *
	 * @return bool True if a new value has been inserted, false otherwise.
	 */
	static public function addProperty (&$properties, $name, $value, $replace_mode = 0, $browser = null) {
		switch ($replace_mode) {

			case self::PROPERTY_ADD:
				$properties[] = array(
					'name' => $name,
					'value' => (array)$value,
					'browser' => $browser
				);

				return true;
			
			case self::PROPERTY_REPLACE:
				if (($key = self::searchProperty($properties, $name)) === false) {
					$properties[] = array(
						'name' => $name,
						'value' => (array)$value,
						'browser' => $browser
					);
				} else {
					$properties[$key] = array(
						'name' => $name,
						'value' => (array)$value,
						'browser' => $browser
					);
				}

				return true;
			
			case self::PROPERTY_IF_FAMILY_UNDEFINED:
				if (self::searchProperty($properties, $name) === false) {
					if (strpos($name, '-') !== false) {
						$short_name = current(explode('-', $name, 2));
						
						if (self::searchProperty($properties, $short_name) !== false) {
							return true;
						}
					}

					$properties[] = array(
						'name' => $name,
						'value' => (array)$value,
						'browser' => $browser
					);
				}
				return true;

			case self::PROPERTY_IF_UNDEFINED:
				if (self::searchProperty($properties, $name) === false) {
					$properties[] = array(
						'name' => $name,
						'value' => (array)$value,
						'browser' => $browser
					);
				}
				return true;

			case self::PROPERTY_APPEND:
				if (($key = self::searchProperty($properties, $name)) === false) {
					$properties[] = array(
						'name' => $name,
						'value' => (array)$value,
						'browser' => $browser
					);
				} else {
					$properties[$key]['value'][] = $value;
				}
				return true;
		}

		return false;
	}



	/**
	 * Utils: Explode a string in an array using a delimiter. Ignore the delimiter placed between parenthesis or other characters
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

		while ($string) {
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

				if (($l === $delimiter || $l === $str_out || $n === $length) && !$in) {
					$array[] = trim(substr($string, 0, $n));
					$string = trim(substr($string, $n+1));

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
	 * Utils: Explode a string into an array and trim its value. All empty values will be ignored
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

		$explode = self::explode($delimiter, $text, $limit, $str_in, $str_out);

		foreach ($explode as $text_value) {
			$text_value = trim($text_value);

			if ($text_value !== '') {
				$return[] = $text_value;
			}
		}

		return $return;
	}



	/**
	 * Utils: Search for all the css functions in a css code, for example scale(1, 1.2) and execute a callback
	 *
	 * @param string $string The css code to parse
	 * @param string $function If it's defined, only apply the callback to the function specified
	 * @param callable $callback The function to execute
	 *
	 * @return array  List of all functions found. Each function is an array with the name and all parameters.
	 */
	static public function executeFunctions ($string, $function, $callback) {
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
			$result = $callback(empty($parameters) ? array() : self::explodeTrim(',', $parameters), $name);

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


	/**
	 * Utils: Execute the callback for each level of css code
	 *
	 * @param array $array_code The parsed code
	 * @param callable $callback The function to execute. It has two argument with the code data and the depth
	 * @param boolean $recursive True to execute recursively
	 *
	 * @return array  The executed array
	 */
	static public function walk ($array_code, $callback, $depth = 0) {
		$new_code = $callback($array_code, $depth);

		if (isset($new_code)) {
			$array_code = $new_code;
		}

		++$depth;

		foreach ($array_code as &$code) {
			if ($code['content']) {
				$code['content'] = self::walk($code['content'], $callback, $depth);
			}
		}

		return $array_code;
	}


	/**
	 * Utils: Execute the callback for each group of selectors in the array of the parsed code
	 *
	 * @param array $array_code The parsed code
	 * @param callable $callback The function to execute. It has just one argument with the properties data
	 * @param boolean $recursive True to execute recursively
	 *
	 * @return array  The executed array
	 */
	static public function selectorsWalk ($array_code, $callback, $recursive = true) {
		foreach ($array_code as &$code) {
			if ($code['selector'] && $code['is_css']) {
				$new_selector = $callback($code['selector']);

				if (isset($new_selector)) {
					$code['selector'] = $new_selector;
				}
			}

			if ($recursive === true && $code['content']) {
				$code['content'] = self::selectorsWalk($code['content'], $callback, $recursive);
			}
		}

		return $array_code;
	}


	/**
	 * Utils: Execute the callback for each properties key in the array of the parsed code
	 *
	 * @param array $array_code The parsed code
	 * @param callable $callback The function to execute. It has just one argument with the properties data
	 * @param boolean $recursive True to execute recursively
	 *
	 * @return array  The executed array
	 */
	static public function propertiesWalk ($array_code, $callback, $recursive = true) {
		foreach ($array_code as &$code) {
			if ($code['properties']) {
				$new_properties = $callback($code['properties']);

				if (isset($new_properties)) {
					$code['properties'] = $new_properties;
				}
			}

			if ($recursive === true && $code['content']) {
				$code['content'] = self::propertiesWalk($code['content'], $callback, $recursive);
			}
		}

		return $array_code;
	}


	/**
	 * Utils: Execute the callback for each individual value in the array of the parsed code
	 *
	 * @param array $array_code The parsed code
	 * @param callable $callback The function to execute. It has two arguments, the value and the name
	 * @param boolean $recursive True to execute recursively
	 *
	 * @return array  The executed array
	 */
	static public function valueWalk ($properties, $callback) {
		foreach ($properties as &$property) {
			foreach ($property['value'] as &$value) {
				$new_value = $callback($value, $property['name']);

				if (isset($new_value)) {
					$value = $new_value;
				}
			}
		}

		return $properties;
	}
}
?>