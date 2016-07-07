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
use Punic\Calendar;
use Symfony\Component\Translation\TranslatorInterface;

class DateTime extends Carbon implements \JsonSerializable
{
    /**
     * 15 hours, 2 minutes
     */
    const DISPLAY_DURATION = 'duration';

    /**
     * Date : 'EEEE, MMMM d, y' - 'Wednesday, August 20, 2014'
     * Time : 'h:mm:ss a zzzz' - '11:42:13 AM GMT+2:00'
     */
    const DISPLAY_FULL = 'full';

    /**
     * Date : 'MMMM d, y' - 'August 20, 2014'
     * Time : 'h:mm:ss a z' - '11:42:13 AM GMT+2:00'
     */
    const DISPLAY_LONG = 'long';

    /**
     * Date : 'MMM d, y' - 'August 20, 2014'
     * Time : 'h:mm:ss a' - '11:42:13 AM'
     */
    const DISPLAY_MEDIUM = 'medium';

    /**
     * Date : 'M/d/yy' - '8/20/14'
     * Time : 'h:mm a' - '11:42 AM'
     */
    const DISPLAY_SHORT = 'short';

    use TranslatorFactory {
        TranslatorFactory::translator as private cawaTranslator;
    }

    /**
     * @var bool
     */
    private static $init = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($time = null, $timezone = null)
    {
        if (!self::$init) {
            self::init();
        }

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
     * Reorder the days property based on current language
     */
    private static function init()
    {
        $day = Calendar::getFirstWeekday();
        DateTime::$weekStartsAt = $day;

        while (array_keys(self::$days)[0] != $day) {
            $key = array_keys(self::$days)[0];
            $value = self::$days[$key];
            unset(self::$days[$key]);
            self::$days[$key] = $value;
        }

        self::$init = true;
    }

    /**
     * @return array
     */
    public static function getDays() : array
    {
        if (!self::$init) {
            self::init();
        }

        return self::$days;
    }

    /**
     * @return array
     */
    public static function getLocalizeDays() : array
    {
        if (!self::$init) {
            self::init();
        }

        $return = [];
        foreach (self::getDays() as $i => $name) {
            $return[$i] = DateTime::localizeDay($i);
        }

        return $return;
    }

    /**
     * @param Time $time
     *
     * @return static
     */
    public function setTimeFromTime(Time $time)
    {
        return $this->setTimeFromTimeString($time->format());
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
            return gmdate('Y-m-d\\TH:i:s.uP', $this->getTimestamp());
        } else {
            return gmdate('Y-m-d\\TH:i:sP', $this->getTimestamp());
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
     * @param string|array $type
     *
     * @return string
     */
    public function display($type = null) : string
    {
        if ($type == self::DISPLAY_DURATION) {
            return $this->diffForHumans(DateTime::now(), true);
        }

        if (is_null($type)) {
            $type = [self::DISPLAY_SHORT, self::DISPLAY_SHORT];
        } elseif (!is_array($type)) {
            $type = [$type, $type];
        } elseif (is_array($type)) {
            if (!isset($type[1])) {
                return Calendar::formatDate($this, $type[0]);
            } elseif (is_null($type[0])) {
                return Calendar::formatTime($this, $type[1]);
            }
        }

        return Calendar::formatDatetime($this, implode('|', $type));
    }
}
