<?php

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Tabular\TokenReplacer;

class RowDefinitionExpander
{
    private $tokenReplacer;

    public function __construct(TokenReplacer $tokenReplacer = null)
    {
        $this->tokenReplacer = $tokenReplacer ?: new TokenReplacer();
    }

    public function expand(array $rowDefinitions, array $parameters)
    {
        $expanded = array();
        foreach ($rowDefinitions as $rowDefinition) {
            foreach ($this->doRow($rowDefinition, $parameters) as $rowDefinition) {
                $expanded[] = $rowDefinition;
            }
        }

        return $expanded;
    }

    private function doRow(array $rowDefinition, array $parameters)
    {
        $rowItems = array(null);
        $definitions = array();

        if (isset($rowDefinition['with_items'])) {
            $rowItems = $rowDefinition['with_items'];
        }

        // one item equals one row
        foreach ($rowItems as $rowItem) {
            $definitions[] = $this->doRowItem($rowDefinition, $parameters, $item);
        }

        return $definitions;
    }

    private function doRowItem(array $rowDefinition, array $parameters, $rowItem)
    {
        $expanded = $rowDefinition;

        if (isset($rowDefinition['with_query'])) {
            $expanded['with_query'] = $this->tokenReplacer->replaceTokens($rowDefinition['with_query'], $rowItem, null, $parameters);
        }
    }

    private function getColumnNames(array $rowDefinitions)
    {
    }
}
