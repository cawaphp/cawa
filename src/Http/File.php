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

namespace Cawa\Http;

class File
{
    /**
     * @param string $name
     * @param array $file
     *
     * @throws \Exception
     */
    public function __construct(string $name, array $file)
    {
        switch ($file["error"]) {
            case UPLOAD_ERR_INI_SIZE:
                $this->error =  new \OverflowException(sprintf(
                    "The uploaded file '%s' exceeds the upload_max_filesize directive in php.ini",
                    $name
                ));
                break;

            case UPLOAD_ERR_FORM_SIZE:
                $this->error = new \OverflowException(sprintf(
                    "The uploaded file '%s' exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
                    $name
                ));
                break;

            case UPLOAD_ERR_PARTIAL:
                $this->error = new \OverflowException(sprintf(
                    "The uploaded file '%s' was only partially uploaded",
                    $name
                ));
                break;

            case UPLOAD_ERR_NO_FILE:
                $this->error = new \Exception(sprintf(
                    "No file was uploaded on file '%s'",
                    $name
                ));
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error = new \Exception(sprintf(
                    "Missing a temporary folder on file '%s'",
                    $name
                ));
                break;

            case UPLOAD_ERR_CANT_WRITE:
                $this->error = new \Exception(sprintf(
                    "Failed to write file to disk on file '%s'",
                    $name
                ));
                break;

            case UPLOAD_ERR_EXTENSION:
                $this->error = new \Exception(sprintf(
                    "A PHP extension stopped the file upload of '%s'",
                    $name
                ));
                break;

            case UPLOAD_ERR_OK:
                $this->name = $file["name"];
                $this->path = $file["tmp_name"];
                $this->size = $file["size"];
                $this->type = $file["type"];
                break;

            default:
                throw new \LogicException(sprintf("Invalid upload error '%s'",  $file["error"]));

        }
    }

    /**
     * @var \Exception
     */
    private $error;

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     * @throws \Exception
     */
    public function getName() : string
    {
        if ($this->error && $this->error instanceof \Throwable) {
            throw $this->error;
        } else if ($this->error) {
            throw new \Exception($this->error);
        }
        return $this->name;
    }

    /**
     * @var string
     */
    private $type;

    /**
     * @return string
     * @throws \Exception
     */
    public function getType() : string
    {
        if ($this->error && $this->error instanceof \Throwable) {
            throw $this->error;
        } else if ($this->error) {
            throw new \Exception($this->error);
        }

        return $this->type;
    }

    /**
     * @var string
     */
    private $path;

    /**
     * @return string
     * @throws \Exception
     */
    public function getPath() : string
    {
        if ($this->error && $this->error instanceof \Throwable) {
            throw $this->error;
        } else if ($this->error) {
            throw new \Exception($this->error);
        }

        return $this->path;
    }

    /**
     * @var int
     */
    private $size;

    /**
     * @return int
     * @throws \Exception
     */
    public function getSize() : int
    {
        if ($this->error && $this->error instanceof \Throwable) {
            throw $this->error;
        } else if ($this->error) {
            throw new \Exception($this->error);
        }

        return $this->size;
    }

    /**
     * @return string
     */
    public function getContent() : string
    {
        return file_get_contents($this->path);
    }

    /**
     * Exception are not clonable
     */
    public function __clone()
    {
        if ($this->error) {
            $this->error = $this->error->getMessage();
        }
    }
}
