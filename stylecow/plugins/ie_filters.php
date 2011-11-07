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

class Ie_filters implements iPlugins {
	public $position = 2;

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
			foreach ($code['properties'] as $property) {
				if ($property['settings'] && in_array('ignore ie_filters', $property['settings'])) {
					continue;
				}

				switch ($property['name']) {
					case 'opacity':
						$this->addFilter($array_code[$k_code], 'alpha(opacity='.($property['value'][0] * 100).')');
						break;
					
					case 'transform':
						if (!($functions = $this->Css->explodeFunctions($property['value'][0]))) {
							break;
						}

						foreach ($functions as $function) {
							list($function, $params) = $function;

							switch ($function) {
								case 'rotate':
									$this->rotate($array_code[$k_code], $params);
									break;
								
								case 'scaleX':
									if ($params[0] == '-1') {
										$this->addFilter($array_code[$k_code], 'flipH');
									}
									break;
								
								case 'scaleY':
									if ($params[0] == '-1') {
										$this->addFilter($array_code[$k_code], 'flipV');
									}
									break;

								case 'scale':
									if ($params[0] == '-1' && $params[1] == '-1') {
										$this->addFilter($array_code[$k_code], 'flipH');
										$this->addFilter($array_code[$k_code], 'flipV');
									}
									break;
							}
						}
						break;

					case 'background':
					case 'background-image':
						if (!($functions = $this->Css->explodeFunctions($property['value'][0]))) {
							break;
						}

						foreach ($functions as $function) {
							list($function, $params) = $function;

							switch ($function) {
								case 'linear-gradient':
									$this->linearGradient($array_code[$k_code], $params);
									break;
								
								case 'rgba':
									$this->rgba($array_code[$k_code], $params);
									break;
							}
						}

						break;
				}
			}
		}

		return $array_code;
	}


	/**
	 * private function rotate (&array $code, array $params)
	 *
	 * return none
	 */
	private function rotate (&$code, $params) {
		$value = intval($params[0]);

		if ($value < 0) {
			$value += 360;
		}

		switch ($value) {
			case 90:
				$filter = 'progid:DXImageTransform.Microsoft.BasicImage(rotation=1)';
				break;

			case 180:
				$filter = 'progid:DXImageTransform.Microsoft.BasicImage(rotation=2)';
				break;

			case 270:
				$filter = 'progid:DXImageTransform.Microsoft.BasicImage(rotation=3)';
				break;

			case 360:
				$filter = 'progid:DXImageTransform.Microsoft.BasicImage(rotation=4)';
				break;

			default:
				$rad = ($value * pi() * 2) / 360;
				$cos = cos($rad);
				$sin = sin($rad);
				$filter = 'progid:DXImageTransform.Microsoft.Matrix(sizingMethod="auto expand", M11 = '.$cos.', M12 = '.(-$sin).', M21 = '.$sin.', M22 = '.$cos.')';
		}

		$this->addFilter($code, $filter);
	}


	/**
	 * private function linearGradient (&array $code, array $params)
	 *
	 * Return array
	 */
	private function linearGradient (&$code, $params) {
		$point = 'top';

		if (preg_match('/(top|bottom|left|right|deg)/', $params[0])) {
			$point = array_shift($params);
		}

		switch ($point) {
			case 'top':
			case '90deg':
				$direction = 'vertical';
				$reverse = false;
				break;

			case 'bottom':
			case '-90deg':
				$direction = 'vertical';
				$reverse = true;
				break;

			case 'left':
			case '180deg':
			case '-180deg':
				$direction = 'horizontal';
				$reverse = false;
				break;

			case 'right':
			case '0deg':
			case '360deg':
				$direction = 'vertical';
				$reverse = true;
				break;
		}

		$colors = $params;

		if ($direction && count($colors) == 2 && $colors[0][0] == '#' && $colors[1][0] == '#') {
			if (strlen($colors[0]) == 4) {
				$colors[0] = $colors[0][0].$colors[0][1].$colors[0][1].$colors[0][2].$colors[0][2].$colors[0][3].$colors[0][3];
			}
			if (strlen($colors[1]) == 4) {
				$colors[1] = $colors[1][0].$colors[1][1].$colors[1][1].$colors[1][2].$colors[1][2].$colors[1][3].$colors[1][3];
			}

			if ($reverse) {
				$colors = array_reverse($colors);
			}

			if ($direction == 'horizontal') {
				$this->addFilter($code, 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$colors[0].'\', endColorStr=\''.$colors[1].'\', GradientType=1)');
			} else {
				$this->addFilter($code, 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$colors[0].'\', endColorStr=\''.$colors[1].'\')');
			}
		}
	}


	/**
	 * private function rgba (&array $code, array $params)
	 *
	 * Return array
	 */
	private function rgba (&$code, $params) {
		$r = dechex($params[0]);
		$g = dechex($params[1]);
		$b = dechex($params[2]);

		if (strlen($r) == 1) {
			$r = str_repeat($r, 2);
		}
		if (strlen($g) == 1) {
			$g = str_repeat($g, 2);
		}
		if (strlen($b) == 1) {
			$b = str_repeat($b, 2);
		}

		$a = dechex(round(255*floatval($params[3])));

		$color = '#'.$a.$r.$g.$b;

		$this->addFilter($code, 'progid:DXImageTransform.Microsoft.gradient(startColorStr=\''.$color.'\', endColorStr=\''.$color.'\')');
	}


	/**
	 * private function addFilter (array &$array_code, string $filter)
	 *
	 * return none
	 */
	private function addFilter (&$array_code, $filter) {
		$filter_key = $this->Css->getPropertyKey($array_code['properties'], 'filter');
		$ms_filter_key = $this->Css->getPropertyKey($array_code['properties'], '-ms-filter');

		if ($filter_key === false) {
			$array_code['properties'][] = array(
				'name' => 'filter',
				'value' => array($filter)
			);
		} else {
			$array_code['properties'][$filter_key]['value'][] = $filter;
		}

		if ($ms_filter_key === false) {
			$array_code['properties'][] = array(
				'name' => '-ms-filter',
				'value' => array($filter)
			);
		} else {
			$array_code['properties'][$ms_filter_key]['value'][] = $filter;
		}
	}
}