Tabular
=======

Tabular is a library for transforming a source XML document into a tabular XML
document using a given configuration. The resulting tabular XML document can
then transformed or used to easily render tables (for example in HTML or in
the console).

The primary use case is for creating complex **reports**.

This library is in a **state of development**.

Features
--------

- Dynamically generate a table structure from an XML document via.
  configuration.
- Evaulate cell values using XPath expressions.
- Registration of custom XPath functions.
- Custom formatters.
- Iterate using XPath queries.
- Iterate using parameters (which can be used in queries / expressions).
- Dynamically create new cells based on parameters.
- Sort the results (by cell class).
- Format the results (by cell class).
- Support for "compiler passes".

Documentation
-------------

There is currently no documentation, below is just a simple, inexhaustive
guide.

Initialization
--------------

It is intended that the library be bootstrapped using a dependency injection
container, with the `Tabular` class being the "point of entry".

It should be instantiated as follows:

````php
use PhpBench\Tabular\Dom\XPathResolver;
use PhpBench\Tabular\TableBuilder;
use JsonSchema\Validator;
use PhpBench\Tabular\Formatter;
use PhpBench\Tabular\Formatter\Registry\ArrayRegistry;
use PhpBench\Tabular\Tabular;

$xpathResolver = new XPathResolver();
$tableBuilder = new TableBuilder($xpathResolver);
$validator = new Validator();
$registry = new ArrayRegistry();
$registry->register('printf', new PrintfFormat());
$formatter = new Formatter($registry);
$tabular = new Tabular($tableBuilder, $validator, $formatter);
````

Example
-------

There is a book shop with 2 books in it. The owner keeps his inventory in an
XML file:

````xml
<?xml version="1.0"?>
<store>
    <book>
        <title>War and Peace</title>
        <author>Leo Tolstoy</author>
        <stock>5</stock>
        <price>5.00</price>
    </book>
    <book>
        <title>One Hundered Years of Soliture</title>
        <author>Gabriel García Márquez</author>
        <stock>2</stock>
        <price>7</price>
    </book>
</store>
````

The owner wants to create a table listing the title, author, stock and price
of the book, this can be accomplished as follows:

````php
$tableDom = $tabular->tabulate($dom, array(
    'rows' => array(
        array(
            'cells' => array(
                'title' => array('expr' => 'string(./title)'),
                'author' => array('expr' => 'string(./author)'),
                'stock' => array('expr' => 'string(./stock)'),
                'price' => array('expr' => 'string(./price)'),
            ),
            'with_query' => '//book',
        )
    ),
));
````

This will generate the following XML:

````xml
<?xml version="1.0"?>
<table>
  <group name="_default">
    <row>
      <cell name="title">War and Peace</cell>
      <cell name="author">Leo Tolstoy</cell>
      <cell name="stock">5</cell>
      <cell name="price">5.00</cell>
    </row>
    <row>
      <cell name="title">One Hundered Years of Soliture</cell>
      <cell name="author">Gabriel Garc&#xED;a M&#xE1;rquez</cell>
      <cell name="stock">2</cell>
      <cell name="price">7</cell>
    </row>
  </group>
</table>
````

The owner now wants to add a "totals" row at the bottom:

````php
$tableDom = $tabular->tabulate($dom, array(
    'rows' => array(
        // ...
        array(
            'cells' => array(
                'stock' => array('expr' => 'sum(//book/stock)'),
                'price' => array('expr' => 'sum(//book/price)'),
            ),
        )
    ),
));
````

Lets add some formatting to the price cells:

````php
$tableDom = $tabular->tabulate($dom, array(
    'rows' => array(
        // ...
        array(
            'cells' => array(
                // ...
                'price' => array('class' => 'money', 'expr' => 'string(./price)'),
            ),
                // ...
                'price' => array('class' => 'money', 'expr' => 'sum(//book/price)'),
            ),
        )
    ),
    'classes' => array(
        'money' => array('printf', array('format' => '%s euro')),
    ),
));
````

The word "euro" will now be suffixed on all the cells with the class
``price``.
