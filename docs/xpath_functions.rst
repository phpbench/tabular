XPath Functions
===============

You can use all of the standard `XPath 1.0 functions`_, in addition
Tabular extends the default function set by leveraging string
manipulation and PHP's
`registerphpfunctions <http://php.net/manual/en/domxpath.registerphpfunctions.php>`_ method of the DOM
XPath class. This chapter documents the default functions.

.. _xpathfuncaverage:

``average``
~~~~~~~~~~~

Return the average value of a set of (numerical) node values.

Arguments:

- **selector**: Selector for cell values.

Example::

    average(//price)

Will return the average price.

.. _xpathfuncmin:

``min``
~~~~~~~

Return the minumum value of a set of (numerical) node values.

Example::

    min(//price)

Will return the minimum price.

.. _xpathfuncmax:

``max``
~~~~~~~

Return the maximum value of a set of (numerical) node values.

Example::

    max(//price)

Will return the minimum price.

.. _xpathfuncmedian:

``median``
~~~~~~~~~~

Return the median value of a set of (numerical) node values.

Example::

    median(//price)

Will return the median price.

.. _xpathfuncdeviation:

``deviation``
~~~~~~~~~~~~~

Return the deviation as a percentage between two values

Example::

    deviation(10, 20)

Would return "100" because 20 is 100% more than 10.

This can also be used effectively with ``average`` function::

    deviation(average(//price), ./price)

Will show the deviation of the price from the average value.

Custom Functions
----------------

Custom functions can be registered with the ``PhpBench\Tabular\Dom\XPathResolver`` instance.

Note that due to limitations with the PHP XML implementation, functions must
be standalone (not part of a class).

Lets add a function which doubles whatever value it is given. First we need to
define the function somewhere:

.. code-block:: php

    <?php

    namespace Foobar\Barfoo;

    function double($value)
    {
        return $value * 2;
    }

This should be included somehow in your code (e.g. by ``require_once``).

We can then register the function with the XPath instance:

.. code-block:: php

    <?php

    $xpathResolver->registerFunction('double_me', 'Foobar\Barfoo\double');

It is recommended that you insantiate Tabular within a dependency injection
container so that you have access to the XPathResolver class. See
:doc:`getting started <getting-started>`.

.. _XPath 1.0 functions: https://developer.mozilla.org/en-US/docs/Web/XPath/Functions
