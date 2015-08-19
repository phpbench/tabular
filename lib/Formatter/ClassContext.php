<?php

namespace PhpBench\Tabular\Formatter;

class ClassContext
{
    private $class;
    private $formatter;
    private $options = array();

    public function __construct($class, Format $formatter, array $options)
    {
        $this->class = $class;
        $this->formatter = $formatter;
        $this->options = $options;
    }
}
