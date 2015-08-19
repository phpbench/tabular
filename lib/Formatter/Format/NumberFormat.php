<?php

namespace PhpBench\Tabular\Formatter\Formatter;

use PhpBench\Tabular\Formatter\FormatInterface;

class NumberFormat implements FormatInterface
{
    public function format($subject, array $options)
    {
        return number_format($subject, $options['decimal_places'], $options['decimal_point'], $options['thousands_separator']);
    }

    public function getDefaultOptions()
    {
        return array(
            'decimal_places' => 0,
            'decimal_point' => '.',
            'thousands_separator' => ','
        );
    }
}
