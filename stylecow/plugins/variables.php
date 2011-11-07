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

class Variables implements iPlugins {
	public $position = 1;

	private $variables = array();
	private $styles = array();
	private $Css;


	/**
	 * public function __construct (Stylecow $Css)
	 *
	 * return none
	 */
	public function __construct (Stylecow $Css) {
		$this->Css = $Css;
	}

	
	/**
	 * public function transform ()
	 *
	 * return none
	 */
	public function transform () {
		$this->Css->code = $this->_transform($this->Css->code);
	}


	/**
	 * private function _transform (array $array_code)
	 *
	 * return none
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

			$array_code[$k_code] = $code;

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content']);
			}
		}

		return $array_code;
	}


	/**
	 * private function replace (array $matches)
	 *
	 * return none
	 */
	private function replace ($matches) {
		if (isset($this->variables[$matches[0]])) {
			return $this->variables[$matches[0]];
		}

		return $matches[0];
	}
}