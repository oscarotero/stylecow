<?php
/**
 * Stylecow PHP library
 *
 * Property class. To store an property with the name, value and vendor
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.1.0 (2013)
 */

namespace Stylecow;

use Stylecow\Parser;

class Property {
	public $parent;

	public $name;
	public $value;
	public $vendor;
	public $comments = array();
	public $sourceMap = array();


	/**
	 * Parses a css property code and creates a new Property object
	 * 
	 * @param string $string The css code
	 * @return Stylecow\Property
	 */
	static public function createFromString ($string) {
		$pieces = Parser::explodeTrim(':', $string, 2);

		if (isset($pieces[1])) {
			return new static($pieces[0], $pieces[1]);
		}

		return new static($pieces[0], null);
	}


	/**
	 * Constructor function.
	 * 
	 * @param string $name The name of the property
	 * @param string $value The value of the property
	 */
	public function __construct ($name, $value) {
		$this->set($name, $value);
	}

	
	/**
	 * Magic function co convert this property in css code
	 */
	public function __toString () {
		$comments = empty($this->comments) ? '' : ' /*'.implode(', ', $this->comments).'*/';

		return $this->name.': '.$this->value.$comments;
	}


	/**
	 * Stores an alias to access to parent where the property is stored
	 *
	 * @param Stylecow\Css $parent The parent css object
	 */
	public function setParent (Css $parent) {
		$this->parent = $parent;
	}


	/**
	 * Set the line, column and file of the original source
	 */
	public function setSourceMap ($line, $column, $file) {
		$this->sourceMap = array($line, $column, $file);

		return $this;
	}


	/**
	 * Add a new comment to the css code
	 *
	 * @param string $comment The comment to add
	 */
	public function addComment ($comment) {
		$this->comments[] = $comment;
	}


	/**
	 * Returns the position of this property in the parent.
	 *
	 * @return int The position of the object or false if the parent is not defined or the property is not placed here
	 */
	public function getPositionInParent () {
		if (isset($this->parent)) {
			foreach ($this->parent->properties as $key => $property) {
				if ($property === $this) {
					return $key;
				}
			}
		}

		return false;
	}

	
	/**
	 * Check if the property has a specific name and optional value
	 *
	 * @param string/array $name The name to check. You can check also a list of names using an array
	 * @param string $value The value to check
	 *
	 * @return boolean True if the property has this name (and value if is defined) and false if don't
	 */
	public function is ($name, $value = null) {
		if (is_array($name)) {
			if (!in_array($this->name, $name)) {
				return false;
			}
		} else if ($this->name !== $name) {
			return false;
		}

		if (isset($value)) {
			if (is_array($value)) {
				if (!in_array($this->value, $value)) {
					return false;
				}
			} else if ($this->value !== $value) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Defines a new name and value for this property
	 *
	 * @param string $name The new name of the property
	 * @param string $value The value to set
	 */
	public function set ($name, $value) {
		$this->name = $name;
		$this->value = $value;

		if (($name[0] === '-') && preg_match('/^\-(\w+)\-/', $name, $match)) {
			$this->vendor = $match[1];
		} else {
			$this->vendor = null;
		}
	}

	

	/**
	 * Append a value to current value. Useful for properties with multiple values (such background, shadows, etc)
	 *
	 * @param string $value The new value or values separated by comma
	 */
	public function addValue ($value) {
		if ($this->value) {
			if (strpos($this->value, $value) === false) {
				$this->value .= ', '.$value;
			} else {
				$values = Parser::explode(',', $this->value);

				if (!in_array($value, $values)) {
					$this->value .= ', '.$value;
				}
			}
			
		} else {
			$this->value = $value;
		}
	}

	
	/**
	 * Execute a specific css function in the value (for example, url(), rgba(), etc)
	 *
	 * @param string $function The function name to search (url, rgba, hsl, etc)
	 * @param callable $callback The function to execute with the css function. If the functions returns a string, it replaces the css code. It's passed two parameters to the function with all arguments of css function and the function name.
	 */
	public function executeFunction ($function, $callback) {
		$this->value = Parser::executeFunctions($this->value, $function, $callback, $this);
	}

	
	/**
	 * Execute all css functions found in the css value
	 *
	 * @param callable $callback The function to execute with the css functions. If the functions returns a string, it replaces the css code. It's passed two parameters to the function with all arguments of css function and the function name.
	 */
	public function executeAllFunctions ($callback) {
		$this->value = Parser::executeFunctions($this->value, null, $callback, $this);
	}
}
