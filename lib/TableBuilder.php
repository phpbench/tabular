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

use PhpBench\Tabular\Dom\Document;
use PhpBench\Tabular\Dom\Element;
use PhpBench\Tabular\Dom\XPath;
use PhpBench\Tabular\Dom\XPathResolver;

class TableBuilder
{
    const DEFAULT_GROUP = '_default';

    private $xpathResolver;

    public function __construct(XPathResolver $xpathResolver)
    {
        $this->xpathResolver = $xpathResolver;
    }

    /**
     * Transform the source DOM into a series of row elements according
     * to the row definitions.
     *
     * @return Document
     */
    public function buildTable(\DOMDocument $sourceDom, array $rowDefinitions)
    {
        $tableDom = new Document();
        $sourceXpath = new XPath($sourceDom);
        $this->xpathResolver->registerXPathFunctions($tableDom->xpath());
        $this->xpathResolver->registerXPathFunctions($sourceXpath);

        $tableEl = $tableDom->createRoot('table');
        $tableInfo = $this->getTableInfo($rowDefinitions);
        $this->iterateRowDefinitions($tableInfo, $tableEl, $sourceXpath, $rowDefinitions);
        $this->executePasses($tableInfo, $tableEl);

        return $tableDom;
    }

    private function executePasses(TableInfo $tableInfo, Element $tableEl)
    {
        foreach ($tableInfo->passes as $pass) {
            $passCellEls = $tableEl->ownerDocument->xpath()->query('//cell[@pass="' . $pass . '"]');

            foreach ($passCellEls as $passCellEl) {
                $rowEls = $tableEl->ownerDocument->xpath()->query('ancestor::row', $passCellEl);
                $rowEl = $rowEls->item(0);
                $value = $tableEl->ownerDocument->xpath()->evaluate($passCellEl->nodeValue, $rowEl);
                $passCellEl->nodeValue = $value;
            }
        }
    }

    private function iterateRowDefinitions(TableInfo $tableInfo, Element $tableEl, XPath $sourceXpath, $rowDefinitions)
    {
        foreach ($rowDefinitions as $rowDefinition) {
            $selector = '/';

            if (isset($rowDefinition['with_query'])) {
                $selector = $rowDefinition['with_query'];
            }

            $selector = $this->xpathResolver->replaceFunctions($selector);

            $rowItems = array(null);

            if (isset($rowDefinition['with_items'])) {
                $rowItems = $rowDefinition['with_items'];
            }

            foreach ($rowItems as $rowItem) {
                foreach ($sourceXpath->query($selector) as $sourceEl) {
                    if (isset($rowDefinition['group'])) {
                        $group = $rowDefinition['group'];
                    } else {
                        $group = self::DEFAULT_GROUP;
                    }

                    $groupEls = $tableEl->ownerDocument->xpath()->query('//group[@name="' . $group .'"]');

                    if ($groupEls->length > 0) {
                        $groupEl = $groupEls->item(0);
                    } else {
                        $groupEl = $tableEl->appendElement('group');
                        $groupEl->setAttribute('name', $group);
                    }

                    $rowEl = $groupEl->appendElement('row');

                    foreach ($tableInfo->columns as $columnName => $column) {
                        $cellEl = $rowEl->appendElement('cell');
                        $cellEl->setAttribute('name', $columnName);

                        $definitionName = isset($rowDefinition['cells'][$column->originalName]) ? $column->originalName : $column->name;

                        if (!isset($rowDefinition['cells'][$definitionName])) {
                            continue;
                        }

                        $cellDefinition = $rowDefinition['cells'][$definitionName];

                        $pass = null;
                        if (isset($cellDefinition['pass'])) {
                            $pass = $cellDefinition['pass'];
                            $cellEl->setAttribute('pass', $pass);
                        }

                        $cellItem = null;
                        $value = null;

                        if (isset($cellDefinition['with_items'])) {
                            if (isset($cellDefinition['with_items'][$column->itemIndex])) {
                                $cellItem = $cellDefinition['with_items'][$column->itemIndex];
                            }
                        }

                        if (isset($cellDefinition['class'])) {
                            $cellEl->setAttribute('class', $cellDefinition['class']);
                        }

                        if (isset($cellDefinition['expr'])) {
                            $expr = $cellDefinition['expr'];
                            $expr = $this->substituteTokens($expr, 'row', $rowItem);
                            $expr = $this->substituteTokens($expr, 'cell', $cellItem);
                            $expr = $this->xpathResolver->replaceFunctions($expr);

                            if (null === $pass) {
                                $value = $sourceXpath->evaluate($expr, $sourceEl);
                            } else {
                                $value = $expr;
                            }
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
        $passes = array();

        foreach ($rowDefinitions as $rowDefinition) {
            if (isset($rowDefinition['group'])) {
                $groups[$rowDefinition['group']] = true;
            }

            foreach ($rowDefinition['cells'] as $cellName => $cellDefinition) {
                $cellItems = array(null);
                if (isset($cellDefinition['with_items'])) {
                    $cellItems = $cellDefinition['with_items'];
                }

                if (isset($cellDefinition['pass'])) {
                    $passes[] = $cellDefinition['pass'];
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
        sort($passes);
        $tableInfo->columns = $columns;
        $tableInfo->groups = array_keys($groups);
        $tableInfo->passes = $passes;

        return $tableInfo;
    }

    private function substituteTokens($subject, $context, $value)
    {
        if (null === $value) {
            return $subject;
        }

        $result = preg_replace('/{{\s*?' . $context . '\.item\s*}}/', $value, $subject);

        return $result;
    }
}
