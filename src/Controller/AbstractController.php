<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\Controller;

use Cawa\App\App;
use Cawa\Core\DI;

abstract class AbstractController
{
    /**
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function route(string $name, array $data = [])
    {
        return App::router()->getUri($name, $data);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function getAssetData(string $path) : array
    {
        $return = [null, null];

        // file hash
        $hashes = DI::config()->getIfExists('assets/hashes');
        if ($hashes) {
            if (isset($hashes[$path])) {
                $path = $hashes[$path];
                $return[1] = true;
            }
        }

        // relative path like "vendor.js", add assets/url
        if (substr($path, 0, 4) != 'http' && // remove "http//host/vendor.js"
            substr($path, 0, 1) != '/' && // remove "/vendor.js" & "//host/vendor.js"
            $assetsPath = DI::config()->get('assets/url')) {
            $path = rtrim($assetsPath, '/')  . '/' . $path;
        }

        $return[0] = $path;

        return $return;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function asset(string $path) : string
    {
        list($path) = $this->getAssetData($path);

        return $path;
    }
}