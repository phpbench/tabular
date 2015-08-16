<?php

namespace PhpBench\Tabular\Dom;

use PhpBench\Tabular\Dom\Element;

class Document extends \DOMDocument
{
    private $xpath;

    public function __construct()
    {
        parent::__construct('1.0');
        $this->registerNodeClass('DOMElement', 'PhpBench\Tabular\Dom\Element');
    }

    public function createRoot($name)
    {
        return $this->appendChild(new Element($name));
    }

    public function xpath()
    {
        if ($this->xpath) {
            return $this->xpath;
        }

        $this->xpath = new \DOMXpath($this);

        return $this->xpath;
    }
}
