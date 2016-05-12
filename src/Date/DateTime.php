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

namespace Cawa\Date;

use Carbon\Carbon;
use Cawa\Core\DI;
use Cawa\Intl\TranslatorFactory;
use Symfony\Component\Translation\TranslatorInterface;

class DateTime extends Carbon implements \JsonSerializable
{
    use TranslatorFactory {
        TranslatorFactory::translator as private cawaTranslator;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct($time = null, $timezone = null)
    {
        parent::__construct($time, $timezone);

        if ($timezone) {
            $convert = is_string($timezone) && $timezone != date_default_timezone_get();
            $convert = $timezone instanceof \DateTimeZone && $timezone->getName() != date_default_timezone_get() ?
                true : $convert;

            if ($convert) {
                $this->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            }
        }
    }

    /**
     * @var \DateTimeZone
     */
    private static $userTimezone;

    /**
     * @return \DateTimeZone
     */
    public static function getUserTimezone() : \DateTimeZone
    {
        if (!self::$userTimezone) {
            $timezone = DI::config()->get('timezone');
            self::$userTimezone = new \DateTimeZone($timezone);
        }

        return self::$userTimezone;
    }

    /**
     * @param \DateTimeZone|string $timezone
     */
    public static function setUserTimezone($timezone)
    {
        if (!$timezone instanceof \DateTimeZone) {
            $timezone = new \DateTimeZone($timezone);
        }

        self::$userTimezone = $timezone;
    }

    /**
     * @return $this
     */
    public function applyUserTimeZone()
    {
        $this->setTimezone(self::getUserTimezone());

        return $this;
    }

    /**
     * Intialize the translator instance if necessary.
     *
     * @return Translator
     */
    protected static function translator()
    {
        if (static::$translator === null) {
            static::$translator = new Translator();
            static::setLocale(self::cawaTranslator()->getLocale());
        }

        return static::$translator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public static function setTranslator(TranslatorInterface $translator)
    {
        if (!$translator instanceof Translator) {
            throw new \Exception(sprintf('DateTime translator must not be %s', get_class($translator)));
        }

        static::$translator = $translator;
    }

    /**
     * Set the current translator locale
     *
     * @param string $locale
     */
    public static function setLocale($locale)
    {
        $reflection = new \ReflectionClass(get_class());

        $path = dirname($reflection->getParentClass()->getFileName()) . '/Lang/';
        self::cawaTranslator()->addFile($path . '/' . parent::translator()->getLocale(), 'carbon', false);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        if ($this->micro) {
            return $this->format('Y-m-d\\TH:i:s.uP');
        } else {
            return $this->format('Y-m-d\\TH:i:sP');
        }
    }

    /**
     * @param int $day
     * @param bool $short
     *
     * @return string
     */
    public static function localizeDay(int $day, bool $short = false)
    {
        $date = new static();
        $date->next($day);

        return ucfirst($date->formatLocalized($short ? '%a' : '%A'));
    }

    /**
     * @param int $month
     * @param bool $short
     *
     * @return string
     */
    public static function localizeMonth(int $month, bool $short = false)
    {
        $date = new static('1970-' . $month . '-01');

        return ucfirst($date->formatLocalized($short ? '%b' : '%B'));
    }

    /**
     * {@inheritdoc}
     */
    public function format($format = null)
    {
        if (is_null($format)) {
            $format = 'Y-m-d H:i:s';
        }

        return parent::format($format);
    }

    /**
     * @param string|null $format
     *
     * @return string
     */
    public function formatTz(string $format = null)
    {
        $clone = clone $this;
        $clone->setTimezone(self::getUserTimezone());

        return $clone->format($format);
    }

    /**
     * @param bool $day
     * @param bool $hour
     *
     * @return string
     */
    public function display(bool $day = true, bool $hour = true) : string
    {
        $clone = clone $this;
        $clone->setTimezone(self::getUserTimezone());

        if ($day && $hour) {
            $format = '%c';
        } elseif ($day && !$hour) {
            $format = '%x';
        } elseif (!$day && $hour) {
            $format = '%X';
        } else {
            throw new \InvalidArgumentException("Can't display date with no format");
        }

        return $clone->formatLocalized($format);
    }
}
