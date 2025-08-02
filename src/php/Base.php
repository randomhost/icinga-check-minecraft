<?php

namespace randomhost\Icinga\Check\Minecraft;

use randomhost\Icinga\Check\Base as CheckBase;
use randomhost\Minecraft\Status;

/**
 * Base class for Minecraft Icinga check plugins.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2025 Random-Host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD License (3 Clause)
 *
 * @see      https://github.random-host.tv
 */
abstract class Base extends CheckBase
{
    protected Status $mcStatus;

    /**
     * Constructor.
     */
    public function __construct(Status $mcStatus)
    {
        $this->mcStatus = $mcStatus;

        $this->setLongOptions(
            [
                'host:',
                'port:',
                'thresholdWarning:',
                'thresholdCritical:',
            ]
        );

        $this->setRequiredOptions(
            [
                'host',
                'port',
                'thresholdWarning',
                'thresholdCritical',
            ]
        );

        $this->setHelp(
            <<<'EOT'
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
     */
    protected function preRun(): self
    {
        parent::preRun();

        $options = $this->getOptions();

        $this->mcStatus
            ->setHostname($options['host'])
            ->setPort($options['port'])
        ;

        return $this;
    }
}
