<?php

class Usuarios_Model_UsuarioEquipos
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Usuarios_Model_DbTable_UsuarioEquipos();
    }

    public function obtenerTodo($idUSuario)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("idUsuario = ?", $idUSuario);
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEquipo($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr)
    {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($id, $arr)
    {
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
}
