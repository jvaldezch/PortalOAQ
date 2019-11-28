<?php

class Default_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function _initAutoLoader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            "namespace" => "Default_",
            "basePath" => APPLICATION_PATH . "/modules/default",
            "resourceTypes" => array(
                "form" => array(
                    "path" => "forms",
                    "namespace" => "Form",
                ),
                "model" => array(
                    "path" => "models",
                    "namespace" => "Model",
                ),
            )
        ));
        return $autoloader;
    }

    protected function _initFrontController() {
        $front = Zend_Controller_Front::getInstance();
        $front->addModuleDirectory(APPLICATION_PATH . "/modules");
        $front->setDefaultModule("default");
        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
            "module" => "default",
            "controller" => "error",
            "action" => "index"
        )));
        return $front;
    }

}
