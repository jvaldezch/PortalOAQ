<?php

class Vucem_Model_VucemPedimentosMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemPedimentos();
    }

    public function verificar($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificarDesaduando($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("numeroOperacion", "rfcCliente"))
                    ->where("desaduanado IS NULL")
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function sinDesaduanar($limit) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("numeroOperacion IS NULL")
                    ->limit($limit);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificarOperacion($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("numeroOperacion", "rfcCliente"))
                    ->where("numeroOperacion IS NOT NULL")
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return array("numeroOperacion" => $stmt->numeroOperacion, "rfcCliente" => $stmt->rfcCliente);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("idTrafico", "patente", "aduana", "pedimento", "numeroOperacion"))
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

    public function agregarVacio($idTrafico, $patente, $aduana, $pedimento) {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function add($arr) {
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
    
    public function agregar($idTrafico, $patente, $aduana, $pedimento, $numeroOperacion, $partidas, $archivo) {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "numeroOperacion" => $numeroOperacion,
                "partidas" => $partidas,
                "archivo" => $archivo,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarOperacion($id, $numOperacion, $rfcCliente, $partidas) {
        try {
            $stmt = $this->_db_table->update(array("numeroOperacion" => $numOperacion, "rfcCliente" => $rfcCliente, "partidas" => $partidas), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }  catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function desaduanado($id, $fechaLiberacion) {
        try {
            $stmt = $this->_db_table->update(array("desaduanado" => 1, "fechaLiberacion" => $fechaLiberacion), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }  catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
