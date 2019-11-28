<?php

class Zend_View_Helper_AppConfig extends Zend_View_Helper_Abstract
{
    public function appConfig($param)
    {
        $this->_appconfig = new Application_Model_ConfigMapper();
        return $this->_appconfig->getParam($param);
    }
    
}
