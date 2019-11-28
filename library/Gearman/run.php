<?php
error_reporting(E_ALL);
 
/* Inicializar el proceso de bootstrap
 * Nota: Esto variará en función de vuestro entorno por lo que se
 * oculta en otro fichero al no ser relevante en el ejemplo
 */

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
 
if (count($argv) > 1) { 
    $workerName = $argv[1]; 
    define('APPLICATION_ENVIRONMENT', (!empty($argv[2])) ? $argv[2] : 'DEVEL'); 
    $logFile = (!empty($argv[3])) ? $argv[3] : null; 
// Params error
} else {
    $message = "
        Usage: php run.php WORKER_NAME [ENVIRONMENT] [LOG_FILE] \n
        Example: php runWorker topsearches PRODUCTION \tmp\gearman.log \n
        Default Environment: DEVEL  \n
    ";
    die($message);
} 
// Instantiates a new worker
Gearman_Manager::runWorker($workerName, $logFile);