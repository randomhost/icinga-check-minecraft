<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Format class definition
 *
 * PHP version 5
 *
 * @category  Monitoring
 * @package   Mcstat
 * @author    Anders G. Jørgensen <anders@spirit55555.dk>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2014 random-host.com
 * @license   http://www.gnu.org/licenses/ GNU General Public License
 * @link      https://pear.random-host.com/
 */
namespace winny\Mcstat;

/**
 * Provides formatting for strings returned by the Minecraft server
 *
 * @category  Monitoring
 * @package   Mcstat
 * @author    Anders G. Jørgensen <anders@spirit55555.dk>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2014 random-host.com
 * @license   http://www.gnu.org/licenses/ GNU General Public License
 * @version   Release: @package_version@
 * @link      https://pear.random-host.com/
 */
class Format
{
    const REGEX = '/§([0-9a-fklmnor])/i';
    const START_TAG = '<span style="%s">';
    const CLOSE_TAG = '</span>';
    const CSS_COLOR = 'color: #';
    const EMPTY_TAGS = '/<[^\/>]*>([\s]?)*<\/[^>]*>/';

    /**
     * Color mappings
     * 
     * @var array
     */
    private static $_colors
        = array(
            '0' => '000000', // black
            '1' => '0000AA', // dark blue
            '2' => '00AA00', // dark green
            '3' => '00AAAA', // dark aqua
            '4' => 'AA0000', // dark red
            '5' => 'AA00AA', // dark purple
            '6' => 'FFAA00', // gold
            '7' => 'AAAAAA', // gray
            '8' => '555555', // dark gray
            '9' => '5555FF', // blue
            'a' => '55FF55', // green
            'b' => '55FFFF', // aqua
            'c' => 'FF5555', // red
            'd' => 'FF55FF', // light purple
            'e' => 'FFFF55', // yellow
            'f' => 'FFFFFF'  // white
        );

    /**
     * Formatting mappings
     * 
     * @var array
     */
    private static $_formatting
        = array(
            'k' => '', // obfuscated
            'l' => 'font-weight: bold;', // bold
            'm' => 'text-decoration: line-through;', // strike through
            'n' => 'text-decoration: underline;', // underline
            'o' => 'font-style: italic;', // italic
            'r' => '' // reset
        );

    /**
     * Returns $string with all Minecraft color codes removed
     *
     * @param string $string String containing Minecraft color codes
     *
     * @return mixed
     */
    public static function clean($string)
    {
        $string = self::_UFT8Encode($string);
        $string = htmlspecialchars($string);
        return preg_replace(self::REGEX, '', $string);
    }

    /**
     * Encodes $string in UTF-8 if it's not already encoded
     *
     * @param string $string Input string
     *
     * @return string
     */
    private static function _UFT8Encode($string)
    {
        if (mb_detect_encoding($string) != 'UTF-8') {
            $string = utf8_encode($string);
        }
        return $string;
    }

    /**
     * Converts Minecraft color codes to HTML
     *
     * @param string $text String containing Minecraft color codes
     *
     * @return string
     */
    public static function convertToHTML($text)
    {
        $text = self::_UFT8Encode($text);
        $text = htmlspecialchars($text);
        preg_match_all(self::REGEX, $text, $offsets);
        $colors = $offsets[0]; // this is what we are going to replace with HTML
        $color_codes = $offsets[1]; // this is the color numbers/characters only
        // No colors? Just return the text.
        if (empty($colors)) {
            return $text;
        }
        $open_tags = 0;
        foreach ($colors as $index => $color) {
            $color_code = strtolower($color_codes[$index]);

            if (isset(self::$_colors[$color_code])) {
                // We have a normal color
                $html = sprintf(
                    self::START_TAG,
                    self::CSS_COLOR . self::$_colors[$color_code]
                );
                // New color clears the other colors and formatting
                if ($open_tags != 0) {
                    $html = str_repeat(self::CLOSE_TAG, $open_tags) . $html;
                    $open_tags = 0;
                }
                $open_tags++;
            } else {
                // We have some formatting
                switch ($color_code) {
                    // Reset is special, just close all open tags
                case 'r':
                    $html = '';
                    if ($open_tags != 0) {
                        $html = str_repeat(self::CLOSE_TAG, $open_tags);
                        $open_tags = 0;
                    }
                    break;
                    // Can't do obfuscated in CSS
                case 'k':
                    $html = '';
                    break;
                default:
                    $html = sprintf(
                        self::START_TAG, self::$_formatting[$color_code]
                    );
                    $open_tags++;
                    break;
                }
            }
            /*
             * Replace the color with the HTML code. We use preg_replace because
             * of the limit parameter.
             */
            $text = preg_replace('/' . $color . '/', $html, $text, 1);
        }
        // Still open tags? Close them!
        if ($open_tags != 0) {
            $text = $text . str_repeat(self::CLOSE_TAG, $open_tags);
        }
        /*
         * Return the text without empty HTML tags. Only to clean up bad color
         * formatting from the user.
         */
        return preg_replace(self::EMPTY_TAGS, '', $text);
    }
} 
