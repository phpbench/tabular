<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Formatter\Registry;

use PhpBench\Tabular\Formatter\FormatInterface;
use PhpBench\Tabular\Formatter\RegistryInterface;

class ArrayRegistry implements RegistryInterface
{
    private $formatters = array();

    public function register($name, FormatInterface $formatter)
    {
        if (isset($this->formatters[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Formatter with name "%s" is already registered',
                $name
            ));
        }

        $this->formatters[$name] = $formatter;
    }

    public function get($name)
    {
        if (!isset($this->formatters[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown formatter "%s", known formatters: "%s"',
                $name, implode(', ', array_keys($this->formatters))
            ));
        }

        return $this->formatters[$name];
    }
}
