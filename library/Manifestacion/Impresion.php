<?php

class Manifestacion_Impresion
{
    protected $_config;
    protected $_firephp;

    public function __construct(array $options = null)
    {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_firephp = Zend_Registry::get("firephp");
    }
}
