<?php

class Administracion_Model_CorresponsalesCuentas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Administracion_Model_DbTable_CorresponsalesCuentas();
    }

    public function find(Administracion_Model_Table_CorresponsalesCuentas $table) {
        try {
            $result = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $table->getId())
            );
            if (0 == count($result)) {
                return;
            }
            $table->setId($result->id);
            $table->setIngresos($result->ingresos);
            $table->setCostos($result->costos);
            $table->setNombre($result->nombre);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function getAll() {
        try {
            $result = $this->_db_table->fetchAll(
                    $this->_db_table->select()
                            ->order("nombre ASC")
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
