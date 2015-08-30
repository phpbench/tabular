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

class PathUtil
{
    /**
     * Return the absolute path fo the given path.
     *
     * If the given path is absolute it is returned unmodified, otherwise
     * the $basePath is prefixed.
     *
     * @param string $path
     * @param string $basePath
     *
     * @return string
     */
    public static function getPath($path, $basePath)
    {
        if (substr($path, 0, 1) == '/') {
            return $path;
        }

        return $basePath . '/' . $path;
    }
}
