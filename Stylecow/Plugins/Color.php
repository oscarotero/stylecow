<?php
/**
* Color plugin (version 0.1)
* for styleCow PHP library
*
* 2011. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
*/

namespace Stylecow;

class Color implements Plugins_interface {
	public $position = 4;

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
			if ($code['properties']) {
				foreach ($code['properties'] as $k_property => $property) {
					foreach ($property['value'] as $k_value => $value) {
						if (strpos($value, 'color(') === false) {
							continue;
						}

						$array_code[$k_code]['properties'][$k_property]['value'][$k_value] = preg_replace_callback('/color\(([^\)]+)\)/', array($this, 'colorCallback'), $value);
					}
				}
			}
		}

		return $array_code;
	}



	/**
	 * private function colorCallback (array $matches)
	 *
	 * return none
	 */
	private function colorCallback ($matches) {
		list($color, $operations) = $this->Css->explodeTrim(',', $matches[1], 2);

		$rgba = $this->toRGBA($color);

		foreach (explode(',', $operations) as $operation) {
			list($function, $value) = $this->Css->explodeTrim(':', $operation, 2);

			switch ($function) {
				case 'hue':
				case 'saturation':
				case 'light':
					$hsla = $this->RGBA_HSLA($rgba);
					$this->editChannel($hsla, $function, $value);
					$rgba = $this->HSLA_RGBA($hsla);
					break;
				
				case 'red':
				case 'green':
				case 'blue':
				case 'alpha':
					$this->editChannel($rgba, $function, $value);
					break;
			}
		}

		if ($rgba[3] < 1) {
			return 'rgba('.implode(', ', $rgba).')';
		}

		return '#'.$this->RGBA_HEX($rgba);
	}



	/**
	 * private function editChannel (array &$channels, string $channel_name, string/ing $new_value)
	 *
	 * return string
	 */
	private function editChannel (&$channels, $channel_name, $new_value) {
		static $channels_info = array(
			'hue' => array(0, 255),
			'saturation' => array(1, 100),
			'light' => array(2, 100),
			'red' => array(0, 255),
			'green' => array(1, 255),
			'blue' => array(2, 255),
			'alpha' => array(3, 1)
		);

		$channel_info = $channels_info[$channel_name];

		if (!$channel_info) {
			return false;
		}

		if ($new_value[0] === '+' || $new_value[0] === '-') {
			$new_value = floatval($channels[$channel_info[0]]) + floatval($new_value);
		}

		if ($new_value > $channel_info[1]) {
			$value = $channel_info[1];
		} else if ($value < 0) {
			$value = 0;
		}

		$channels[$channel_info[0]] = $new_value;
	}



	/**
	 * private function toRGBA (string $color)
	 *
	 * return string
	 */
	private function toRGBA ($color) {
		if ($color[0] === '#') {
			return $this->HEX_RGBA(substr($color, 1));
		}
	}



	/**
	 * private function HSLA_RGBA (array $hsla)
	 *
	 * return array
	 */
	private function HSLA_RGBA ($hsla) {
		list($h, $s, $l, $a) = $hsla;

		$h = intval($h)/360;
		$s = intval($s)/100;
		$l = intval($l)/100;

		if ($s == 0) {
			return array(
				round($l * 255),
				round($l * 255),
				round($l * 255),
				$a
			);
		}

		if ($l <= 0.5) {
			$var_2 = $l * (1 + $s);
		} else {
			$var_2 = ($l + $s) - ($s * $l);
		}

		$var_1 = 2 * $l - $var_2;

		return array(
			round(255 * $this->Hue_RGB($var_1, $var_2, $h + (1/3), true)),
			round(255 * $this->Hue_RGB($var_1, $var_2, $h)),
			round(255 * $this->Hue_RGB($var_1, $var_2, $h - (1/3))),
			$a
		);
	}


	/**
	 * private function Hue_RGB (int $v1, int $v2, int $vH)
	 *
	 * return array
	 */
	private function Hue_RGB ($v1, $v2, $vH, $k = false) {
		if ($vH < 0) {
			$vH += 1;
		} else if ($vH > 1) {
			$vH -= 1;
		}

		if ((6 * $vH) < 1 ) {
			return $v1 + ($v2 - $v1) * 6 * $vH;
		}

		if ((2 * $vH) < 1 ) {
			return $v2;
		}

		if ((3 * $vH) < 2 ) {
			return $v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6;
		}

		return $v1;
	}



	/**
	 * private function RGBA_HSLA (array $rgba)
	 *
	 * return array
	 */
	private function RGBA_HSLA ($rgba) {
		list($r, $g, $b, $a) = $rgba;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);
		$delta = $max - $min;

		$l = ($max + $min) / 510;

		if ($delta === 0) {
			return array(0, '0%', round($l * 100).'%', $a);
		}

		if ($l < 0.5) {
			$s = $delta / ($max + $min);
		} else {
			$s = $delta / (2 - $max - $min);
		}

		if ($r === $max) {
			$h = ($g - $b ) / $delta;
		} else if ($g === $max) {
			$h = 2 + ($b - $r) / $delta;
		} else if ($b === $max) {
			$h = 4 + ($r - $g) / $delta;
		}

		$h = round($h * 60);

		if ($h < 0) {
			$h += 360;
		}

		return array($h, round($s * 100).'%', round($l * 100).'%', $a);
	}



	/**
	 * private function RGBA_HEX (array $rgba)
	 *
	 * return string
	 */
	private function RGBA_HEX ($rgba) {
		$r = dechex($rgba[0]);
		$g = dechex($rgba[1]);
		$b = dechex($rgba[2]);

		if (strlen($r) == 1) {
			$r = str_pad($r, 2, 0, STR_PAD_LEFT);
		}
		if (strlen($g) == 1) {
			$g = str_pad($g, 2, 0, STR_PAD_LEFT);
		}
		if (strlen($b) == 1) {
			$b = str_pad($b, 2, 0, STR_PAD_LEFT);
		}

		return $r.$g.$b;
	}



	/**
	 * private function HEXA_RGBA (string $hex)
	 *
	 * return array
	 */
	private function HEX_RGBA ($hex) {
		if (strlen($hex) === 3) {
			list($r, $g, $b) = array($hex[0].$hex[0], $hex[1].$hex[1], $hex[2].$hex[2]);
		} else {
			list($r, $g, $b) = str_split($hex, 2);
		}

		return array(hexdec($r), hexdec($g), hexdec($b), 1);
	}
}