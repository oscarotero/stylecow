<?php
/**
 * Stylecow PHP library
 *
 * Animate plugin
 * Implement predefined animations easily
 * 
 * Example:
 * $animate: flash;
 *
 * Based in the work of Dan Eden in http://daneden.me/animate
 * LICENSED UNDER THE  MIT LICENSE (MIT)
 * Copyright (c) 2011 Dan Eden
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class Snippets extends Plugin implements PluginsInterface {
	static protected $position = 1;

	private $snippets = array();


	/**
	 * Search for snippets to place.
	 *
	 * @param array $array_code The piece of the parsed css code
	 *
	 * @return array The transformed code
	 */
	public function transform (array $array_code) {
		$self = $this;

		$array_code = Stylecow::walk($array_code, function ($code) use ($self) {
			foreach ($code as $k => &$row) {
				$new_properties = array();

				foreach ($row['properties'] as $k => $property) {
					if ($property['name'] === 'snippet') {
						foreach ($property['value'] as $value) {
							$new_properties = array_merge($new_properties, $self->getSnippetElementCode($value));
						}

						unset($row['properties'][$k]);
					}
				}

				if ($new_properties) {
					$row['properties'] = array_merge($row['properties'], $new_properties['properties']);
					$row['content'] = array_merge($row['content'], $new_properties['content']);
				}
			}

			return $code;
		});

		$array_code = array_merge($array_code, $this->getSnippetsRootCode());

		return $array_code;
	}


	public function getSnippetElementCode ($name) {
		if (!isset($this->snippets[$name])) {
			$this->loadSnippet($name);
		}

		return $this->snippets[$name]['element'];
	}


	public function getSnippetsRootCode () {
		$code = array();

		foreach ($this->snippets as $snippet) {
			$code = array_merge($code, $snippet['root']);
		}

		return $code;
	}


	public function loadSnippet ($name) {
		$filename = __DIR__.'/snippets/'.strtolower($name).'.css';

		if (!is_file($filename)) {
			return;
		}

		$array_code = Stylecow::parse(file_get_contents($filename));

		$this->snippets[$name] = array(
			'element' => array(),
			'root' => array()
		);

		foreach ($array_code as $code) {
			if ($code['selector'][0] === '#element') {
				$this->snippets[$name]['element'] = $code;
			} else {
				$this->snippets[$name]['root'][] = $code;
			}
		}
	}
}