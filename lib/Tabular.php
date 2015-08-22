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

class Tabular
{
    const DEFAULT_GROUP = '_default';

    private $validator;
    private $tableBuilder;
    private $formatter;

    public function __construct(TableBuilder $tableBuilder, Validator $validator, Formatter $formatter)
    {
        $this->validator = $validator;
        $this->tableBuilder = $tableBuilder;
        $this->formatter = $formatter;
    }

    public function tabulate(\DOMDocument $sourceDom, array $definition)
    {
        $this->validateDefinition($definition);

        $tableDom = $this->tableBuilder->buildTable($sourceDom, $definition['rows']);

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

    private function validateDefinition(array $definition)
    {
        $definition = json_decode(json_encode($definition));
        $this->validator->check($definition, json_decode(file_get_contents(__DIR__ . '/schema/table.json')));

        if (!$this->validator->isValid()) {
            $errorString = array();
            foreach ($this->validator->getErrors() as $error) {
                $errorString[] = sprintf('[%s] %s', $error['property'], $error['message']);
            }

            throw new \InvalidArgumentException(sprintf(
                'Invalid table definition: %s%s',
                PHP_EOL . PHP_EOL, implode(PHP_EOL, $errorString)
            ));
        }
    }
}
