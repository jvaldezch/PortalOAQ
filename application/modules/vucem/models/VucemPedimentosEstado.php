<?php

class Vucem_Model_VucemPedimentosEstado {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemPedimentosEstado();
    }

    public function verificar($idPedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idPedimento = ?", $idPedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificarDesdeTrafico($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idPedimento, $numValidador, $fechaEstado, $estado, $descEstado, $subEstado, $descSubEstado) {
        try {
            $arr = array(
                "idPedimento" => $idPedimento,
                "validador" => $numValidador,
                "estado" => $estado,
                "descripcionEstado" => $descEstado,
                "subEstado" => $subEstado,
                "descripcionSubEstado" => $descSubEstado,
                "fechaEstado" => date("Y-m-d H:i:s", strtotime($fechaEstado)),
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function agregarDesdeTrafico($idTrafico, $numValidador, $fechaEstado, $estado, $descEstado, $subEstado, $descSubEstado, $observacion = null) {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "validador" => $numValidador,
                "estado" => $estado,
                "descripcionEstado" => $descEstado,
                "subEstado" => $subEstado,
                "descripcionSubEstado" => $descSubEstado,
                "observacion" => $observacion,
                "fechaEstado" => date("Y-m-d H:i:s", strtotime($fechaEstado)),
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarDesdeTrafico($id, $numValidador, $fechaEstado, $estado, $descEstado, $subEstado, $descSubEstado, $observacion = null) {
        try {
            $arr = array(
                "validador" => $numValidador,
                "estado" => $estado,
                "descripcionEstado" => $descEstado,
                "subEstado" => $subEstado,
                "descripcionSubEstado" => $descSubEstado,
                "observacion" => $observacion,
                "fechaEstado" => date("Y-m-d H:i:s", strtotime($fechaEstado)),
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
