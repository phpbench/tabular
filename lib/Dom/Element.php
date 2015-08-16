<?php

namespace PhpBench\Tabular\Dom;

class Element extends \DOMElement
{
    public function appendElement($name)
    {
        return $this->appendChild(new Element($name));
    }
}
