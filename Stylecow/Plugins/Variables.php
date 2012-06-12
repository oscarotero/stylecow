<?php
/**
 * Stylecow PHP library
 *
 * Variables plugin
 * Allows use predefined variables in the css
 *
 * Example:
 * color: $my-color;
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.2.0 (2012)
 */

namespace Stylecow;

class Variables implements Plugins_interface {
	public $position = 1;

	private $variables = array();
	private $styles = array();
	private $Css;


	/**
	 * Constructor
	 *
	 * @param Stylecow  $Css       The Stylecow instance
	 * @param array     $settings  The settings for this plugin
	 */
	public function __construct (Stylecow $Css, array $settings) {
		$this->Css = $Css;

		if ($settings && isset($settings['variables'])) {
			foreach ($settings['variables'] as $name => $value) {
				$this->variables['$'.$name] = $value;
			}
		}
	}

	
	/**
	 * Transform the parsed css code
	 */
	public function transform () {
		$this->Css->code = $this->_transform($this->Css->code);
	}


	/**
	 * Private function to transform recursively the parsed css code
	 *
	 * @param array  $array_code        The piece of the parsed css code
	 *
	 * @return array  The transformed code
	 */
	private function _transform ($array_code) {
		foreach ($array_code as $k_code => $code) {
			if ($code['type'] == '$variables') {
				foreach ($code['properties'] as $property) {
					$this->variables['$'.$property['name']] = current($property['value']);
				}

				foreach ($code['content'] as $content) {
					$this->styles['$'.substr(current($content['selector']), 0, -1)] = array(
						'properties' => $content['properties'],
						'content' => $content['content'],
					);
				}

				unset($array_code[$k_code]);

				continue;
			}

			if (!$code['is_css']) {
				continue;
			}

			if ($code['properties']) {
				$unset = array();

				foreach ($code['properties'] as $k_property => $property) {
					if (isset($this->styles[$property['name']])) {
						$code = array_merge_recursive($code, $this->styles[$property['name']]);
						$unset[] = $k_property;
					}
				}

				foreach ($unset as $k) {
					unset($code['properties'][$k]);
				}

				foreach ($code['properties'] as $k_property => $property) {
					foreach ($property['value'] as $k_value => $value) {
						if (strpos($value, '$') !== false) {
							$code['properties'][$k_property]['value'][$k_value] = preg_replace_callback('/\$[\w-]+/', array($this, 'replace'), $value);
						}
					}
				}
			}

			foreach ($code['selector'] as $k_selector => $selector) {
				if (strpos($selector, '$') !== false) {
					$code['selector'][$k_selector] = preg_replace_callback('/\$[\w-]+/', array($this, 'replace'), $selector);
				}
			}

			$array_code[$k_code] = $code;

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content']);
			}
		}

		return $array_code;
	}


	/**
	 * The internal callback to replace the variables for their values
	 *
	 * @param array  $matches  The matches found in the preg_replace_callback
	 *
	 * @return string  The value of the variable. If it's not exists, returns the same string
	 */
	private function replace ($matches) {
		if (isset($this->variables[$matches[0]])) {
			return $this->variables[$matches[0]];
		}

		return $matches[0];
	}
}