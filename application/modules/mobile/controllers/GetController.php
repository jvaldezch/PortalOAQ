<?php

class Mobile_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
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

    public function obtenerFacturasAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $model = new Trafico_Model_TraficoFacturasMapper();
                $rows = $model->obtenerFacturas($input->id);
                if (isset($rows)) {
                    $this->_helper->json(array("success" => true, "result" => $rows));
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function obtenerGuiasAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                if ($input->isValid()) {
                    $model = new Trafico_Model_TraficoGuiasMapper();
                    $rows = $model->obtenerGuias($input->id);
                    if (isset($rows)) {
                        $this->_helper->json(array("success" => true, "result" => $rows));
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function obtenerBultosAction() {
        try {
            
            
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                if ($input->isValid()) {
                    $model = new Bodega_Model_Bultos();
                    $rows = $model->obtenerBultos($input->id);
                    if (isset($rows)) {
                        $this->_helper->json(array("success" => true, "result" => $rows));
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function readThumbnailAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Digits", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_Imagenes();
                $miniatura = $mppr->obtenerMiniatura($input->id);
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-Type: image/jpeg");
                header("Content-Length: " . filesize($miniatura));
                echo file_get_contents($miniatura);
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function readImageAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Digits", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_Imagenes();
                $image = $mppr->obtenerImagen($input->id);
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-Type: image/jpeg");
                header("Content-Length: " . filesize($image));
                echo file_get_contents($image);
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function downloadFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $gets = $this->_request->getParams();
        if (isset($gets["id"]) && isset($gets["id"])) {
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'id' => array('Digits'),
            );
            $input = new Zend_Filter_Input($filters, null, $gets);
            if ($input->isValid()) :
                $data = $input->getEscaped();
                if (isset($data["id"]) && is_int((int) $data["id"])) {
                    $archive = new Archivo_Model_RepositorioMapper();
                    $fileinfo = $archive->getFileById((int) $data["id"]);
                    if ($fileinfo["tipo_archivo"] == 22 && preg_match('/.xml$/i', $fileinfo["nom_archivo"])) {
                        $misc = new OAQ_Misc();
                        if (is_readable($fileinfo["ubicacion"]) && file_exists($fileinfo["ubicacion"])) {
                            $sha = sha1_file($fileinfo["ubicacion"]);
                            $basename = basename($fileinfo["ubicacion"]);
                            if (copy($fileinfo["ubicacion"], '/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                                $xml = file_get_contents($fileinfo["ubicacion"], '/tmp' . DIRECTORY_SEPARATOR . $sha);
                                $cleanXml = $misc->removeSecurityHeaders($xml);
                                header('Content-Type: application/octet-stream');
                                header("Content-Transfer-Encoding: Binary");
                                header("Content-Length: " . strlen($cleanXml));
                                header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                                echo $cleanXml;
                            }
                        }
                    } else {
                        if (is_readable($fileinfo["ubicacion"]) && file_exists($fileinfo["ubicacion"])) {
                            $sha = sha1_file($fileinfo["ubicacion"]);
                            $basename = basename($fileinfo["ubicacion"]);
                            if (copy($fileinfo["ubicacion"], '/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                                if (file_exists('/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                                    header('Content-Type: application/octet-stream');
                                    header("Content-Transfer-Encoding: Binary");
                                    header("Content-Length: " . filesize('/tmp' . DIRECTORY_SEPARATOR . $sha));
                                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                                    readfile('/tmp' . DIRECTORY_SEPARATOR . $sha);
                                    unlink('/tmp' . DIRECTORY_SEPARATOR . $sha);
                                }
                            }
                            unset($fileinfo);
                        }
                    }
                }
            endif;
        }
    }

}
