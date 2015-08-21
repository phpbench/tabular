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

class XPath extends \DOMXPath
{
    public function evaluate($expr, \DOMNode $contextEl = null, $registerNodeNs = null)
    {
        return $this->execute('evaluate', 'expression', $expr, $contextEl, $registerNodeNs);
    }

    public function query($expr, \DOMNode $contextEl = null, $registerNodeNs = null)
    {
        return $this->execute('query', 'query', $expr, $contextEl, $registerNodeNs);
    }

    private function execute($method, $context, $query, \DOMNode $contextEl = null, $registerNodeNs)
    {
        libxml_use_internal_errors(true);

        $value = @parent::$method($query, $contextEl, $registerNodeNs);

        if (false === $value) {
            $xmlErrors = libxml_get_errors();
            $errors = array();
            foreach ($xmlErrors as $xmlError) {
                $errors[] = sprintf('[%s] %s', $xmlError->code, $xmlError->message);
            }

            throw new \InvalidArgumentException(sprintf(
                'Errors encountered when evaluating XPath %s "%s": %s%s',
                $context, $query, PHP_EOL, implode(PHP_EOL, $errors)
            ));
        }

        libxml_use_internal_errors(false);

        return $value;
    }
}
