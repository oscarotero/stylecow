<?php
/**
 * Stylecow PHP library
 *
 * Abstract class to extend all remaining plugins
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.4.4 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

abstract class Plugin {
	static protected $position = 0;
	
	protected $settings;


	/**
	 * Constructor
	 *
	 * @param array $settings The settings for this plugin
	 */
	public function __construct (array $settings = array()) {
		$this->settings = $settings;
	}


	/**
	 * Returns the position in which the plugin will be executed (respecting other plugins)
	 *
	 * @return int The position number
	 */
	public function getPosition () {
		return self::$position;
	}
}