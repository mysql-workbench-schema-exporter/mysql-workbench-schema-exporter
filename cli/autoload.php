<?php
// enable autoloading of classes
$autoloadFile = null;
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	$autoloadFile = __DIR__ . '/../vendor/autoload.php';
}elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
	$autoloadFile = __DIR__ . '/../../../autoload.php';
}

if (null === $autoloadFile) {
	throw new Exception('Unable to load the composer autoload file, have you ran "composer update"');
}
require_once $autoloadFile;
