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

class TableDom extends \DOMDocument
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

    public function toArray($group = null)
    {
        $selector = '//row';

        if ($group) {
            $selector = '//group[@name="' . $group . '"]/row';
        }

        $rows = array();
        foreach ($this->xpath()->query($selector) as $rowEl) {
            $row = array();
            foreach ($this->xpath()->query('.//cell', $rowEl) as $cellEl) {
                $colName = $cellEl->getAttribute('name');

                // exclude cells
                if (isset($config['exclude']) && in_array($colName, $config['exclude'])) {
                    continue;
                }

                $row[$colName] = $cellEl->nodeValue;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
