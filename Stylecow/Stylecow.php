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
 * @version 0.3.3 (2012)
 */

namespace Stylecow;

class Stylecow {
	public $code = array();

	private $current_base_path = null;
	private $current_base_url = null;


	/**
	 * Loads a css file and resolves its included css files
	 *
	 * @param string  $file_path  The file to load
	 * @param string  $file_url   The url of the file (required to resolve the url of images or @import)
	 *
	 * @return $this
	 */
	public function load ($file_path, $file_url = null) {
		if (is_null($file_url)) {
			$file_url = $file_path;
		}

		$this->code = array();

		if (!is_file($file_path)) {
			die("'".$file_path."' does not exists");
		}

		$code = file_get_contents($file_path);

		$this->code = $this->resolve($code, $file_path, $file_url);

		return $this;
	}



	/**
	 * Resolves all url() and @import requests and removes the comments
	 *
	 * @param string  $code       The css code to resolve
	 * @param string  $base_path  The base path used to include the import
	 * @param string  $base_url   The base url to fix the urls, etc
	 *
	 * @return string  The resolved code
	 */
	public function resolve ($code, $base_path, $base_url) {
		$current_base_path = $this->current_base_path;
		$current_base_url = $this->current_base_url;

		$this->current_base_path = dirname($base_path);
		$this->current_base_url = dirname($base_url);

		//Remove comments
		$code = preg_replace('|/\*\s*stylecow\s+(.*)\s*\*/|Us', '|$stylecow \\1$|', $code);
		$code = preg_replace('|/\*.*\*/|Us', '', $code);

		//Url
		if (strpos($code, 'url(') !== false) {
			$code = preg_replace_callback('#url\(["\']?([^\)\'"]*)["\']?\)#', array($this, 'urlCallback'), $code);
		}

		//Import
		if (strpos($code, '@import') !== false) {
			$code = preg_replace_callback('/\@import([^;]*);/', array($this, 'importCallback'), $code);
		}

		$this->current_base_path = $current_base_path;
		$this->current_base_url = $current_base_url;

		return $code;
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

		if (parse_url($file, PHP_URL_SCHEME)) {
			return $matches[0];
		}

		if ($file[0] === '/') {
			$file_url = $file_path = $file;
		} else {
			$file_url = preg_replace('#/\w+/\.\./#', '/', $this->current_base_url.'/'.$file);
			$file_path = preg_replace('#/\w+/\.\./#', '/', $this->current_base_path.'/'.$file);
		}

		if (is_file($file_path)) {
			return $this->resolve(file_get_contents($file_path), $file_path, $file_url);
		}

		return $matches[0];
	}



	/**
	 * The callback used in the function resolve() to fix the urls in the url() functions.
	 *
	 * @param string  $matches  The matches of the preg_replace_callback
	 *
	 * @return string  The new code
	 */
	private function urlCallback ($matches) {
		$url = $matches[1];

		if (parse_url($url, PHP_URL_SCHEME) || $url[0] === '/') {
			return 'url(\''.$url.'\')';
		}

		$url = $this->current_base_url.'/'.$url;

		while (preg_match('#/\w+/\.\./#', $url)) {
			$url = preg_replace('#/\w+/\.\./#', '/', $url);
		}

		return 'url(\''.$url.'\')';
	}



	/**
	 * Transform the css code using the plugins
	 *
	 * @param array  $plugins  The list of the plugins to execute
	 *
	 * @return $this
	 */
	public function transform ($plugins) {
		if (is_string($this->code)) {
			$this->code = $this->parse($this->code);
		}

		$plugins_dir = __DIR__.'/Plugins/';
		$plugins_objects = array();
		$plugins_positions = array();

		include_once($plugins_dir.'Plugins_interface.php');

		foreach ((array)$plugins as $plugin => $settings) {
			if (is_int($plugin)) {
				$plugin = $settings;
				$settings = array();
			}

			$plugin = ucfirst($plugin);
			$plugin_file = $plugins_dir.$plugin.'.php';

			if (!is_file($plugin_file)) {
				echo "'$plugin_file' does not exists!";
				die();
			}

			include_once($plugin_file);

			$plugin = '\\Stylecow\\'.$plugin;
			$plugins_objects[$plugin] = new $plugin($this, $settings);
			$plugins_positions[$plugin] = $plugins_objects[$plugin]->position;
		}

		asort($plugins_positions);

		//Execute plugins
		foreach ($plugins_positions as $plugin => $pos) {
			$plugins_objects[$plugin]->transform();
		}

		return $this;
	}



	/**
	 * Search a property name in an array of properties and returns its key.
	 * This function is used by some plugins and other functions to search and replace css properties.
	 *
	 * @param array   $properties  The list of properties. Each property is a subarray with 'name' and 'values' keys.
	 * @param string  $name        The name of the property to search
	 *
	 * @return int/false  The key of the property or false if it's not found
	 */
	public function getPropertyKey ($properties, $name) {
		foreach ($properties as $k => $property) {
			if ($property['name'] === $name) {
				return $k;
			}
		}

		return false;
	}



	/**
	 * Returns the values of a property.
	 * This function is used by some plugins and other functions to access to all values of a property
	 *
	 * @param array   $properties  The list of properties. Each property is a subarray with 'name' and 'values' keys.
	 * @param string  $name        The name of the property to search
	 * @param int     $key         If it's defined, returns just this value, otherwise returns all values.
	 *
	 * @return array/string/false  The value of the property, an array of all values or false if the property is not found
	 */
	public function getProperty ($properties, $name, $key = false) {
		$k = $this->getPropertyKey($properties, $name);

		if ($k === false) {
			return false;
		}

		return ($key === false) ? $properties[$k] : $properties[$k][$key];
	}



	/**
	 * Adds a new property to a list of properties.
	 * This function is used by some plugins and other functions to add or modify css properties
	 *
	 * @param array   &$properties   The list of properties. Each property is a subarray with 'name' and 'values' keys.
	 * @param string  $name          The name of the property to add
	 * @param int     $value         The value of the property
	 * @param int     $replace_mode  The type of the replace mode. 0 = add new without check / 1 = add new or replace if exists / 2 = add new if not exists
	 *
	 * @return bool  True if a new value has been inserted, false otherwise.
	 */
	public function addProperty (&$properties, $name, $value, $replace_mode = 0) {
		switch ($replace_mode) {

			//Add new, no check for duplications
			case 0:
				$properties[] = array(
					'name' => $name,
					'value' => (array)$value
				);
				return true;
			
			//Replace if exists
			case 1:
				$key = $this->getPropertyKey($properties, $name);

				if ($key === false) {
					$properties[] = array(
						'name' => $name,
						'value' => (array)$value
					);
				} else {
					$properties[$key] = array(
						'name' => $name,
						'value' => (array)$value
					);
				}
				return true;
			
			//Add only if doesn't exit
			case 2:
				if ($this->getPropertyKey($properties, $name) === false) {
					if (strpos($name, '-') !== false) {
						$short_name = current(explode('-', $name, 2));
						
						if ($this->getPropertyKey($properties, $short_name) !== false) {
							return true;
						}
					}

					$properties[] = array(
						'name' => $name,
						'value' => (array)$value
					);
				}
				return true;

			//Add only if doesn't exit strictly
			case 3:
				if ($this->getPropertyKey($properties, $name) === false) {
					$properties[] = array(
						'name' => $name,
						'value' => (array)$value
					);
				}
				return true;
		}

		return false;
	}



	/**
	 * Parses the css code into an multidimensional array with all selectors, properties and values.
	 *
	 * @param string  $string_code  The css code to parse
	 *
	 * @return array  The parsed css code
	 */
	public function parse ($string_code) {
		$array_code = array();

		while ($string_code) {
			$pos = strpos($string_code, '{');
			$pos2 = strpos($string_code, ';');

			if (($pos2 !== false) && $pos2 < $pos) {
				$selector = trim(substr($string_code, 0, $pos2));
				$type = '';

				if ($selector[0] == '@' || $selector[0] == '$') {
					$selector = $this->explodeTrim(' ', $selector, 2);
				
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
				$selector = $this->explodeTrim(' ', $selector, 2);
				
				$type = $selector[0];
				$selector = isset($selector[1]) ? $selector[1] : '';
			}

			if ($selector !== '' && $selector[0] === '\\') {
				$selector = substr($selector, 1);
			}

			$selector = $this->explodeTrim(',', $selector);

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
					foreach ($this->explodeTrim(';', $properties_string) as $property) {
						list($n, $v) = $this->explodeTrim(':', $property, 2);

						$this->explodeSettings($v, $settings);

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
					$code['content'] = $this->parse($content_string);
				}

				$array_code[] = $code;

				break;
			}
		}

		return $array_code;
	}



	/**
	 * Explode a string in an array using a delimiter. Ignore the delimiter placed between parenthesis or other characters
	 *
	 * @param string  $delimiter  The delimiter used.
	 * @param string  $string     The string to explode
	 * @param int     $limit      The limit of th explode
	 * @param string  $str_in     The character to start to ignore the delimiter. By default "("
	 * @param string  $str_out    The character to end to ignore the delimiter. By default ")"
	 *
	 * @return array  The exploded array.
	 */
	public function explode ($delimiter, $string, $limit = null, $str_in = '(', $str_out = ')') {
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
	 * Search for all the css functions in a css code, for example scale(1, 1.2)
	 *
	 * @param string  $string  The css code to parse
	 *
	 * @return array  List of all functions found. Each function is an array with the name and all parameters.
	 */
	public function explodeFunctions ($string) {
		$functions = array();

		$parts = $this->explode(' ', $string);

		foreach ($parts as $str) {
			if (($pos = strpos($str, '(')) === false) {
				continue;
			}

			$name = substr($str, 0, $pos);
			
			if (strpos($name, ' ') !== false) {
				$name = substr($name, strrpos($name, ' '));
			}

			$params = substr(trim(substr($str, $pos + 1)), 0, -1);

			if ($params) {
				$params = $this->explode(',', $params);
			} else {
				$params = array();
			}

			$functions[] = array($name, $params);
		}

		return $functions;
	}



	/**
	 * Explode the stylecow settings: css comments with the syntax "stylecow some-custom-settings"
	 *
	 * @param string  &$string    The css string
	 * @param array   &$settings  The found settings will be stored here
	 */
	public function explodeSettings (&$string, &$settings) {
		$settings = array();

		if (strpos($string, '|$') && preg_match('/\|\$stylecow (.*)\$\|/i', $string, $matches)) {
			$string = str_replace($matches[0], '', $string);
			$settings = $this->explodeTrim(',', strtolower($matches[1]));
		}
	}



	/**
	 * Explode a string into an array and trim its value. All empty values will be ignored
	 *
	 * @param string  $delimiter  The delimiter used.
	 * @param string  $text       The string to explode
	 * @param int     $limit      The limit of th explode
	 *
	 * @return array  The exploded array
	 */
	public function explodeTrim ($delimiter, $text, $limit = null) {
		$return = array();

		$explode = $this->explode($delimiter, $text, $limit);

		foreach ($explode as $text_value) {
			$text_value = trim($text_value);

			if ($text_value !== '') {
				$return[] = $text_value;
			}
		}

		return $return;
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
			$tab_selector = str_repeat("\t", $tabs);
			$tab_property = str_repeat("\t", $tabs + 1);
			$type_separator = ",\n".$tab_selector;
			$property_start = ": ";
			$property_end = ";\n";
			$property_separator = ', ';
			$selector_start = " {\n";
			$selector_end = "}\n";
		} else {
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
}
?>