<?php
/**
 * Stylecow PHP library
 *
 * Selector class. Stores all selector data
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow;

use Stylecow\Parser;

class Selector {
	public $parent;
	public $type = null;
	public $selectors = array();
	public $vendor;


	/**
	 * Parses a css selector code and creates a new Selector object
	 * 
	 * @param string $string The css code
	 * @return Stylecow\Selector
	 */
	static public function createFromString ($string) {
		$string = trim($string);

		if ($string[0] === '@') {
			$pieces = Parser::explodeTrim(' ', $string, 2);

			return new static($pieces[0], isset($pieces[1]) ? Parser::explodeTrim(',', $pieces[1]) : null);
		}

		return new static(null, Parser::explodeTrim(',', $string));
	}

	
	/**
	 * The constructor function
	 *
	 * @param string $type The type of the selector (for example, @media, @document, etc)
	 * @param string/array $selectors The selectors collection
	 */
	public function __construct ($type = null, $selectors = null) {
		$this->type = $type;

		if ($selectors !== null) {
			$this->set($selectors);
		}
	}

	
	/**
	 * Converts the selector object to css string code
	 *
	 * @return string The css code with the selector
	 */
	public function __toString () {
		return (empty($this->type) ? '' : $this->type.' ').implode(', ', $this->selectors);
	}

	
	/**
	 * Stores an alias to access to parent where the selector is stored
	 *
	 * @param Stylecow\Css $parent The parent css object
	 */
	public function setParent (Css $parent) {
		$this->parent = $parent;
	}

	
	/**
	 * Appends a new selector to the list of selectors
	 *
	 * @param string $selector The css selector
	 */
	public function add ($selector) {
		$this->selectors[] = $selector;
	}

	
	/**
	 * Check if there is any selector with this value
	 *
	 * @param string $value The css selector to search
	 *
	 * @return boolean True if the selector exists and false if doesn't
	 */
	public function is ($value) {
		if (!is_array($value)) {
			$value = array($value);
		}

		foreach ($this->selectors as $selector) {
			if (in_array($selector, $value)) {
				return true;
			}
		}

		return false;
	}

	

	/**
	 * Returns all selectors stored or the specified
	 *
	 * @param int $key The css selector key
	 *
	 * @return string The css selector found or an empty string
	 */
	public function get ($key = null) {
		if (isset($key)) {
			return isset($this->selectors[$key]) ? $this->selectors[$key] : '';
		} else {
			return $this->selectors;
		}
	}

	
	/**
	 * Sets a new collection of selectors (replace the existing selectors)
	 *
	 * @param array/string $selectors The new selectors
	 */
	public function set ($selectors) {
		if (is_array($selectors)) {
			$this->selectors = $selectors;
		} else {
			$this->selectors = array($selectors);
		}
	}


	/**
	 * Deletes all selectors or the selector with specific key
	 *
	 * @param int $key The key of the selector to delete
	 */
	public function delete ($key = null) {
		if (isset($key)) {
			if (isset($this->selectors[$key])) {
				unset($this->selectors[$key]);
			}
		} else {
			$this->selectors = array();
		}
	}
}