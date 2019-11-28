<?php

class Mobile_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace("OAQmobile");
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect("/mobile/main/logout");
        }
    }

    public function subirFotosAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("id")) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }

            $misc = new OAQ_Misc();
            if (APPLICATION_ENV == "production") {
                $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            } else {
                $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
            }
            $bodega = new OAQ_Bodega(array("idTrafico" => $input->id));
            $arr = $bodega->obtenerDatos();
            
            Zend_Debug::dump($arr);

            $mpr = new Bodega_Model_Bodegas();
            $b = $mpr->obtener($input->idBodega);

            $model = new Archivo_Model_RepositorioMapper();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                    ->addValidator("Extension", false, array("extension" => "png,jpg,msg", "case" => false));

            if (($path = $misc->directorioExpedienteDigitalBodega($b['siglas'], $input->referencia))) {
                $upload->setDestination($path);
            }

            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                
            }
            if (isset($errors)) {
                $this->_helper->json(array("success" => false, "errors" => $errors));
            } else {
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
}
