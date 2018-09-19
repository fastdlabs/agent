<?php /** @noinspection ALL */

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
    protected $send_count = 0;
    protected $max_try_count = 10;

    protected $nodes = [];

    protected $path;

    protected $list;

    /**
     * Subscription constructor.
     * @param $uri
     */
    public function __construct($uri, $path = null, $list = false)
    {
        parent::__construct($uri, true);

        $this->list = $list;
        $this->path = is_null($path) ? SentinelInterface::PATH : $path;
    }

    public function tryReconnect()
    {
        if ($this->try_count <= $this->max_try_count) {
            echo 'try connecting: ' . $this->try_count . PHP_EOL;
            $this->connect();
            $this->try_count++;

            // 重连时间递增
            sleep($this->try_count * 2 - 1);
        }
    }

    /**
     * 接受两种情况，一种是全量，一种是单节
     *      在首次启动agent的时候会接受全量数据
     *      在接受更新同步的时候会单节点接收消息
     *
     * @param swoole_client $client
     * @param string $data
     * @return mixed|void
     * @throws \FastD\Packet\Exceptions\PacketException
     */
    public function onReceive(swoole_client $client, $data)
    {
        $data = json_decode($data, true);

        if (!empty($data)) {
            foreach ($data as $name => $nodes) {
                echo $name, PHP_EOL, PHP_EOL;
                if (empty($nodes)) {
                    try {
                        unlink($this->path . '/' . $name . '.php');
                        continue;
                    } catch (\Exception $exception) {
                        echo "unlink $name failed:{$exception->getMessage()}";
                    }
                }

                is_null($this->nodes) && $this->setNode($name, $nodes);

                $file = new FileObject($this->path . '/' . $name . '.php', 'rw+');
                $file->ftruncate(0);
                $file->fwrite('<?php return ' . var_export($nodes, true) . ';');
            }
        }
    }

    /**
     * @param swoole_client $client
     */
    public function onError(swoole_client $client)
    {
        $this->tryReconnect();
    }

    /**
     * @param swoole_client $client
     * @throws \FastD\Packet\Exceptions\PacketException
     */
    public function onConnect(swoole_client $client)
    {
        $client->send(Json::encode([
            'method' => 'GET',
            'path' => '/services',
        ]));

        $this->try_count = 0;

        sleep(5);
        timer_tick(5000, function ($id) use ($client) {
            try {
                if (!$this->client->isConnected()) {
                    echo 'unconnected', PHP_EOL;
                    timer_clear($id);
                } elseif (false === $client->send(Json::encode([
                        'method' => 'HEAD',
                        'path' => '/heart-beats',
                    ]))) {
                    echo 'error', PHP_EOL;
                    timer_clear($id);
                }
            } catch (\Exception $exception) {
                echo $exception->getMessage(), PHP_EOL, '异常', PHP_EOL;
                timer_clear($id);
            }
        });
    }

    /**
     * @param swoole_client $client
     */
    public function onClose(swoole_client $client)
    {
        $this->tryReconnect();
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param array $node
     */
    public function setNode($serverName, $node)
    {
        $this->nodes[$serverName] = $node;
        if ($this->list) {
            echo $serverName . ' : ' . count($node ?? []) . PHP_EOL;
        }
    }
}
