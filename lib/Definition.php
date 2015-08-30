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

/**
 * Object representing the table definition, implements ArrayAccess.
 */
class Definition extends \ArrayObject
{
    /**
     * @var integer[]
     */
    private $passes = array();

    /**
     * @var string[]
     */
    private $columnNames = array();

    /**
     * @var string
     */
    private $path;

    /**
     * @param array $definition
     * @param string $path
     */
    public function __construct(array $definition, $path = null)
    {
        $this->path = $path;
        parent::__construct($definition);
    }

    /**
     * Set the column names and compiler passes.
     *
     * @param array $columnNames
     * @param array $passes
     */
    public function setMetadata(array $columnNames, array $passes)
    {
        $this->columnNames = $columnNames;
        $this->passes = $passes;
    }

    /**
     * Return the path that included files should be relative to.
     *
     * @return string
     */
    public function getBasePath()
    {
        return dirname($this->path);
    }

    /**
     * Return the compiler pass numbers.
     *
     * @return integer[]
     */
    public function getPasses()
    {
        return $this->passes;
    }

    /**
     * Return the column names of this definition
     *
     * @return string[]
     */
    public function getColumnNames()
    {
        return $this->columnNames;
    }
}
