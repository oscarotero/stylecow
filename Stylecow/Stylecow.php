<?php
/**
* styleCow PHP library (version 0.1b1)
*
* 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*
* styleCow is released under the GNU Affero GPL version 3.
* More information at http://www.gnu.org/licenses/agpl-3.0.html
*/

namespace Stylecow;

class Stylecow {
	public $code = array();


	/**
	 * public function load (string $file_path, [string $file_url])
	 *
	 * Loads a css file and parse it
	 * Returns boolean
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
	 * public function resolve (string $code, string $base_path, string $base_url)
	 *
	 * Resolves @import, fixes urls, remove comments, etc
	 * Returns string
	 */
	public function resolve ($code, $base_path, $base_url) {
		$current_base_path = $this->current_base_path;
		$current_base_url = $this->current_base_url;

		$this->current_base_path = dirname($base_path);
		$this->current_base_url = dirname($base_url);

		//Remove comments
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
	 * private function importCallback (string $matches)
	 *
	 * Returns string
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
	 * private function urlCallback (string $matches)
	 *
	 * Returns string
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
	 * public function transform (array/string $plugins)
	 *
	 * Process the css file
	 * Returns this
	 */
	public function transform ($plugins) {
		if (is_string($this->code)) {
			$this->code = $this->parse($this->code);
		}

		$plugins_dir = __DIR__.'/Plugins/';
		$plugins_objects = array();
		$plugins_positions = array();

		include_once($plugins_dir.'Plugins_interface.php');

		foreach ((array)$plugins as $plugin) {
			$plugin = ucfirst($plugin);
			$plugin_file = $plugins_dir.$plugin.'.php';

			if (!is_file($plugin_file)) {
				echo "'$plugin_file' does not exists!";
				die();
			}

			include_once($plugin_file);

			$plugin = '\\Stylecow\\'.$plugin;
			$plugins_objects[$plugin] = new $plugin($this);
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
	 * public function getPropertyKey (array $properties, string $name)
	 *
	 * Returns a property numeric key
	 * Returns int/false
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
	 * public function getProperty (array $properties, string $name, [int $key])
	 *
	 * Returns a property values
	 * Returns array/false
	 */
	public function getProperty ($properties, $name, $key = false) {
		$k = $this->getPropertyKey($properties, $name);

		if ($k === false) {
			return false;
		}

		return ($key === false) ? $properties[$k] : $properties[$k][$key];
	}



	/**
	 * public function addProperty (&array $properties, string $name, string $value, int $replace_mode)
	 *
	 * Adds a css property
	 * Returns boolean
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
				$key = $this->getPropertyKey($properties, $name);

				if ($key === false) {
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
	 * public function parse (string $string_code)
	 *
	 * Converts a css string to multidimensional array
	 * Returns array
	 */
	public function parse ($string_code) {
		$array_code = array();

		while ($string_code) {
			$pos = strpos($string_code, '{');
			$pos2 = strpos($string_code, ';');

			if ($pos2 < $pos) {
				$selector = trim(substr($string_code, 0, $pos2));
				$type = '';

				if ($selector[0] == '@' || $selector[0] == '$') {
					list($type, $selector) = $this->explodeTrim(' ', $selector, 2);
				}

				$array_code[] = array(
					'selector' => array($selector),
					'type' => $type,
					'is_css' => ($type[0] === '$') ? false : true,
					//'properties' => array(),
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

			if ($selector[0] == '@' || $selector[0] == '$') {
				list($type, $selector) = $this->explodeTrim(' ', $selector, 2);
			}

			$selector = $this->explode($selector);

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

					if ($code['type'][0] === '$') {
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
	 * public function explode (string $string, [string $delimiter], [string $str_in], [string $str_out])
	 *
	 * Converts strings in arrays
	 * Returns array
	 */
	public function explode ($string, $delimiter = ',', $str_in = '(', $str_out = ')') {
		$array = array();

		while ($string) {
			if (strpos($string, $delimiter) === false) {
				$array[] = trim($string);
				break;
			}

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
					continue 2;
				}
			}

			$array[] = trim($string);
			break;
		}

		return $array;
	}



	/**
	 * public function explodeFunctions (string $string)
	 *
	 * Returns false/array
	 */
	public function explodeFunctions ($string) {
		if (!preg_match_all('/([\w-]+)\(([^\)]+)\)/', $string, $matches, PREG_SET_ORDER)) {
			return false;
		}

		$return = array();

		foreach ($matches as $match) {
			$return[] = array(trim($match[1]), $this->explodeTrim(',', $match[2]), $match[3]);
		}

		return $return;
	}



	/**
	 * public function explodeSettings (string $string, array &$settings)
	 *
	 * Returns array
	 */
	public function explodeSettings (&$string, &$settings) {
		$settings = array();

		if (strpos($string, '|$') && preg_match('/\|\$stylecow (.*)\$\|/i', $string, $matches)) {
			$string = str_replace($matches[0], '', $string);
			$settings = $this->explodeTrim(',', strtolower($matches[1]));
		}
	}



	/**
	 * public function explodeTrim (string $delimiter, string $text, [int $limit])
	 *
	 * Explodes a string and trim its values
	 * Returns string
	 */
	public function explodeTrim ($delimiter, $text, $limit = null) {
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



	/**
	 * public function show ([bool $header], [int $cache])
	 *
	 * Prints the css file
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
	 * Returns transformed text
	 * Returns string
	 */
	public function toString () {
		if (is_string($this->code)) {
			return $this->code;
		}

		return $this->_toString($this->code);
	}



	/**
	 * private function _toString (array $array_code)
	 *
	 * Returns transformed text
	 * Returns string
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

			if (isset($code['properties'])) {
				$text .= $tab_selector.$selector." {\n";
				
				foreach ($code['properties'] as $property) {
					$text .= $tab_property.$property['name'].': '.implode(', ', $property['value']).";\n";
				}

				if ($code['content']) {
					$text .= $this->_toString($code['content'], $tabs + 1);
				}

				$text .= $tab_selector."}\n";
			} else {
				$text .= $tab_selector.$selector.";\n";
			}
		}

		return $text;
	}
}
?>