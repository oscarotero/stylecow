<?php
/**
 * Stylecow PHP library
 *
 * Rem plugin
 * Allows use rem values for old browsers (ie8 and lower)
 *
 * Example:
 * font-size: 2rem;
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.1.0 (2013)
 */

namespace Stylecow\Plugins;

use Stylecow\Css;

class BaseUrl {
	const POSITION = 1;


	/**
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 * @param int $rem The default value for rem. This value can be overwritten for the font-face property in :root, html or body
	 */
	static public function apply (Css $css, $baseUrl) {
		$css->executeRecursive(function ($code) use ($baseUrl) {
			foreach ($code->getProperties() as $property) {
				$property->executeFunction('url', function ($params) use ($baseUrl) {
					$url = $params[0];

					if (($url[0] === "'") || ($url[0] === '"')) {
						$url = substr($url, 1, -1);
					}

					if (parse_url($url, PHP_URL_SCHEME) || $url[0] === '/') {
						return;
					}

					return 'url(\''.$baseUrl.$url.'\')';
				});
			}
		});
	}
}
