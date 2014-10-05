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
--port              Query port
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
            $response = $this->mcStatus->query(true);

            if (!is_array($response)
                || !array_key_exists('player_count', $response)
            ) {
                $this->setMessage('No response from Minecraft server query.');
                $this->setCode(self::STATE_UNKNOWN);
            } elseif ($response['player_count'] >= (int)$options['thresholdWarning']
            ) {
                $this->setMessage(
                    sprintf(
                        'CRITICAL - %1$u players currently logged in|users=%1$u',
                        $response['success']
                    )
                );
                $this->setCode(self::STATE_CRITICAL);
            } elseif ($response['player_count'] >= (int)$options['thresholdWarning']
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
                        $response['player_count']
                    )
                );
                $this->setCode(self::STATE_OK);
            }
        } catch (\Exception $e) {
            $this->setMessage('Error from Mcstat: ' . $e->getMessage());
            $this->setCode(self::STATE_UNKNOWN);
        }
    }
} 
