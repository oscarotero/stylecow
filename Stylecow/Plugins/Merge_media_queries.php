<?php
/**
 * Stylecow PHP library
 *
 * Merge_media_queries plugin
 * Merge all media queries for browsers that don't support them. You can filter the mediaqueries
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.1 (2012)
 */

namespace Stylecow;

class Merge_media_queries implements Plugins_interface {
	public $position = 1;

	private $matches = array();
	
	private $Css;


	/**
	 * Constructor
	 *
	 * @param Stylecow  $Css       The Stylecow instance
	 * @param array     $settings  The settings for this plugin
	 */
	public function __construct (Stylecow $Css, array $settings) {
		$this->Css = $Css;

		if (is_array($settings) && is_array($settings['matches'])) {
			foreach ($settings['matches'] as $match) {
				$this->matches[] = str_replace(' ', '', $match);
			}
		}
	}

	
	/**
	 * Transform the parsed css code
	 */
	public function transform () {
		$new_code = array();

		foreach ($this->Css->code as $k_code => $code) {
			if ($code['type'] === '@media') {
				foreach ($code['selector'] as $selector) {
					if (!in_array(str_replace(' ', '', $selector), $this->matches)) {
						continue 2;
					}
				}

				$new_code = array_merge($new_code, $code['content']);
			} else {
				$new_code[] = $code;
			}
		}

		$this->Css->code = $new_code;
	}
}