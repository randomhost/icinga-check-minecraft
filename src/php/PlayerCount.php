<?php
namespace randomhost\Icinga\Check\Minecraft;

use randomhost\Minecraft\Status;

/**
 * Checks the player count of the Minecraft server.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://github.random-host.com/icinga-check-minecraft/
 */
class PlayerCount extends Base
{
    /**
     * Constructor.
     *
     * @param Status $mcStatus \randomhost\Minecraft\Status instance.
     */
    public function __construct(Status $mcStatus)
    {
        parent::__construct($mcStatus);

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
     * @return $this
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

                return $this;
            }

            $playerCount = $response['player_count'];

            if ($playerCount >= (int)$options['thresholdCritical']) {
                $this->setMessage(
                    sprintf(
                        'CRITICAL - %1$u players currently logged in|users=%1$u',
                        $response['player_count']
                    )
                );
                $this->setCode(self::STATE_CRITICAL);

                return $this;
            }

            if ($playerCount >= (int)$options['thresholdWarning']) {
                $this->setMessage(
                    sprintf(
                        'WARNING - %u players currently logged in|users=%1$u',
                        $response['player_count']
                    )
                );
                $this->setCode(self::STATE_WARNING);

                return $this;
            }

            $this->setMessage(
                sprintf(
                    'OK - %u players currently logged in|users=%1$u',
                    $playerCount
                )
            );
            $this->setCode(self::STATE_OK);

            return $this;

        } catch (\Exception $e) {
            $this->setMessage('Error from Mcstat: ' . $e->getMessage());
            $this->setCode(self::STATE_UNKNOWN);

            return $this;
        }
    }
}
