<?php



if (file_exists(__DIR__.'/../../../../../vendor/autoload.php')) {
    // dependencies were installed via composer - this is the main project
    $classLoader = require __DIR__.'/../../../../../vendor/autoload.php';
} else {
    throw new Exception('Can\'t find autoload.php. Did you install dependencies via Composer?');
}

/* @var $classLoader \Composer\Autoload\ClassLoader */
$classLoader->add('Pixidos\\GPWebPay\\', __DIR__ . '/../src/');
//unset($classLoader);
// configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

// create temporary directory
define('TEMP_DIR', __DIR__.'/tmp/test'.getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
\Tester\Helpers::purge(TEMP_DIR);

$_SERVER = array_intersect_key($_SERVER, array_flip(array(
    'PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS', 'argc', 'argv')));
$_SERVER['REQUEST_TIME'] = 1234567890;
$_ENV = $_GET = $_POST = array();

function id($val) {
    return $val;
}

function run(Tester\TestCase $testCase) {
    $testCase->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);
}