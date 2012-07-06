Stylecow
========

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>

GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html

Stylecow is a php library that allows parsing and manipulating css files.

Features:

* Written in php 5.3
* Includes the @import files (only for files with relative path)
* Extensible with plugins
* Easy to use
* Uses the PSR-0 autoloader standard


Why another CSS preprocessor?
------------------------------

The main purpose of Stylecow is to bring more CSS support to all browsers. You write CSS and you get CSS. You don't have to learn another different language such LESS, SASS, etc. Stylecow converts your code to be more compatible with all browsers throught plugins without writing any non-standard code. There is a plugin to add automatically the vendor prefixes to all selectors, properties and values in need. There is another plugin that allows using rem values with fallback for IE<=8. There is another plugin to use css variables with the same syntax of the w3c standard (http://dev.w3.org/csswg/css-variables/). And other plugins emulate some CSS effects (rotate, opacity, etc) in IE using the property "filter". So you can use Stylecow just to fix your CSS code and make it compatible with more browsers. And if you stop using Stylecow, your CSS code will remain CSS code.

But if you don't mind to write "non pure CSS code", there are more plugins that can help you to write styles faster. For example, Color allows manipulate colors changing some of the values (saturation, light, etc), Math can execute math operations, Grid makes easier to work with columns, etc.


How to use
----------

```php
//Load and parse the code
$css = Stylecow\Parser::parseFile('my-styles.css');

//Transform the css code using the plugins.
Stylecow\Plugins\Rem::aply($css);
Stylecow\Plugins\Variables::aply($css);
Stylecow\Plugins\VendorPrefixes::aply($css);

//You also can apply plugins in this way:
$css->applyPlugins(array(
	'Rem',
	'Variables',
	'VendorPrefixes'
));

//Print the result css code
echo $css->toString();
```


Plugins
=======

Stylecow provides some basic plugins (but you can make your owns):

Plugins to bring CSS support:

* [VendorPrefixes](#vendorprefixes) Adds automatically the vendor prefixes to all properties in need
* [Matches](#matches) Support for the CSS4 selector :matches()
* [Variables](#variables) Support for variables (W3C syntax)
* [IeFilters](#iefilters) IE support for some CSS effect (some 2d transform, opacity, background gradients, etc)
* [Rem](#rem) IE<=8 support for rem values

Other plugins with non-standard syntax:

* [NestedRules](#nestedrules) Brings nested rules support
* [Grid](#grid) Useful to work with one or various grids.
* [Color](#color) Provides the function color() to manipulate color values
* [Math](#math) Provides the function math() to execute math operations


VendorPrefixes
--------------

Adds the vendor prefixes to all properties in need. For example.

#### You write

```css
div.foo {
	border-radius: 4px;
	border-top-left-radius: 0;
	background: linear-gradient(#333, #999);
}

div.foo ::selection {
	background: red;
}
```

#### And Stylecow converts to

```css
div.foo {
	border-radius: 4px;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	-o-border-radius: 4px;
	border-top-left-radius: 0;
	-moz-border-radius-topleft: 0;
	-webkit-border-top-left-radius: 0;
	background: linear-gradient(#333, #999);
	background: -webkit-gradient(linear, left top, left bottom, from(#333), to(#999));
	background: -moz-linear-gradient(#333, #999);
	background: -webkit-linear-gradient(#333, #999);
}

div.foo ::selection {
	background: red;
}

div.foo ::-moz-selection {
	background: red;
}
```

Matches
-------

Resolve the :matches() css4 selector (http://www.w3.org/TR/2011/WD-selectors4-20110929/#matches)

#### You write

```css
div.foo :matches(h1, h2, h3, h4, h5, h6) a {
	color: blue;
}

div.foo :matches(article, section) header :matches(h1, h2) {
	color: black;
}
```

#### And Stylecow converts to

```css
div.foo h1 a,
div.foo h2 a,
div.foo h3 a,
div.foo h4 a,
div.foo h5 a,
div.foo h6 a {
	color: blue;
}

div.foo article header h1,
div.foo article header h2,
div.foo section header h1,
div.foo section header h2 {
	color: black;
}
```

Variables
---------

You can store values in variables to use later. The syntax is the same than the w3c syntax: http://dev.w3.org/csswg/css-variables/. For global variables (available in all properties), you have to define them in the selectors :root, html or body:

#### You write

```css
:root {
	var-title-font: Helvetica, Arial, sans-serif;
	var-title-size: 2em;
	var-title-color: red;
}

div.foo h1 {
	font-family: $title-font;
	font-size: $title-size;
	color: $title-color;
	border-bottom: solid 1px $title-color;
}

/* You can change the values of the variables just for one selector */
div.foo h2 {
	var-title-color: blue;

	font-family: $title-font;
	font-size: $title-size;
	color: $title-color;
	border-bottom: solid 1px $title-color;
}

/* And you can use the function var() to provide a fallback if the variable is not defined: */
div.foo h3 {
	font-family: $title-font;
	font-size: $title-size;
	font-weight: var(title-weight, bold);
	color: $title-color;
	border-bottom: solid 1px $title-color;
}
```


#### And Stylecow converts to

```css
div.foo h1 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 2em;
	color: red;
	border-bottom: solid 1px red;
}

div.foo h2 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 2em;
	color: blue;
	border-bottom: solid 1px blue;
}

div.foo h3 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 2em;
	font-weight: bold;
	color: red;
	border-bottom: solid 1px red;
}
```

You can define global variables in the settings of the plugin:

```php
$styleCow->transform(array(
	'Stylecow\\Plugins\\Variables' => array(
		'variables' => array(
			'title-color' => '#369',
			'title-size' => '2em'
		)
	)
));
```


IeFilters
---------

Adds Internet Explorer filters to emulate some css properties no supported by IE (for example, some 2d transform functions, opacity or linear gradients)

#### You write

```css
div.foo {
	background: linear-gradient(#666, #999);
	transform: rotate(45deg) scaleY(-1);
	opacity: 0.5;
}
```

#### And Stylecow converts to

```css
div.foo {
	background: linear-gradient(#666, #999);
	transform: rotate(45deg) scaleY(-1);
	opacity: 0.5;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#666666', endColorStr='#999999'), progid:DXImageTransform.Microsoft.Matrix(sizingMethod="auto expand", M11 = 0.707106781187, M12 = -0.707106781187, M21 = 0.707106781187, M22 = 0.707106781187), flipV, alpha(opacity=50);
}
```

You can configure which properties you want to fix in the settings (by default fix all available properties):

```php
$styleCow->transform(array(
	'Stylecow\\Plugins\\IeFilters' => array(
		'fix' => array('opacity') /* only fix the opacity property */
	)
));
```

The available properties are:

* opacity; Adds the filter to any element with -> opacity: N;
* rotate: Adds the filter to any element with -> transform: rotate(Ndeg);
* flip: Adds the filters flipH or flipV to any element with -> transform: scaleY(-1) / scaleX(-1) / scale(-1, -1)
* rgba: Adds the filter to any element with the background defined as rgba()
* linear-gradient: Adds the filter to any element with linear-gradient.


Rem
---

Allows use the rem value (http://snook.ca/archives/html_and_css/font-size-with-rem) to define the text size in a safe way for old browsers.

The default rem is 1em (16px) but you can change it with the :root, html or body selector.

#### You write

```css
body {
	font-size: 1.2em;
}
.foo {
	font-size: 2em;
}
.foo div {
	font-size: 1rem;
}
```

#### And Stylecow converts to

```css
body {
	font-size: 1.2em;
}
.foo {
	font-size: 2em;
}
.foo div {
	font-size: 19.2px;
	font-size: 1rem;
}
```



NestedRules
-----------

Resolves the nested rules, allowing to write css in a more legible way:

#### You write

```css
article.main {
	padding: 4px;

	header {
		margin-bottom: 20px;

		h1, h2 {
			font-size: Helvetica, sans-serif;
			color: #000;
		}

		p {
			color: #666;

			a {
				text-decoration: none;
				color: green;
			}

			a:hover {
				text-decoration: underline;
			}
		}
	}
}
```

#### And Stylecow converts to

```css
article.main {
	padding: 4px;
}

article.main header {
	margin-bottom: 20px;
}

article.main header h1,
article.main header h2 {
	font-size: Helvetica, sans-serif;
	color: #000;
}

article.main header p {
	color: #666;
}

article.main header p a {
	text-decoration: none;
	color: green;
}

article.main header p a:hover {
	text-decoration: underline;
}
```

This function can be combined with variables to make scoped changes.


Grid
----

You can configurate and use one or various grids for the layout. You simply have to define the available width, number of columns and the gutter between.

The available function of grid plugin are:

* cols() Floats the element, define the with in columns and the gutter as margin-right
* cols-with() With in columns
* left() Margin left in columns
* right() Margin right in columns
* background() Define a background-image with the grid (using griddle.it service)
* columns() Overwrites the default number of columns
* width() Overwrites the default width of the grid
* gutter() Overwrites the default gutter of the grid
* in-cols() Useful to insert columns inside columns with padding

#### You write

```css
$grid {
	width: 950px;
	columns: 24;
	gutter: 10px;
}

.left-column {
	$grid: cols(8);
}

.center-column {
	$grid: cols(12);
}

.right-column {
	$grid: cols(4);
	margin-right: 0;
}
```


#### And Stylecow converts to

```css
.left-column {
	width: 310px;
	float: left;
	display: inline;
	margin-right: 10px;
}

.center-column {
	width: 470px;
	float: left;
	display: inline;
	margin-right: 10px;
}

.right-column {
	margin-right: 0;
	width: 150px;
	float: left;
	display: inline;
}
```

Color
-----

Manipulate color dinamically. Changes the hue, saturation, light, red, green, blue, alpha and tint values.
You can use absolute or relative values:

* saturation:50  Set the saturation value to 50
* saturation:+10  Increments 10% the current saturation

This function supports all css color formats:

* names (black, red, blue, etc)
* hexadecimal (#333, #34FC98, etc)
* rgb / rgba
* hsl / hsla

#### You write

```css
div.foo {
	background: color(#369, light:50, alpha: 0.5);
	color: color(#369, blue:-30);
	border: solid 1px color(black, 20); /* Shortcut for color(black, tint:20) */
}
```

#### And Stylecow converts to

```css
div.foo {
	background: rgba(64, 128, 191, 0.5);
	color: #33667b;
	border: solid 1px #CCCCCC;
}
```


Math
----

You can execute math operations (+-*/):


#### You write

```css
.foo {
	font-size: math(2+4)em;
	height: math((30*5)/3)px;
}
```

#### And Stylecow converts to

```css
.foo {
	font-size: 6em;
	height: 50px;
}
```