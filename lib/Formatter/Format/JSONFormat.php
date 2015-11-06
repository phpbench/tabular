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

class JSONFormat implements FormatInterface
{
    public function format($value, array $options)
    {
        $value = json_decode($value);
        $value = json_encode($value, JSON_PRETTY_PRINT);

        return $value;
    }

    public function getDefaultOptions()
    {
        return array();
    }
}
