<?php

namespace PhpBench\Tabular;

use JsonSchema\Validator;
use PhpBench\Tabular\Definition;

class DefinitionLoader
{
    /**
     * @var Validator
     */
    private $validator;

    public function __construct(Validator $validator = null)
    {
        $this->validator = $validator ?: new Validator();
    }

    public function load($definition)
    {
        $definition = $this->normalizeDefinition($definition);

        $this->processDefinitionIncludes($definition);
        $this->validateDefinition($definition);

        return $definition;
    }

    private function normalizeDefinition($definition)
    {
        if ($definition instanceof Definition) {
            return $definition;
        }

        if (is_array($definition)) {
            return new Definition($definition);
        }

        if (!is_string($definition)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid definition type "%s"',
                is_object($definition) ? get_class($definition) : gettype($definition)
            ));
        }

        $definitionArray = $this->loadDefinition($definition);
        return new Definition($definitionArray, $definition);
    }

    private function loadDefinition($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException(sprintf(
                'Definition file "%s" does not exist.',
                $filePath
            ));
        }

        $definition = json_decode(file_get_contents($filePath), true);

        if (null === $definition) {
            throw new \RuntimeException(sprintf(
                'Could not decode JSON file "%s"',
                $filePath
            ));
        }

        return $definition;
    }

    private function validateDefinition(Definition $definition)
    {
        $definition = json_decode(json_encode($definition));
        $this->validator->check($definition, json_decode(file_get_contents(__DIR__ . '/schema/table.json')));

        if (!$this->validator->isValid()) {
            $errorString = array();
            foreach ($this->validator->getErrors() as $error) {
                $errorString[] = sprintf('[%s] %s', $error['property'], $error['message']);
            }

            throw new \InvalidArgumentException(sprintf(
                'Invalid table definition: %s%s',
                PHP_EOL . PHP_EOL, implode(PHP_EOL, $errorString)
            ));
        }
    }

    /**
     * Merge any included definitions
     */
    private function processDefinitionIncludes(Definition $definition)
    {
        if (!isset($definition['includes']) || empty($definition['includes'])) {
            return;
        }

        $baseDefinition =  array();
        $validKeys = array('rows', 'sort', 'classes', 'params', 'includes');

        foreach ($definition['includes'] as $include) {
            $keys = array();
            if (count($include) == 2) {
                list($file, $keys) = $include;
            } elseif (count($include) == 1) {
                list($file) = $include;
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid number of arguments given for "include" configuration key, got: "%s"',
                    print_r($include, true)
                ));
            }

            if (empty($keys)) {
                $keys = $validKeys;
            }

            $filePath = PathUtil::getPath($file, $definition->getBasePath());
            $includeDefinition = $this->loadDefinition($filePath);
            $baseDefinition = $this->mergeDefinition($baseDefinition, $includeDefinition, $keys);
        }

        $definition->exchangeArray(
            $this->mergeDefinition($baseDefinition, $definition->getArrayCopy(), $validKeys)
        );
    }

    private function mergeDefinition($baseDefinition, $definition, array $keys)
    {
        foreach ($keys as $key) {
            // keys are already validated by JSON schema, so just skip them if they are not existing
            if (!isset($definition[$key])) {
                continue;
            }

            if (isset($baseDefinition[$key])) {
                $baseDefinition[$key] = array_merge(
                    $baseDefinition[$key], $definition[$key]
                );
            } else {
                $baseDefinition[$key] = $definition[$key];
            }
        }

        return $baseDefinition;
    }
}
