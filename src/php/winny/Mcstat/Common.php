<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Common class definition
 *
 * PHP version 5
 *
 * @category  Monitoring
 * @package   Mcstat
 * @author    Winston Weinert <WinstonOne@fastmail.fm>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2014 random-host.com
 * @license   http://opensource.org/licenses/mit-license.html The MIT License (MIT)
 * @link      https://pear.random-host.com/
 */
namespace winny\Mcstat;

/**
 * Common functionality shared between Mcstat classes
 *
 * @category  Monitoring
 * @package   Mcstat
 * @author    Winston Weinert <WinstonOne@fastmail.fm>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2014 random-host.com
 * @license   http://opensource.org/licenses/mit-license.html The MIT License (MIT)
 * @version   Release: @package_version@
 * @link      https://pear.random-host.com/
 */
class Common
{
    /**
     * Default network timeout
     *
     * @const int
     */
    const NETWORK_TIMEOUT = 5;

    /**
     * Throws an Exception if given data is not found within stream resource
     *
     * @param resource $fp     Stream resource
     * @param string   $string String to expect.
     *
     * @throws \Exception
     *
     * @return void
     */
    public static function expect($fp, $string)
    {
        $receivedString = '';
        for ($bytes = strlen($string), $cur = 0; $cur < $bytes; $cur++) {
            $receivedByte = fread($fp, 1);
            $expectedByte = $string[$cur];
            $receivedString .= $receivedByte;
            if ($receivedByte !== $expectedByte) {
                $errorMessage
                    = 'Expected ' . bin2hex($string) . ' but received ' .
                    bin2hex($receivedString);
                $errorMessage
                    .= ' problem byte: ' . bin2hex($receivedByte) .
                    ' (position ' . $cur . ')';
                throw new \Exception($errorMessage);
            }
        }
    }
} 
