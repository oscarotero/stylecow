<?php
/**
 * Color plugin (version 0.4)
 * for styleCow PHP library
 *
 * 2012. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
 */

namespace Stylecow;

class Color implements Plugins_interface {
	public $position = 4;

	private $Css;
	private $color_names = array(
		'AliceBlue' => '#F0F8FF',
		'AntiqueWhite' => '#FAEBD7',
		'Aqua' => '#00FFFF',
		'Aquamarine' => '#7FFFD4',
		'Azure' => '#F0FFFF',
		'Beige' => '#F5F5DC',
		'Bisque' => '#FFE4C4',
		'Black' => '#000000',
		'BlanchedAlmond' => '#FFEBCD',
		'Blue' => '#0000FF',
		'BlueViolet' => '#8A2BE2',
		'Brown' => '#A52A2A',
		'BurlyWood' => '#DEB887',
		'CadetBlue' => '#5F9EA0',
		'Chartreuse' => '#7FFF00',
		'Chocolate' => '#D2691E',
		'Coral' => '#FF7F50',
		'CornflowerBlue' => '#6495ED',
		'Cornsilk' => '#FFF8DC',
		'Crimson' => '#DC143C',
		'Cyan' => '#00FFFF',
		'DarkBlue' => '#00008B',
		'DarkCyan' => '#008B8B',
		'DarkGoldenRod' => '#B8860B',
		'DarkGray' => '#A9A9A9',
		'DarkGrey' => '#A9A9A9',
		'DarkGreen' => '#006400',
		'DarkKhaki' => '#BDB76B',
		'DarkMagenta' => '#8B008B',
		'DarkOliveGreen' => '#556B2F',
		'Darkorange' => '#FF8C00',
		'DarkOrchid' => '#9932CC',
		'DarkRed' => '#8B0000',
		'DarkSalmon' => '#E9967A',
		'DarkSeaGreen' => '#8FBC8F',
		'DarkSlateBlue' => '#483D8B',
		'DarkSlateGray' => '#2F4F4F',
		'DarkSlateGrey' => '#2F4F4F',
		'DarkTurquoise' => '#00CED1',
		'DarkViolet' => '#9400D3',
		'DeepPink' => '#FF1493',
		'DeepSkyBlue' => '#00BFFF',
		'DimGray' => '#696969',
		'DimGrey' => '#696969',
		'DodgerBlue' => '#1E90FF',
		'FireBrick' => '#B22222',
		'FloralWhite' => '#FFFAF0',
		'ForestGreen' => '#228B22',
		'Fuchsia' => '#FF00FF',
		'Gainsboro' => '#DCDCDC',
		'GhostWhite' => '#F8F8FF',
		'Gold' => '#FFD700',
		'GoldenRod' => '#DAA520',
		'Gray' => '#808080',
		'Grey' => '#808080',
		'Green' => '#008000',
		'GreenYellow' => '#ADFF2F',
		'HoneyDew' => '#F0FFF0',
		'HotPink' => '#FF69B4',
		'IndianRed ' => '#CD5C5C',
		'Indigo ' => '#4B0082',
		'Ivory' => '#FFFFF0',
		'Khaki' => '#F0E68C',
		'Lavender' => '#E6E6FA',
		'LavenderBlush' => '#FFF0F5',
		'LawnGreen' => '#7CFC00',
		'LemonChiffon' => '#FFFACD',
		'LightBlue' => '#ADD8E6',
		'LightCoral' => '#F08080',
		'LightCyan' => '#E0FFFF',
		'LightGoldenRodYellow' => '#FAFAD2',
		'LightGray' => '#D3D3D3',
		'LightGrey' => '#D3D3D3',
		'LightGreen' => '#90EE90',
		'LightPink' => '#FFB6C1',
		'LightSalmon' => '#FFA07A',
		'LightSeaGreen' => '#20B2AA',
		'LightSkyBlue' => '#87CEFA',
		'LightSlateGray' => '#778899',
		'LightSlateGrey' => '#778899',
		'LightSteelBlue' => '#B0C4DE',
		'LightYellow' => '#FFFFE0',
		'Lime' => '#00FF00',
		'LimeGreen' => '#32CD32',
		'Linen' => '#FAF0E6',
		'Magenta' => '#FF00FF',
		'Maroon' => '#800000',
		'MediumAquaMarine' => '#66CDAA',
		'MediumBlue' => '#0000CD',
		'MediumOrchid' => '#BA55D3',
		'MediumPurple' => '#9370D8',
		'MediumSeaGreen' => '#3CB371',
		'MediumSlateBlue' => '#7B68EE',
		'MediumSpringGreen' => '#00FA9A',
		'MediumTurquoise' => '#48D1CC',
		'MediumVioletRed' => '#C71585',
		'MidnightBlue' => '#191970',
		'MintCream' => '#F5FFFA',
		'MistyRose' => '#FFE4E1',
		'Moccasin' => '#FFE4B5',
		'NavajoWhite' => '#FFDEAD',
		'Navy' => '#000080',
		'OldLace' => '#FDF5E6',
		'Olive' => '#808000',
		'OliveDrab' => '#6B8E23',
		'Orange' => '#FFA500',
		'OrangeRed' => '#FF4500',
		'Orchid' => '#DA70D6',
		'PaleGoldenRod' => '#EEE8AA',
		'PaleGreen' => '#98FB98',
		'PaleTurquoise' => '#AFEEEE',
		'PaleVioletRed' => '#D87093',
		'PapayaWhip' => '#FFEFD5',
		'PeachPuff' => '#FFDAB9',
		'Peru' => '#CD853F',
		'Pink' => '#FFC0CB',
		'Plum' => '#DDA0DD',
		'PowderBlue' => '#B0E0E6',
		'Purple' => '#800080',
		'Red' => '#FF0000',
		'RosyBrown' => '#BC8F8F',
		'RoyalBlue' => '#4169E1',
		'SaddleBrown' => '#8B4513',
		'Salmon' => '#FA8072',
		'SandyBrown' => '#F4A460',
		'SeaGreen' => '#2E8B57',
		'SeaShell' => '#FFF5EE',
		'Sienna' => '#A0522D',
		'Silver' => '#C0C0C0',
		'SkyBlue' => '#87CEEB',
		'SlateBlue' => '#6A5ACD',
		'SlateGray' => '#708090',
		'SlateGrey' => '#708090',
		'Snow' => '#FFFAFA',
		'SpringGreen' => '#00FF7F',
		'SteelBlue' => '#4682B4',
		'Tan' => '#D2B48C',
		'Teal' => '#008080',
		'Thistle' => '#D8BFD8',
		'Tomato' => '#FF6347',
		'Turquoise' => '#40E0D0',
		'Violet' => '#EE82EE',
		'Wheat' => '#F5DEB3',
		'White' => '#FFFFFF',
		'WhiteSmoke' => '#F5F5F5',
		'Yellow' => '#FFFF00',
		'YellowGreen' => '#9ACD32'
	);


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

						$array_code[$k_code]['properties'][$k_property]['value'][$k_value] = preg_replace_callback('/color\((((rgba?|hsla?)?\([^\)]+\))?[^\)]+)\)/', array($this, 'colorCallback'), $value);
					}
				}
			}

			if ($code['content']) {
				$array_code[$k_code]['content'] = $this->_transform($code['content']);
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
			if (strpos($operation, ':') === false) {
				if (preg_match('/^[0-9]+$/', $operation)) {
					$function = 'tint';
					$value = $operation;
				} else {
					continue;
				}
			} else {
				list($function, $value) = $this->Css->explodeTrim(':', $operation, 2);
			}

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
				
				case 'tint':
					$rgba[0] += round(((255 - $rgba[0]) * (100 - floatval($value))) / 100);
					$rgba[1] += round(((255 - $rgba[1]) * (100 - floatval($value))) / 100);
					$rgba[2] += round(((255 - $rgba[2]) * (100 - floatval($value))) / 100);
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
		if (isset($this->color_names[$color])) {
			return $this->HEX_RGBA(substr($this->color_names[$color], 1));
		}
		if (preg_match('/rgb\((\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return array(intval($matches[1]), intval($matches[2]), intval($matches[3]), 1);
		}
		if (preg_match('/rgba\((\d+)[,\s]+(\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return array(intval($matches[1]), intval($matches[2]), intval($matches[3]), floatval($matches[4]));
		}
		if (preg_match('/hsl\((\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return $this->HSLA_RGBA(array(intval($matches[1]), intval($matches[2]), intval($matches[3]), 1));
		}
		if (preg_match('/hsla\((\d+)[,\s]+(\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return $this->HSLA_RGBA(array(intval($matches[1]), intval($matches[2]), intval($matches[3]), floatval($matches[4])));
		}

		return array(0, 0, 0, 1);
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

		if ($s === 0) {
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
			return ($v2 < 0) ? 0 : $v2;
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

		$s = round($s * 100);

		if ($s < 0) {
			$s *= -1;
		}

		if ($s > 100) {
			$s = 100;
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

		return array($h, $s.'%', round($l * 100).'%', $a);
	}



	/**
	 * private function RGBA_HEX (array $rgba)
	 *
	 * return string
	 */
	private function RGBA_HEX ($rgba) {
		$r = dechex(($rgba[0] > 255) ? 255 : $rgba[0]);
		$g = dechex(($rgba[1] > 255) ? 255 : $rgba[1]);
		$b = dechex(($rgba[2] > 255) ? 255 : $rgba[2]);

		if (strlen($r) === 1) {
			$r = str_pad($r, 2, 0, STR_PAD_LEFT);
		}
		if (strlen($g) === 1) {
			$g = str_pad($g, 2, 0, STR_PAD_LEFT);
		}
		if (strlen($b) === 1) {
			$b = str_pad($b, 2, 0, STR_PAD_LEFT);
		}

		return strtoupper($r.$g.$b);
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