<?php

class Vucem_Model_VucemUnidadesMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemUnidades();
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getAllUnits() {
        try {
            $sql = $this->_db_table->select();
            $sql->order("unidad_medida ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $busqueda
     * @return type
     * @throws Exception
     */
    public function obtenerUnidades($busqueda = null) {
        try {
            $sql = $this->_db_table->select()
                    ->order("unidad_medida ASC");
            if (isset($busqueda)) {
                $sql->where("(unidad_medida LIKE '%{$busqueda}%' OR desc_en LIKE '%{$busqueda}%')");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $unit
     * @return type
     * @throws Exception
     */
    public function getMeasurementUnit($unit) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_unidades", array("desc_es"))
                    ->where("unidad_medida = ?", $unit);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["desc_es"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $unit
     * @return type
     * @throws Exception
     */
    public function getMeasurementUnitEnglish($unit) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_unidades", array("desc_en"))
                    ->where("unidad_medida = ?", $unit);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["desc_en"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $umc
     * @return type
     * @throws Exception
     */
    public function getOma($umc) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_unidades", array("unidad_medida"))
                    ->where("umc = ?", $umc);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["unidad_medida"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
