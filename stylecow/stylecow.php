<?php
/**
* styleCow php library (version 0.1)
*
* 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*
* styleCow is released under the GNU Affero GPL version 3.
* More information at http://www.gnu.org/licenses/agpl-3.0.html
*/

namespace stylecow;

class Stylecow {
	public $file;
	public $code = array();


	/**
	 * public function load (string $file)
	 *
	 * Load a css file and parse it
	 *
	 * return boolean
	 */
	public function load ($file) {
		$this->file = '';
		$this->code = array();

		if (!is_file($file)) {
			echo "'%s' does not exists";
			die();
		}

		$this->file = $file;

		$code = file_get_contents($this->file);

		//Remove comments and spaces
		$code = preg_replace('|/\*.*\*/|Us', '', $code);
		$code = preg_replace('/(\s{2,}|\r|\n)/', ' ', $code);

		//Resolve @import
		while (strpos($code, '@import') !== false) {
			$code = preg_replace_callback('/\@import([^;]*);/', array($this, 'import'), $code);
		}

		//Remove comments and spaces
		$code = preg_replace('|/\*.*\*/|Us', '', $code);
		$code = preg_replace('/(\s{2,}|\r|\n)/', ' ', $code);

		//Parse the code
		$this->code = $this->parse($code);

		return $this;
	}


	/**
	 * private function relativeUrls (array $array_code)
	 *
	 * return none
	 */
	private function relativeUrls ($array_code) {
		foreach ($array_code as $k_code => $code) {
			foreach ($code['properties'] as $k_property => $property) {
				foreach ($property['value'] as $k_value => $value) {
					if (strpos($value, 'url') !== FALSE && !strpos($value, '://')) {
						$value = preg_replace('#url\(["\']?([^\)\'"]*)["\']?\)#', 'url(\''.$this->settings['www_path'].'\1\')', $value);
						$value = preg_replace('#/\w+/\.\./#', '/', $value);

						$array_code[$k_code]['properties'][$k_property]['value'][$k_value] = $value;
					}
				}
			}

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->relativeUrls($code['content']);
			}
		}

		return $array_code;
	}


	/**
	 * public function transform (array/string $plugins)
	 *
	 * Process the css file
	 *
	 * return boolean
	 */
	public function transform ($plugins) {
		$plugins_dir = __DIR__.'/plugins/';
		$array_plugins = array();

		include($plugins_dir.'interface.php');

		foreach ((array)$plugins as $plugin) {
			$plugin_file = $plugins_dir.$plugin.'.php';

			if (!is_file($plugin_file)) {
				echo "'$plugin_file' does not exists!";
				die();
			}

			include($plugin_file);
			$plugin = '\\stylecow\\'.$plugin;
			$plugin = new $plugin($this);

			array_splice($array_plugins, $plugin->position, 0, array($plugin));
		}

		//Execute plugins
		foreach ($array_plugins as $plugin) {
			$plugin->transform();
		}

		return $this;
	}


	/**
	 * public function getPropertyKey (array $properties, string $name)
	 *
	 * Return the property values
	 *
	 * return int/boolean
	 */
	public function getPropertyKey ($properties, $name) {
		foreach ($properties as $k => $property) {
			if ($property['name'] == $name) {
				return $k;
			}
		}

		return false;
	}


	/**
	 * public function addProperty (&array $properties, string $name, string $value, int $replace_mode)
	 *
	 * Add a css property
	 *
	 * return int/boolean
	 */
	public function addProperty (&$properties, $name, $value, $replace_mode = 0) {
		switch ($replace_mode) {

			//Add new, no check for duplications
			case 0:
				$properties[] = array(
					'name' => $name,
					'value' => array($value)
				);
				return;
			
			//Replace if exists
			case 1:
				$key = $this->getPropertyKey($properties, $name);

				if ($key === false) {
					$properties[] = array(
						'name' => $name,
						'value' => array($value)
					);
				} else {
					$properties[$key] = array(
						'name' => $name,
						'value' => array($value)
					);
				}
				return;
			
			//Add only if doesn't exit
			case 2:
				$key = $this->getPropertyKey($properties, $name);

				if ($key === false) {
					$properties[] = array(
						'name' => $name,
						'value' => array($value)
					);
				}
				return;
		}

		return false;
	}


	/**
	 * private function import (string $values)
	 *
	 * Load imported code
	 *
	 * return boolean
	 */
	private function import ($values) {
		$file = trim(str_replace(array('\'', '"', 'url(', ')'), '', $values[1]));

		if (!parse_url($file, PHP_URL_SCHEME) && $file[0] != '/') {
			$file = pathinfo($this->file, PATHINFO_DIRNAME).'/'.$file;
		}

		return file_get_contents($file);
	}


	/**
	 * private function parse (string $string_code)
	 *
	 * Convert a css string to multidimensional array
	 *
	 * return array
	 */
	private function parse ($string_code) {
		$array_code = array();

		while ($string_code) {
			$pos = strpos($string_code, '{');

			if ($pos === false) {
				break;
			}

			$selector = trim(substr($string_code, 0, $pos));
			$type = '';

			if ($selector[0] == '@' || $selector[0] == '$') {
				list($type, $selector) = explodeTrim(' ', $selector, 2);
			}

			$selector = $this->explode($selector);

			$string_code = trim(substr($string_code, $pos + 1));
			$length = strlen($string_code);
			$in = 1;

			for ($n = 0; $n <= $length; $n++) {
				$letter = $string_code[$n];

				if ($letter == '{') {
					$in++;
					continue;
				}

				if ($letter == '}') {
					$in--;

					if (!$in) {
						$string_piece = trim(substr($string_code, 0, $n-1));
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
							foreach (explodeTrim(';', $properties_string) as $property) {
								list($n, $v) = explodeTrim(':', $property, 2);

								$this->explodeSettings($v, $settings);

								$code['properties'][] = array(
									'name' => $n,
									'value' => $v === '' ? array() : array($v),
									'settings' => $settings
								);
							}

							if ($code['type'][0] == '$') {
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
			}
		}

		return $array_code;
	}


	/**
	 * public function explode (string $string, [string $delimiter], [string $str_in], [string $str_out])
	 *
	 * Convert strings in arrays
	 *
	 * return array
	 */
	public function explode ($string, $delimiter = ',', $str_in = '(', $str_out = ')') {
		$array = array();

		$length = strlen($string);
		$in = 0;

		for ($n = 0; $n <= $length; $n++) {
			if ($string[$n] == $str_in) {
				$in++;
				continue;
			}

			if ($string[$n] == $str_out) {
				$in--;
				continue;
			}

			if (($string[$n] == $delimiter) && !$in) {
				$array[] = trim(substr($string, 0, $n));
				$string = trim(substr($string, $n+1));
			}
		}

		if ($string) {
			$array[] = trim($string);
		}

		return $array;
	}


	/**
	 * public function explodeFunctions (string $string)
	 *
	 * return false/array
	 */
	public function explodeFunctions ($string) {
		if (preg_match_all('/([\w-]+)\(([^\)]+)\)/', $string, $matches, PREG_SET_ORDER)) {
			$return = array();

			foreach ($matches as $match) {
				$return[] = array(trim($match[1]), explodeTrim(',', $match[2]), $match[3]);
			}

			return $return;
		}

		return false;
	}


	/**
	 * public function explodeSettings (string $string, array &$settings)
	 *
	 * return array
	 */
	public function explodeSettings (&$string, &$settings) {
		$settings = array();

		if (strpos($string, '|$')) {
			if (preg_match('/\|\$stylecow (.*)\$\|/i', $string, $matches)) {
				$string = str_replace($matches[0], '', $string);
				$settings = explodeTrim(',', strtolower($matches[1]));
			}
		}
	}


	/**
	 * public function show ([bool $header], [int $cache])
	 *
	 * Print the css file
	 */
	public function show ($header = true, $cache = 0) {
		if ($header) {
			header('Content-type: text/css');

			if ($cache && is_int($cache)) {
				header('Expires: '.gmdate('D, d M Y H:i:s',(time() + $cache).' GMT'));
			}
		}

		//Get text
		echo $this->toString();

		die();
	}


	/**
	 * public function toString ()
	 *
	 * Return transformed text
	 *
	 * return string
	 */
	public function toString () {
		return $this->_toString($this->code);
	}


	/**
	 * private function _toString (array $array_code)
	 *
	 * Return transformed text
	 *
	 * return string
	 */
	private function _toString ($array_code, $tabs = 0) {
		$text = '';
		$tab_selector = str_repeat("\t", $tabs);
		$tab_property = str_repeat("\t", $tabs + 1);

		//Get text
		foreach ($array_code as $code) {
			if (!$code['is_css']) {
				continue;
			}

			if ($code['type']) {
				$selector = trim($code['type'].' '.implode(', ', $code['selector']));
			} else {
				$selector = implode(', ', $code['selector']);
			}

			$text .= $tab_selector.$selector." {\n";

			foreach ($code['properties'] as $property) {
				$text .= $tab_property.$property['name'].': '.implode(', ', $property['value']).";\n";
			}

			if ($code['content']) {
				$text .= $this->_toString($code['content'], $tabs + 1);
			}

			$text .= $tab_selector."}\n";
		}

		return $text;
	}
}


/**
 * function explodeTrim (string $delimiter, string $text, [int $limit])
 *
 * Return string
 */
function explodeTrim ($delimiter, $text, $limit = null) {
	$return = array();

	$explode = is_null($limit) ? explode($delimiter, $text) : explode($delimiter, $text, $limit);

	foreach ($explode as $text_value) {
		$text_value = trim($text_value);

		if ($text_value !== '') {
			$return[] = $text_value;
		}
	}

	return $return;
}
?>