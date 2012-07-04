<?php
/**
 * Stylecow PHP library
 *
 * Property class
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow;

class Property {
	public $parent;

	public $name;
	public $value;
	public $vendor;


	public function __construct ($name, $value) {
		$this->set($name, $value);
	}

	public function __toString () {
		return $this->name.': '.$this->value;
	}


	public function setParent (Css $parent) {
		$this->parent = $parent;
	}

	public function getParentPosition () {
		if (isset($this->parent)) {
			foreach ($this->parent->properties as $key => $property) {
				if ($property === $this) {
					return $key;
				}
			}
		}

		return false;
	}

	public function is ($name) {
		if (is_array($name)) {
			return in_array($this->name, $name);
		}

		return ($this->name === $name) ? true : false;
	}


	public function set ($name, $value) {
		$this->name = $name;
		$this->value = $value;

		if (($name[0] === '-') && preg_match('/^\-(\w+)\-/', $name, $match)) {
			$this->vendor = $match[1];
		} else {
			$this->vendor = null;
		}
	}

	public function addValue ($value) {
		if ($this->value) {
			$this->value .= ', '.$value;
		} else {
			$this->value = $value;
		}
	}

	public function executeFunction ($function, $callback) {
		$this->value = Stylecow::executeFunctions($this->value, $function, $callback, $this);
	}
}