<?php

class Trafico_Model_NotificacionesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Notificaciones();
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function noEnviada($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id)
                    ->where("enviado IS NULL");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("n" => "trafico_notificaciones"), array("*"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "n.idAduana = a.id", array("patente", "aduana"))
                    ->where("n.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function enviada($id) {
        try {
            $stmt = $this->_db_table->update(array("enviada" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }
    
    public function save(Automatizacion_Model_Table_Notificaciones $tbl) {
        try {
            $arr = array(
                "id" => $tbl->getId(),
                "idAduana" => $tbl->getIdAduana(),
                "idTrafico" => $tbl->getIdTrafico(),
                "pedimento" => $tbl->getPedimento(),
                "referencia" => $tbl->getReferencia(),
                "contenido" => $tbl->getContenido(),
                "de" => $tbl->getDe(),
                "para" => $tbl->getPara(),
                "tipo" => $tbl->getTipo(),
                "estatus" => $tbl->getEstatus(),
                "enviado" => $tbl->getEnviado(),
                "creado" => $tbl->getCreado(),
            );
            if (null === ($id = $tbl->getId())) {
                $id = $this->_db_table->insert($arr);
                $tbl->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }

}
