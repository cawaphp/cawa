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

namespace Cawa\Intl;

use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberToTimeZonesMapper;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber
{
    use TranslatorFactory;

    /**
     * House Phone
     */
    const TYPE_FIXED_LINE = 0;

    /**
     * Mobile Phone
     */
    const TYPE_MOBILE = 1;

    /**
     * In some regions (e.g. the USA), it is impossible to distinguish between fixed-line and
     * mobile numbers by looking at the phone number itself.
     */
    const TYPE_FIXED_LINE_OR_MOBILE = 2;

    /**
     * Freephone lines
     */
    const TYPE_TOLL_FREE = 3;

    /**
     * Premium Rate
     */
    const TYPE_PREMIUM_RATE = 4;

    /**
     * The cost of this call is shared between the caller and the recipient, and is hence typically
     * less than PREMIUM_RATE calls. See // http://en.wikipedia.org/wiki/Shared_Cost_Service for
     * more information.
     */
    const TYPE_SHARED_COST = 5;

    /**
     * Voice over IP numbers. This includes TSoIP (Telephony Service over IP).
     */
    const TYPE_VOIP = 6;

    /**
     * A personal number is associated with a particular person, and may be routed to either a
     * MOBILE or FIXED_LINE number. Some more information can be found here:
     * http://en.wikipedia.org/wiki/Personal_Numbers
     */
    const TYPE_ERSONAL_NUMBER = 7;

    /**
     * Pager
     */
    const TYPE_PAGER = 8;

    /**
     * Used for "Universal Access Numbers" or "Company Numbers". They may be further routed to
     * specific offices, but allow one number to be used for a company.
     */
    const TYPE_UAN = 9;

    /**
     * A phone number is of type UNKNOWN when it does not fit any of the known patterns for a
     * specific region.
     */
    const TYPE_UNKNOWN = 10;

    /**
     * Voicemail
     */
    const TYPE_VOICEMAIL = 28;

    /**
     * Short Code
     */
    const TYPE_SHORT_CODE = 29;

    /**
     * @param string $phone
     */
    public function __construct(string $phone)
    {
        $this->util = PhoneNumberUtil::getInstance();
        $this->number = $this->util->parse($phone, null);
    }

    /**
     * @var PhoneNumberUtil
     */
    private $util;

    /**
     * @var \libphonenumber\PhoneNumber
     */
    private $number;

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->util->isValidNumber($this->number);
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        list($language, $region) = explode('-', $this->translator()->getIETF());

        $phoneNumberGeocoder = PhoneNumberOfflineGeocoder::getInstance();

        return $phoneNumberGeocoder->getDescriptionForNumber(
            $this->number,
            $language,
            $region
        );
    }

    /**
     * @return string
     */
    public function getExtension() : string
    {
        return $this->number->getExtension();
    }

    /**
     * @return int
     */
    public function getCountryCode() : int
    {
        return $this->number->getCountryCode();
    }

    /**
     * @return string
     */
    public function getCountry() : string
    {
        return $this->util->getRegionCodeForNumber($this->number);
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->util->getNumberType($this->number);
    }

    /**
     * @return string
     */
    public function getE164() : string
    {
        return $this->util->format($this->number, PhoneNumberFormat::E164);
    }

    /**
     * @return string
     */
    public function getNational() : string
    {
        return $this->util->format($this->number, PhoneNumberFormat::NATIONAL);
    }

    /**
     * @return string
     */
    public function getInternational() : string
    {
        return $this->util->format($this->number, PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * @param string $country
     *
     * @return string
     */
    public function callFrom(string $country) : string
    {
        return $this->util->formatOutOfCountryCallingNumber($this->number, $country);
    }

    /**
     * @return string
     */
    public function getCarrier() : string
    {
        $carrierMapper = PhoneNumberToCarrierMapper::getInstance();

        return $carrierMapper->getNameForNumber($this->number, $this->locale());
    }

    /**
     * @return array
     */
    public function getTimezone() : array
    {
        $timeZoneMapper = PhoneNumberToTimeZonesMapper::getInstance();

        return $timeZoneMapper->getTimeZonesForNumber($this->number);
    }
}
