<?php
/**
 * Stylecow PHP library
 *
 * Selector class
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow;

class Selector {
	public $parent;
	public $type = null;
	public $selectors = array();
	public $vendor;

	public function __construct ($type = null) {
		$this->type = $type;
	}

	public function __toString () {
		return (isset($this->type) ? $this->type.' ' : '').implode(', ', $this->selectors);
	}

	public function setParent (Css $parent) {
		$this->parent = $parent;
	}

	public function add ($selector) {
		$this->selectors[] = $selector;
	}

	public function match ($value) {
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

	public function get ($key = null) {
		if (isset($key)) {
			return isset($this->selectors[$key]) ? $this->selectors[$key] : '';
		} else {
			return $this->selectors;
		}
	}

	public function set ($selectors) {
		if (is_array($selectors)) {
			$this->selectors = $selectors;
		} else {
			$this->selectors = array($selectors);
		}
	}

	public function delete ($key = null) {
		if (isset($key)) {
			if (isset($this->selectors[$key])) {
				unset($this->selectors[$key]);
			}
		} else {
			return $this->selectors = array();
		}
	}
}