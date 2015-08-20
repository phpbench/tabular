<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Dom;

class Element extends \DOMElement
{
    public function appendElement($name)
    {
        return $this->appendChild(new self($name));
    }

    public function query($xpath)
    {
        return $this->ownerDocument->xpath()->query($xpath, $this);
    }
}
