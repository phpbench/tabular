<?php

/*
 * This file is part of the Tabular  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tabular\Definition;

use JsonSchema\Validator;
use PhpBench\Tabular\Definition;
use PhpBench\Tabular\Dom\XPathResolver;
use PhpBench\Tabular\PathUtil;
use PhpBench\Tabular\TokenReplacer;

/**
 * Loads the table definition, processes any includes and determines
 * meta information such as the number of columns and the compiler pass numbers.
 */
class Loader
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var TokenReplacer
     */
    private $tokenReplacer;

    /**
     * @var XPathResolver
     */
    private $xpathResolver;

    /**
     * @param Validator $validator
     * @param TokenReplacer $tokenReplacer
     */
    public function __construct(Validator $validator = null, TokenReplacer $tokenReplacer = null, XPathResolver $xpathResolver = null)
    {
        $this->validator = $validator ?: new Validator();
        $this->tokenReplacer = $tokenReplacer ?: new TokenReplacer();
        $this->xpathResolver = $xpathResolver ?: new XPathResolver();
    }

    /**
     * Load the definition.
     *
     * $definition can be a file name, an array or a Definition class.
     *
     * Note that in order for relative file includes to work either a filename
     * or a Definition instance with the configured basepath must be given.
     *
     * @param mixed $definition
     */
    public function load($definition, $sourceDom = null)
    {
        $definition = $this->normalizeDefinition($definition);

        $this->processDefinitionIncludes($definition);
        $this->validateDefinition($definition);
        $this->processMetadata($definition, $sourceDom);

        return $definition;
    }

    /**
     * Normalize the definition to a Definition class.
     *
     * @param mixed $definition
     *
     * @return Definition
     */
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

    /**
     * Iterate over the definition and determine the number of columns and compiler
     * passes.
     *
     * @param Definition
     */
    private function processMetadata(Definition $definition, $sourceDom = null)
    {
        $columns = array();
        $passes = array();

        if ($sourceDom) {
            $xpath = new \DOMXpath($sourceDom);
            $this->xpathResolver->registerXPathFunctions($xpath);
        }

        foreach ($definition['rows'] as &$rowDefinition) {
            foreach ($rowDefinition['cells'] as &$cellDefinition) {
                $cellName = $cellDefinition['name'];

                if (isset($cellDefinition['pass'])) {
                    $passes[] = $cellDefinition['pass'];
                }

                // if an expression is given to with_items then we need to query
                // the source document to evaluate the item names.
                if (isset($cellDefinition['with_items']['selector'])) {
                    if (null === $sourceDom) {
                        throw new \RuntimeException(
                            'You must pass the source document to the loader in order to use `with_items_expr`'
                        );
                    }

                    $itemsExpr = $cellDefinition['with_items'];
                    $selector = $this->xpathResolver->replaceFunctions($itemsExpr['selector']);
                    $nodes = $xpath->query($selector);
                    $items = array();
                    foreach ($nodes as $node) {
                        // todo: Support keys ?
                        $value = $xpath->evaluate($itemsExpr['value'], $node);
                        $items[] = $value;
                    }
                    $cellDefinition['with_items'] = $items;
                }

                $cellItems = array(null);
                if (isset($cellDefinition['with_items'])) {
                    $cellItems = $cellDefinition['with_items'];
                }

                foreach ($cellItems as $cellItem) {
                    $evaledCellName = $this->tokenReplacer->replaceTokens($cellName, null, $cellItem);
                    $columns[$evaledCellName] = $evaledCellName;
                }
            }
        }

        sort($passes);

        $definition->setMetadata(array_values($columns), $passes);
    }

    /**
     * Load the definition data from a file.
     *
     * @param string $filePath
     *
     * @return array
     */
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

    /**
     * Validate the definition file.
     *
     * @param Definition
     */
    private function validateDefinition(Definition $definition)
    {
        $definition = json_decode(json_encode($definition));
        $this->validator->check($definition, json_decode(file_get_contents(__DIR__ . '/../schema/table.json')));

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
     * Merge any included definitions.
     *
     * @param Definition
     */
    private function processDefinitionIncludes(Definition $definition)
    {
        if (!isset($definition['includes']) || empty($definition['includes'])) {
            return;
        }

        $baseDefinition = array();
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
