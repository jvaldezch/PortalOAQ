<?php

class Clientes_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function _initAutoLoader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Clientes_',
            'basePath' => APPLICATION_PATH . '/modules/clientes',
            'resourceTypes' => array(
                'form' => array(
                    'path' => 'forms',
                    'namespace' => 'Form',
                ),
                'model' => array(
                    'path' => 'models',
                    'namespace' => 'Model',
                ),
            )
        ));
        return $autoloader;
    }

}
