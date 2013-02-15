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

use Stylecow\Parser;
use Stylecow\Css;

class Color {
	const POSITION = 4;

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
	 * Apply the plugin to Css object
	 *
	 * @param Stylecow\Css $css The css object
	 */
	static public function apply (Css $css) {
		$css->executeRecursive(function ($code) {
			foreach ($code->getProperties() as $property) {
				$property->executeFunction('color', function ($parameters) {
					$rgba = Color::resolveColor(array_shift($parameters));

					foreach ($parameters as $operation) {
						if (strpos($operation, ':') === false) {
							if (preg_match('/^[\+\-]?[0-9]+$/', $operation)) {
								$function = 'tint';
								$value = $operation;
							} else if (preg_match('/^[\+\-]?[0-9\.]+$/', $operation)) {
								$function = 'alpha';
								$value = $operation;
							} else {
								continue;
							}
						} else {
							list($function, $value) = Parser::explodeTrim(':', $operation, 2);
						}

						switch ($function) {
							case 'hue':
							case 'saturation':
							case 'light':
								$rgba = Color::HSLA_RGBA(Color::editChannel(Color::RGBA_HSLA($rgba), $function, $value));
								break;
							
							case 'red':
							case 'green':
							case 'blue':
							case 'alpha':
								$rgba = Color::editChannel($rgba, $function, $value);
								break;
							
							case 'tint':
								$rgba = Color::editTint($rgba, $value);
								break;
						}
					}

					if ($rgba[3] < 1) {
						return 'rgba('.implode(', ', $rgba).')';
					}

					return '#'.Color::RGBA_HEX($rgba);
				});
			}
		});
	}


	/**
	 * Converts a color declaration in rgba format.
	 *
	 * @param string $colors The css color declaration
	 * 
	 * @return array The rgba color values
	 */
	static public function resolveColor ($colors) {
		$colors = Parser::explodeTrim(' ', $colors);

		if (count($colors) === 1) {
			return Color::toRGBA($colors[0]);
		}

		$sumColors = array(0, 0, 0, 0);

		foreach ($colors as $k => $color) {
			$color = Color::toRGBA($color);

			$sumColors[0] += $color[0];
			$sumColors[1] += $color[1];
			$sumColors[2] += $color[2];
			$sumColors[3] += $color[3];
		}

		$total = count($colors);

		$sumColors[0] = round($sumColors[0]/$total);
		$sumColors[1] = round($sumColors[1]/$total);
		$sumColors[2] = round($sumColors[2]/$total);
		$sumColors[3] = round($sumColors[3]/$total);

		return $sumColors;
	}



	/**
	 * Edit one of the color channel (red, green, blue, alpha, hue, saturation and light)
	 *
	 * @param array $color The array with all channels of the color
	 * @param array $channel The name of the channel to edit
	 * @param string/int $value The new value of the channel
	 */
	static public function editChannel ($color, $channel, $value) {
		static $channels = array(
			'hue' => array(0, 255),
			'saturation' => array(1, 100),
			'light' => array(2, 100),
			'red' => array(0, 255),
			'green' => array(1, 255),
			'blue' => array(2, 255),
			'alpha' => array(3, 1)
		);

		$channel = $channels[$channel];

		if (!$channel) {
			return $color;
		}

		if ($value[0] === '+' || $value[0] === '-') {
			$value = floatval($color[$channel[0]]) + floatval($value);
		}

		if ($value > $channel[1]) {
			$value = $channel[1];
		} else if ($value < 0) {
			$value = 0;
		}

		$color[$channel[0]] = $value;

		return $color;
	}



	/**
	 * Edit the tint of a color
	 *
	 * @param array $rgba The array with all channels of the color in rgba
	 */
	static public function editTint ($rgba, $tint) {
		$rgba[0] += round(((255 - $rgba[0]) * (100 - floatval($tint))) / 100);
		$rgba[1] += round(((255 - $rgba[1]) * (100 - floatval($tint))) / 100);
		$rgba[2] += round(((255 - $rgba[2]) * (100 - floatval($tint))) / 100);

		return $rgba;
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
		if (strtolower($color) === 'transparent') {
			return array(0, 0, 0, 0);
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

		$s = intval($s)/100;
		$l = intval($l)/100;

		if ($h > 0) {
			$h /= 360;
		}

		if ($s === 0) {
			$r = $l;
			$g = $l;
			$b = $l;
		} else {
			if ($l < .5) {
				$t2 = $l * (1.0 + $s);
			} else {
				$t2 = ($l + $s) - ($l * $s);
			}

			$t1 = 2.0 * $l - $t2;

			$rt3 = $h + 1.0/3.0;
			$gt3 = $h;
			$bt3 = $h - 1.0/3.0;

			if ($rt3 < 0) {
				$rt3 += 1.0;
			}
			if ($rt3 > 1) {
				$rt3 -= 1.0;
			}
			if ($gt3 < 0) {
				$gt3 += 1.0;
			}
			if ($gt3 > 1) {
				$gt3 -= 1.0;
			}
			if ($bt3 < 0) {
				$bt3 += 1.0;
			}
			if ($bt3 > 1) {
				$bt3 -= 1.0;
			}

			if (6.0 * $rt3 < 1) {
				$r = $t1 + ($t2 - $t1) * 6.0 * $rt3;
			} else if (2.0 * $rt3 < 1) {
				$r = $t2;
			} else if (3.0 * $rt3 < 2) {
				$r = $t1 + ($t2 - $t1) * ((2.0/3.0) - $rt3) * 6.0;
			} else {
				$r = $t1;
			}

			if (6.0 * $gt3 < 1) {
				$g = $t1 + ($t2 - $t1) * 6.0 * $gt3;
			} else if (2.0 * $gt3 < 1) {
				$g = $t2;
			} else if (3.0 * $gt3 < 2) {
				$g = $t1 + ($t2 - $t1) * ((2.0/3.0) - $gt3) * 6.0;
			} else {
				$g = $t1;
			}

			if (6.0 * $bt3 < 1) {
				$b = $t1 + ($t2 - $t1) * 6.0 * $bt3;
			} else if (2.0 * $bt3 < 1) {
				$b = $t2;
			} else if (3.0 * $bt3 < 2) {
				$b = $t1 + ($t2 - $t1) * ((2.0/3.0) - $bt3) * 6.0;
			} else {
				$b = $t1;
			}
		}

		$r = (int)round(255.0 * $r);
		$g = (int)round(255.0 * $g);
		$b = (int)round(255.0 * $b);

		return array($r, $g, $b, $a);
	}



	/**
	 * Convert RGBA color values to HSLA
	 *
	 * @param array  $rgba  The rgba color values
	 *
	 * @return array  The hsla value
	 */
	static public function RGBA_HSLA ($rgba) {
		$r = $rgba[0] / 255;
		$g = $rgba[1] / 255;
		$b = $rgba[2] / 255;
		$a = $rgba[3];

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);
		$delta = $max - $min;

		$l = ($max + $min) / 2;

		if ($delta == 0) {
			$h = 0;
			$s = 0;
		} else {
			if ($l < 0.5) {
				$s = $delta / ($max + $min);
			} else {
				$s = $delta / ( 2 - $max - $min );
			}

			$delta_r = ((($max - $r) / 6) + ($delta / 2)) / $delta;
			$delta_g = ((($max - $g) / 6) + ($delta / 2)) / $delta;
			$delta_b = ((($max - $b) / 6) + ($delta / 2)) / $delta;

			if ($r === $max) {
				$h = $delta_b - $delta_g;
			} else if ($g === $max) {
				$h = ( 1 / 3 ) + $delta_r - $delta_b;
			} else if ($b === $max) {
				$h = ( 2 / 3 ) + $delta_g - $delta_r;
			}

			if ($h < 0) {
				$h += 1;
			}

			if ($h > 1) {
				$h -= 1;
			}
		}

		return array(round($h * 360), (round($s, 2) * 100).'%', (round($l, 2) * 100).'%', $a);
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
