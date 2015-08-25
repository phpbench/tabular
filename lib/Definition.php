<?php

namespace PhpBench\Tabular;

class Definition extends \ArrayObject
{
    private $path;

    public function __construct(array $definition, $path = null)
    {
        $this->path = $path;
        parent::__construct($definition);
    }

    public function getBasePath()
    {
        return dirname($this->path);
    }
}
