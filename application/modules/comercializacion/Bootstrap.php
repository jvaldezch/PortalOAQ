<?php

class Comercializacion_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function _initAutoLoader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            "namespace" => "Comercializacion_",
            "basePath" => APPLICATION_PATH . "/modules/comercializacion",
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

}
