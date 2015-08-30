<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Definition;

use PhpBench\Tabular\Definition;
use PhpBench\Tabular\TokenReplacer;

/**
 * Expands the definition as far as is possible. All tokens are replaced
 * and additional rows are added according to the row definitions `with_items` key.
 */
class Expander
{
    /**
     * @var TokenReplacer
     */
    private $tokenReplacer;

    /**
     * @param TokenReplacer $tokenReplacer
     */
    public function __construct(TokenReplacer $tokenReplacer = null)
    {
        $this->tokenReplacer = $tokenReplacer ?: new TokenReplacer();
    }

    /**
     * Expand the defintion by reference.
     *
     * @param Definition $definition
     * @param array $parameters
     */
    public function expand(Definition $definition, array $parameters = array())
    {
        $expRows = array();

        foreach ($definition['rows'] as $rowDefinition) {
            $rowItems = array(null);
            if (isset($rowDefinition['with_items'])) {
                $rowItems = $rowDefinition['with_items'];
            }

            $cellDefinitions = array();
            if (isset($rowDefinition['cells'])) {
                // Index the cell definitions..
                // This needs to be done because we cannot index them by default
                // in the definition because column names are dynamic and may validly be
                // duplicated (e.g. "name": "{{ cell.item }}").
                $cellDefinitions = $this->indexCellDefinitions($rowDefinition['cells']);
            }

            foreach ($rowItems as $rowItem) {
                $expRow = $this->expandRow(
                    $definition->getColumnNames(),
                    $rowDefinition,
                    $cellDefinitions,
                    $rowItem,
                    $parameters
                );

                // no longer need this
                unset($expRow['with_items']);
                $expRows[] = $expRow;
            }
        }

        $definition['rows'] = $expRows;
    }

    /**
     * Replace tokens and add additional rows if items are specified
     * in the definition.
     *
     * @param array $rowDefinition
     * @param array $cellDefinitions
     * @param mixed $rowItem
     */
    private function expandRow(array $columnNames, array $rowDefinition, array $cellDefinitions, $rowItem, array $parameters)
    {
        $expRow = $rowDefinition;

        // expand the query tokens
        if (isset($rowDefinition['with_query'])) {
            $expRow['with_query'] = $this->tokenReplacer->replaceTokens($rowDefinition['with_query'], $rowItem, null, $parameters);
        }

        $expCells = array();
        foreach ($columnNames as $columnName) {
            if (!isset($cellDefinitions[$columnName])) {
                $expCells[] = array(
                    'name' => $columnName,
                    'literal' => null,
                );
                continue;
            }

            $cellDefinition = $cellDefinitions[$columnName];

            // expanded cell name is actually the column name
            $cellDefinition['name'] = $columnName;

            // we only temporarily store the item name in the definition
            $cellItem = $cellDefinition['_item'];
            unset($cellDefinition['_item']);

            // we no longer need the with_items
            unset($cellDefinition['with_items']);

            if (isset($cellDefinition['class'])) {
                $cellDefinition['class'] = $this->tokenReplacer->replaceTokens($cellDefinition['class'], $rowItem, $cellItem, $parameters);
            }

            if (isset($cellDefinition['expr'])) {
                $cellDefinition['expr'] = $this->tokenReplacer->replaceTokens($cellDefinition['expr'], $rowItem, $cellItem, $parameters);
            }

            if (array_key_exists('literal', $cellDefinition)) {
                $cellDefinition['literal'] = $this->tokenReplacer->replaceTokens($cellDefinition['literal'], $rowItem, $cellItem, $parameters);
            }
            $expCells[] = $cellDefinition;
        }

        $expRow['cells'] = $expCells;

        return $expRow;
    }

    /**
     * Index the given cell definitions by name. The name can
     * be dynamic, in which case the name must be evaluated.
     *
     * @param array $cellDefinitions
     *
     * @return array
     */
    private function indexCellDefinitions(array $cellDefinitions)
    {
        $indexedDefinitions = array();
        foreach ($cellDefinitions as $cellDefinition) {
            $cellName = $cellDefinition['name'];

            $cellItems = array(null);
            if (isset($cellDefinition['with_items'])) {
                $cellItems = $cellDefinition['with_items'];
            }

            // we need to evaluate the name for each column
            foreach ($cellItems as $cellItem) {
                $evaledCellName = $this->tokenReplacer->replaceTokens($cellName, null, $cellItem);
                $cellDefinition['_item'] = $cellItem;
                $indexedDefinitions[$evaledCellName] = $cellDefinition;
            }
        }

        return $indexedDefinitions;
    }
}
