<?php

class Automatizacion_Model_EmailsLeidos {

    protected $_dbTable;

    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception("Invalid table data gateway provided");
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable("Automatizacion_Model_DbTable_EmailsLeidos");
        }
        return $this->_dbTable;
    }

    public function find(Automatizacion_Model_Table_EmailsLeidos $tbl) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("idEmail = ?", $tbl->getIdEmail())
                            ->where("uuidEmail = ?", $tbl->getUuidEmail())
                            ->where("fecha = ?", $tbl->getFecha())
            );
            if (0 == count($stmt)) {
                return;
            }
            $tbl->setId($stmt->id);
            $tbl->setIdEmail($stmt->idEmail);
            $tbl->setUuidEmail($stmt->uuidEmail);
            $tbl->setFecha($stmt->fecha);
            $tbl->setHora($stmt->hora);
            $tbl->setDe($stmt->de);
            $tbl->setAsunto($stmt->asunto);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function save(Automatizacion_Model_Table_EmailsLeidos $tbl) {
        try {
            $arr = array(
                "id" => $tbl->getId(),
                "idEmail" => $tbl->getIdEmail(),
                "uuidEmail" => $tbl->getUuidEmail(),
                "fecha" => $tbl->getFecha(),
                "hora" => $tbl->getHora(),
                "de" => $tbl->getDe(),
                "asunto" => $tbl->getAsunto(),
                "creado" => date("Y-m-d H:i:s"),
            );
            if (null === ($id = $tbl->getId())) {
                unset($arr["id"]);
                $id = $this->getDbTable()->insert($arr);
                $tbl->setId($id);
            } else {
                $this->getDbTable()->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function verificar($id, $fecha) {
        try {
            $sql = $this->getDbTable()->select()
                    ->where("idEmail = ?", $id)
                    ->where("fecha = ?", $fecha);
            $stmt = $this->getDbTable()->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function agregar($arr) {
        try {
            $stmt = $this->getDbTable()->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
