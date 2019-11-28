<?php

/**
 * Description of Gearman_Worker
 * http://blog.digitalstruct.com/2010/10/17/integrating-gearman-into-zend-framework/
 *
 */
class Gearman_Manager {

    private static $_gearmanServers = null;
    private static $_stdLogger = null;

    /**
     * Retrieves the current Gearman Servers
     *
     * @return array
     */
    public static function getServers() {
        if (self::$_gearmanServers === null) {
            $servers = "127.0.0.1:4730";
            self::$_gearmanServers = $servers;
        }
        return self::$_gearmanServers;
    }

    /**
     * Creates a GearmanClient instance and sets the job servers
     *
     * @return GearmanClient
     */
    public static function getClient() {
        $gmclient = new GearmanClient();
        $servers = self::getServers();
        $gmclient->addServers($servers);
        return $gmclient;
    }

    /**
     * Creates a GearmanWorker instance
     *
     * @return GearmanWorker
     */
    public static function getWorker() {
        $worker = new GearmanWorker();
        $servers = self::getServers();
        $worker->addServers($servers);
        return $worker;
    }

    /**
     * Given a worker name, it checks if it can be loaded. If it's possible,
     * it creates and returns a new instance.
     *
     * @param string $workerName
     * @param string $logFile
     * @return Model_Gearman_Worker
     */
    public static function runWorker($workerName, $logFile = null) {
        $workerName .= 'Worker';
        $workerFile = APPLICATION_PATH . '/workers/' . $workerName . '.php';
        if (!file_exists($workerFile)) {
            throw new InvalidArgumentException(
                "El Worker no existe: {$workerFile}"
            );
        }
        require $workerFile;
        if (!class_exists($workerName)) {
            throw new InvalidArgumentException(
                "La clase {$workerName} no existe en el archivo: {$workerFile}"
            );
        }
        return new $workerName($logFile);
    }

    public static function getLogger($logFile = 'php://output') {
        if (self::$_stdLogger === null) {
            self::$_stdLogger = new Zend_Log();
            $writer = new Zend_Log_Writer_Stream($logFile);
            $filter = new Zend_Log_Filter_Priority(Zend_Log::DEBUG);
            $writer->addFilter($filter);
            self::$_stdLogger->addWriter($writer);
        }
        return self::$_stdLogger;
    }

}
