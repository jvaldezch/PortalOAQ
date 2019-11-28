<?php

class Automatizacion_SicaController extends Zend_Controller_Action {

    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }
    
    public function cxpAction() {
        try {
            $sica = new OAQ_SicaDb();            
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
