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

require_once(__DIR__ . '/functions.php');

class XPathResolver
{
    private $functions = array();
    private $xpathFunctions = array(
        'last' => true,
        'position' => true,
        'count' => true,
        'id' => true,
        'local-name' => true,
        'namespace-uri' => true,
        'name' => true,
        'string' => true,
        'concat' => true,
        'starts-with' => true,
        'contains' => true,
        'substring-before' => true,
        'substring-after' => true,
        'substring' => true,
        'string-length' => true,
        'normalize-space' => true,
        'translate' => true,
        'boolean' => true,
        'not' => true,
        'true' => true,
        'false' => true,
        'lang' => true,
        'number' => true,
        'sum' => true,
        'floor' => true,
        'ceiling' => true,
        'round' => true,
    );

    public function registerXPathFunctions(\DOMXpath $xpath)
    {
        $xpath->registerNamespace('php', 'http://php.net/xpath');
        $xpath->registerPhpFunctions(array_values($this->functions));
        $this->registerDefaultFunctions();
    }

    public function registerFunction($name, $fqn)
    {
        $this->functions[$name] = $fqn;
    }

    public function replaceFunctions($xpathQuery)
    {
        preg_match_all('{([a-zA-Z0-9_-]+)\((\)?)}', $xpathQuery, $matches);

        foreach ($matches[1] as $index => $name) {
            if (isset($this->xpathFunctions[$name])) {
                continue;
            }

            if (!isset($this->functions[$name])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown Tabular XPath function "%s" in query "%s". Known Tabular functions: "%s"',
                    $name, $xpathQuery, implode('", "', array_keys($this->functions))
                ));
            }

            $fqn = $this->functions[$name];
            $expanded = 'php:function(\'' . $fqn . '\'';

            if (empty($matches[2][$index])) {
                $expanded = $expanded . ', ';
            }

            $xpathQuery = str_replace(
                $name . '(',
                $expanded,
                $xpathQuery
            );
        }

        return $xpathQuery;
    }

    private function registerDefaultFunctions()
    {
        $this->registerFunction('average', 'PhpBench\Tabular\Dom\functions\mean');
        $this->registerFunction('deviation', 'PhpBench\Tabular\Dom\functions\deviation');
        $this->registerFunction('min', 'PhpBench\Tabular\Dom\functions\min');
        $this->registerFunction('max', 'PhpBench\Tabular\Dom\functions\max');
        $this->registerFunction('median', 'PhpBench\Tabular\Dom\functions\median');
    }

}
