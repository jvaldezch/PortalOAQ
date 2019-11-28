<?php

class Zend_View_Helper_Uuid extends Zend_View_Helper_Abstract {

    protected $_config;

    public function uuid($key) {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);

        require_once 'UUID.php';
        return UUID::v5($this->_config->app->uuid, $key);
    }

}
