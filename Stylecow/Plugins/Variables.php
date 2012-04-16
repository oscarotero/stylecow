<?php
/**
 * Stylecow PHP library
 *
 * Variables plugin
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1 (2011)
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
	 * @param Stylecow  $Css  The Stylecow instance
	 */
	public function __construct (Stylecow $Css) {
		$this->Css = $Css;
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
					if ($this->styles[$property['name']]) {
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

			$array_code[$k_code] = $code;

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content']);
			}
		}

		return $array_code;
	}
}