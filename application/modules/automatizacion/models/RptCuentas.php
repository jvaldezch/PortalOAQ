<?php

class Automatizacion_Model_RptCuentas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_RptCuentas();
    }

    public function verificar($idSucursal, $folio, $patente, $aduana, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idSucursal = ?', $idSucursal)
                    ->where('folio = ?', $folio)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('pedimento = ?', $pedimento)
                    ->where('referencia = ?', $referencia);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr) {
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

    public function sinAnalizar($folio = null, $limit = 250) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('id', 'folio'));
            if (isset($folio)) {
                $sql->where("folio = ?", $folio);
            } else {
                $sql->where('analizado IS NULL')
                    ->where('conceptos IS NULL')
                    ->where('noConceptos IS NULL')
                        ->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function folio($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('*'))
                    ->where('folio = ?', $id);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function sinTrafico($limit = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('id', 'folio', 'patente', 'aduana', 'pedimento', 'referencia'))
                    ->where('idTrafico IS NULL');
            if (isset($limit)) {
                $sql->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function sinPagar($limit = null, $fecha = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('id', 'folio', 'patente', 'aduana', 'pedimento', 'referencia', 'total'))
                    ->where('analizado = 1')
                    ->where('cancelada IS NULL')
                    ->where('pagada IS NULL')
                    ->order('folio ASC');
            if (isset($fecha)) {
                $sql->where('fechaFacturacion >= ?', date('Y-m-d', strtotime($fecha)));
            }
            if (isset($limit)) {
                $sql->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerFolio($folio) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('id', 'folio', 'patente', 'aduana', 'pedimento', 'referencia', 'total'))
                    ->where('folio = ?', $folio);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
