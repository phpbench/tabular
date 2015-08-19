<?php

namespace PhpBench\Tabular\Formatter;

class ArrayRegistry implements RegistryInterface
{
    private $formatters = array();

    public function register($name, FormatterInterface $formatter)
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
