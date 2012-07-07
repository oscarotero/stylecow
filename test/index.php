<?php
use Stylecow\Parser;

$plugins = array(
	'Color' => true,
	'Grid' => true,
	'IeFilters' => true,
	'Matches' => true,
	'Math' => true,
	'NestedRules' => true,
	'Rem' => true,
	'Variables' => true,
	'VendorPrefixes' => true
);
$input_code = $output_code = '';

//If the form has been send
if (isset($_POST['send'])) {

	//Use a loader PSR-0 compatible
	include('Loader.php');

	Loader::setLibrariesPath(dirname(__DIR__));
	Loader::register();


	//Initialize stylecow
	$input_code = $_POST['code'];
	$css = Parser::parseString($input_code);


	//Load the css file
	$applyPlugins = array();

	foreach (array_keys($plugins) as $plugin) {
		if (isset($_POST['plugin'][$plugin])) {
			$applyPlugins[] = $plugin;
		} else {
			$plugins[$plugin] = false;
		}
	}

	$css->applyPlugins($applyPlugins);

	//Get the code
	$output_code = $css->toString();
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
				<legend>Plugins to apply</legend>

				<?php foreach ($plugins as $plugin => $checked): ?>
				<label><input type="checkbox" name="plugin[<?php echo $plugin; ?>]"<?php echo $checked ? ' checked' : ''; ?>> <?php echo $plugin; ?></label><br>
				<?php endforeach; ?>
			</fieldset>

			<fieldset>
				<legend>Code to convert</legend>

				<textarea name="code" onkeydown="insertTab(this, event);"><?php echo $input_code; ?></textarea>
			</fieldset>

			<button type="submit">Convert code</button>
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