<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/fastdlabs
 * @see      http://www.fastdlabs.com/
 */

namespace FastD\Sentinel;


use FastD\Swoole\Client as C;

/**
 * Class Client
 * @package FastD\Sentinel
 */
class Client
{
    protected $nodes = [];

    protected $node = [];

    public function __construct($service)
    {
        $serviceNodes = SentinelInterface::PATH.'/'.$service.'.json';
        if (!file_exists($serviceNodes)) {
            throw new \LogicException(sprintf('Service %s is unregisted', $service));
        }

        $this->nodes = json_decode(file_get_contents($serviceNodes), true);

        if (empty($this->nodes)) {
            throw new \LogicException(sprintf('Service %s is unavailable', $service));
        }

        $this->node = $this->nodes[mt_rand(0, count($this->nodes)-1)];
    }

    protected function createRequest($route, array $parameters = [])
    {

    }

    protected function send()
    {

    }

    /**
     * 串行
     *
     * @param $name
     * @param $parameters
     * @return mixed
     */
    public function call($name, array $parameters = [])
    {
        $client = new C($this->node['service_host']);

        if (!isset($this->node['routes'][$name])) {
            throw new \LogicException(sprintf('Service %s is unregisted', $name));
        }

        list($method, $path) = $this->node['routes'][$name];

        $client->setMethod($method);
        $client->setPath($path);

        return $client->send($parameters);
    }

    /**
     * 并行
     */
    public function selectCall()
    {

    }

    /**
     * 异步
     */
    public function asyncCall()
    {

    }
}