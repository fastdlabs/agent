<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/fastdlabs
 * @see      http://www.fastdlabs.com/
 */

namespace FastD\Sentinel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SentinelAgent
 * @package FastD\Sentinel
 */
class SentinelCommand extends Command
{
    const COMMAND_NAME = 'sentinel';

    public function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this
            ->addArgument('url', InputArgument::REQUIRED)
            ->addOption('conf', '-c', InputOption::VALUE_OPTIONAL)
            ->addOption('daemon', '-d', InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('sentinel agent started');

        $agent = new Agent($input);
        $agent->start();
        $agent->wait(function ($ret) use ($output) {
            $output->writeln(sprintf('sentinel agent is exists. pid: %s exit. code: %s. signal: %s', $ret['pid'], $ret['code'], $ret['signal']));
        });
    }
}
