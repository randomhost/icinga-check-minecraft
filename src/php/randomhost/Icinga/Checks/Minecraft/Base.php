<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Minecraft class definition
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

use randomhost\Icinga\Checks\Base as CheckBase;
use winny\Mcstat\Status;

/**
 * Base class for Minecraft Icinga plugins
 *
 * @category  Monitoring
 * @package   PHP_Icinga_Minecraft
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2014 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @version   Release: @package_version@
 * @link      https://pear.random-host.com/
 */
abstract class Base extends CheckBase
{
    /**
     * Instance of winny\Mcstat\Status class
     *
     * @var Status
     */
    protected $mcStatus = null;

    /**
     * Constructor for this class.
     */
    public function __construct()
    {

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
     * @return void
     */
    protected function preRun()
    {
        parent::preRun();

        $options = $this->getOptions();

        // load Mcstat Status class
        $this->mcStatus = new Status(
            $options['host'],
            $options['port']
        );
    }
} 
