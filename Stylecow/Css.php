<?php
/**
 * Stylecow PHP library
 *
 * Css class. Stores the css structure code
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow;

class Css extends \ArrayObject {
	public $parent;
	public $selector;
	public $properties = array();


	public function __construct (array $selectors = null, $type = null) {
		$this->selector = new Selector($type);
		$this->selector->setParent($this);

		if ($selectors) {
			foreach ($selectors as $selector) {
				$this->selector->add($selector);
			}
		}
	}

	
	/**
	 * Magic function to clone the css object
	 */
	public function __clone () {
		$this->selector = clone $this->selector;

		foreach ($this->properties as $k => $property) {
			$this->properties[$k] = clone $property;
		}

		$children = array();

		foreach ($this as $key => $child) {
			$children[$key] = clone $child;
		}

		$this->exchangeArray($children);
	}

	
	/**
	 * Magic function to convert the object in css string
	 */
	public function __toString () {
		return $this->toString();
	}


	/**
	 * Stores an alias to access to parent css object
	 *
	 * @param Stylecow\Css $parent The parent css object
	 */
	public function setParent (Css $parent) {
		$this->parent = $parent;
	}


	/**
	 * Removes this object from its parent.
	 */
	public function removeFromParent () {
		if (($position = $this->getPositionInParent()) !== false) {
			$siblings = $this->parent->getArrayCopy();
			array_splice($siblings, $position, 1);
			$this->parent->exchangeArray($siblings);
			$this->parent = null;
		}
	}


	/**
	 * Returns the position of this object in the parent.
	 *
	 * @return int The position of the object or false if the parent is not defined or the child has not found
	 */
	public function getPositionInParent () {
		if (isset($this->parent)) {
			foreach ($this->parent as $key => $child) {
				if ($child === $this) {
					return $key;
				}
			}
		}

		return false;
	}


	
	/**
	 * Adds a new css object as child
	 *
	 * @param Stylecow\Css $css The css object to add as child
	 * @param int The optional position. If its not defined, the child will be append
	 *
	 * @return Stylecow\Css The new css object inserted;
	 */
	public function addChild (Css $css, $position = null) {
		$css->setParent($this);

		if (!isset($position)) {
			return $this[] = $css;
		}

		$children = $this->getArrayCopy();
		array_splice($children, $position, 0, array($css));
		$this->exchangeArray($children);

		return $css;
	}


	/**
	 * Returns some or all children of the object
	 *
	 * @param string $filter A optional string to search in the selector
	 *
	 * @return array An array with the children found
	 */
	public function getChildren ($filter = null) {
		if (!isset($filter)) {
			return $this->getArrayCopy();
		}

		$children = array();

		foreach ($this as $child) {
			if ($child->selector->is($filter)) {
				$children[] = $child;
			}
		}

		return $children;
	}


	/**
	 * Adds a new property to the css object
	 *
	 * @param Stylecow\Property $property The property object to add
	 * @param int $position The position in which the property will be placed. If it's not defined, the property will be appended
	 *
	 * @return Stylecow\Property The inserted property
	 */
	public function addProperty (Property $property, $position = null) {
		$property->setParent($this);

		if (!isset($position)) {
			return $this->properties[] = $property;
		}

		array_splice($this->properties, $position, 0, array($property));

		return $property;
	}


	/**
	 * Gets some or all properties of the css object
	 *
	 * @param string $name The property name to filter
	 * @param string $value The optional value to filter
	 *
	 * @return array The properties found
	 */
	public function getProperties ($name = null, $value = null) {
		if (!isset($name)) {
			return $this->properties ? $this->properties : array();
		}

		$properties = array();

		if ($this->properties) {
			foreach ($this->properties as $property) {
				if ($property->is($name, $value)) {
					$properties[] = $property;
				}
			}
		}

		return $properties;
	}


	
	/**
	 * Check if the css object has a property with this name and optional value
	 *
	 * @param string $name The property name to search
	 * @param string $value The optional value to search
	 *
	 * @return boolean True if the property exists, false if not.
	 */
	public function hasProperty ($name, $value = null) {
		foreach ($this->properties as $property) {
			if ($property->is($name, $value)) {
				return true;
			}
		}

		return false;
	}


	
	/**
	 * Execute a function recursively to the css object and all children
	 *
	 * @param callable $callback The function to execute. Two arguments will be passed to the function: the css code and an variable to store data througt all childrens
	 * @param mixed $contextData An optional variable to pass values throught all childrens in cascade
	 */
	public function executeRecursive ($callback, $contextData = null) {
		$callback($this, $contextData);

		foreach ($this->getArrayCopy() as $child) {
			$childData = $contextData;

			$child->executeRecursive($callback, $childData);
		}
	}



	/**
	 * Removes all properties and children with a vendor that don't match with the specified.
	 *
	 * @param string $vendor The vendor to filter (all vendors that don't match with this will be removed)
	 */
	public function filterVendor ($vendor) {
		if ($this->properties) {
			foreach ($this->properties as $k => $property) {
				if (!empty($property->vendor) && ($property->vendor !== $vendor)) {
					unset($this->properties[$k]);
				}
			}
		}

		foreach ($this as $child) {
			$child->filterVendor($vendor);
		}
	}


	
	/**
	 * Converts the current css object and its children in css code string
	 *
	 * @param int $indent An indent value to execute recursively by the children
	 *
	 * @return string The css code
	 */
	public function toString ($indent = 0) {
		$indentation = str_repeat("\t", $indent);

		$selector = (string)$this->selector;
		$properties = '';

		if (isset($this->properties)) {
			$indProp = $selector ? $indentation."\t" : $indentation;

			foreach ($this->properties as $property) {
				$properties .= $indProp.(string)$property.";\n";
			}
		}

		if (count($this)) {
			$indent += $selector ? 1 : 0;

			foreach ($this as $child) {
				$properties .= "\n".$child->toString($indent);
			}
		}

		if ($properties && $selector) {
			return $indentation.$selector." {\n".$properties.$indentation."}\n";
		}

		if ($properties) {
			return $properties;
		}

		if ($selector) {
			return $indentation.$selector.";\n";
		}
	}


	
	/**
	 * Converts the current css object and children in an array with all selectors, properties and values
	 *
	 * @return array The css data
	 */
	public function toArray () {
		$array = array(
			'type' => $this->selector->type,
			'selector' => $this->selector->get(),
			'properties' => array()
		);

		foreach ($this->properties as $property) {
			$array['properties'][] = array(
				'name' => $property->name,
				'value' => $property->value,
				'vendor' => $property->vendor
			);
		}

		foreach ($this as $child) {
			$array[] = $child->toArray();
		}

		return $array;
	}


	/**
	 * Transform the css code using the plugins
	 *
	 * @param array $plugins The list of the plugins to execute
	 */
	public function applyPlugins (array $plugins) {
		$pluginPositions = array();
		$pluginSettings = array();

		foreach ($plugins as $plugin => $settings) {
			if (is_int($plugin)) {
				$plugin = $settings;
				$settings = array();
			}

			$plugin = __NAMESPACE__.'\\Plugins\\'.$plugin;

			if (!class_exists($plugin)) {
				echo "'$plugin' does not exists!";
				die();
			}

			$pluginPositions[$plugin] = $plugin::POSITION;
			$pluginSettings[$plugin] = $settings;
		}

		asort($pluginPositions);

		foreach (array_keys($pluginPositions) as $plugin) {
			$plugin::apply($this, $pluginSettings[$plugin]);
		}
	}
}