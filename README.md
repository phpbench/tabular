Tabular
=======

[![Build Status](https://travis-ci.org/phpbench/tabular.svg?branch=master)](https://travis-ci.org/phpbench/tabular)
[![StyleCI](https://styleci.io/repos/40823691/shield)](https://styleci.io/repos/40823691)

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

````javascript
{
    "rows": [
        {
            "cells": [
                {
                    "name": "title",
                    "expr": "string(./title)"
                },
                {
                    "name": "author",
                    "expr": "string(./author)"
                },
                {
                    "name": "stock",
                    "expr": "number(./stock)"
                },
                {
                    "name": "price",
                    "expr": "number(./price)"
                },
                {
                    "name": "stock_price",
                    "expr": "number(./price) * number(./stock)"
                }
            ],
            "with_query": "//book"
        },
        {
            "cells": []
        },
        {
            "cells": [
                {
                    "name": "title",
                    "literal": "total >>"
                },
                {
                    "name": "stock",
                    "expr": "sum(//stock)"
                },
                {
                    "name": "price",
                    "expr": "sum(//price)"
                }
            ]
        }
    ]
}
````

Note that we add a blank row and then a footer containing the sum totals of
the stock and price columns.

````php
use PhpBench\Tabular\Tabular;

$dom = new DomDocument('1.0');
$dom->load('books.xml');

$tableDom = Tabular::getInstance()->tabulate($dom, 'book_report.json');
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
      <cell name="price">5</cell>
      <cell name="stock_price">25</cell>
    </row>
    <row>
      <cell name="title">One Hundered Years of Soliture</cell>
      <cell name="author">Gabriel Garc&#xED;a M&#xE1;rquez</cell>
      <cell name="stock">2</cell>
      <cell name="price">7</cell>
      <cell name="stock_price">14</cell>
    </row>
    <row>
      <cell name="title"></cell>
      <cell name="author"></cell>
      <cell name="stock"></cell>
      <cell name="price"></cell>
      <cell name="stock_price"></cell>
    </row>
  </group>
  <group name="footer">
    <row>
      <cell name="title">total &gt;&gt;</cell>
      <cell name="author"></cell>
      <cell name="stock">7</cell>
      <cell name="price">12</cell>
      <cell name="stock_price"></cell>
    </row>
  </group>
</table>
````

You can retrieve an array representation of the entire table or a specific
group using the `toArray` method on the returned DOM object.

For example, using the Symfony Table component you could render a table as
follows:

````php
$rows = $tableDom->toArray();
$table = new Table();
$table->setHeaders(array_keys(reset($rows)));
$table->addRows($rows);
$table->render($output);
````
