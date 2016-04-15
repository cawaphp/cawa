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
use Cawa\Intl\TranslatorFactory;

abstract class AbstractController
{
    use TranslatorFactory;

    /**
     * @var array
     */
    protected $path;

    /**
     *
     */
    public function __construct()
    {
        $reflection = new \ReflectionClass($this);
        $filename = $reflection->getFileName();
        $this->path = pathinfo($filename);
    }

    /**
     * Escape content
     *
     * @param string $content
     *
     * @return string
     */
    public function escape(string $content) : string
    {
        return htmlspecialchars($content);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getLocalePath(string $path) : string
    {
        if (!isset($this->path['dirname'])) {
            throw new \RuntimeException('call too early before AbstractController::__construct call');
        }

        if (substr($path, 0, 2) == '..') {
            return $this->path['dirname'] . '/' . $path;
        } elseif (substr($path, 0, 1) == '/') {
            return $path;
        } else {
            return $this->path['dirname'] . '/lang/' . $path;
        }
    }

    /**
     * @param string $path
     * @param string $name
     *
     * @return bool
     */
    protected function addLocaleFile(string $path, $name) : bool
    {
        return self::translator()->addFile($this->getLocalePath($path), $name);
    }

    /**
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function trans(string $name, array $data = null)
    {
        return self::translator()->trans($name, $data);
    }

    /**
     * @param string $name
     * @param int $number
     * @param array $data
     *
     * @return string
     */
    public function transChoice(string $name, int $number, array $data = null)
    {
        return self::translator()->transChoice($name, $number, $data);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function transArray(string $name)
    {
        return self::translator()->transArray($name);
    }

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
