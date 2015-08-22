<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Formatter\Format;

use PhpBench\Tabular\Formatter\FormatInterface;

class NumberFormat implements FormatInterface
{
    public function format($subject, array $options)
    {
        if (!is_numeric($subject)) {
            throw new \InvalidArgumentException(sprintf(
                'Non-numeric value encountered: "%s"',
                print_r($subject, true)
            ));
        }
        return number_format($subject, $options['decimal_places'], $options['decimal_point'], $options['thousands_separator']);
    }

    public function getDefaultOptions()
    {
        return array(
            'decimal_places' => 0,
            'decimal_point' => '.',
            'thousands_separator' => ',',
        );
    }
}
