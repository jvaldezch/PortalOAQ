<?php

class Webservice_ServiceController extends Zend_Controller_Action {

    protected $_wsdl;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_wsdl = $this->_config->app->wsdldata;
    }

    public function indexAction() {
        echo 'This a data provider Web Service';
    }

    public function dataAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (isset($_GET['wsdl'])) {
                $this->dataWSDL();
            } else {
                $this->dataSOAP();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    private function dataSOAP() {
//        $soap = new Zend_Soap_Server($this->_wsdl);
        $soap = new Zend_Soap_Server(null, array('uri' => $this->_wsdl));
        $soap->setClass('OAQ_Data');
        $soap->handle();
    }

    private function dataWSDL() {
        $autodiscover = new Zend_Soap_AutoDiscover();
        $autodiscover->setClass('OAQ_Data');
        $autodiscover->handle();
    }

}
