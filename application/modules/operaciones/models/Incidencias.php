<?php

class Operaciones_Model_Incidencias {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_Incidencias();
    }

    public function incidenciasSelect() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("i" => "incidencias"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "i.idCliente = c.id", array("nombre"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "i.idAduana = a.id", array("patente", "aduana"))
                    ->joinLeft(array("e" => "incidencia_tipo_error"), "e.id = i.idTipoError", array("tipoError"));
            return $sql;
        } catch (Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtener($id) {
        try {            
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);            
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($id, $arr) {
        try {           
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    

    public function verificar($idAduana, $idCliente, $acta) {
        try {           
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("idCliente = ?", $idCliente)
                    ->where("acta = ?", $acta);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
        try {           
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrar($id) {
        try {           
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function reporte($year, $idCliente = null, $idAduana = null)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 1 THEN 1 ELSE 0 END) AS Ene"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 2 THEN 1 ELSE 0 END) AS Feb"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 3 THEN 1 ELSE 0 END) AS Mar"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 4 THEN 1 ELSE 0 END) AS Abr"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 5 THEN 1 ELSE 0 END) AS May"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 6 THEN 1 ELSE 0 END) AS Jun"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 7 THEN 1 ELSE 0 END) AS Jul"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 8 THEN 1 ELSE 0 END) AS Ago"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 9 THEN 1 ELSE 0 END) AS Sep"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 10 THEN 1 ELSE 0 END) AS 'Oct'"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 11 THEN 1 ELSE 0 END) AS Nov"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 12 THEN 1 ELSE 0 END) AS Dic"),
            );
            $sql = $this->_db_table->select()
                ->from($this->_db_table, $fields)
                ->where("fecha IS NOT NULL")
                ->where("YEAR(fecha) = ?", $year);
            if ($idCliente) {
                $sql->where('idCliente = ?', $idCliente);
            }
            if ($idAduana) {
                $sql->where('idAduana = ?', $idAduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
}
