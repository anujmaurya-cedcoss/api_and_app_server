<?php
use Phalcon\Di;
use Phalcon\Di\FactoryDefault;

ini_set('display_errors',1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);

define('BASE_DIR', dirname(__DIR__));
define('APP_DIR', BASE_DIR . '/app');

set_include_path(
    ROOT_PATH . PATH_SEPARATOR . get_include_path()
);

// Required for phalcon/incubator
include __DIR__ . "/../vendor/autoload.php";

// Use the application autoloader to autoload the classes
// Autoload the dependencies found in composer
$loader = new \Phalcon\Loader();

$loader->registerDirs(
    array(
        ROOT_PATH
    )
);

$loader->registerNamespaces(array(
    'MyApp\Controllers' => __DIR__ . '/../app/controllers',
    'MyApp' => __DIR__ . '/../app'
));

$loader->register();
