<?php

namespace PhpBench\Tabular;

class Tabular
{
    public static function tabularize(\DOMDocument $sourceDocument, array $definition)
    {
        $tabulizer = new Tabulizer();

        return $tabulizer->tabularize($sourceDocument, $definition);
    }
}
