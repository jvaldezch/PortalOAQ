<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initDefaultModule() {
        $this->bootstrap("FrontController");
        require_once APPLICATION_PATH . "/modules/default/Bootstrap.php";
        $defaultBootstrap = new Default_Bootstrap($this);
        $defaultBootstrap->bootstrap();
        return $defaultBootstrap;
    }

    public function _initPagination() {
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(array("pagination-traffic.phtml", "default"));
    }

    public function _initDatabase() {
        $resource = $this->getPluginResource("multidb");
        $resource->init();
        Zend_Registry::set("oaqintranet", $resource->getDb("oaqintranet"));
    }

    protected function _initAutoload() {
        $autoLoader = Zend_Loader_Autoloader::getInstance();
        $autoLoader->registerNamespace("Plugin_");
        return $autoLoader;
    }

    protected function _initLogger() {
        require_once 'FirePHPCore/fb.php';
        $firephp = FirePHP::getInstance(true);
        Zend_Registry::set("firephp", $firephp);
    }
    
    protected function _initLogMapper() {
        $mapper = new Application_Model_LogMapper();
        Zend_Registry::set("logDb", $mapper);
    }

}
