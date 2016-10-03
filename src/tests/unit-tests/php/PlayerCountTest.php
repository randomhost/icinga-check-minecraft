<?php
namespace randomhost\Icinga\Check\Minecraft;

use randomhost\Icinga\Plugin;
use randomhost\Minecraft\Status;
use RuntimeException;

/**
 * Unit test for PlayerCount.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://github.random-host.com/icinga-check-minecraft/
 */
class PlayerCountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests PlayerCount::run() without required parameters.
     */
    public function testRunWithoutParametersReturnsError()
    {
        $status = $this->getMinecraftStatusMock('127.0.0.1', 9876);

        $playerCount = new PlayerCount($status);

        $this->assertSame(
            $playerCount,
            $playerCount->run()
        );

        $this->assertEquals(
            Plugin::STATE_UNKNOWN,
            $playerCount->getCode()
        );

        $this->assertEquals(
            'Missing required parameters: host, port, thresholdWarning, thresholdCritical',
            $playerCount->getMessage()
        );
    }

    /**
     * Tests PlayerCount::run() without required parameters.
     */
    public function testRunWithHelpParametersReturnsHelp()
    {
        $status = $this->getMinecraftStatusMock('127.0.0.1', 9876);

        $expectedOutput
            = <<<EOT
Icinga plugin for checking Minecraft services.

--host              Minecraft server IP address or hostname
--port              Query port
--thresholdWarning  Player threshold to trigger the WARNING state
--thresholdCritical Player threshold to trigger the CRITICAL state
EOT;
        $playerCount = new PlayerCount($status);

        $this->assertSame(
            $playerCount,
            $playerCount->setOptions(
                array(
                    'help' => ''
                )
            )
        );

        $this->assertSame(
            $playerCount,
            $playerCount->run()
        );

        $this->assertEquals(
            Plugin::STATE_UNKNOWN,
            $playerCount->getCode()
        );

        $this->assertEquals(
            $expectedOutput,
            $playerCount->getMessage()
        );
    }

    /**
     * Tests PlayerCount::run() with an empty response from the Minecraft server.
     */
    public function testRunWithEmptyResponse()
    {
        $status = $this->getMinecraftStatusMock('127.0.0.1', 9876);
        $status
            ->expects($this->atLeastOnce())
            ->method('query')
            ->with(true)
            ->will(
                $this->returnValue(
                    array()
                )
            );

        $playerCount = new PlayerCount($status);

        $playerCount->setOptions(
            array(
                'host' => '127.0.0.1',
                'port' => 9876,
                'thresholdWarning' => 5,
                'thresholdCritical' => 10
            )
        );

        $this->assertSame(
            $playerCount,
            $playerCount->run()
        );

        $this->assertEquals(
            Plugin::STATE_UNKNOWN,
            $playerCount->getCode()
        );

        $this->assertEquals(
            'No response from Minecraft server query.',
            $playerCount->getMessage()
        );
    }

    /**
     * Tests PlayerCount::run() with an Exception thrown by the Status object.
     */
    public function testRunWithException()
    {
        $status = $this->getMinecraftStatusMock('127.0.0.1', 9876);
        $status
            ->expects($this->atLeastOnce())
            ->method('query')
            ->with(true)
            ->will(
                $this->throwException(
                    new RuntimeException(
                        'Something went horribly wrong'
                    )
                )
            );

        $playerCount = new PlayerCount($status);

        $playerCount->setOptions(
            array(
                'host' => '127.0.0.1',
                'port' => 9876,
                'thresholdWarning' => 5,
                'thresholdCritical' => 10
            )
        );

        $this->assertSame(
            $playerCount,
            $playerCount->run()
        );

        $this->assertEquals(
            Plugin::STATE_UNKNOWN,
            $playerCount->getCode()
        );

        $this->assertEquals(
            'Error from Mcstat: Something went horribly wrong',
            $playerCount->getMessage()
        );
    }

    /**
     * Tests PlayerCount::run() with no players online.
     */
    public function testRunWithNoPlayers()
    {
        $status = $this->getMinecraftStatusMock('127.0.0.1', 9876);
        $status
            ->expects($this->atLeastOnce())
            ->method('query')
            ->with(true)
            ->will(
                $this->returnValue(
                    array(
                        'player_count' => 0
                    )
                )
            );

        $playerCount = new PlayerCount($status);

        $playerCount->setOptions(
            array(
                'host' => '127.0.0.1',
                'port' => 9876,
                'thresholdWarning' => 5,
                'thresholdCritical' => 10
            )
        );

        $this->assertSame(
            $playerCount,
            $playerCount->run()
        );

        $this->assertEquals(
            Plugin::STATE_OK,
            $playerCount->getCode()
        );

        $this->assertEquals(
            'OK - 0 players currently logged in|users=0',
            $playerCount->getMessage()
        );
    }

    /**
     * Tests PlayerCount::run() with a critical amount of players.
     */
    public function testRunWithCriticalAmountOfPlayers()
    {
        $status = $this->getMinecraftStatusMock('127.0.0.1', 9876);
        $status
            ->expects($this->atLeastOnce())
            ->method('query')
            ->with(true)
            ->will(
                $this->returnValue(
                    array(
                        'player_count' => 15
                    )
                )
            );

        $playerCount = new PlayerCount($status);

        $playerCount->setOptions(
            array(
                'host' => '127.0.0.1',
                'port' => 9876,
                'thresholdWarning' => 5,
                'thresholdCritical' => 10
            )
        );

        $this->assertSame(
            $playerCount,
            $playerCount->run()
        );

        $this->assertEquals(
            Plugin::STATE_CRITICAL,
            $playerCount->getCode()
        );

        $this->assertEquals(
            'CRITICAL - 15 players currently logged in|users=15',
            $playerCount->getMessage()
        );
    }

    /**
     * Tests PlayerCount::run() with a high amount of players.
     */
    public function testRunWithHighAmountOfPlayers()
    {
        $status = $this->getMinecraftStatusMock('127.0.0.1', 9876);
        $status
            ->expects($this->atLeastOnce())
            ->method('query')
            ->with(true)
            ->will(
                $this->returnValue(
                    array(
                        'player_count' => 6
                    )
                )
            );

        $playerCount = new PlayerCount($status);

        $playerCount->setOptions(
            array(
                'host' => '127.0.0.1',
                'port' => 9876,
                'thresholdWarning' => 5,
                'thresholdCritical' => 10
            )
        );

        $this->assertSame(
            $playerCount,
            $playerCount->run()
        );

        $this->assertEquals(
            Plugin::STATE_WARNING,
            $playerCount->getCode()
        );

        $this->assertEquals(
            'WARNING - 6 players currently logged in|users=6',
            $playerCount->getMessage()
        );
    }

    /**
     * Returns a mocked \randomhost\Minecraft\Status object.
     *
     * @param string $host Server host name.
     * @param int    $port Server port.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Status
     */
    protected function getMinecraftStatusMock($host, $port)
    {
        $status = $this
            ->getMockBuilder('randomhost\\Minecraft\\Status')
            ->setConstructorArgs(array($host, $port))
            ->setMethods(array('query'))
            ->getMock();

        return $status;
    }
}
