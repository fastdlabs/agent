<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/fastdlabs
 * @see      http://www.fastdlabs.com/
 */

namespace FastD\Sentinel\Command;

use FastD\Sentinel\Agent;
use FastD\Sentinel\SentinelInterface;
use FastD\Utils\FileObject;
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
    const COMMAND_NAME = 'start';

    public function configure()
    {
        $this->setName(static::COMMAND_NAME);

        $this->addArgument('url', InputArgument::REQUIRED)
            ->addOption('daemon', '-d', InputOption::VALUE_OPTIONAL)
            ->addOption('path', '-p', InputOption::VALUE_OPTIONAL)
            ->addOption('list', '-l', InputOption::VALUE_OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('sentinel agent started');
        self::version($output);

        $this->agent($input, $output);
    }

    /**
     * @param OutputInterface $output
     */
    public static function version(OutputInterface $output)
    {
        $output->writeln('sentinel agent version: <info>' . Agent::AGENT_VERSION . '<info>');
    }

    /**
     * start agent
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function agent(InputInterface $input, OutputInterface $output)
    {
        $agent = new Agent($input);

        if ($input->hasParameterOption(['--path', '-p'])) {
            $path = $input->getOption('path');
        }else{
            $path = SentinelInterface::PATH;
        }
        $path = $path . '/' . Agent::PROCESS_NAME . '.pid';
        $file = new FileObject($path, 'rw+');
        $file->ftruncate(0);
        $file->fwrite($agent->start());

        $agent->wait(function ($ret) use ($output,$path) {
            if (file_exists($path)) {
                @unlink($path);
            }
            $output->writeln(sprintf('sentinel agent is exists. pid: %s exit. code: %s. signal: %s', $ret['pid'], $ret['code'], $ret['signal']));
        });
    }
}
