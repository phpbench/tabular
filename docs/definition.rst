Definition
==========

The definition is the JSON file which determines how the table will be
generated. The generated table is provided as a DOM document and will be
referred to as the *table representation* in the rest of this document.

The JSON definition is very strict, but do not fear. It is validated against a JSON schema
which provides accurate clues as to what goes wrong, when it goes wrong.

Some of the Tabular definition file has been influenced by
`Ansible <http://ansible.com>`_.

Rows
----

In Tabular you define row *prototypes* which will be expanded into the final
table representation.

The simplest (well almost the simplest) valid definition is as follows:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "Cell One",
                        "literal": "Value of cell one"
                    }
                ]
            }
        ]
    }

This will produce a table representation with a single row which has a single
cell with the value ``Value of cell one``::

    ┌───────────────────┐
    │ Cell One          │
    ├───────────────────┤
    │ Value of cell one │
    └───────────────────┘


- The ``rows`` property is an array of "row" *objects*.

- Each row object can defines a single or a potential set of rows, depending on if
  *iteration* is used (``with_query``, ``with_items``).  More on this later.

- Each row must deifine the ``cells`` property which should contain an array of
  *cell* objects. This object can be empty which will create an empty row.

Expressions
-----------

Expressions allow the value of cell to be evaluated from an XPath expression
which acts upon the given XML source document.

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "Evaluated Cell",
                        "expr": "sum(//price)"
                    }
                ]
            }
        ]
    }

The ``expr`` property is an XPath expression, and will return the sum total of
all the `<price/>` node values.

Tabular leverages the
`registerphpfunctions <http://php.net/manual/en/domxpath.registerphpfunctions.php>`_ feature of PHP's XPath object
to register custom functions. The following example shows the use of the
:ref:`xpathfuncmin`, :ref:`xpathfuncmax` and :ref:`xpathfuncaverage`:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "Minimum Price",
                        "expr": "min(//price)"
                    },
                    {
                        "name": "Maximum Price",
                        "expr": "max(//price)"
                    },
                    {
                        "name": "Average Price",
                        "expr": "average(//price)"
                    }
                ]
            }
        ]
    }

Which yields::

    ┌───────────────┬───────────────┬───────────────┐
    │ Minimum Price │ Maximum Price │ Average Price │
    ├───────────────┼───────────────┼───────────────┤
    │ 5.00          │ 7             │ 6             │
    └───────────────┴───────────────┴───────────────┘

See the :doc:`xpath functions <xpath_functions>` chapter for more information
on available functions.

Row Iteration
-------------

Above we define single rows but it is also possible to iterate over the same
row object and dynamically create multiple rows.

With a query
~~~~~~~~~~~~

You can iterate over a query result:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "Price",
                        "expr": "number(./price)"
                    }
                ],
                "with_query": "//book"
            }
        ]
    }

Here a new row will be created for each ``<book/>`` element of the source XML
document and the cell expressions will be relative to the DOMNode representing
the row.

With items
~~~~~~~~~~

Alternatively you can iterate over a set of "items", either as scalar values - in which case
the scalar value can be accessed by ``row.item``:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "column_1",
                        "literal": "{{ row.item }}"
                    }
                ],
                "with_items": [ "hello", "goodbye" ]
            }
        ]
    }

Or with items as associative arrays, where the value can be accessed as
`row.<key>`:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "column_1",
                        "literal": "{{ row.salutation }} {{ row.name }}!"
                    }
                ],
                "with_items": [ 
                    { "name": "Daniel", "salutation": "Hello" },
                    { "name": "Susan", "salutation": "Ciao" }
                ]
            }
        ]
    }

Which yields::

    ┌───────────────┐
    │ column_1      │
    ├───────────────┤
    │ Hello Daniel! │
    │ Ciao Susan!   │
    └───────────────┘

You can also use items in association with `with_query`:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "Price",
                        "expr": "number(./price)"
                    }
                ],
                "with_query": "//book[price={{ row.item }}]"
                "with_items": [ 5, 7 ]
            }
        ]
    }

The above will add rows for books which have the prices 5 and 7
respectively, we only have two books which conveniently are priced 5 and 7, so
we have a table with two rows::

    ┌───────┐
    │ Price │
    ├───────┤
    │ 5     │
    │ 7     │
    └───────┘

Cell Iteration
--------------

It is also possible to dynamically create cells by using the `with_items`
property within the cell object and using the token within the cell name:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "{{ cell.item }}",
                        "expr": "{{ cell.item }}(//price)",
                        "with_items": [ "sum", "average", "min", "max"  ]
                    }
                ]
            }
        ]
    }

The items above are names of functions, we add a column named after each
function and use the function to calculate the cell value::

    ┌─────┬─────────┬──────┬─────┐
    │ sum │ average │ min  │ max │
    ├─────┼─────────┼──────┼─────┤
    │ 12  │ 6       │ 5.00 │ 7   │
    └─────┴─────────┴──────┴─────┘

In addition to iterating over a static set of items you can iterate over a
query on the source document:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "{{ cell.item }}",
                        "literal": "Hello {{ cell.item }}",
                        "with_items": {
                            "selector": "//person",
                            "value": "string(./@name)"
                        }
                    }
                ]
            }
        ]
    }

Above we iterate over each `<person/>` element and use the `@name` attribute
as the `cell.item` value.

Passes
------

Sometimes it is desirable to evaluate cell values based on already evaluated
cell values. This can be done using the *pass* feature. Expressions which use
a pass operate on the DOM of the table representation rather than the orignal XML source.

The table definition XML upon which the expression will be evaluated looks as
follows:

.. code-block:: xml

    <table>
        <group name="...">
            <row>
                <cell name="...">...</cell>
            </row>
        </group>
    </table>

Cells are evaluated in subsequent passes if the ``pass`` property is used on the
cell object. The value must be an integer, lower numbers are executed before
higher numbers, they need not be contiguous.

The following will evaluate the values for cells ``pass_1`` and ``pass_2`` in
two passes:

.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "price",
                        "expr": "sum(//price)"
                    },
                    {
                        "name": "pass_1",
                        "pass": 1,
                        "expr": "number(./cell[@name='price']) * 2"
                    },
                    {
                        "name": "pass_2",
                        "pass": 2,
                        "expr": "number(./cell[@name='pass_1']) * 2"
                    }
                ]
            }
        ]
    }

Which yields::

    ┌───────┬────────┬────────┐
    │ price │ pass_1 │ pass_2 │
    ├───────┼────────┼────────┤
    │ 12    │ 24     │ 48     │
    └───────┴────────┴────────┘

Groups
------

Groups are a way of "breaking a table into sections". For example, you may
have the groups "header", "body" and "footer".

The below definition makes use of a few of the things already covered in this
chapter:

.. code-block:: javascript

    {
        "rows": [
            {
                "group": "body",
                "cells": [
                    {
                        "name": "value",
                        "literal": "{{ row.item }}"
                    }
                ],
                "with_items": [ 1, 1, 2, 3, 5, 8 ]
            },
            {
                "group": "footer",
                "cells": [
                    {
                        "name": "value",
                        "pass": 1,
                        "expr": "sum(//group[@name='body']//cell[@name='value'])"
                    },
                    {
                        "name": "",
                        "literal": "<< Total"
                    }
                ]
            }
        ]
    }

Note that in the expression in the footer we explicitly specify the name of
the group in the query. This is beause otherwise the ``sum`` will take into
account the value of the footer column, which would result in a ``NAN`` (not a
number) error.

The generated table XML would look as follows:

.. code-block:: xml

    <table>
        <group name="body">
            <row>
                <cell name="value">1</cell>
                <cell name=""></cell>
            </row>
            <row>
                <cell name="value">1</cell>
                <cell name=""></cell>
            </row>
            <!-- ... -->
        </group>
        <group name="footer">
            <row>
                <cell name="value">20</cell>
                <cell name=""><< Total</cell>
            </row>
        </group>
    </table>

Which yields::

    ┌───────┬──────────┐
    │ value │          │
    ├───────┼──────────┤
    │ 1     │          │
    │ 1     │          │
    │ 2     │          │
    │ 3     │          │
    │ 5     │          │
    │ 8     │          │
    │ 20    │ << Total │
    └───────┴──────────┘

If no groups are specified, then the default group name is used, which is:
"_default".

Classes
-------

Classes allow you to format cell values using formatters (see the formatters
chapter to find out about the default formatters). Classes are defined at the
top level and each cell can specify a class to use:

.. code-block:: javascript

    {
        "classes": {
            "euro": [
                [ "printf", { "format": "€%2d" } ],
                [ "printf", { "format": "%s please" } ],
                [ "printf", { "format": "Can I have %s?" } ]
            ]
        },
        "rows": [
            {
                "cells": [
                    {
                        "name": "value",
                        "class": "euro",
                        "literal": "{{ row.item }}"
                    }
                ],
                "with_items": [ 1, 1, 2, 3 ]
            }
        ]
    }

Above we define the class ``euro``, which will process the original cell value
through three formatters, eventually the number in each cell will look like
`Can I have €<cell value> please?`::

    ┌────────────────────────┐
    │ value                  │
    ├────────────────────────┤
    │ Can I have € 1 please? │
    │ Can I have € 1 please? │
    │ Can I have € 2 please? │
    │ Can I have € 3 please? │
    └────────────────────────┘

Sorting
-------

Tables can be sorted on a per-group basis, for example:

.. code-block:: javascript

    {
        "rows": [
            {
                "group": "main",
                "cells": [
                    {
                        "name": "value",
                        "literal": "{{ row.item }}"
                    }
                ],
                "with_items": [ 1, 1, 2, 3 ]
            }
        ],
        "sort": {
            "main#value": "desc"
        }
    }

The group name is prefixed before the `#` delimter. If no group name is given
then the default group will be used.

Parameters
----------

Parameters allow you both to define variables in your definition and
provide a way for the end user to change these variables.


.. code-block:: javascript

    {
        "rows": [
            {
                "cells": [
                    {
                        "name": "value",
                        "expr": "string(./title)"
                    }
                ],
                "with_query": "{{ param.selector }}"
            }
        ],
        "params": {
            "selector": "//book"
        }
    }

The above would enable the end-user to control *which books* will be included
in the report as follows:

.. code-block:: php

    <?php

    $table = Tabular::getInstance()->tabulate(
        $sourceXml, 
        'my_definition.json', 
        array(
            'selector' => '//book[price > 5]',
        )
    );

    print_r($table);
    // array(1) {
    //   [0] =>
    //   array(1) {
    //     'value' =>
    //     string(30) "One Hundered Years of Soliture"
    //   }
    // }

You can also set or override parameters via. an **expression** on a row-by-row
basis using the ``param_exprs`` key:

.. code-block:: javascript

    {
        "rows": [
            {
                "param_exprs": {
                    "my_class": "string(./@class)"
                },
                "cells": [
                    {
                        "name": "value",
                        "class": "{{ param.my_class }}",
                        "expr": "string(./title)"
                    }
                ],
                "with_query": "//book"
            }
        ]
    }

In the above example we assume the the ``<book/>`` element has an attribute
``class`` which we then use as the class for the table element.

Includes
--------

Includes allow you to merge parts of other definition files into the including
definition. A common use case might to be to include a common set of classes
into many definitions.

Given there exists the file ``classes.json``:

.. code-block:: javascript

    {
        "classes": {
            "number": [
                [ "number" ]
            ],
            "green": [
                [ "printf", {"format": "<green>%s</green>"} ]
            ]
        }
    }

We can include it as follows:

.. code-block:: javascript

    {
        "includes": [
            [ "classes.json", [ "classes" ] ]
        ],
        "rows": [
            {
                "cells": [
                    {
                        "name": "one",
                        "literal": "10000",
                        "class": "number"
                    }
                ]
            }
        ]
    }

The first element in the tuple is the name of the file (relative to the
current file), the second is the list of keys to import from it::

    ┌────────┐
    │ one    │
    ├────────┤
    │ 10,000 │
    └────────┘
