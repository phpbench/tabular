<?php

namespace PhpBench\Tabular;

class TokenReplacer
{
    public function replaceTokens($subject, $rowItem, $cellItem)
    {
        preg_match_all('/{{\s*(.*?)\s*}}/', $subject, $matches);

        $tokens = $matches[0];

        if (empty($tokens)) {
            return $subject;
        }

        foreach ($tokens as $index => $token) {
            $key = $matches[1][$index];
            $parts = explode('.', $key);

            $context = array_shift($parts);

            if (!$context) {
                throw new \InvalidArgumentException(sprintf(
                    'Empty token in string "%s"',
                    $subject
                ));
            }

            if (!in_array($context, array('cell', 'row'))) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown parameter context "%s" in "%s" must be either "cell" or "row"',
                    $context, $token
                ));
            }

            if ($context === 'row') {
                $value = $rowItem;
            } else {
                $value = $cellItem;
            }

            if (is_scalar($value)) {
                if ($parts !== array('item')) {
                    throw new \InvalidArgumentException(sprintf(
                        'The token "%s" is to be replaced by a simple scalar value, it should be named "%s.item"',
                        $token, $context
                    ));
                }

                $subject = str_replace($token, $value, $subject);
                continue;
            }

            if (is_array($value)) {
                $replacement = $this->resolveValue($parts, $value);
                $subject = str_replace($token, $replacement, $subject);
                continue;
            }

            throw new \InvalidArgumentException(sprintf(
                'Invalid parameter type "%s" for token "%s"',
                is_object($value) ? get_class($value) : gettype($value), $token
            ));
        }

        return $subject;
    }

    private function resolveValue($parts, $value)
    {
        if (is_scalar($value)) {
            return $value;
        }

        $part = array_shift($parts);

        if (!isset($value[$part])) {
            throw new \InvalidArgumentException(sprintf(
                'Key "%s" not present in value "%s"',
                $part, print_r($value, true)
            ));
        }

        return $this->resolveValue($parts, $value[$part]);
    }
}
