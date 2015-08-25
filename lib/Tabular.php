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

    public function tabulate(\DOMDocument $sourceDom, $definition, array $parameters = array())
    {
        $definition = $this->getDefinition($definition);
        $this->validateDefinition($definition);

        if (isset($definition['params'])) {
            $parameters = array_merge(
                $definition['params'], $parameters
            );
        }

        $tableDom = $this->tableBuilder->buildTable($sourceDom, $definition['rows'], $parameters);

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

    private function getDefinition($definition)
    {
        if ($definition instanceof Definition) {
            return $definition;
        }

        if (is_array($definition)) {
            return new Definition($definition);
        }

        if (!is_string($definition)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid definition type "%s"',
                is_object($definition) ? get_class($definition) : gettype($definition)
            ));
        }

        if (!file_exists($definition)) {
            throw new \InvalidArgumentException(sprintf(
                'Definition file "%s" does not exist.',
                $definition
            ));
        }

        $filePath = $definition;
        $definition = json_decode(file_get_contents($filePath), true);

        if (null === $definition) {
            throw new \RuntimeException(sprintf(
                'Could not decode JSON file "%s"',
                $filePath
            ));
        }

        return new Definition($definition, $filePath);
    }

    private function validateDefinition(Definition $definition)
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
