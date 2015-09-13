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
