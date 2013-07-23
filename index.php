<?php
include('Stylecow/autoloader.php');

use Stylecow\Parser;

$plugins = array(
	'Color' => array(
		'checked' => false,
		'options' => array()
	),
	'Grid' => array(
		'checked' => false,
		'options' => array()
	),
	'IeBackgroundAlpha' => array(
		'checked' => false,
		'options' => array()
	),
	'IeClip' => array(
		'checked' => false,
		'options' => array()
	),
	'IeFloat' => array(
		'checked' => false,
		'options' => array()
	),
	'IeInlineBlock' => array(
		'checked' => false,
		'options' => array()
	),
	'IeLinearGradient' => array(
		'checked' => false,
		'options' => array()
	),
	'IeMinHeight' => array(
		'checked' => false,
		'options' => array()
	),
	'IeOpacity' => array(
		'checked' => false,
		'options' => array()
	),
	'Initial' => array(
		'checked' => false,
		'options' => array()
	),
	'Matches' => array(
		'checked' => true,
		'options' => array()
	),
	'Math' => array(
		'checked' => false,
		'options' => array()
	),
	'MediaQuery' => array(
		'checked' => false,
		'options' => array(
			'width' => array('string', ''),
			'type' => array('string', 'all')
		)
	),
	'NestedRules' => array(
		'checked' => true,
		'options' => array()
	),
	'Rem' => array(
		'checked' => true,
		'options' => array()
	),
	'IeTransform' => array(
		'checked' => false,
		'options' => array()
	),
	'Variables' => array(
		'checked' => true,
		'options' => array()
	),
	'VendorPrefixes' => array(
		'checked' => true,
		'options' => array()
	)
);


$input_code = $output_code = '';

//If the form has been send
if (isset($_POST['send'])) {

	//Initialize stylecow
	$input_code = $_POST['code'];
	$css = Parser::parseString($input_code);

	//Load the plugin configuration
	$applyPlugins = array();

	foreach ($plugins as $plugin => $settings) {
		if (isset($_POST['plugin'][$plugin])) {
			$applyPlugins[$plugin] = array();
			$plugins[$plugin]['checked'] = true;

			foreach ($settings['options'] as $name => $value) {
				$valuePost = isset($_POST['pluginOptions'][$plugin][$name]) ? $_POST['pluginOptions'][$plugin][$name] : '';

				if ($valuePost === '') {
					switch ($value[0]) {
						case 'bool':
							$plugins[$plugin]['options'][$name][1] = false;
							break;

						default:
							$plugins[$plugin]['options'][$name][1] = null;
							break;
					}
				} else {
					$plugins[$plugin]['options'][$name][1] = $valuePost;
				}

				$applyPlugins[$plugin][$name] = $plugins[$plugin]['options'][$name][1];
			}

		} else {
			$plugins[$plugin]['checked'] = false;
		}
	}

	//Apply the plugins
	$css->applyPlugins($applyPlugins);

	//Get the code
	$output_code = $css->toString(array('sourceMap' => true));
}
?>

<!doctype html>

<html>
	<head>
		<title>Stylecow converter</title>

		<style type="text/css">
			body {
				font-family: Helvetica, Arial;
			}
			body > h1 {
				padding-top: 20px;
				margin: 0;
			}
			body > p {
				margin: 0 0 20px 0;
			}
			body {
				font-family: Helvetica, Arial;
			}
			body .input {
				margin-bottom: 20px;
			}
			textarea {
				width: 100%;
				height: 300px;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
			}
			.output h1 {
				margin: 0 0 10px 0;
			}
			.output textarea {
				height: 600px;
			}
			fieldset {
				margin-bottom: 20px;
			}
			fieldset > div {
				-webkit-column-count: 2;
				-moz-column-count: 2;
				column-count: 2;
			}
			fieldset ul {
				margin: 0 0 10px 0;
				padding-left: 25px;
				list-style: none;
			}
			fieldset ul input {
				width: 50px;
			}
			legend {
				font-weight: bold;
			}
			button {
				font-size: 1.8em;
				cursor: pointer;
			}
			form {
				background: #CCC;
				padding: 20px;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
				border-radius: 10px;
			}
			@media (min-width: 600px) {
				body .input {
					float: left;
					width: 50%;
					margin-right: 20px;
				}
				body .output {
					overflow: hidden;
				}
			}
		</style>
	</head>

	<body>
		<h1>Stylecow demo</h1>
		<p>Code and documentation in <a href="https://github.com/oscarotero/stylecow">github</a></p>

		<form class="input" action="index.php" method="post">
			<input type="hidden" name="send" value="1">
			<fieldset>
				<legend>Code to convert</legend>

				<textarea name="code" onkeydown="insertTab(this, event);"><?php echo $input_code; ?></textarea>
				<button type="submit">Convert code</button>
			</fieldset>

			<fieldset>
				<legend>Plugins to apply</legend>

				<div>

				<?php foreach ($plugins as $plugin => $settings): ?>
					<label><input type="checkbox" name="plugin[<?php echo $plugin; ?>]" value="1"<?php echo $settings['checked'] ? ' checked' : ''; ?>> <?php echo $plugin; ?></label><br>
					<?php if ($settings['options']): ?>
						<ul>
							<?php foreach ($settings['options'] as $name => $value): ?>
								<li>
									<label><?php echo $name ?>
										<?php if ($value[0] === 'bool'): ?>
										<input type="checkbox" name="pluginOptions[<?php echo $plugin; ?>][<?php echo $name; ?>]" value="1"<?php echo empty($value[1]) ? '' : ' checked'; ?>>
										<?php elseif ($value[0] === 'null'): ?>
										<input type="checkbox" name="pluginOptions[<?php echo $plugin; ?>][<?php echo $name; ?>]" value="1"<?php echo empty($value[1]) ? '' : ' checked'; ?>>
										<?php else: ?>
										<input type="text" name="pluginOptions[<?php echo $plugin; ?>][<?php echo $name; ?>]" value="<?php echo $value[1]; ?>">
										<?php endif; ?>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endforeach; ?>
				</div>
			</fieldset>
		</form>

		<section class="output">
			<h1>Result</h1>

			<textarea onkeydown="insertTab(this, event);"><?php echo $output_code; ?></textarea>
		</section>

		<script type="text/javascript">
			var insertTab = function (o, e) {
				var kC = e.keyCode ? e.keyCode : e.charCode ? e.charCode : e.which;
				
				if (kC == 9 && !e.shiftKey && !e.ctrlKey && !e.altKey) {
					var oS = o.scrollTop;

					if (o.setSelectionRange) {
						var sS = o.selectionStart;
						var sE = o.selectionEnd;
						o.value = o.value.substring(0, sS) + "\t" + o.value.substr(sE);
						o.setSelectionRange(sS + 1, sS + 1);
						o.focus();
					} else if (o.createTextRange) {
						document.selection.createRange().text = "\t";
						e.returnValue = false;
					}

					o.scrollTop = oS;

					if (e.preventDefault) {
						e.preventDefault();
					}

					return false;
				}

				return true;
			}
		</script>
	</body>
</html>
