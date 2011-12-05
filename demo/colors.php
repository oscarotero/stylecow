<html>
	<head></head>

	<style>
		span {
			display: inline-block;
			width: 50px;
			height: 50px;
		}
	</style>
	<body>
		<?php

/**
	 * function HSL_RGB (array $hsl)
	 *
	 * return array
	 */
	function HSL_RGB ($hsl) {
		list($h, $s, $l) = $hsl;

		$h = intval($h)/360;
		$s = intval($s)/100;
		$l = intval($l)/100;

		if ($s == 0) {
			return array(
				round($l * 255),
				round($l * 255),
				round($l * 255)
			);
		}

		if ($l < 0.5) {
			$var_2 = $l * (1 + $s);
		} else {
			$var_2 = ($l + $s) - ($s * $l);
		}

		$var_1 = 2 * $l - $var_2;

		return array(
			round(255 * Hue_RGB($var_1, $var_2, $h + (1/3))),
			round(255 * Hue_RGB($var_1, $var_2, $h)),
			round(255 * Hue_RGB($var_1, $var_2, $h - (1/3)))
		);
	}



	/**
	 * function RGB_HSL (array $rgb)
	 *
	 * return array
	 */
	function RGB_HSL ($rgb) {
		list($r, $g, $b) = $rgb;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);
		$del = $max - $min;

		$l = ($max + $min) / 510;

		if ($del === 0) {
			return array(0, '0%', round($l * 100).'%');
		}

		if ($l < 0.5) {
			$s = $del / ($max + $min);
		} else {
			$s = $del / (2 - $max - $min);
		}

		if ($r === $max) {
			$h = ($g - $b ) / $del;
		} else if ($g === $max) {
			$h = 2 + ($b - $g) / $del;
		} else if ($b === $max) {
			$h = 4 + ($r - $g) / $del;
		}

		/*

		$del_r = ((($max - $r) / 6) + ($del / 2)) / $del;
		$del_g = ((($max - $g) / 6) + ($del / 2)) / $del;
		$del_b = ((($max - $b) / 6) + ($del / 2)) / $del;

		if ($r === $max) {
			$h = $del_b - $del_g;
		} else if ($g === $max) {
			$h = (1/3) + $del_r - $del_b;
		} else if ($b === $max) {
			$h = (2/3) + $del_g - $del_r;
		}

		if ($h < 0) {
			$h += 1;
		} else if ($h > 1) {
			$h -= 1;
		}
		
		$h = $h * 60;

		if ($h < 0) {
			$h += 360;
		}
		*/

		$h = round($h * 60);

		if ($h < 0) {
			$h += 360;
		}

		return array($h, round($s * 100).'%', round($l * 100).'%');
	}



	/**
	 * function RGB_HEX (array $rgb)
	 *
	 * return string
	 */
	function RGB_HEX ($rgb) {
		$r = dechex($rgb[0]);
		$g = dechex($rgb[1]);
		$b = dechex($rgb[2]);

		if (strlen($r) == 1) {
			$r = str_repeat($r, 2);
		}
		if (strlen($g) == 1) {
			$g = str_repeat($g, 2);
		}
		if (strlen($b) == 1) {
			$b = str_repeat($b, 2);
		}

		return $r.$g.$b;
	}



	/**
	 * function HEX_RGB (string $hex)
	 *
	 * return array
	 */
	function HEX_RGB ($hex) {
		if (strlen($hex) === 3) {
			list($r, $g, $b) = array($hex[0].$hex[0], $hex[1].$hex[1], $hex[2].$hex[2]);
		} else {
			list($r, $g, $b) = str_split($hex, 2);
		}

		return array(
			hexdec($r),
			hexdec($g),
			hexdec($b)
		);
	}



	/**
	 * function Hue_RGB (int $v1, int $v2, int $vH)
	 *
	 * return array
	 */
	function Hue_RGB ($v1, $v2, $vH) {
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

		function _color_rgb2hsl($rgb) {
			$r = $rgb[0];
			$g = $rgb[1];
			$b = $rgb[2];
			$min = min($r, min($g, $b));
			$max = max($r, max($g, $b));
			$delta = $max - $min;
			$l = ($min + $max) / 2;
			$s = 0;
			if ($l > 0 && $l < 1) {
				$s = $delta / ($l < 0.5 ? (2 * $l) : (2 - 2 * $l));
			}
			$h = 0;
			if ($delta > 0) {
				if ($max == $r && $max != $g) $h += ($g - $b) / $delta;
				if ($max == $g && $max != $b) $h += (2 + ($b - $r) / $delta);
				if ($max == $b && $max != $r) $h += (4 + ($r - $g) / $delta);
				$h /= 6;
			}
			return array($h, $s, $l);
		}

		function _color_hsl2rgb($hsl) {
			$h = $hsl[0];
			$s = $hsl[1];
			$l = $hsl[2];
			$m2 = ($l <= 0.5) ? $l * ($s + 1) : $l + $s - $l*$s;
			$m1 = $l * 2 - $m2;
			return array(_color_hue2rgb($m1, $m2, $h + 0.33333),
									 _color_hue2rgb($m1, $m2, $h),
									 _color_hue2rgb($m1, $m2, $h - 0.33333));
		}
		/**
		 * Helper function for _color_hsl2rgb().
		 */
		function _color_hue2rgb($m1, $m2, $h) {
			$h = ($h < 0) ? $h + 1 : (($h > 1) ? $h - 1 : $h);
			if ($h * 6 < 1) return $m1 + ($m2 - $m1) * $h * 6;
			if ($h * 2 < 1) return $m2;
			if ($h * 3 < 2) return $m1 + ($m2 - $m1) * (0.66666 - $h) * 6;
			return $m1;
		}
		/**
		 * Convert a hex color into an RGB triplet.
		 */
		function _color_unpack($hex, $normalize = false) {
			if (strlen($hex) == 4) {
				$hex = $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
			}
			$c = hexdec($hex);
			for ($i = 16; $i >= 0; $i -= 8) {
				$out[] = (($c >> $i) & 0xFF) / ($normalize ? 255 : 1);
			}
			return $out;
		}
		/**
		 * Convert an RGB triplet to a hex color.
		 */
		function _color_pack($rgb, $normalize = false) {
			foreach ($rgb as $k => $v) {
				$out |= (($v * ($normalize ? 255 : 1)) << (16 - $k * 8));
			}
			return '#'. str_pad(dechex($out), 6, 0, STR_PAD_LEFT);
		}

		/* $testrgb = array(0.2,0.75,0.4); //RGB to start with
		print_r($testrgb); */
		echo '<pre>';

			$testhex = "#C5003E";
			print "Hex: ".$testhex;
			echo '<span style="background: '.$testhex.'"></span>';
			echo "\n";


			$testhex2rgb = _color_unpack($testhex,true);
			$rgb = HEX_RGB(substr($testhex, 1));
			print "RGB: ";
			print_r($rgb);
			print_r($testhex2rgb);
			echo '<span style="background: rgb('.implode(',', $rgb).');"></span>';
			echo "\n";


			$hsl = RGB_HSL($rgb);
			$testrgb2hsl = _color_rgb2hsl($testhex2rgb); //Converteren naar HSL
			print "HSL color module: hsl(341, 100%, 39%)";
			print_r($hsl);

			print_r($testrgb2hsl);
			echo '<span style="background: hsl('.implode(',', $hsl).');"></span>';
			echo "\n";


			$testhsl2rgb = _color_hsl2rgb($testrgb2hsl); // En weer terug naar RGB
			$rgb = HSL_RGB($hsl);
			print "RGB: ";
			print_r($testhsl2rgb);
			print_r($rgb);
			echo '<span style="background: rgb('.implode(',', $rgb).');"></span>';
			echo "\n";


			die();
			print "Hex: ";

			$testrgb2hex = _color_pack($testhsl2rgb,true);
			print_r($testrgb2hex);

			echo '</pre>';
			?>

</body>
</html>