<?php

class Automatizacion_SessionsController extends Zend_Controller_Action {

    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_appconfig = new Application_Model_ConfigMapper();
    }

    public function cleanSessionsAction() {
        $mapper = new Application_Model_UsuarioSesiones();
        $offline = $mapper->usuariosNoActivos($this->_appconfig->getParam("session-exp"));
        if (count($offline)) {
            foreach ($offline as $item) {
                $mapper->borrar($item["id"]);
            }
        }
    }

    /**
     * /automatizacion/sessions/archivos-ftp
     */
    public function archivosFtpAction() {
        $mppr = new Clientes_Model_FtpLinks();
        $arr = $mppr->archivosCaducos();
        if (isset($arr) && !empty($arr)) {
            foreach ($arr as $item) {
                if (file_exists($item["ubicacion"])) {
                    unlink($item["ubicacion"]);
                    $mppr->borrar($item["id"]);
                }
            }
        }
    }

}
