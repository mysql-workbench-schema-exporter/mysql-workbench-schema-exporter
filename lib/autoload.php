<?php

// check composer autoloader
if (!is_file($autoload = dirname(__FILE__).'/../vendor/autoload.php')) {
    throw new Exception('Composer has not been setup properly, goto http://getcomposer.org/ for instruction.');
}

return require_once($autoload);
