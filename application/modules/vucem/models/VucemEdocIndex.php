<?php

class Vucem_Model_VucemEdocIndex {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemEdocIndex();
    }

    public function obtenerSolicitudes($usuario = null, $pedimento = null, $referencia = null, $edocument = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "rfc", "solicitud", "patente", "aduana", "pedimento", "referencia", "tipoDoc", "subTipoArchivo", "nomArchivo", "usuario", "edoc", "estatus", "enviado", "actualizado", "usuario", "enPedimento", "expediente", "size"))
                    ->order("enviado DESC")
                    ->limit(200);
            if (isset($pedimento)) {
                $sql->where("pedimento  = ?", $pedimento);
            }
            if (isset($referencia)) {
                $sql->where("referencia LIKE ?", "%{$referencia}%");
            }
            if (isset($edocument)) {
                $sql->where("edoc LIKE ?", "%{$edocument}%");
            }
            if (isset($usuario)) {
                $sql->where("usuario  = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($id, $rfc, $patente, $aduana, $pedimento, $referencia, $solicitud, $tipoDoc, $subTipoArchivo, $nomArchivo, $usuario, $estatus, $edoc, $size, $enviado) {
        try {
            $stmt = $this->_db_table->insert(array(
                "id" => $id,
                "rfc" => $rfc,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "solicitud" => $solicitud,
                "tipoDoc" => $tipoDoc,
                "subTipoArchivo" => $subTipoArchivo,
                "nomArchivo" => $nomArchivo,
                "usuario" => $usuario,
                "estatus" => $estatus,
                "edoc" => $edoc,
                "size" => $size,
                "enviado" => $enviado
            ));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarEdoc($id, $estatus, $edoc = null) {
        try {
            $arr = array(
                "edoc" => $edoc,
                "estatus" => $estatus,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function saved($id) {
        try {
            $stmt = $this->_db_table->update(array("expediente" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function enPedimento($id) {
        try {
            $stmt = $this->_db_table->update(array("enPedimento" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function consultar($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function update($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
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
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

}
