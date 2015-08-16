<?php

namespace PhpBench\Tabular;

use PhpBench\Tabular\Dom\Document;
use PhpBench\Tabular\Dom\Element;

class Tabulizer
{
    public function tabularize(\DOMDocument $sourceDom, array $definition)
    {
        $tableDom = new Document();
        $tableEl = $tableDom->createRoot('table');
        $sourceXpath = new \DOMXpath($sourceDom);

        $this->iterateRowDefinitions($tableEl, $sourceXpath, $definition['rows']);
        $this->sortTable($tableDom, $definition['sort']);

        return $tableDom;
    }

    public function iterateRowDefinitions(Element $tableEl, \DOMXpath $sourceXpath, $rowDefinitions)
    {
        $tableInfo = $this->getTableInfo($rowDefinitions);

        foreach ($rowDefinitions as $rowDefinition) {

            $selector = '/';

            if (isset($rowDefinition['with_query'])) {
                $selector = $rowDefinition['with_query'];
            }

            $rowItems = array(null);

            if (isset($rowDefinition['with_items'])) {
                $rowItems = $rowDefinition['with_items'];
            }

            foreach ($rowItems as $rowItem) {
                foreach ($sourceXpath->query($selector) as $sourceEl) {
                    $rowEl = $tableEl->appendElement('row');

                    if (isset($rowDefinition['group'])) {
                        $rowEl->setAttribute('group', $rowDefinition['group']);
                    }

                    foreach ($tableInfo->columns as $columnName => $column) {

                        $cellEl = $rowEl->appendElement('cell');
                        $cellEl->setAttribute('name', $columnName);

                        if (!isset($rowDefinition['cells'][$column->originalName])) {
                            continue;
                        }

                        $cellDefinition = $rowDefinition['cells'][$column->originalName];

                        $cellItem = null;
                        $value = null;

                        if (isset($cellDefinition['with_items'])) {
                            if (isset($cellDefinition['with_items'][$column->itemIndex])) {
                                $cellItem = $cellDefinition['with_items'][$column->itemIndex];
                            }
                        }

                        if (isset($cellDefinition['expr'])) {
                            $value = $this->substituteTokens($sourceXpath->evaluate($cellDefinition['expr'], $sourceEl), 'row', $rowItem);
                            $value = $this->substituteTokens($value, 'cell', $cellItem);
                        }

                        if (array_key_exists('literal', $cellDefinition)) {
                            $value = $this->substituteTokens($cellDefinition['literal'], 'row', $rowItem);
                            $value = $this->substituteTokens($value, 'cell', $cellItem);
                        }

                        $cellEl->nodeValue = $value;
                    }
                }
            }
        }
    }

    private function getTableInfo($rowDefinitions)
    {
        $tableInfo = new TableInfo();
        $columns = array();
        $groups = array();

        foreach ($rowDefinitions as $rowDefinition) {
            if (isset($rowDefinition['group'])) {
                $groups[$rowDefinition['group']] = true;
            }

            foreach ($rowDefinition['cells'] as $cellName => $cellDefinition) {
                $cellItems = array(null);
                if (isset($cellDefinition['with_items'])) {
                    $cellItems = $cellDefinition['with_items'];
                }

                foreach ($cellItems as $paramIndex => $cellItem) {
                    $column = new ColumnInfo();
                    $column->itemIndex = $paramIndex;
                    $column->originalName = $cellName;
                    $evaledCellName = $this->substituteTokens($cellName, 'cell', $cellItem);
                    $column->name = $evaledCellName;
                    $columns[$evaledCellName] = $column;
                }
            }
        }

        $tableInfo->columns = $columns;
        $tableInfo->groups = array_keys($groups);

        return $tableInfo;
    }

    private function substituteTokens($subject, $context, $value)
    {
        if (null === $value) {
            return $subject;
        }

        return preg_replace('/{{\s*?' . $context . '\.item\s*}}/', $value, $subject);
    }

    private function sortTable(Document $tableDom, $sortDefinition)
    {
        $array = iterator_to_array($tableDom->xpath()->query('//row'));
    }
}
