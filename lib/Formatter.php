<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular;

use PhpBench\Tabular\Dom\Document;
use PhpBench\Tabular\Dom\Element;
use PhpBench\Tabular\Formatter\RegistryInterface;

class Formatter
{
    private $registry;
    private $classDefinitions = array();

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function formatTable(Document $document)
    {
        $document->formatOutput = true;
        $cellEls = $document->xpath()->query('//cell[@class]');

        foreach ($cellEls as $cellEl) {
            $this->formatCell($cellEl);
        }
    }

    private function formatCell(Element $cellEl)
    {
        $class = $cellEl->getAttribute('class');

        if (!isset($this->classDefinitions[$class])) {
            throw new \InvalidArgumentException(sprintf(
                'No class defined with name "%s", known classes: "%s"',
                $class, implode('", "', array_keys($this->classDefinitions))
            ));
        }

        $formatterDefinitions = $this->classDefinitions[$class];
        $value = $cellEl->nodeValue;

        foreach ($formatterDefinitions as $formatterDefinition) {
            if (count($formatterDefinition) == 2) {
                list($formatterName, $options) = $formatterDefinition;
            } else {
                list($formatterName) = $formatterDefinition;
            }
            $formatter = $this->registry->get($formatterName);
            $defaultOptions = $formatter->getDefaultOptions();

            $diff = array_diff_key($options, $defaultOptions);

            if (count($diff)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown options ["%s"] for formatter "%s" (class "%s"). Known options "%s"',
                    implode('", "', array_keys($diff)),
                    $formatterName,
                    $class,
                    implode('", "', array_keys($defaultOptions))
                ));
            }

            $options = array_merge($defaultOptions, $options);
            $value = $formatter->format($value, $options);
        }

        $cellEl->nodeValue = $value;
    }

    public function setClassDefinition($class, $formatterDefinitions)
    {
        $this->classDefinitions[$class] = $formatterDefinitions;
    }
}
