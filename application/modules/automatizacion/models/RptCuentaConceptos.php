<?php

class Automatizacion_Model_RptCuentaConceptos
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Automatizacion_Model_DbTable_RptCuentaConceptos();
    }

    public function verificar($idSucursal, $idCuenta, $idConcepto)
    {
        try {
            $sql = $this->_db_table->select()
                ->where('idSucursal = ?', $idSucursal)
                ->where('idCuenta= ?', $idCuenta)
                ->where('idConcepto = ?', $idConcepto);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function conceptos($idCuenta)
    {
        try {
            $sql = $this->_db_table->select()
                ->where('idCuenta = ?', $idCuenta)
                ->order("reglon ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr)
    {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
}
