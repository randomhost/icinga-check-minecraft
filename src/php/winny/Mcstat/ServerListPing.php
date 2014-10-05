<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ServerListPing class definition
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
 * Example of how to get a Minecraft server's status using a "Server List Ping"
 * packet.
 *
 * See details here: http://www.wiki.vg/Server_List_Ping
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
class ServerListPing
{
    /**
     * Sends a ping to the given Minecraft server (versions 1.4.2 - 1.5.2).
     *
     * @param string $hostname Server hostname
     * @param int    $port     Query port
     * @param bool   $debug    Debug flag
     *
     * @return array
     * @throws \Exception
     */
    public static function ping($hostname, $port = 25565, $debug = false)
    {
        // 1. pack data to send
        $request = pack('nc', 0xfe01, 0xfa) .
            self::_packString('MC|PingHost') .
            pack('nc', 7 + 2 * strlen($hostname), 73) .
            self::_packString($hostname) .
            pack('N', 25565);

        // 2. open communication socket and make transaction
        $time = microtime(true);
        $fp = stream_socket_client(
            'tcp://' . $hostname . ':' . $port,
            $errorNumber,
            $errorMessage,
            Common::NETWORK_TIMEOUT
        );
        stream_set_timeout($fp, Common::NETWORK_TIMEOUT);
        if (!$fp) {
            throw new \Exception($errorMessage);
        }
        fwrite($fp, $request);
        $response = fread($fp, 2048);
        $socketInfo = stream_get_meta_data($fp);
        fclose($fp);
        if ($socketInfo['timed_out']) {
            throw new \Exception('Connection timed out');
        }
        $time = round((microtime(true) - $time) * 1000);

        // 3. unpack data and return
        if (strpos($response, 0xFF) !== 0) {
            throw new \Exception('Bad reply from server');
        }
        $responseData = substr($response, 3);
        $responseData = explode(pack('n', 0), $responseData);

        $stats = array(
            'player_count' => self::_decodeUTF16BE($responseData[4]),
            'player_max' => self::_decodeUTF16BE($responseData[5]),
            'motd' => self::_decodeUTF16BE($responseData[3]),
            'server_version' => self::_decodeUTF16BE($responseData[2]),
            'protocol_version' => self::_decodeUTF16BE($responseData[1]),
            'latency' => $time,
        );

        if ($debug) {
            $stats['debug'] = array(
                'request' => $request,
                'response' => $response,
            );
        }

        return $stats;
    }

    /**
     * Packs the given data into a binary string.
     *
     * @param string $string String data to be packed
     *
     * @return string
     */
    private static function _packString($string)
    {
        $letterCount = strlen($string);
        return pack('n', $letterCount) . mb_convert_encoding(
            $string, 'UTF-16BE'
        );
    }

    /**
     * Converts the given string from UTF-16BE to UTF-8.
     *
     * This is needed since UTF-16BE text rendered as UTF-8 contains unnecessary
     * null bytes which could cause unexpected behaviour of other components
     * such as string functions.
     *
     * @param string $string String to be decoded
     *
     * @return string
     */
    private static function _decodeUTF16BE($string)
    {
        return mb_convert_encoding($string, 'UTF-8', 'UTF-16BE');
    }

    /**
     * Sends a ping to the given Minecraft server (versions >= 1.7).
     *
     * @param string $hostname Server hostname
     * @param int    $port     Query port
     * @param bool   $debug    Debug flag
     *
     * @return array
     * @throws \Exception
     */
    public static function ping17($hostname, $port = 25565, $debug = false)
    {
        $handshakePacket = self::_packData(
            chr(0) .
            self::_packVarInt(4) .
            self::_packData($hostname) .
            pack('n', (int)$port) .
            self::_packVarInt(1)
        );
        $statusRequestPacket = self::_packData(chr(0));

        $time = microtime(true);
        $fp = stream_socket_client(
            'tcp://' . $hostname . ':' . $port,
            $errorNumber,
            $errorMessage,
            Common::NETWORK_TIMEOUT
        );
        stream_set_timeout($fp, Common::NETWORK_TIMEOUT);
        if (!$fp) {
            throw new \Exception($errorMessage);
        }

        fwrite($fp, $handshakePacket);
        fwrite($fp, $statusRequestPacket);

        $response = '';
        self::_unpackVarInt($fp, $response); // Length of packet
        $time = round((microtime(true) - $time) * 1000);
        self::_unpackVarInt($fp, $response); // Packet ID
        $jsonLength = self::_unpackVarInt($fp, $response);

        $jsonString = '';
        while (strlen($jsonString) < $jsonLength) {
            $chunk = fread($fp, 2048);
            $jsonString .= $chunk;
        }
        $response .= $jsonString;

        fclose($fp);
        $players = array();
        $json = json_decode($jsonString, true);
        if (isset($json['players']['sample'])) {
            foreach ($json['players']['sample'] as $player) {
                $players[] = $player['name'];
            }
        }

        $stats = array(
            'json' => $json,
            'latency' => $time,
            'server_version' => $json['version']['name'],
            'protocol_version' => $json['version']['protocol'],
            'player_count' => $json['players']['online'],
            'player_max' => $json['players']['max'],
            'motd' => $json['description'],
            'players' => $players,
        );

        if ($debug) {
            $stats['debug'] = array(
                'handshake' => $handshakePacket,
                'request' => $statusRequestPacket,
                'response' => $response,
            );
        }

        return $stats;
    }

    /**
     * Returns the packed version of the given data.
     *
     * @param string $data String data
     *
     * @return string
     */
    private static function _packData($data)
    {
        return self::_packVarInt(strlen($data)) . $data;
    }

    /**
     * Returns the packed version of the given integer.
     *
     * @param int $int Integer data
     *
     * @return string
     */
    private static function _packVarInt($int)
    {
        $varInt = '';
        while (true) {
            if (($int & 0xFFFFFF80) === 0) {
                $varInt .= chr($int);
                return $varInt;
            }
            $varInt .= chr($int & 0x7F | 0x80);
            $int >>= 7;
        }

        return $varInt;
    }

    /**
     * Reads data from the given stream resource and writes unpacked data to
     * $response.
     *
     * @param resource $fp        Stream resource
     * @param string   &$response Output string
     *
     * @return int
     * @throws \Exception
     */
    private static function _unpackVarInt($fp, &$response = null)
    {
        $int = 0;
        $pos = 0;
        while (true) {
            $chunk = fread($fp, 1);
            if ($response !== null) {
                $response .= $chunk;
            }
            $byte = ord($chunk);
            $int |= ($byte & 0x7F) << $pos++ * 7;
            if ($pos > 5) {
                throw new \Exception('VarInt too big');
            }
            if (($byte & 0x80) !== 128) {
                break;
            }
        }
        return $int;
    }
} 
