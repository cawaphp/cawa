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

namespace Cawa\Date;

use Cake\Chronos\Chronos;
use Cake\Chronos\DifferenceFormatter;
use Cawa\Intl\TranslatorFactory;
use Punic\Calendar;

class DateTime extends Chronos implements \JsonSerializable
{
    /**
     * 15 hours, 2 minutes.
     */
    const DISPLAY_DURATION = 'duration';

    /**
     * Date : 'EEEE, MMMM d, y' - 'Wednesday, August 20, 2014'
     * Time : 'h:mm:ss a zzzz' - '11:42:13 AM GMT+2:00'.
     */
    const DISPLAY_FULL = 'full';

    /**
     * Date : 'MMMM d, y' - 'August 20, 2014'
     * Time : 'h:mm:ss a z' - '11:42:13 AM GMT+2:00'.
     */
    const DISPLAY_LONG = 'long';

    /**
     * Date : 'MMM d, y' - 'August 20, 2014'
     * Time : 'h:mm:ss a' - '11:42:13 AM'.
     */
    const DISPLAY_MEDIUM = 'medium';

    /**
     * Date : 'M/d/yy' - '8/20/14'
     * Time : 'h:mm a' - '11:42 AM'.
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
                $date = $this->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                parent::__construct($date->format('Y-m-d H:i:s.u'));
            }
        }
    }

    /**
     * Reorder the days property based on current language.
     */
    private static function init()
    {
        $day = Calendar::getFirstWeekday();
        $day = $day == 0 ? 7 : $day;
        self::$weekStartsAt = $day;

        $days = self::$days;
        while (array_keys($days)[0] != $day) {
            $key = array_keys($days)[0];
            $value = $days[$key];
            unset($days[$key]);
            $days[$key] = $value;
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
            $return[$i] = self::localizeDay($i);
        }

        return $return;
    }

    /**
     * @param Time $time
     *
     * @return $this|self
     */
    public function setTimeFromTime(Time $time)
    {
        return $this->setTimeFromTimeString($time->format());
    }

    /**
     * {@inheritdoc}
     */
    public static function diffFormatter($formatter = null)
    {
        if (static::$diffFormatter === null) {
            static::$diffFormatter = new DifferenceFormatter(new Translator());
        }

        return static::$diffFormatter;
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
        $date = (new static())
            ->next($day);

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
     * Format the instance with the current locale.  You can set the current
     * locale using setlocale() http://php.net/setlocale.
     *
     * @param string $format
     *
     * @return string
     */
    public function formatLocalized($format)
    {
        // Check for Windows to find and replace the %e
        // modifier correctly
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }

        return strftime($format, strtotime((string) $this));
    }

    /**
     * @param string|array $type
     *
     * @return string
     */
    public function display($type = null) : string
    {
        if ($type == self::DISPLAY_DURATION) {
            return $this->diffForHumans(self::now(), true);
        }

        if (is_null($type)) {
            $type = [self::DISPLAY_SHORT, self::DISPLAY_SHORT];
        } elseif (!is_array($type)) {
            $type = [$type, $type];
        } elseif (is_array($type)) {
            if (!isset($type[1])) {
                return Calendar::formatDate($this->toMutable(), $type[0]);
            } elseif (is_null($type[0])) {
                return Calendar::formatTime($this->toMutable(), $type[1]);
            }
        }

        return Calendar::formatDatetime($this->toMutable(), implode('|', $type));
    }
}
