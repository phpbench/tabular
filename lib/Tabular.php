<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular;

use JsonSchema\Validator;
use PhpBench\Tabular\Definition;
use PhpBench\Tabular\Dom\Document;
use PhpBench\Tabular\Definition\Loader;

class Tabular
{
    const DEFAULT_GROUP = '_default';

    /**
     * @Var DefinitionLoader
     */
    private $definitionLoader;

    /**
     * @var TableBuilder
     */
    private $tableBuilder;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @param TableBuilder $tableBuilder
     * @param Validator $validator
     * @param Formatter $formatter
     */
    public function __construct(TableBuilder $tableBuilder, Loader $definitionLoader, Formatter $formatter)
    {
        $this->definitionLoader = $definitionLoader;
        $this->tableBuilder = $tableBuilder;
        $this->formatter = $formatter;
    }

    /**
     * Process the source document using the given Tabular definition and return a table document.
     *
     * The definition can be passed either as an array, a Defintion class or a file name.
     *
     * @param \DOMDocument $sourceDom
     * @param array|string|Definition $definition
     * @param array $parameters
     *
     * @return Document
     */
    public function tabulate(\DOMDocument $sourceDom, $definition, array $parameters = array())
    {
        $definition = $this->definitionLoader->load($definition);

        if (isset($definition['params'])) {
            $parameters = array_merge(
                $definition['params'], $parameters
            );
        }

        $tableDom = $this->tableBuilder->buildTable($sourceDom, $definition, $parameters);

        if (isset($definition['sort'])) {
            Sort::sortTable($tableDom, $definition['sort']);
        }

        if (isset($definition['classes'])) {
            foreach ($definition['classes'] as $class => $classDefinition) {
                $formatters = array();
                foreach ($classDefinition as $formatDefinition) {
                    $options = array();
                    if (count($formatDefinition) == 2) {
                        list($formatter, $options) = $formatDefinition;
                    } else {
                        list($formatter) = $formatDefinition;
                    }

                    $formatters[] = array($formatter, $options ?: array());
                }

                $this->formatter->setClassDefinition($class, $formatters);
            }
        }

        $this->formatter->formatTable($tableDom);

        return $tableDom;
    }

}
