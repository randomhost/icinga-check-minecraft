<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Status class definition
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
 * Provides status information about the given Minecraft server
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
class Status
{
    const SERVER_LIST_PING = 'Server List Ping';

    const SERVER_LIST_PING_1_7 = 'Server List Ping 1.7';

    const BASIC_QUERY = 'Basic Query';

    const FULL_QUERY = 'Full Query';

    /**
     * Server hostname
     *
     * @var string
     */
    public $hostname;

    /**
     * Server port
     *
     * @var int
     */
    public $port;

    /**
     * Last error
     *
     * @var null
     */
    public $lastError;

    /**
     * Server stats
     *
     * @var array
     */
    public $stats;

    /**
     * @var array
     */
    private $_methodTable;

    /**
     * Constructor
     *
     * @param string $hostname Server hostname
     * @param int    $port     Query port
     */
    function __construct($hostname, $port = 25565)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->lastError = null;
        $this->stats = array();
        $this->_methodTable = array(
            self::SERVER_LIST_PING => array(
                __NAMESPACE__ . '\ServerListPing', 'ping'
            ),
            self::SERVER_LIST_PING_1_7 => array(
                __NAMESPACE__ . '\ServerListPing', 'ping17'
            ),
            self::BASIC_QUERY => array(
                __NAMESPACE__ . '\Query', 'basicQuery'
            ),
            self::FULL_QUERY => array(
                __NAMESPACE__ . '\Query', 'fullQuery'
            ),
        );
    }

    /**
     * Returns the ping of the Minecraft server
     * 
     * @param bool $useLegacy Use legacy protocol (versions < 1.7)
     *
     * @return bool|mixed
     */
    public function ping($useLegacy = true)
    {
        if ($useLegacy) {
            return $this->_performStatusMethod(self::SERVER_LIST_PING);
        } else {
            return $this->_performStatusMethod(self::SERVER_LIST_PING_1_7);
        }
    }

    /**
     * Returns the output of the given status method
     *
     * @param string $statusMethodName Name of the status method
     *
     * @return bool|mixed
     */
    private function _performStatusMethod($statusMethodName)
    {
        $method = $this->_methodTable[$statusMethodName];
        $arguments = array($this->hostname, $this->port);
        try {
            $newStats = call_user_func_array($method, $arguments);
        } catch (\Exception $e) {
            $newStats = false;
            $this->lastError = $e->getMessage();
        }
        $this->stats[microtime()] = array(
            'stats' => $newStats,
            'method' => $statusMethodName,
            'hostname' => $this->hostname,
            'port' => $this->port,
        );

        return $newStats;
    }

    /**
     * Queries the status of the Minecraft server
     *
     * @param bool $fullQuery Use full query
     *
     * @return bool|mixed
     */
    public function query($fullQuery = true)
    {
        if ($fullQuery) {
            return $this->_performStatusMethod(self::FULL_QUERY);
        } else {
            return $this->_performStatusMethod(self::BASIC_QUERY);
        }
    }
}
