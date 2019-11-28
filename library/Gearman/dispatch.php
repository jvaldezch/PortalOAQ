<?php

/**
 * Dispatch a Gearman Worker
 *
 * Handles the bootstrapping process and dispatches
 * a worker to handle the task at hand.
 *
 * @package    Gearman
 * @subpackage Application
 * @version    $Id$
 */
set_time_limit(0);

if (!isset($argv[1])) {
    throw new InvalidArgumentException('The worker name must be passed in as a parameter');
}
if (isset($argv[2])) {
    define('APPLICATION_ENV', $argv[2]);
} else {
    define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
}

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

echo "\n\n" . realpath(dirname(__FILE__) . '/../../application') . "\n\n";

//require_once 'Zend/Application.php';
//$app = new Zend_Application(
//    APPLICATION_ENV,
//    APPLICATION_PATH . '/configs/application.ini'
//);