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
     * @var \DateTimeZone
     */
    private $userTimezone;

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
     * @return \DateTimeZone
     */
    protected function getUserTimezone() : \DateTimeZone
    {
        if (!$this->userTimezone) {
            $timezone = DI::config()->get('timezone');
            $this->userTimezone = new \DateTimeZone($timezone);
        }

        return $this->userTimezone;
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
        $clone->setTimezone($this->getUserTimezone());

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
        $clone->setTimezone($this->getUserTimezone());

        if ($day && $hour) {
            $format = '%x %X';
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
