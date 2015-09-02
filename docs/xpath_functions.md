XPath Functions
---------------

## XPath 1.0 Functions

The following is a list of the standard XPath 1.0 functions, please see
elsewhere for more detailed information.

- `last`: Last element of a set
- `position`: Return the index of a node relative to its siblings.
- `count`: Return the count of a set of nodes
- `id`:
- `local-name`: 
- `namespace-uri`:
- `name`:
- `string`: Cast the node value to a string.
- `concat`:
- `starts-with`: 
- `contains`: 
- `substring-before`:
- `substring-after`:
- `substring`:
- `string-length`:
- `normalize-space`:
- `translate`:
- `boolean`:
- `not`:
- `true`:
- `false`:
- `lang`:
- `number`: Cast the node value to a number.
- `sum`: Calculate the sum value of a set.
- `floor`:
- `ceiling`:
- `round`:

## Tabular Functions

Tabular extends the default XPath function set by leveraging string
manipulation and PHP's
[registerphpfunctions](
http://php.net/manual/en/domxpath.registerphpfunctions.php) method of the DOM
XPath class.


### `average`

Return the average value of a set of (numerical) node values.

Arguments:

- **selector**: Selector for cell values.

````
average(//price)
````

Will return the average price.

### `min`

Return the minumum value of a set of (numerical) node values.

````
min(//price)
````

Will return the minimum price.

### `max`

Return the maximum value of a set of (numerical) node values.

````
max(//price)
````

Will return the minimum price.

### `median`

Return the median value of a set of (numerical) node values.

````
median(//price)
````

Will return the median price.

### `deviation`

Return the deviation as a percentage between two values

````
deviation(10, 20)
````

Would return "100" because 20 is 100% more than 10.

This can also be used effectively with `average` function:

````
deviation(average(//price), ./price)
````

Will show the deviation of the price from the average value.

## Custom Functions

Custom functions can be registered with the `PhpBench\Tabular\Dom\XPathResolver` instance.

Note that due to limitations with the PHP XML implementation, functions must
be standalone (not part of a class).

Lets add a function which doubles whatever value it is given. First we need to
define the function somewhere:

````
<?php

namespace Foobar\Barfoo;

function double($value)
{
    return $value * 2;
}
````

This should be included somehow in your code (e.g. by `require_once`).

We can then register the function with the XPath instance:

````
$xpathResolver->registerFunction('double_me', 'Foobar\Barfoo\double`);
````

It is recommended that you insantiate Tabular within a dependency injection
container so that you have access to the XPathResolver class. See
[getting started](getting-started.md).
