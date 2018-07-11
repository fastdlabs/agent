<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/fastdlabs
 * @see      http://www.fastdlabs.com/
 */

namespace FastD\Sentinel;


use FastD\Packet\Json;
use FastD\Swoole\Client;
use FastD\Utils\FileObject;
use swoole_client;

/**
 * Class Subscription
 * @package FastD\Sentinel
 */
class Subscription extends Client
{
    protected $try_count = 0;
    protected $max_try_count = 10;

    /**
     * Subscription constructor.
     * @param $uri
     */
    public function __construct($uri)
    {
        parent::__construct($uri, true);
    }

    public function tryReconnect()
    {
        if ($this->try_count <= $this->max_try_count) {
            echo 'try connecting: ' . $this->try_count.PHP_EOL;
            $this->connect();
            $this->try_count++;
            sleep(1);
        }
    }

    /**
     * @param swoole_client $client
     * @param string $data
     * @return mixed|void
     * @throws \FastD\Packet\Exceptions\PacketException
     */
    public function onReceive(swoole_client $client, $data)
    {
        $data = Json::decode($data);
        $nodes = json_encode($data['list'], JSON_PRETTY_PRINT);
        $file = new FileObject(SentinelInterface::PATH.'/'.$data['service'].'.json', 'rw+');
        $file->fwrite($nodes);
        echo "接收信息: ".$nodes.PHP_EOL;
    }

    /**
     * @param swoole_client $client
     * @return mixed
     */
    public function onError(swoole_client $client)
    {
        $this->tryReconnect();
    }

    /**
     * @param swoole_client $client
     * @return mixed
     */
    public function onConnect(swoole_client $client)
    {
        $client->send('agent');
        $this->try_count = 0;
    }

    /**
     * @param swoole_client $client
     * @return mixed
     */
    public function onClose(swoole_client $client)
    {
        $this->tryReconnect();
    }
}