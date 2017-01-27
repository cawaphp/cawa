<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\Http;

class File implements \Serializable
{
    /**
     * @param string $name
     * @param array $file
     *
     * @throws \Exception
     */
    public function __construct(string $name, array $file)
    {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $this->error = new \OverflowException(sprintf(
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
                $this->name = $file['name'];
                $this->path = $file['tmp_name'];
                $this->size = $file['size'];
                $this->type = $file['type'];
                break;

            default:
                throw new \LogicException(sprintf("Invalid upload error '%s'", $file['error']));
        }
    }

    /**
     * @param array $raw
     *
     * @return $this[]|self[]
     */
    public static function create(array $raw) : array
    {
        $return = [];
        foreach ($raw as $postName => $fileValue) {
            if (!is_array($fileValue['error'])) {
                if ($fileValue['error'] != 4) {
                    $return[$postName] = new File($postName, $fileValue);
                }
            } else {
                $keys = [$postName];

                $browse = function (\RecursiveArrayIterator $iterator) use (
                    &$browse,
                    &$keys,
                    &$return,
                    $postName,
                    $fileValue
                ) {
                    while ($iterator->valid()) {
                        $arrayKeys = array_keys($iterator->getArrayCopy());
                        $currentKey = $arrayKeys[sizeof($arrayKeys) - 1];
                        $currentValue = $iterator->getArrayCopy()[$iterator->key()];

                        if ($iterator->hasChildren()) {
                            $keys[] = $iterator->key();
                            $browse($iterator->getChildren());
                        } else {
                            $names = &$fileValue['name'];
                            $types = &$fileValue['type'];
                            $sizes = &$fileValue['size'];
                            $tmps = &$fileValue['tmp_name'];
                            $errors = &$fileValue['error'];

                            $clone = array_merge($keys, [$iterator->key()]);
                            array_shift($clone);

                            $returnRef = &$return[$postName];

                            while (!is_null($key = array_shift($clone))) {
                                $names = &$names[$key];
                                $types = &$types[$key];
                                $sizes = &$sizes[$key];
                                $tmps = &$tmps[$key];
                                $errors = &$errors[$key];

                                $returnRef = &$returnRef[$key];
                            }

                            if (!is_null($names) && $errors != 4) {
                                $returnRef = new File(implode('/', $keys), [
                                    'name' => $names,
                                    'type' => $types,
                                    'size' => $sizes,
                                    'tmp_name' => $tmps,
                                    'error' => $errors,
                                ]);
                            }
                        }

                        // reset, iterator change tree
                        if (!is_array($currentValue) && $currentKey == $iterator->key()) {
                            $keys = [$postName];
                        }

                        $iterator->next();
                    }
                };

                $iterator = new \RecursiveArrayIterator($fileValue['name']);
                iterator_apply($iterator, $browse, [$iterator]);
            }
        }

        return $return;
    }

    /**
     * @var \Exception
     */
    private $error;

    /**
     * @throws \Exception
     */
    private function raiseException()
    {
        if ($this->error && $this->error instanceof \Throwable) {
            throw $this->error;
        } elseif ($this->error) {
            throw new \Exception($this->error);
        }
    }

    /**
     * @var string
     */
    private $name;

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getName() : string
    {
        $this->raiseException();

        return $this->name;
    }

    /**
     * @var string
     */
    private $type;

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getType() : string
    {
        $this->raiseException();

        return $this->type;
    }

    /**
     * @var string
     */
    private $path;

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getPath() : string
    {
        $this->raiseException();

        return $this->path;
    }

    /**
     * @var int
     */
    private $size;

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function getSize() : int
    {
        $this->raiseException();

        return $this->size;
    }

    /**
     * @return string
     */
    public function getContent() : string
    {
        $this->raiseException();

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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        if ($this->error) {
            $this->error = $this->error->getMessage();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
    }
}
