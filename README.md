Tabular
=======

[![Build Status](https://travis-ci.org/phpbench/tabular.svg?branch=master)](https://travis-ci.org/phpbench/tabular)
[![StyleCI](https://styleci.io/repos/40823691/shield)](https://styleci.io/repos/40823691)

Tabular is a library for transforming a source XML document into a tabular XML
document using a given configuration. The resulting tabular XML document can
then transformed or used to easily render tables (for example in HTML or in
the console).

Tabular is better than spreadsheets.

Documentation
-------------

See the [official documentation](http://tabular.readthedocs.org).

Example
-------

The central concept is the definition file:

```javascript
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

```xml
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

The generated table might look like this (as rendered by the [Tabular
CLI](https://github.com/phpbench/tabular-cli)):

```
┌────────────────────────────────┬───────┐
│ title                          │ price │
├────────────────────────────────┼───────┤
│ War and Peace                  │ 5     │
│ One Hundered Years of Soliture │ 7     │
│                                │ 12    │
└────────────────────────────────┴───────┘
```
