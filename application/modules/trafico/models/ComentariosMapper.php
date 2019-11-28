<?php

class Trafico_Model_ComentariosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Comentarios();
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("m" => "trafico_comentarios"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "m.idUsuario = u.id", array("nombre"))
                    ->joinLeft(array("t" => "traficos"), "m.idTrafico = t.id", array("referencia"))
                    ->where("m.id = ?", $id)
                    ->order("creado ASC");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodos($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("m" => "trafico_comentarios"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "m.idUsuario = u.id", array("nombre"))
                    ->joinLeft(array("t" => "repositorio_temporal"), "t.idComentario = m.id", array("id AS idArchivo", "nombreArchivo"))
                    ->where("m.idTrafico = ?", $idTrafico)
                    ->order("creado ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idTrafico, $idUsuario, $comentario) {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "idUsuario" => $idUsuario,
                "mensaje" => $comentario,
                "creado" => date("Y-m-d H:i:s")
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

}
