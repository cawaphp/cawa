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

namespace Cawa\Intl;

use Cawa\Core\App;
use Cawa\Http\Cookie;
use Symfony\Component\Translation\MessageSelector;

class Translator
{

    /**
     *  Token cookie name
     */
    const COOKIE_LANGUAGE = 'L';

    /**
     *
     */
    public function __construct()
    {
        $this->locales = App::config()->getIfExists('locale/available') ?: ['en' => 'en_US.utf8'];
        $this->initLocale();
    }

    /**
     * @var array
     */
    private $locales = [];

    /**+
     * @return array
     */
    public function getLocales() : array
    {
        return array_keys($this->locales);
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function isValidLocale(string $locale) : bool
    {
        return in_array($locale, $this->locales);
    }

    /**
     * @var string
     */
    private $locale = 'en';

    /**
     * @return string
     */
    public function getLocale() : string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        if (!$this->isValidLocale($locale)) {
            throw new \InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }

        $this->locale = $locale;
    }

    /**
     * detect locale & set cookie if neccesary
     */
    private function initLocale()
    {
        $this->locale = $this->detectLocale();

        // datetime format
        if (!setlocale(LC_TIME, $this->locales[$this->locale])) {
            throw new \Exception(sprintf("Unable to set locale to '%s'", $this->locales[$this->locale]));
        }

        if (!App::request()->getCookie(self::COOKIE_LANGUAGE)) {
            App::response()->addCookie(new Cookie(self::COOKIE_LANGUAGE, $this->locale, 60*60*24*365));
        }
    }

    /**
     * @return string
     */
    private function detectLocale() : string
    {
        // detection from url
        $explode = explode('/', App::request()->getUri()->getPath());
        if (isset($explode[1]) && in_array($explode[1], $this->getLocales())) {
            return $explode[1];
        }

        // detection from cookie
        if ($cookie = App::request()->getCookie(self::COOKIE_LANGUAGE)) {
            if (in_array($cookie, $this->getLocales())) {
                return $cookie->getValue();
            }
        }

        // detection from headers
        $accepted = App::request()->getAcceptedLanguage();

        array_walk($accepted, function (&$value) {
            $value = substr($value, 0, 2);
        });

        $accepted = array_unique($accepted);

        $intersect = array_intersect($accepted, $this->getLocales());

        if (sizeof($intersect) >= 1) {
            return $intersect[array_keys($intersect)[0]];
        } elseif (sizeof($intersect) == 0) {
            return App::config()->get('locale/default');
        }

        return $this->locales;
    }

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @param string $file
     * @param bool $appendLang
     *
     * @return string
     */
    private function getAbsolutePath($file, bool $appendLang = true) : string
    {
        if (substr($file, 0, 1) == '/') {
            $path = '';
        } else {
            $path = App::getAppRoot() . '/lang/';
        }

        if ($appendLang) {
            $path .= $file . '.' . $this->locale . '.php';
        } else {
            $path .= $file . '.php';
        }

        return $path;
    }

    /**
     * @param string $name
     * @param string $rename
     * @param bool $appendLang
     *
     * @return bool
     */
    public function addFile(string $name, string $rename = null, bool $appendLang = true) : bool
    {
        if (substr($name, 0, 1) == '/') {
            $path = $name;
            if (is_null($rename)) {
                throw new \LogicException(sprintf("Missing rename parameter on '%s'", $name));
            }

            $name = $rename;
        } else {
            $path = App::getAppRoot() . '/lang/' . $name;
        }

        if ($appendLang) {
            $path .= '.' . $this->locale . '.php';
        } else {
            $path .= '.php';
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf("Invalid locale files path '%s'", $name));
        }

        /* @noinspection PhpIncludeInspection */
        $data = require $path;

        if (!is_array($data)) {
            throw new \LogicException(sprintf("Invalid locale files '%' format, must be a php array", $path));
        }

        $this->translations[$name] = $data;

        return true;
    }

    /**
     * @param string|null $text
     * @param array|null $data
     *
     * @return null|string
     */
    private function replace(string $text = null, array $data = null)
    {
        if ($text && !is_null($data) && $text && is_numeric(array_keys($data)[0])) {
            return vsprintf($text, $data);
        } elseif ($text && !is_null($data) && sizeof($data) > 0) {
            return strtr($text, $data);
        } else {
            return $text ?? null;
        }
    }

    /**
     * @param string $name
     * @param array $keys
     *
     * @return string|array|null
     */
    private function findKey(string $name, array $keys)
    {
        // 1 level keys > optimization
        if (sizeof($keys) == 1) {
            if (isset($this->translations[$name][$keys[0]])) {
                return $this->translations[$name][$keys[0]];
            } else {
                return null;
            }
        }

        // multi level keys
        $ref = &$this->translations[$name];

        while ($key = array_shift($keys)) {
            $ref = &$ref[$key];
        }

        return $ref;
    }

    /**
     * @param string $key
     *
     * @return string|array|null
     */
    public function getKey(string $key)
    {
        $keys = explode('.', $key);
        $file = array_shift($keys);
        $keys = explode('/', implode('.', $keys));

        if (!isset($this->translations[$file])) {
            $this->addFile($file);
        }

        return $this->findKey($file, $keys);
    }

    /**
     * @param string $key
     * @param array $data
     * @param bool $warmIfMissing
     *
     * @return string|null
     */
    public function trans(string $key, array $data = null, bool $warmIfMissing = true)
    {
        $text = $this->getKey($key);

        if (is_null($text) && $warmIfMissing) {
            App::logger()->warning("Missing translation '" . $key . "'");
        } elseif (!is_string($text)) {
            throw new \LogicException(sprintf(
                "Invalid translation '%s' with type '%s'",
                $key,
                gettype($text)
            ));
        }

        return $this->replace($text, $data);
    }

    /**
     * @var MessageSelector
     */
    private $messageChoice;

    /**
     * @param string $key
     * @param int $number
     * @param array|null $data
     * @param bool $warmIfMissing
     *
     * @return string
     */
    public function transChoice(string $key, int $number, array $data = null, bool $warmIfMissing = true)
    {
        $text = $this->trans($key, null, $warmIfMissing);

        if (!$this->messageChoice) {
            $this->messageChoice = new MessageSelector();
        }

        return $this->replace($this->messageChoice->choose($text, $number, $this->locale), $data);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function transArray(string $key) : array
    {
        return $this->getKey($key);
    }

}
