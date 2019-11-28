<?php

class Application_Model_NoticiasInternas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_NoticiasInternas();
    }

    public function obtenerTodos() {
        try {
            $stmt = $this->_db_table->fetchAll(
                    $this->_db_table->select()
                            ->where("estatus = 1")
                            ->where("'" . date("Y-m-d H:i:s") . "' >= validoDesde AND '" . date("Y-m-d H:i:s") . "' <= validoHasta")
            );
            if (count($stmt) > 0) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener() {
        try {
            $stmt = $this->_db_table->fetchAll(
                    $this->_db_table->select()
            );
            if (count($stmt) > 0) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function activar($id) {
        try {
            $stmt = $this->_db_table->update(array("estatus" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function desactivar($id) {
        try {
            $stmt = $this->_db_table->update(array("estatus" => 0), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarFechaDesde($id, $fecha) {
        try {
            $stmt = $this->_db_table->update(array("validoDesde" => date("Y-m-d H:i:s", strtotime($fecha))), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarFechaHasta($id, $fecha) {
        try {
            $stmt = $this->_db_table->update(array("validoHasta" => date("Y-m-d H:i:s", strtotime($fecha))), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarAlerta($id, $alerta) {
        try {
            $stmt = $this->_db_table->update(array("alerta" => $alerta), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarContenido($id, $contenido) {
        try {
            $stmt = $this->_db_table->update(array("contenido" => $contenido), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
