#!/usr/bin/env php
<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      https://fastdlabs.com
 */

use FastD\Sentinel\SentinelCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

set_time_limit(0);
date_default_timezone_set('PRC');

// autoload composer
foreach ([
             __DIR__ . '/../../../autoload.php',
             __DIR__ . '/../../vendor/autoload.php',
             __DIR__ . '/../vendor/autoload.php',
             __DIR__ . '/vendor/autoload.php',
         ] as $value) {
    if (file_exists($value)) {
        define('COMPOSER_INSTALL', $value);
        break;
    }
}

if (!defined('COMPOSER_INSTALL')) {
    fwrite(STDERR,
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'wget http://getcomposer.org/composer.phar' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

include COMPOSER_INSTALL;

class App extends Application
{
    public function __construct()
    {
        parent::__construct('sentinel agent', '1.0.0');
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $argv = $_SERVER['argv'];

        $script = array_shift($argv);

        array_unshift($argv, SentinelCommand::COMMAND_NAME);
        array_unshift($argv, $script);

        return parent::run(new ArgvInput($argv), $output);
    }
}

$app = new App();

$app->add(new SentinelCommand());

$app->run();
