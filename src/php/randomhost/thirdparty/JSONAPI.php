<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * JSONAPI class definition
 *
 * PHP version 5
 *
 * @category Monitoring
 * @package  JSONAPI
 * @author   Alec Gorge <alecgorge@gmail.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://github.com/alecgorge/JSONAPI
 */
namespace randomhost\thirdparty;

/**
 * A PHP class for accessing Minecraft servers which run Bukkit with the
 * {@link http://github.com/alecgorge/JSONAPI JSONAPI} plugin installed.
 *
 * This class handles everything from key creation to URL creation to actually
 * returning the decoded JSON as an associative array.
 *
 * @category Monitoring
 * @package  JSONAPI
 * @author   Alec Gorge <alecgorge@gmail.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://github.com/alecgorge/JSONAPI
 */
class JSONAPI
{
    /**
     * JSONAPI host
     * 
     * @var string
     */
    public $host;

    /**
     * JSONAPI port
     * 
     * @var int
     */
    public $port;

    /**
     * JSONAPI salt
     * 
     * @var string
     */
    public $salt;

    /**
     * JSONAPI username
     *
     * @var string
     */
    public $username;

    /**
     * JSONAPI password
     *
     * @var string
     */
    public $password;

    /**
     * @var array
     */
    private $_urlFormats = array(
        "call" => "http://%s:%s/api/call?method=%s&args=%s&key=%s",
        "callMultiple" => "http://%s:%s/api/call-multiple?method=%s&args=%s&key=%s"
    );

    /**
     * Creates a new JSONAPI instance.
     *
     * @param string $host     JSONAPI host
     * @param int    $port     JSONAPI port
     * @param string $username JSONAPI user name
     * @param string $password JSONAPI password
     * @param string $salt     JSONAPI salt
     *
     * @throws \Exception
     */
    public function __construct($host, $port, $username, $password, $salt)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;

        if (!extension_loaded("cURL")) {
            throw new \Exception(
                "JSONAPI requires cURL extension in order to work."
            );
        }
    }

    /**
     * Generates the proper SHA256 based key from the given method suitable for
     * use as the key GET parameter in a JSONAPI API call.
     *
     * @param string $method The name of the JSONAPI API method to generate the
     *                       key for.
     *
     * @return string The SHA256 key suitable for use as the key GET parameter
     * in a JSONAPI API call.
     */
    public function createKey($method)
    {
        if (is_array($method)) {
            $method = json_encode($method);
        }
        return hash(
            'sha256', $this->username . $method . $this->password . $this->salt
        );
    }

    /**
     * Generates the proper URL for a standard API call the given method and
     * arguments.
     *
     * @param string $method The name of the JSONAPI API method to generate the
     *                       URL for.
     * @param array  $args   An array of arguments that are to be passed in the
     *                       URL.
     *
     * @return string A proper standard JSONAPI API call URL.
     */
    public function makeURL($method, array $args)
    {
        return sprintf(
            $this->_urlFormats["call"], $this->host, $this->port,
            rawurlencode($method), rawurlencode(json_encode($args)),
            $this->createKey($method)
        );
    }

    /**
     * Generates the proper URL for a multiple API call the given method and
     * arguments.
     *
     * @param array $methods An array of strings, where each string is the name
     *                       of the JSONAPI API method to generate the URL for.
     * @param array $args    An array of arrays, where each array contains the
     *                       arguments that are to be passed in the URL.
     *
     * @return string A proper multiple JSONAPI API call URL.
     */
    public function makeURLMultiple(array $methods, array $args)
    {
        return sprintf(
            $this->_urlFormats["callMultiple"], $this->host, $this->port,
            rawurlencode(json_encode($methods)),
            rawurlencode(json_encode($args)), $this->createKey($methods)
        );
    }

    /**
     * Calls the single given JSONAPI API method with the given args.
     *
     * @param string $method The name of the JSONAPI API method to call.
     * @param array  $args   An array of arguments that are to be passed.
     *
     * @return array An associative array representing the JSON that was returned.
     */
    public function call($method, array $args = array())
    {
        if (is_array($method)) {
            return $this->callMultiple($method, $args);
        }

        $url = $this->makeURL($method, $args);

        return json_decode($this->_curl($url), true);
    }

    /**
     * Calls the given JSONAPI API methods with the given args.
     *
     * @param array $methods An array strings, where each string is the name of
     *                       a JSONAPI API method to call.
     * @param array $args    An array of arrays of arguments that are to be passed.
     *
     * @throws \Exception When the length of the $methods array and the $args
     *                    array are different, an exception is thrown.
     *
     * @return array An array of associative arrays representing the JSON that
     *               was returned.
     */
    public function callMultiple(array $methods, array $args = array())
    {
        if (count($methods) !== count($args)) {
            throw new \Exception(
                "The length of the arrays \$methods and \$args are different! ".
                "You need an array of arguments for each method!"
            );
        }

        $url = $this->makeURLMultiple($methods, $args);

        return json_decode($this->_curl($url), true);
    }

    /**
     * Performs a cURL call.
     * 
     * @param string $url cURL URL
     *
     * @return mixed
     */
    private function _curl($url)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_PORT, $this->port);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($c);
        curl_close($c);
        return $result;
    }
}
