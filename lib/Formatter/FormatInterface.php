<?php

namespace PhpBench\Tabular\Formatter;

interface FormatInterface
{
    public function format($subject, array $options);

    public function getDefaultOptions();
}
