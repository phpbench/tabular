<?php

namespace PhpBench\Tabular\Formatter\Format;

use PhpBench\Tabular\Formatter\FormatInterface;

class BalanceFormat implements FormatInterface
{
    public function format($subject, array $options)
    {
        if ($subject < 0) {
            // switch back to positive so we can use our own prefix
            $subject = $subject * -1;
            return sprintf($options['negative_format'], $subject);
        }

        if ($subject > 0) {
            return sprintf($options['positive_format'], $subject);
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
