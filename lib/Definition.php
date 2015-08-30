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

class Definition extends \ArrayObject
{
    private $passes = array();
    private $columnNames = array();
    private $path;

    public function __construct(array $definition, $path = null)
    {
        $this->path = $path;
        parent::__construct($definition);
    }

    public function setMetadata(array $columnNames, array $passes)
    {
        $this->columnNames = $columnNames;
        $this->passes = $passes;
    }

    public function getBasePath()
    {
        return dirname($this->path);
    }

    public function getPasses()
    {
        return $this->passes;
    }

    public function getColumnNames()
    {
        return $this->columnNames;
    }
}
