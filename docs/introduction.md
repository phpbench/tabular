Introduction
============

Tabular is a PHP library for generating table data structures (e.g. an
invoice, a report) from an XML data source.

If you ever thought "I could do this task in a spreadsheet, but doing it in
code is a pain in the ass" then this library is for you.

It allows you to:

- Define a configuration which dynamically creates table *rows* composed of
  *cells*. 
- Evaluate values dynamically using *extended* XPath expressions.
- Tokenize expressions, literal values and cell names, and iterate over sets
  of parameters.
- Assign rows (or sets of rows) to groups, which can be targeted in subsequent
  passes.
- Format cells using pre-configured and custom formatters.
- Define parameters which can be passed at the time of generation.

It accepts a source XML document and a Tabular JSON definition file, it gives
back an XML file structured as a table which can then be used in anyway you
would like.

Why?
----

You could generate tabular data only with PHP, but as anybody who has ever
tried this can testify this results in a nightmare of variables and spaghetti
code.

You could do generate reports with XSLT transformations, but I think this is hugely
verbose and detached from the familiar PHP environment.

How?
----

The central concept is the definition file:

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
                    "name": "price",
                    "expr": "number(./price)"
                }
            ],
            "with_query": "//book"
        },
        {
            "cells": [
                {
                    "name": "price",
                    "expr": "sum(//price)"
                }
            ]
        }
    ]
}
````

The above definition will generate a table representation in XML with a row
for each `<book/>` element in the given XML file and provide an additional row
showing the sum of all the `<price/>` elements of the `<book/>` element.

So given the following XML file:

````xml
<?xml version="1.0"?>
<store>
    <book>
        <title>War and Peace</title>
        <price>5.00</price>
    </book>
    <book>
        <title>One Hundered Years of Soliture</title>
        <price>7</price>
    </book>
</store>
````

We can generate an array of tabular data as follows:

````php
use PhpBench\Tabular\Tabular;

$dom = new DomDocument('1.0');
$dom->load('books.xml');

// report.json contains the above JSON definition
$tableDom = Tabular::getInstance()->tabulate($dom, 'report.json');
````

We can then either iterate the table data with an XPath query:

````php
foreach ($tableDom->xpath()->query('//row') as $rowEl) {
    foreach ($tableDom->xpath()->query('.//cell', $rowEl) as $cellEl) {
        $value = $cellEl->nodeValue;
    }
}
````

or dump it as an array

````php
$rows = $tableDom->toArray();
````
