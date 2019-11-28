<?php

class Automatizacion_RevisionController extends Zend_Controller_Action {

    protected $_config;
    protected $_db;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_db = Zend_Registry::get("oaqintranet");
    }
    
    protected function _checarReposotirio($idTrafico) {
        try {
            $sql = $this->_db->select()
                    ->from(array("r" => "repositorio_index"), array("id"))
                    ->where("revisionAdministracion = 1 AND revisionOperaciones = 1")
                    ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _sinFtp() {
        try {
            $sql = $this->_db->select()
                    ->from(array("t" => "traficos"), array("id", "fechaLiberacion"))
                    ->where("ftpListo IS NULL")
                    ->where("fechaLiberacion > '2018-04-01'")
                    ->where("estatus <> 4");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    protected function _ftpListo($idTrafico) {
        try {
            $stmt = $this->_db->update("traficos",array("ftpListo" => date("Y-m-d H:i:s"), "estatusRepositorio" => 3), array("id = ?" => $idTrafico));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    protected function _estatus($idTrafico, $estatus) {
        try {
            $stmt = $this->_db->update("traficos",array("estatusRepositorio" => $estatus), array("id = ?" => $idTrafico));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * /automatizacion/revision/expedientes-completos
     * 
     */
    public function expedientesCompletosAction() {
        try {
            $arr = $this->_sinFtp();
            if (!empty($arr)) {
                for ($i = 0; $i < count($arr); $i++) {
                    $array = $this->_checarReposotirio($arr[$i]["id"]);
                    if (!empty($array)) {
                        $this->_ftpListo($arr[$i]["id"]);
                    } else {
                        $now = time();
                        $your_date = strtotime($arr[$i]["fechaLiberacion"]);
                        $datediff = round(($now - $your_date) / (60 * 60 * 24));
                        if ($datediff <= 1) {
                            $this->_estatus($arr[$i]["id"], 0);
                        }
                        if ($datediff > 1 && $datediff <= 2) {
                            $this->_estatus($arr[$i]["id"], 1);
                        }
                        if ($datediff > 2) {
                            $this->_estatus($arr[$i]["id"], 2);
                        }
                    }
                }
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function revisarTraficosSinExpedientesAction() {
        try {
            
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
