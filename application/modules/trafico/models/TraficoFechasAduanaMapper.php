<?php

class Trafico_Model_TraficoFechasAduanaMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoFechasAduana();
    }

    /**
     * 
     * @param int $tipoAduana
     * @param string $tipoOperacion
     * @param string $cvePedimento
     * @return type
     * @throws Exception
     */
    public function obtener($tipoAduana, $tipoOperacion = null, $cvePedimento = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("tipoOperacion = ?", $tipoOperacion)
                    ->where("tipoAduana = ?", $tipoAduana);
            if (isset($cvePedimento)) {
                $sql->where("cvePedimento = ?", $cvePedimento);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            } else {
                $sql = $this->_db_table->select()
                        ->where("tipoOperacion = ?", $tipoOperacion)
                        ->where("tipoAduana = ?", $tipoAduana)
                        ->where("cvePedimento IS NULL");
                $stmt = $this->_db_table->fetchRow($sql);
                if ($stmt) {
                    return $stmt->toArray();
                } else {
                    $sql = $this->_db_table->select()
                            ->where("tipoAduana = ?", 0);
                    $stmt = $this->_db_table->fetchRow($sql);
                    if ($stmt) {
                        return $stmt->toArray();
                    }
                    return;
                }
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorAduana($tipoAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("tipoAduana = ?", $tipoAduana);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
