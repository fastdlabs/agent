<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/fastdlabs
 * @see      http://www.fastdlabs.com/
 */

namespace FastD\Sentinel;

use FastD\Swoole\Process;
use swoole_process;
use Symfony\Component\Console\Input\InputInterface;

class Agent extends Process
{
    const PROCESS_NAME = 'sentinel-agent';

    const AGENT_VERSION = '0.0.1-beta';

    protected $input;

    protected $url;

    protected $config;

    protected $path;

    protected $list = false;

    public function __construct(InputInterface $input, $redirect = false, $pipe = true)
    {
        $this->input = $input;

        parent::__construct(static::PROCESS_NAME, null, $redirect, $pipe);

        $this->bootstrap($input);
    }

    public function bootstrap(InputInterface $input)
    {
        if ($input->hasArgument('url')) {
            $this->url = $input->getArgument('url');
        }

        if ($input->hasParameterOption(['--list', '-l'])) {
            $this->list = true;
        }

        if ($input->hasParameterOption(['--path', '-p'])) {
            $this->path = $input->getOption('path');
        }

        if ($input->hasParameterOption(['--daemon', '-d'])) {
            $this->daemon();
        }
    }

    public function handle(swoole_process $swoole_process)
    {
        $subscript = new Subscription($this->url, $this->path, $this->list);

        $subscript->start();
    }
}
