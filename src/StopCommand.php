<?php /** @noinspection ALL */

/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/19
 */

namespace FastD\Sentinel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StopCommand
 * @package FastD\Sentinel
 */
class StopCommand extends Command
{
    const COMMAND_NAME = 'stop';

    public function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->addOption('path', '-p', InputOption::VALUE_OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption(['--path', '-p'])) {
            $path = $input->getOption('path');
        } else {
            $path = SentinelInterface::PATH;
        }

        $pid = (int)@file_get_contents($path);
        if (process_kill($pid, SIGTERM)) {
            unlink($path);
        }

        $output->writeln("<info></info>");
    }
}
