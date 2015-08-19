<?php

namespace PhpBench\Tabular;

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
        $cellEls = $document->xpath()->query('//cell[@class]');

        foreach ($cellEls as $cellEl) {
            $class = $cellEl->getAttribute('class');
            $classContext = $this->getClassDefinition($class);
            $formatter = $this->registry->get($formatter);
            $options = $this->resolveOptions($formatter, $options);
            $value = $formatter->format($cellEl->nodeValue, $options);
            $cellEl->nodeValue = $value;
        }
    }

    public function registerClassDefinition($class, $formatter, array $definition)
    {
        $this->classDefinitions[$class] = array($formatter, $definition);
    }

    private function getClassDefinition($class)
    {
        if (!isset($this->classDefinitions[$class])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown class "%s", known classes: "%s"',
                $class,
                implode('", "', array_keys($this->classDefinitions))
            ));
        }

        list($formatter, $options) = $this->classDefinitions[$class];
        $formatter = $this->registry->get($formatter);

        return new ClassContext($class, $formatter, $options);
    }
}
