<?php

class Trafico_Model_Tarifas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Tarifas();
    }

    public function guardar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
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

    public function removerArchivoTarifa($idCliente, $idArchivo, $usuario) {
        try {
            $stmt = $this->_db_table->update(array("idArchivo" => null, "estatus" => 1, "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $usuario), array("idCliente = ?" => $idCliente, "idArchivo = ?" => $idArchivo));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function archivoTarifa($id, $idArchivo, $username) {
        try {
            $stmt = $this->_db_table->update(array("idArchivo" => $idArchivo, "aprobado" => date("Y-m-d H:i:s"), "aprobadoPor" => $username), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEstatus($id, $estatus) {
        try {
            $stmt = $this->_db_table->update(array("estatus" => $estatus), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function removerTarifasPrevias($idCliente) {
        try {
            $stmt = $this->_db_table->update(array("estatus" => 1), array("idCliente = ?" => $idCliente, "estatus = ?" => 2));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->where("estatus = 0");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTarifa($id) {
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

    public function obtenerTarifasCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "tarifas"), array("id", "estatus", "creado", "modificado", "revisado", "aprobado"))
                    ->joinLeft(array("v" => "tarifa_vigencias"), "v.id = t.tipoVigencia", array("tipoVigencia"))
                    ->where("t.idCliente = ?", $idCliente);
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
