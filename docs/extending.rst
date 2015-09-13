Extending
=========

Custom Formatters
-----------------

Formatters must implement the ``PhpBench\Tabular\Formatter\FormatInterface``
which has two methods: ``format`` and ``getDefaultOptions``.

The ``getDefaultOptions`` method should return an associative array featuring
**all** of the options that your formatter will use (user options will be
validated based on these keys). The ``format`` method should return the new cell
value.

The following is the full ``printf`` formatter:

.. code-block:: php

    <?php

    use PhpBench\Tabular\Formatter\FormatInterface;

    class PrintfFormat implements FormatInterface
    {
        public function format($subject, array $options)
        {
            return sprintf($options['format'], $subject);
        }

        public function getDefaultOptions()
        {
            return array(
                'format' => '%s',
            );
        }
    }

You will need to add the formatter to the ``PhpBench\Tabular\Formatter\RegistryInterface`` implementation. It is recommended that you use a dependency injection container and operate on the registry directly, however if this is not convenient you can dig through the dependencies as follows:

.. code-block:: php

    $myPrintf = new PrintfFormat();
    $tabular = Tabular::getInstance();
    $tabular->getFormatter()->getRegistry()->register('my_printf', $myPrintf);

    // ...
