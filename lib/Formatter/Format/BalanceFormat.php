<?php

namespace PhpBench\Tabular\Formatter\Formatter;

use PhpBench\Tabular\Formatter\FormatInterface;

class BalanceFormat implements FormatInterface
{
    public function format($subject, array $options)
    {
        if ($subject < 0) {
            return sprintf($options['negative_format']);
        }

        if ($subject > 0) {
            return sprintf($options['positive_format']);
        }

        return sprintf($options['neutral_format'], $subject);
    }

    public function getDefaultOptions()
    {
        return array(
            'neutral_format' => '%s',
            'negative_format' => '-%s',
            'positive_format' => '+%s',
        );
    }
}
