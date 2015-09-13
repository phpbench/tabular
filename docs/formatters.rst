Formatters
==========

Formatters are used to modify the cell values for display purposes. Examples
include formatting currency or percentages, or adding media specific
formatting (e.g. italics or color indicators).

The default Tabular instance is preconfigured with a number of formatters.

Formatters are defined by the class property in the [table definition
file](definition.md).

``printf``
----------

The ``printf`` formatter uses, can you guess?, PHP's
``printf <http://php.net/manual/en/function.printf.php>``_ function.

Options:

- **format**: The format string, e.g. ``Hello %s``. See the manual page for
  printf for more information.

``number_format``
-----------------

The ``number_format`` formatter uses PHP's
`number_format <http://php.net/manual/en/function.number_format.php>`_ function.
It can be used to set the number of decimal places which the call value should
have and to separate thousands with a delimiter (e.g. ``1,000,000.00``).

Options:

- **decimal_places**: Number of decimal places to use (default ``0``).
- **decimal_pint**: Character to use as the decimal point (default ``.``).
- **thousands_separator**: Character to use as the thousands separator
  (default ``,``).

``balance``
-----------

The balance operator prefixes the cell value with one of three strings
depending on if the value is negative, zero or positive.

- **zero_format**: ``printf`` format to use when value is zero (default *empty*).
- **negative_format**: ``printf`` format to use when value is negative (default
  ``-``).
- **positive_format**: ``printf`` format to use when value is positive (default
  ``+``).
