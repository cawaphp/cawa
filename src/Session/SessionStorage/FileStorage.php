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

namespace Cawa\Session\SessionStorage;

class FileStorage extends AbstractStorage
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct(string $path = null)
    {
        $this->path = $path ?? ini_get('session.save_path');
    }

    /**
     * {@inheritdoc}
     */
    public function open() : bool
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close() : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $id)
    {
        if (!($content = @file_get_contents($this->path . '/sess_' . $id))) {
            return false;
        }

        $length = strlen($content);
        $saveData = self::unserialize($content);

        return [$saveData['data'], $saveData['startTime'], $saveData['accessTime'], $length];
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, array $data, int $startTime, int $accessTime)
    {
        $saveData = [
            'startTime' => $startTime,
            'accessTime' => $accessTime,
            'data' => $data,
        ];

        $stringData = self::serialize($saveData);
        if (file_put_contents($this->path . '/sess_' . $id, $stringData, LOCK_EX) === false) {
            return false;
        } else {
            return strlen($stringData);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id) : bool
    {
        $file = $this->path . '/sess_' . $id;
        if (file_exists($file)) {
            unlink($file);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function touch(string $id, array $data, int $startTime, int $accessTime)
    {
        return $this->write($id, $data, $startTime, $accessTime);
    }
}
