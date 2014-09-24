<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PlayerCount class definition
 *
 * PHP version 5
 *
 * @category  Monitoring
 * @package   PHP_Icinga_Minecraft
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2014 random-host.com
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://pear.random-host.com/
 */
namespace randomhost\Icinga\Checks\Minecraft;

/**
 * Checks the player count of the Minecraft server
 *
 * @category  Monitoring
 * @package   PHP_Icinga_Minecraft
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2014 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @version   Release: @package_version@
 * @link      https://pear.random-host.com/
 */
class PlayerCount extends Base
{
    /**
     * Constructor for this class.
     */
    function __construct()
    {
        parent::__construct();

        $this->setHelp(
            <<<EOT
Icinga plugin for checking Minecraft services.

--host              Minecraft server IP address or hostname
--port              JSONAPI port
--user              JSONAPI user
--password          JSONAPI password
--salt              JSONAPI salt
--thresholdWarning  Player threshold to trigger the WARNING state
--thresholdCritical Player threshold to trigger the CRITICAL state
EOT
        );
    }

    /**
     * Check the amount of players on the Minecraft server.
     *
     * @see Base::check()
     *
     * @return void
     */
    protected function check()
    {
        try {
            $options = $this->getOptions();
            
            // retrieve player count
            $response = $this->jsonAPI->call('getPlayerCount');

            if ('success' !== $response['result'] || !isset($response['success'])) {
                $this->setMessage('No result from JSON API.');
                $this->setCode(self::STATE_UNKNOWN);
            } elseif ($response['success'] >= (int)$options['thresholdWarning']
            ) {
                $this->setMessage(
                    sprintf(
                        'CRITICAL - %1$u players currently logged in|users=%1$u',
                        $response['success']
                    )
                );
                $this->setCode(self::STATE_CRITICAL);
            } elseif ($response['success'] >= (int)$options['thresholdWarning']
            ) {
                $this->setMessage(
                    sprintf(
                        'WARNING - %u players currently logged in|users=%1$u',
                        $response['success']
                    )
                );
                $this->setCode(self::STATE_WARNING);
            } else {
                $this->setMessage(
                    sprintf(
                        'OK - %u players currently logged in|users=%1$u',
                        $response['success']
                    )
                );
                $this->setCode(self::STATE_OK);
            }
        } catch (\Exception $e) {
            $this->setMessage('Error from JSONAPI: ' . $e->getMessage());
            $this->setCode(self::STATE_UNKNOWN);
        }
    }
} 
