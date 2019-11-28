<?php

require_once "Spout/Autoloader/autoload.php";

class Bodega_CrudController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_filtrosCookies;
    protected $_db;
    protected $_res;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    public function obtenerFechasAction() {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $sql = $this->_db->select()
                        ->from(array("t" => "traficos"), array(
                            new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d %H:%i') AS fechaEta"),
                            new Zend_Db_Expr("DATE_FORMAT(fechaRevision,'%Y-%m-%d %H:%i') AS fechaRevision"),
                            new Zend_Db_Expr("DATE_FORMAT(fechaCarga,'%Y-%m-%d %H:%i') AS fechaCarga"),
                            new Zend_Db_Expr("DATE_FORMAT(fechaDescarga,'%Y-%m-%d %H:%i') AS fechaDescarga"),
                            new Zend_Db_Expr("DATE_FORMAT(fechaSalida,'%Y-%m-%d %H:%i') AS fechaSalida"),
                        ))
                        ->where("t.id = ?", $input->idTrafico);
                $stmt = $this->_db->fetchRow($sql);
                if ($stmt) {
                    return $this->_helper->json(array("success" => true, "dates" => $stmt));
                } else {
                    throw new Exception("No data found.");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
