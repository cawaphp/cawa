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

namespace Cawa\Net;

use Cawa\Core\App;

abstract class Ip
{
    /**
     * @var string
     */
    private static $cachedIp;

    /**
     * @return string|null
     */
    public static function get()
    {
        if (self::$cachedIp) {
            return self::$cachedIp;
        }

        $headers = App::config()->getIfExists('ip/remoteAddressHeaders') ?: 'REMOTE_ADDR';

        if (!is_array($headers)) {
            $headers = [$headers];
        }

        foreach ($headers as $header) {
            $return = App::request()->getServer($header);
            if ($return) {
                self::$cachedIp = $return;

                return $return;
            }
        }

        return null;
    }

    /**
     * Control an ip
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isValid(string $ip = null)
    {
        if (is_null($ip)) {
            $ip = self::get();
        }

        return filter_var($ip, FILTER_VALIDATE_IP) == $ip;
    }

    /**
     * If ip is for local network
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isLocal(string $ip = null)
    {
        if (is_null($ip)) {
            $ip = self::get();
        }

        if (stripos($ip, '127.') !== false) {
            return true;
        }

        return !(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) == $ip);
    }

    /**
     * Return numeric ip value
     *
     * @param string $ip
     *
     * @return int
     */
    public static function toLong(string $ip = null)
    {
        if (is_null($ip)) {
            $ip = self::get();
        }

        return ip2long($ip);
    }

    /**
     * @param int $long
     *
     * @return string|null
     */
    public static function fromLong(int $long)
    {
        return long2ip($long);
    }

    /**
     * Network ranges can be specified as:
     * * Wildcard format: 1.2.3.*
     * * CIDR format: 1.2.3/24 or 1.2.3.4/255.255.255.0
     * * Start-End IP format: 1.2.3.0-1.2.3.255
     *
     * @param string $range
     * @param string $ip
     *
     * @return bool
     */
    public static function isInRange(string $range, string $ip) : bool
    {
        if (strpos($range, '/') !== false) {
            // $range is in IP/NETMASK format
            list($range, $netmask) = explode('/', $range, 2);
            if (strpos($netmask, '.') !== false) {
                // $netmask is a 255.255.0.0 format
                $netmask = str_replace('*', '0', $netmask);
                $netmask_dec = ip2long($netmask);

                return ((ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec));
            } else {
                // $netmask is a CIDR size block
                // fix the range argument
                $x = explode('.', $range);
                while (count($x) < 4) {
                    $x[] = '0';
                }
                list($a, $b, $c, $d) = $x;
                $range = sprintf(
                    '%u.%u.%u.%u',
                    empty($a) ? '0' : $a,
                    empty($b) ? '0' : $b,
                    empty($c) ? '0' : $c,
                    empty($d) ? '0' : $d
                );
                $range_dec = ip2long($range);
                $ip_dec = ip2long($ip);

                # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
                #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

                # Strategy 2 - Use math to create it
                $wildcard_dec = pow(2, (32 - $netmask)) - 1;
                $netmask_dec = ~$wildcard_dec;

                return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
            }
        } else {
            // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
            if (strpos($range, '*') !== false) { // a.b.*.* format
                // Just convert to A-B format by setting * to 0 for A and 255 for B
                $lower = str_replace('*', '0', $range);
                $upper = str_replace('*', '255', $range);
                $range = "$lower-$upper";
            }

            if (strpos($range, '-') !== false) { // A-B format
                list($lower, $upper) = explode('-', $range, 2);
                $lower_dec = (float) sprintf('%u', ip2long($lower));
                $upper_dec = (float) sprintf('%u', ip2long($upper));
                $ip_dec = (float) sprintf('%u', ip2long($ip));

                return (($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec));
            }

            throw new \InvalidArgumentException(
                sprintf("Invalid range '%s', not in format 1.2.3.4/24 or 1.2.3.4/255.255.255.0", $range)
            );
        }
    }
}
