<?php

// check composer autoloader
$autoload = null;
foreach (array(
    '/../vendor/autoload.php',                      // autoloader for stand alone project
    '/../../../../vendor/autoload.php',             // autoloader for composer project
) as $autoloader) {
    $autoloaderPath = dirname(__FILE__).$autoloader;
    if (is_file($autoloaderPath)) {
        $autoload = $autoloaderPath;
        break;
    }
}
if (null === $autoload) {
    throw new Exception('Composer has not been setup properly, goto http://getcomposer.org/ for instruction.');
}

return require_once($autoload);
