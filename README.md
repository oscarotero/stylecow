Stylecow
========

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>

GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html

Stylecow is a php library that allows parsing and manipulating css files. The main class is styleCow that loads and parses the css file(s) creating an internal array with all the selectors, properties and values. Then you can transform the array using one or more available plugins and finally return the css code.

Features:

* Written in php 5.3
* Includes the css files using @import
* Extensible with plugins
* Easy to use


#### How to use

```php
include('Stylecow/Stylecow.php');

//Fist we instance the class (note that we are using namespaces)
$styleCow = new Stylecow\Stylecow;

//Load the file
$styleCow->load('my-styles.css');

//Transform the css file using one or more plugins (for example Vendor_prefixes or Variables)
$styleCow->transform(array(
	'Vendor_prefixes',
	'Variables'
));

//Print the result css code
echo $styleCow->toString();

//You can use also the function show() to print the code with http headers and die
$styleCow->show();

//Print or show the code with options:
$styleCow->toString(array(
	'minify' => true, //Minify the code: remove spaces, linebreaks, etc
	'browser' => 'moz' //Returns only the css properties with the vendor prefix "-moz-"
));
```


Plugins
=======

Stylecow provides some plugins (and you can make your owns):

* [Vendor_prefixes](#vendor_prefixes)
* [Matches](#matches)
* [Variables](#variables)
* [Nested_rules](#nested_rules)
* [Ie_filters](#ie_filters)
* [Grid](#grid)
* [Animate](#animate)
* [Color](#color)
* [Rem](#rem)
* [Math](#math)


Vendor_prefixes
---------------

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

You can use variables to recycle code. The variables can contain a simple value or a set of properties-values.

#### You write

```css
$variables {
	title-font: Helvetica, Arial, sans-serif;

	title-style: {
		font-family: $title-font;
		font-size: 2em;
		text-shadow: 1px 1px #CCC;
	}
}

div.foo h1 {
	$title-style;
}

div.foo h2 {
	font-family: $title-font;
	font-size: 1em;
}
```


#### And Stylecow converts to

```css
div.foo h1 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 2em;
	text-shadow: 1px 1px #CCC;
}

div.foo h2 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 1em;
}
```


Nested_rules
------------

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


Ie_filters
----------

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
	-ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#666666', endColorStr='#999999'), progid:DXImageTransform.Microsoft.Matrix(sizingMethod="auto expand", M11 = 0.707106781187, M12 = -0.707106781187, M21 = 0.707106781187, M22 = 0.707106781187), flipV, alpha(opacity=50);
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

Animate
-------

Inserts the code for the animations availables in http://daneden.me/animate/

#### You write

```css
div.foo {
	$animate: flash;
}
```


#### And Stylecow converts to

```css
div.foo {
	animation: 1s ease;
	-moz-animation: 1s ease;
	-webkit-animation: 1s ease;
	-o-animation: 1s ease;
	-ms-animation: 1s ease;
	animation-name: flash;
	-moz-animation-name: flash;
	-webkit-animation-name: flash;
	-o-animation-name: flash;
	-ms-animation-name: flash;
}

@-moz-keyframes flash {
	0%, 50%, 100% {
		opacity: 1;
		-moz-opacity: 1;
	}
	25%, 75% {
		opacity: 0;
		-moz-opacity: 0;
	}
}

@-webkit-keyframes flash {
	0%, 50%, 100% {
		opacity: 1;
		-webkit-opacity: 1;
	}
	25%, 75% {
		opacity: 0;
		-webkit-opacity: 0;
	}
}

@-ms-keyframes flash {
	0%, 50%, 100% {
		opacity: 1;
	}
	25%, 75% {
		opacity: 0;
	}
}

@-o-keyframes flash {
	0%, 50%, 100% {
		opacity: 1;
	}
	25%, 75% {
		opacity: 0;
	}
}

@keyframes flash {
	0%, 50%, 100% {
		opacity: 1;
		-moz-opacity: 1;
		-webkit-opacity: 1;
	}
	25%, 75% {
		opacity: 0;
		-moz-opacity: 0;
		-webkit-opacity: 0;
	}
}
```

Color
-----

Manipulate color dinamically. Changes the hue, saturation, light, red, green, blue, alpha and tint values.
You can use absolute or relative values:

* saturation:50  Set the saturation value to 50
* saturation:+10  Increments 10% the current saturation

#### You write

```css
div.foo {
	background: color(#369, light:50, alpha: 0.5);
	color: color(#369, blue:-30);
}
```

#### And Stylecow converts to

```css
div.foo {
	background: rgba(64, 128, 191, 0.5);
	color: #33667b;
}
```

Rem
---

Allows use the rem value (http://snook.ca/archives/html_and_css/font-size-with-rem) to define the text size in a safe way for old browsers.
The default rem is 1em (16px) but you can change it in the body styles:

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

Math
----

You can execute math operations (+-*/):


#### You write

```css
.foo {
	font-size: math(2+4)em;
}
```

#### And Stylecow converts to

```css
.foo {
	font-size: 6em;
}
```