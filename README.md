Stylecow
========

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>

GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html

Stylecow is a php library that allows parsing and manipulating css files.

Features:

* Written in php 5.3
* Includes the @import files (only files with relative path)
* Extensible with plugins
* Uses the PSR-0 autoloader standard


Why another CSS preprocessor?
------------------------------

The main purpose of Stylecow is to bring more CSS support to all browsers. You write CSS and you get CSS. You don't have to learn another different language such LESS, SASS, etc. Stylecow converts your code to be more compatible with all browsers throught plugins without writing any non-standard code. There is a plugin to add automatically the vendor prefixes to all selectors, properties and values in need. There is another plugin that allows using rem values with fallback for IE<=8. There is a plugin to use css variables with the same syntax of the w3c standard (http://dev.w3.org/csswg/css-variables/). And other plugins emulate some CSS effects (rotate, opacity, etc) in IE using the property "filter". So you can use Stylecow just to fix your modern CSS code and make it compatible with old browsers. And if you stop using Stylecow, your CSS code will remain CSS code.

But if you don't mind to write "non pure CSS code", there are more plugins that can help you to write styles faster. For example, Color allows manipulate colors changing some of the values (saturation, light, etc), Math can execute math operations, Grid makes easier to work with fixed columns, etc.


Demo
----

Here you can test stylecow: http://oscarotero.com/stylecow/


How to use
----------

```php
//Include the library (if no psr-0 compatible loader is available)
include('Stylecow/autoloader.php');

//Load and parse the code
$css = Stylecow\Parser::parseFile('my-styles.css');

//Transform the css code using the plugins.
$css->applyPlugins(array(
	'Rem',
	'Variables',
	'VendorPrefixes'
));

//Print the result css code
echo $css;
```


Plugins
=======

Stylecow provides some basic plugins (but you can make your owns):

Plugins to bring CSS support:

* [BaseUrl](#baseurl) Changes the base url in url() functions
* [IeBackgroundAlpha](#iebackgroundalpha) IE support for rgba/hsla colors as background (IE <= 8 | http://caniuse.com/#search=rgba)
* [IeClip](#ieclip) Fixes clip css syntax (IE <= 7)
* [IeFloat](#iefloat) Fixes double margin bug in floated elements in (IE 6)
* [IeInlineBlock](#ieinlineblock) Emulate the display: inline-block property (IE <= 7 | http://caniuse.com/#search=inline-block)
* [IeLinearGradient](#ielineargradient) Add ie filters to emulate linear-gradients (IE <= 9 | http://caniuse.com/#search=gradients)
* [IeMinHeight](#ieminheight) Emulate the min-height property (IE <= 6 | http://caniuse.com/#search=min-height)
* [IeOpacity](#ieopacity) Emulate the opacity property (IE <= 8 | http://caniuse.com/#search=opacity)
* [IeTransform](#ietransform) Adds ie filters to emulate some css 2d transform (rotate, reflections, etc) (IE <= 8 | http://caniuse.com/#search=transforms)
* [Initial](#initial) Adds support for "initial" value
* [Matches](#matches) Support for the CSS4 selector :matches() (http://caniuse.com/#search=matches)
* [MediaQuery](#mediaquery) Filters the css code for a specific mediaquery
* [NestedRules](#nestedrules) Brings nested rules support. There is a draft spec of CSS Hierarchies Module Level 3 (http://dev.w3.org/csswg/css3-hierarchies/) but have some differences (in w3c standard you must use the & character in all cases)
* [Rem](#rem) Add support for rem values (IE <= 8 | http://caniuse.com/#search=rem)
* [Variables](#variables) Support for variables (W3C syntax http://dev.w3.org/csswg/css-variables/)
* [VendorPrefixes](#vendorprefixes) Adds automatically the vendor prefixes to all properties in need

Other plugins with non-standard syntax:

* [Color](#color) Provides the function color() to manipulate color values
* [Grid](#grid) Useful to work with one or various grids.
* [Math](#math) Provides the function math() to execute math operations


BaseUrl
-------

Changes the base url in css url() functions. Useful in some cases when the css path is not the same of the assets path (images)

#### You write

```css
div.foo {
	background-image: url('img/bg.png');
}
```

#### And Stylecow converts to

```css
div.foo {
	background-image: url('/my/custom/baseurl/img/bg.png');
}
```

You have to define the base url at the second argument

```php
$css->applyPlugins(array(
	'BaseUrl' => '/my/custom/baseurl/'
));
```

IeBackgroundAlpha
-----------------

Generate Internet Explorer filters to support alpha values as background (rgba/hsla colors)

#### You write

```css
div.foo {
	background: rgba(0, 0, 0, 0.5);
}
```

#### And Stylecow converts to

```css
div.foo {
	background: rgba(0, 0, 0, 0.5);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#80000000', endColorStr='#80000000');
}
```

IeClip
------

Fix the clip syntax in Internet Explorer 6-7 (http://www.ibloomstudios.com/articles/misunderstood_css_clip/)

#### You write

```css
div.foo {
	clip: rect(5px, 40px, 45px, 5px);
}
```

#### And Stylecow converts to

```css
div.foo {
	clip: rect(5px, 40px, 45px, 5px);
	*clip: rect(5px 40px 45px 5px);
}
```

IeFloat
-------

Fix the double margin bug in Internet Explorer 6 in floated elements.

#### You write

```css
div.foo {
	float: left;
}
```

#### And Stylecow converts to

```css
div.foo {
	float: left;
	_display: inline;
}
```

IeInlineBlock
-------------

Adds css code to emulate display: inline-block property in Internet Explorer 6-7

#### You write

```css
div.foo {
	display: inline-block;
}
```

#### And Stylecow converts to

```css
div.foo {
	display: inline-block;
	*zoom: 1;
	*display: inline;
}
```

IeLinearGradient
----------------

Generate ie filters to emulate background linear-gradients

#### You write

```css
div.foo {
	background: linear-gradient(top, red, blue);
}
```

#### And Stylecow converts to

```css
div.foo {
	background: linear-gradient(top, red, blue);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#FF0000', endColorStr='#0000FF');
}
```

IeMinHeight
-----------

Add css code to emulate min-height property in Internet Explorer 6

#### You write

```css
div.foo {
	min-height: 200px;
}
```

#### And Stylecow converts to

```css
div.foo {
	min-height: 200px;
	_height: 200px;
}
```


IeOpacity
---------

Generate ie filters to add opacity support in Internet Explorer 6-8

#### You write

```css
div.foo {
	opacity: 0.5;
}
```

#### And Stylecow converts to

```css
div.foo {
	opacity: 0.5;
	filter: alpha(opacity=50);
}
```

IeTransform
-----------

Generate ie filters to add support for some css 2d transform in Internet Explorer 6-8

#### You write

```css
div.foo {
	transform: rotate(45deg);
}
```

#### And Stylecow converts to

```css
div.foo {
	transform: rotate(45deg);
	filter: progid:DXImageTransform.Microsoft.Matrix(sizingMethod="auto expand", M11 = 0.70710678118655, M12 = -0.70710678118655, M21 = 0.70710678118655, M22 = 0.70710678118655);
}
```

Initial
-------

Replace all "inital" values for the real value

#### You write

```css
div.foo {
	background-position: initial;
	height: initial;
}
```

#### And Stylecow converts to

```css
div.foo {
	background-position: 0 0;
	height: auto;
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
div.foo h1 a, div.foo h2 a, div.foo h3 a, div.foo h4 a, div.foo h5 a, div.foo h6 a {
	color: blue;
}

div.foo article header h1, div.foo article header h2, div.foo section header h1, div.foo section header h2 {
	color: black;
}
```

MediaQuery
----------

Filter all css code for apply to specific mediaquery. This is useful for browser with no support for media queries.

In this example, lets say we want the css code for a browser with a screen of 1024px:

#### You write

```css
@media screen and (max-width:400px) {
	.foo {
		font-size: 1em;
	}
}
@media screen and (max-width:800px) {
	.foo {
		font-size: 2em;
	}
}
@media only screen and (max-width:800px) {
	.foo {
		font-size: 3em;
	}
}
@media all and (max-width:1200px) {
	.foo {
		font-size: 4em;
	}
}
@media print and (max-width:1200px) {
	.foo {
		font-size: 5em;
	}
}
@media (min-width:1024px) {
	.foo {
		font-size: 6em;
	}
}
```


#### And Stylecow converts to

```css
.foo {
	font-size: 4em;
}
.foo {
	font-size: 6em;
}
```

You have to define the browser capabilities at the second argument

```php
$css->applyPlugins(array(
	'MediaQuery' => array(
		'width' => '1024px',
		'type' => 'screen'
	)
));
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

This function can be combined with variables to define variables in specific scope.


Rem
---

Allows use the rem value (http://snook.ca/archives/html_and_css/font-size-with-rem) to define the text size in a safe way for old browsers.

The default rem is 1em (16px) but you can change it with the :root or html selector.

#### You write

```css
html {
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
html {
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

You can define some predefined variables on apply the plugin:

```php
$css->applyPlugins(array(
	'Variables' => array(
		'myColor' => '#456',
		'myFont' => 'Helvetica, Arial, sans-serif'
	)
));
```



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

This function may change in a future due to the possible implementation a color() function in CSS4 color module: http://www.xanthir.com/blog/b4Jp0

#### You write

```css
div.foo {
	background: color(#369, light:50, alpha: 0.5);
	color: color(#369, blue:-30);
	border: solid 1px color(black, 20); /* Integer: Shortcut for color(black, tint:20) */
	box-shadow: 0 0 4px color(black, 0.2); /* Float: Shortcut for color(black, alpha:0.2) */
}
```

#### And Stylecow converts to

```css
div.foo {
	background: rgba(64, 128, 191, 0.5);
	color: #33667b;
	border: solid 1px #CCCCCC;
	box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
}
```

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
	margin-right: 10px;
}

.center-column {
	width: 470px;
	float: left;
	margin-right: 10px;
}

.right-column {
	margin-right: 0;
	width: 150px;
	float: left;
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
