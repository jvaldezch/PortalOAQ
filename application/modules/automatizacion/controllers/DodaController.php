<?php

class Automatizacion_DodaController extends Zend_Controller_Action {

    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }
    
    public function altaAction() {
        $doda = new Doda_Alta();
        $doda->pedimento();
        $doda->set_dir("D:\\Tmp\\OAQ\\Doda\\ConsultaEspecifica");
        $doda->saveToDisk("AltaPedimento.xml");
        Zend_Debug::dump($doda->getXml());
        
        $dodaServicios = new Doda_Servicios();
        $dodaServicios->setXml($doda->getXml());
        $dodaServicios->altaPedimento();
        
        $dodaEjemplos = new Doda_RespuestasEjemplos();
        
        $dodaRespuestas = new Doda_Respuestas();
        $dodaRespuestas->setDebug(true);
        $res = $dodaRespuestas->respuestaAltaPedimento($dodaServicios->getResponse());
    }
        
}
