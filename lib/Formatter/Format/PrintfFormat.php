<?php

namespace PhpBench\Tabular\Formatter\Formatter;

use PhpBench\Tabular\Formatter\FormatInterface;

class PrintfFormat implements FormatInterface
{
    public function format($subject, array $options)
    {
        return sprintf($options['format'], $subject);
    }

    public function getDefaultOptions()
    {
        return array(
            'format' => '%s'
        );
    }
}

