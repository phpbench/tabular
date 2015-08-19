<?php

namespace PhpBench\Tabular;

use PhpBench\Tabular\Formatter\Formatter\PrintfFormat;
use PhpBench\Tabular\Dom\Document;
use PhpBench\Tabular\Dom\Element;
use PhpBench\Tabular\Sort;
use JsonSchema\Validator;

class Tabulizer
{
    const DEFAULT_GROUP = '_default';

    private $validator;
    private $tableBuilder;
    private $formatter;

    public function __construct(TableBuilder $tableBuilder = null, Validator $validator, Formatter $formatter = null)
    {
        $this->validator = $validator ?:  new Validator();
        $this->tableBuilder = $tableBuilder ?: new RowBuilder();
        $this->formatter = $formatter ?: new Formatter();

        if (null === $formatter) {
            $this->formatter->register('printf', new PrintfFormat());
        }
    }

    public function tabularize(\DOMDocument $sourceDom, array $definition)
    {
        $this->validateDefinition($definition);

        $tableDom = $this->tableBuilder->buildTable($sourceDom, $definition['rows']);

        if (isset($definition['sort'])) {
            Sort::sortTable($tableDom, $definition['sort']);
        }

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
