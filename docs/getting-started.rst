Getting Started
===============

Tabular is meant to be used as a dependency to your project. 

If you just want to experiment with the what the library can do then check out
the `Tabular CLI <https://github.com/phpbench/tabular-cli>`_, have a look at
the examples there and read the
:doc:`definition <definition.rst>` chapter. Otherwise you can continue to install
Tabular as a dependency in your project.

Installation
------------

Install it with composer:

.. code-block:: bash

    $ composer require phpbench/tabular

Quick Instantiation
-------------------

A default instance of Tabular can be obtained using the static `getInstance`
method:

.. code-block:: php

    <?php

    use PhpBench\Tabular\Tabular;

    $tabular = Tabular::getInstance();

But for any reasonable project it is recommended that you wire it up using a
dependency injection container.

Manual Instantiation
--------------------

Using `getInstance` is a quick way to get started, but if you want more
control you can wire it up manually (preferably using a dependency injection
container):

.. code-block:: php

    <?php

    require_once(__DIR__ . '/vendor/autoload.php');

    use PhpBench\Tabular\Formatter\Registry\ArrayRegistry;
    use PhpBench\Tabular\Formatter\Format\PrintfFormat;
    use PhpBench\Tabular\Formatter;
    use PhpBench\Tabular\TableBuilder;
    use PhpBench\Tabular\Definition\Loader;
    use PhpBench\Tabular\Definition\Expander;
    use PhpBench\Tabular\Dom\XPathResolver;
    use PhpBench\Tabular\Tabular;

    $functionRegistry = new ArrayRegistry();
    $functionRegistry->register('printf', new PrintfFormat());
    // ...

    $formatter = new Formatter($functionRegistry);

    $xpathResolver = new XPathResolver();
    $xpathResolver->registerFunction('foo', 'foo_function');
    // ...

    $tableBuilder = new TableBuilder($xpathResolver);

    $loader = new Loader();
    $expander = new Expander();

    $tabular = new Tabular($tableBuilder, $loader, $formatter, $expander);
