<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\Intl;

use Cawa\App\AbstractApp;
use Cawa\App\HttpFactory;
use Cawa\Core\DI;
use Cawa\Http\Cookie;
use Cawa\Log\LoggerFactory;
use Punic\Data;
use Symfony\Component\Translation\MessageSelector;

class Translator
{
    use HttpFactory;
    use LoggerFactory;

    /**
     *  Token cookie name.
     */
    const COOKIE_LANGUAGE = 'L';

    /**
     *
     */
    public function __construct()
    {
        $this->locales = DI::config()->getIfExists('locale/available') ?: ['en' => 'en_US.utf8'];
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
        return in_array($locale, array_keys($this->locales));
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
     * @return string
     */
    public function getIETF() : string
    {
        $locale = $this->locales[$this->locale];
        if (strpos($locale, '.') !== false) {
            $locale = substr($locale, 0, strpos($locale, '.'));
        }

        $locale = str_replace('_', '-', $locale);

        return $locale;
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
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return DI::config()->get('locale/default');
    }

    /**
     * detect locale & set cookie if neccesary.
     *
     * @throws \Exception
     */
    private function initLocale()
    {
        $this->locale = $this->detectLocale();

        // datetime format
        if (!setlocale(LC_TIME, $this->locales[$this->locale])) {
            throw new \Exception(sprintf("Unable to set locale to '%s'", $this->locales[$this->locale]));
        }

        // punic default value
        Data::setDefaultLocale($this->getIETF());

        if ('cli' !== PHP_SAPI) {
            if (!self::request()->getCookie(self::COOKIE_LANGUAGE)) {
                self::response()->addCookie(new Cookie(self::COOKIE_LANGUAGE, $this->locale, 60 * 60 * 24 * 365));
            }
        }
    }

    /**
     * @return string
     */
    private function detectLocale() : string
    {
        // cli, can't detect
        if ('cli' === PHP_SAPI) {
            return $this->getDefaultLocale();
        }

        // detection from url
        if (self::request()->getUri()->getPath()) {
            $explode = explode('/', self::request()->getUri()->getPath());
            if (isset($explode[1]) && in_array($explode[1], $this->getLocales())) {
                return $explode[1];
            }
        }

        // detection from cookie
        if ($cookie = self::request()->getCookie(self::COOKIE_LANGUAGE)) {
            if (in_array($cookie, $this->getLocales())) {
                return $cookie->getValue();
            }
        }

        // detection from headers
        $accepted = self::request()->getAcceptedLanguage();

        array_walk($accepted, function (&$value) {
            $value = substr($value, 0, 2);
        });

        $accepted = array_unique($accepted);

        $intersect = array_intersect($accepted, $this->getLocales());

        if (sizeof($intersect) >= 1) {
            return $intersect[array_keys($intersect)[0]];
        } elseif (sizeof($intersect) == 0) {
            return $this->getDefaultLocale();
        }

        return $this->getDefaultLocale();
    }

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @param string $name
     * @param string $rename
     * @param bool $appendLang
     * @param string $type
     *
     * @return bool
     */
    public function addFile(string $name, string $rename = null, bool $appendLang = true, string $type = 'yml') : bool
    {
        if (substr($name, 0, 1) == '/') {
            $path = $name;
            if (is_null($rename)) {
                throw new \LogicException(sprintf("Missing rename parameter on '%s'", $name));
            }

            $name = $rename;
        } else {
            $path = AbstractApp::getAppRoot() . '/lang/' . $name;
        }

        if ($appendLang) {
            $path .= '.' . $this->locale . '.' . $type;
        } else {
            $path .= '.' . $type;
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf("Invalid locale files path '%s'", $name));
        }

        if ($type == 'php') {
            /* @noinspection PhpIncludeInspection */
            $data = require $path;
        } else {
            $data = yaml_parse_file($path);
        }

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
        if ($text && is_array($data) && sizeof($data) > 0 && $text && is_numeric(array_keys($data)[0])) {
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

        $ref = &$this->translations[$name];
        $leave = false;

        while ($leave == false) {
            $key = array_shift($keys);

            if (is_null($key)) {
                $leave = true;
            } else {
                $ref = &$ref[$key];
            }
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
            throw new \DomainException("Missing translation '" . $key . "'");
        } elseif (!is_null($text) && !is_string($text)) {
            throw new \LogicException(sprintf(
                "Invalid translation '%s' with type '%s'",
                $key,
                gettype($text)
            ));
        }

        return $this->replace($text, $data);
    }

    /**
     * @param string[] $keys
     * @param string $glue
     *
     * @return string
     */
    public function transImplode(array $keys, string $glue = ' ')
    {
        $text = [];
        foreach ($keys as $key) {
            $text[] = $this->trans($key);
        }

        return implode($glue, $text);
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
        if (!$data) {
            $data = [$number];
        }

        $text = self::trans($key, null, $warmIfMissing);

        if (is_null($text)) {
            return null;
        }

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
