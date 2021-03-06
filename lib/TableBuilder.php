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

use PhpBench\Dom\Element;
use PhpBench\Dom\XPath;
use PhpBench\Tabular\Dom\TableDom;
use PhpBench\Tabular\Dom\XPathResolver;

class TableBuilder
{
    const DEFAULT_GROUP = '_default';

    private $xpathResolver;
    private $tokenReplacer;

    public function __construct(XPathResolver $xpathResolver = null)
    {
        $this->xpathResolver = $xpathResolver ?: new XPathResolver();
        $this->tokenReplacer = new TokenReplacer();
    }

    /**
     * Transform the source DOM into a series of row elements according
     * to the row definitions.
     *
     * @param \DOMDocument $sourceDom
     * @param array $rowDefinitions
     * @param array $parameters
     *
     * @return Document
     */
    public function buildTable(\DOMDocument $sourceDom, Definition $definition, array $parameters = array())
    {
        $tableDom = new TableDom();
        $sourceXpath = new XPath($sourceDom);
        $this->xpathResolver->registerXPathFunctions($tableDom->xpath());
        $this->xpathResolver->registerXPathFunctions($sourceXpath);

        $tableEl = $tableDom->createRoot('table');
        $this->iterateRowDefinitions($tableEl, $sourceXpath, $definition, $parameters);
        $this->executePasses($definition, $tableEl);

        return $tableDom;
    }

    private function iterateRowDefinitions(Element $tableEl, XPath $sourceXpath, Definition $definition, array $parameters)
    {
        foreach ($definition['rows'] as $rowDefinition) {
            $selector = '/';

            if (isset($rowDefinition['with_query'])) {
                $selector = $rowDefinition['with_query'];
                $selector = $this->xpathResolver->replaceFunctions($selector);
            }

            foreach ($sourceXpath->query($selector) as $sourceEl) {
                if (isset($rowDefinition['group'])) {
                    $group = $this->tokenReplacer->replaceTokens($rowDefinition['group'], null, null, $parameters);
                } else {
                    $group = self::DEFAULT_GROUP;
                }

                $groupEls = $tableEl->ownerDocument->xpath()->query('//group[@name="' . $group .'"]');

                if ($groupEls->length > 0) {
                    $groupEl = $groupEls->item(0);
                } else {
                    $groupEl = $tableEl->appendElement('group');
                    $groupEl->setAttribute('name', $group);
                }

                $rowEl = $groupEl->appendElement('row');

                $rowParameters = $parameters;

                if (isset($rowDefinition['param_exprs'])) {
                    foreach ($rowDefinition['param_exprs'] as $paramName => $paramExpr) {
                        // TODO: We should prevent DOMNodeList instances being returned from evaluate..
                        $paramValue = $sourceXpath->evaluate($paramExpr, $sourceEl);
                        $rowParam = $rowEl->appendElement('param');
                        $rowParam->setAttribute('name', $paramName);
                        $rowParam->nodeValue = $paramValue;
                        $rowParameters[$paramName] = $paramValue;
                    }
                }

                foreach ($rowDefinition['cells'] as $cellDefinition) {
                    $columnName = $cellDefinition['name'];

                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', $columnName);

                    $pass = null;
                    if (isset($cellDefinition['pass'])) {
                        $pass = $cellDefinition['pass'];
                        $cellEl->setAttribute('pass', $pass);
                    }

                    $value = null;

                    if (isset($cellDefinition['class'])) {
                        $class = $this->tokenReplacer->replaceTokens($cellDefinition['class'], null, null, $rowParameters);
                        if ($class) {
                            $cellEl->setAttribute('class', $class);
                        }
                    }

                    if (isset($cellDefinition['expr'])) {
                        $expr = $cellDefinition['expr'];
                        $expr = $this->xpathResolver->replaceFunctions($expr);

                        if (null === $pass) {
                            $value = $sourceXpath->evaluate($expr, $sourceEl);
                        } else {
                            $value = $expr;
                        }
                    }

                    if (array_key_exists('literal', $cellDefinition)) {
                        $value = $cellDefinition['literal'];
                    }

                    $cellEl->nodeValue = $value;
                }
            }
        }
    }

    private function executePasses(Definition $definition, Element $tableEl)
    {
        foreach ($definition->getPasses() as $pass) {
            $passCellEls = $tableEl->ownerDocument->xpath()->query('//cell[@pass="' . $pass . '"]');

            foreach ($passCellEls as $passCellEl) {
                $rowEls = $tableEl->ownerDocument->xpath()->query('ancestor::row', $passCellEl);
                $rowEl = $rowEls->item(0);
                $value = $tableEl->ownerDocument->xpath()->evaluate($passCellEl->nodeValue, $rowEl);
                $passCellEl->nodeValue = $value;
            }
        }
    }
}
