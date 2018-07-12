<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/fastdlabs
 * @see      http://www.fastdlabs.com/
 */

$phar = new Phar('sentinel.phar');

$phar->buildFromDirectory(__DIR__.'/', '/\.php$/');
$phar->compressFiles(Phar::GZ);
$phar->stopBuffering();
$phar->setStub(
    $phar->createDefaultStub('sentinel.php')
);
