<?php
/**
 * Stylecow PHP library
 *
 * Color plugin
 * To manipulate colors dinamically.
 *
 * Examples:
 * background: color(#ccc, tint:50)
 * background: color(red, light:+10)
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 1.0.0 (2012)
 */

namespace Stylecow\Plugins;

use Stylecow\Stylecow;

class Color extends Plugin implements PluginsInterface {
	static protected $position = 4;
	static protected $color_names = array(
		'aliceblue' => '#F0F8FF',
		'antiquewhite' => '#FAEBD7',
		'aqua' => '#00FFFF',
		'aquamarine' => '#7FFFD4',
		'azure' => '#F0FFFF',
		'beige' => '#F5F5DC',
		'bisque' => '#FFE4C4',
		'black' => '#000000',
		'blanchedalmond' => '#FFEBCD',
		'blue' => '#0000FF',
		'blueviolet' => '#8A2BE2',
		'brown' => '#A52A2A',
		'burlywood' => '#DEB887',
		'cadetblue' => '#5F9EA0',
		'chartreuse' => '#7FFF00',
		'chocolate' => '#D2691E',
		'coral' => '#FF7F50',
		'cornflowerblue' => '#6495ED',
		'cornsilk' => '#FFF8DC',
		'crimson' => '#DC143C',
		'cyan' => '#00FFFF',
		'darkblue' => '#00008B',
		'darkcyan' => '#008B8B',
		'darkgoldenrod' => '#B8860B',
		'darkgray' => '#A9A9A9',
		'darkgrey' => '#A9A9A9',
		'darkgreen' => '#006400',
		'darkkhaki' => '#BDB76B',
		'darkmagenta' => '#8B008B',
		'darkolivegreen' => '#556B2F',
		'darkorange' => '#FF8C00',
		'darkorchid' => '#9932CC',
		'darkred' => '#8B0000',
		'darksalmon' => '#E9967A',
		'darkseagreen' => '#8FBC8F',
		'darkslateblue' => '#483D8B',
		'darkslategray' => '#2F4F4F',
		'darkslategrey' => '#2F4F4F',
		'darkturquoise' => '#00CED1',
		'darkviolet' => '#9400D3',
		'deeppink' => '#FF1493',
		'deepskyblue' => '#00BFFF',
		'dimgray' => '#696969',
		'dimgrey' => '#696969',
		'dodgerblue' => '#1E90FF',
		'firebrick' => '#B22222',
		'floralwhite' => '#FFFAF0',
		'forestgreen' => '#228B22',
		'fuchsia' => '#FF00FF',
		'gainsboro' => '#DCDCDC',
		'ghostwhite' => '#F8F8FF',
		'gold' => '#FFD700',
		'goldenrod' => '#DAA520',
		'gray' => '#808080',
		'grey' => '#808080',
		'green' => '#008000',
		'greenyellow' => '#ADFF2F',
		'honeydew' => '#F0FFF0',
		'hotpink' => '#FF69B4',
		'indianred ' => '#CD5C5C',
		'indigo ' => '#4B0082',
		'ivory' => '#FFFFF0',
		'khaki' => '#F0E68C',
		'lavender' => '#E6E6FA',
		'lavenderblush' => '#FFF0F5',
		'lawngreen' => '#7CFC00',
		'lemonchiffon' => '#FFFACD',
		'lightblue' => '#ADD8E6',
		'lightcoral' => '#F08080',
		'lightcyan' => '#E0FFFF',
		'lightgoldenrodyellow' => '#FAFAD2',
		'lightgray' => '#D3D3D3',
		'lightgrey' => '#D3D3D3',
		'lightgreen' => '#90EE90',
		'lightpink' => '#FFB6C1',
		'lightsalmon' => '#FFA07A',
		'lightseagreen' => '#20B2AA',
		'lightskyblue' => '#87CEFA',
		'lightslategray' => '#778899',
		'lightslategrey' => '#778899',
		'lightsteelblue' => '#B0C4DE',
		'lightyellow' => '#FFFFE0',
		'lime' => '#00FF00',
		'limegreen' => '#32CD32',
		'linen' => '#FAF0E6',
		'magenta' => '#FF00FF',
		'maroon' => '#800000',
		'mediumaquamarine' => '#66CDAA',
		'mediumblue' => '#0000CD',
		'mediumorchid' => '#BA55D3',
		'mediumpurple' => '#9370D8',
		'mediumseagreen' => '#3CB371',
		'mediumslateblue' => '#7B68EE',
		'mediumspringgreen' => '#00FA9A',
		'mediumturquoise' => '#48D1CC',
		'mediumvioletred' => '#C71585',
		'midnightblue' => '#191970',
		'mintcream' => '#F5FFFA',
		'mistyrose' => '#FFE4E1',
		'moccasin' => '#FFE4B5',
		'navajowhite' => '#FFDEAD',
		'navy' => '#000080',
		'oldlace' => '#FDF5E6',
		'olive' => '#808000',
		'olivedrab' => '#6B8E23',
		'orange' => '#FFA500',
		'orangered' => '#FF4500',
		'orchid' => '#DA70D6',
		'palegoldenrod' => '#EEE8AA',
		'palegreen' => '#98FB98',
		'paleturquoise' => '#AFEEEE',
		'palevioletred' => '#D87093',
		'papayawhip' => '#FFEFD5',
		'peachpuff' => '#FFDAB9',
		'peru' => '#CD853F',
		'pink' => '#FFC0CB',
		'plum' => '#DDA0DD',
		'powderblue' => '#B0E0E6',
		'purple' => '#800080',
		'red' => '#FF0000',
		'rosybrown' => '#BC8F8F',
		'royalblue' => '#4169E1',
		'saddlebrown' => '#8B4513',
		'salmon' => '#FA8072',
		'sandybrown' => '#F4A460',
		'seagreen' => '#2E8B57',
		'seashell' => '#FFF5EE',
		'sienna' => '#A0522D',
		'silver' => '#C0C0C0',
		'skyblue' => '#87CEEB',
		'slateblue' => '#6A5ACD',
		'slategray' => '#708090',
		'slategrey' => '#708090',
		'snow' => '#FFFAFA',
		'springgreen' => '#00FF7F',
		'steelblue' => '#4682B4',
		'tan' => '#D2B48C',
		'teal' => '#008080',
		'thistle' => '#D8BFD8',
		'tomato' => '#FF6347',
		'turquoise' => '#40E0D0',
		'violet' => '#EE82EE',
		'wheat' => '#F5DEB3',
		'white' => '#FFFFFF',
		'whitesmoke' => '#F5F5F5',
		'yellow' => '#FFFF00',
		'yellowgreen' => '#9ACD32'
	);



	/**
	 * Search for color() function and execute it
	 *
	 * @param array $array_code The piece of the parsed css code
	 *
	 * @return array The transformed code
	 */
	public function transform (array $array_code) {
		$self = $this;

		return Stylecow::propertiesWalk($array_code, function ($properties) use ($self) {
			return Stylecow::valueWalk($properties, function ($value) use ($self) {
				return Stylecow::executeFunctions($value, 'color', function ($arguments) use ($self) {
					return $self->processColor(array_shift($arguments), $arguments);
				});
			});
		});
	}



	/**
	 * The internal callback to replace each color() function with the css color syntax
	 *
	 * @param array $matches The matches found in the preg_replace_callback
	 *
	 * @return array The transformed color
	 */
	public function processColor ($color, $operations) {
		$rgba = self::toRGBA($color);

		foreach ($operations as $operation) {
			if (strpos($operation, ':') === false) {
				if (preg_match('/^[0-9]+$/', $operation)) {
					$function = 'tint';
					$value = $operation;
				} else {
					continue;
				}
			} else {
				list($function, $value) = Stylecow::explodeTrim(':', $operation, 2);
			}

			switch ($function) {
				case 'hue':
				case 'saturation':
				case 'light':
					$hsla = self::RGBA_HSLA($rgba);
					$this->editChannel($hsla, $function, $value);
					$rgba = self::HSLA_RGBA($hsla);
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

		return '#'.self::RGBA_HEX($rgba);
	}



	/**
	 * Edit one of the color channel (red, green, blue, alpha, hue, saturation and light)
	 *
	 * @param array       &$channels     The array with all channels of the color
	 * @param array       $channel_name  The name of the channel to edit
	 * @param string/int  $new_value     The new value of the channel
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
			$new_value = $channel_info[1];
		} else if ($new_value < 0) {
			$new_value = 0;
		}

		$channels[$channel_info[0]] = $new_value;
	}



	/**
	 * Convert any css color syntax to rgba
	 *
	 * @param string  $color  The css color syntax
	 *
	 * @return array  The rgba value
	 */
	static public function toRGBA ($color) {
		if ($color[0] === '#') {
			return self::HEX_RGBA(substr($color, 1));
		}
		if (isset(self::$color_names[strtolower($color)])) {
			return self::HEX_RGBA(substr(self::$color_names[strtolower($color)], 1));
		}
		if (preg_match('/rgb\((\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return array(intval($matches[1]), intval($matches[2]), intval($matches[3]), 1);
		}
		if (preg_match('/rgba\((\d+)[,\s]+(\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return array(intval($matches[1]), intval($matches[2]), intval($matches[3]), floatval($matches[4]));
		}
		if (preg_match('/hsl\((\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return self::HSLA_RGBA(array(intval($matches[1]), intval($matches[2]), intval($matches[3]), 1));
		}
		if (preg_match('/hsla\((\d+)[,\s]+(\d+)[,\s]+(\d+)[,\s]+(\d+)\)/', $color, $matches)) {
			return self::HSLA_RGBA(array(intval($matches[1]), intval($matches[2]), intval($matches[3]), floatval($matches[4])));
		}

		return array(0, 0, 0, 1);
	}



	/**
	 * Convert a HSLA color to RGBA
	 *
	 * @param array  $hsla  The hsla color values
	 *
	 * @return array  The rgba value
	 */
	static public function HSLA_RGBA ($hsla) {
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
			round(255 * self::Hue_RGB($var_1, $var_2, $h + (1/3), true)),
			round(255 * self::Hue_RGB($var_1, $var_2, $h)),
			round(255 * self::Hue_RGB($var_1, $var_2, $h - (1/3))),
			$a
		);
	}


	/**
	 * Internal function used by HSLA_RGBA() to convert a Hue value to R, G, and B
	 */
	static public function Hue_RGB ($v1, $v2, $vH, $k = false) {
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
	 * Convert RGBA color values to HSLA
	 *
	 * @param array  $rgba  The rgba color values
	 *
	 * @return array  The hsla value
	 */
	static public function RGBA_HSLA ($rgba) {
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
	 * Convert RGBA color values to hexadecimal
	 *
	 * @param array  $rgba  The rgba color values
	 *
	 * @return string  The hexadecimal value
	 */
	static public function RGBA_HEX ($rgba) {
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
	 * Convert hexadecimal color value to RGBA
	 *
	 * @param string  $hex  The hex color value
	 *
	 * @return array  The rgba value
	 */
	static public function HEX_RGBA ($hex) {
		if (strlen($hex) === 3) {
			list($r, $g, $b) = array($hex[0].$hex[0], $hex[1].$hex[1], $hex[2].$hex[2]);
		} else {
			list($r, $g, $b) = str_split($hex, 2);
		}

		return array(hexdec($r), hexdec($g), hexdec($b), 1);
	}
}