<?php

class Clientes_Model_FactPro {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_FactPro();
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules) {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "nombre" && $item->value != "") {
                    $sql->where("nombre LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "identificador" && $item->value != "") {
                    $sql->where("identificador LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "clave" && $item->value != "") {
                    $sql->where("clave LIKE ?", "%" . trim($item->value) . "%");
                }
            }
        }
    }

    public function obtenerPorCliente($idCliente, $page = null, $rows = null, $filterRules = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "trafico_factpro"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes_partes"), "c.idPro = p.id", array("*"))
                    ->where("p.idCliente = ?", $idCliente)
                    ->order("p.nombre ASC");            
            if (isset($page) && isset($rows)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            if (isset($filterRules)) {
                $this->_filters($sql, $filterRules);
            }
            
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return array(
                    "total" => (int) $this->_totalProveedores($idCliente, $filterRules),
                    "rows" => $stmt->toArray()
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    protected function _totalProveedores($idCliente, $filterRules = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "trafico_factpro"), array("count(*) as total"))
                    ->joinLeft(array("c" => "trafico_clientes_partes"), "c.idPro = p.id", array(""))
                    ->where("p.idCliente = ?", $idCliente);
            $this->_filters($sql, $filterRules);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->total;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
}
