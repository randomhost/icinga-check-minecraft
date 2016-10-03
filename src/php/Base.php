<?php
namespace randomhost\Icinga\Check\Minecraft;

use randomhost\Icinga\Check\Base as CheckBase;
use randomhost\Minecraft\Status;

/**
 * Base class for Minecraft Icinga check plugins.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://github.random-host.com/icinga-check-minecraft/
 */
abstract class Base extends CheckBase
{
    /**
     * Instance of \randomhost\Minecraft\Status.
     *
     * @var Status
     */
    protected $mcStatus = null;

    /**
     * Constructor.
     *
     * @param Status $mcStatus \randomhost\Minecraft\Status instance.
     */
    public function __construct(Status $mcStatus)
    {
        $this->mcStatus = $mcStatus;

        $this->setLongOptions(
            array(
                'host:',
                'port:',
                'thresholdWarning:',
                'thresholdCritical:'
            )
        );

        $this->setRequiredOptions(
            array(
                'host',
                'port',
                'thresholdWarning',
                'thresholdCritical'
            )
        );

        $this->setHelp(
            <<<EOT
Icinga plugin for checking Minecraft services.

--host              Minecraft server IP address or hostname
--port              Query port
--thresholdWarning  Threshold to trigger the WARNING state
--thresholdCritical Threshold to trigger the CRITICAL state
EOT
        );
    }

    /**
     * Reads command line options and performs pre-run tasks.
     *
     * @return $this
     */
    protected function preRun()
    {
        parent::preRun();

        $options = $this->getOptions();

        $this->mcStatus
            ->setHostname($options['host'])
            ->setPort($options['port']);

        return $this;
    }
}
