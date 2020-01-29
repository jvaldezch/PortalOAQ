<?php

class Bodega_Model_Proveedores {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Proveedores();
    }
    
    public function obtenerProveedores($idCliente, $idBodega) {
        $select = $this->_db_table->select()
                ->where("idCliente = ?", $idCliente)
                ->where("idBodega = ?", $idBodega)
                ->order("nombre ASC");
        $stmt = $this->_db_table->fetchAll($select);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    public function obtener($idPro) {
        $sql = $this->_db_table->select()
                ->where("id = ?", $idPro);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    public function agregar($arr) {
        $stmt = $this->_db_table->insert($arr);
        if ($stmt) {
            return $stmt;
        }
        return null;
    }

    public function buscar($idCliente, $idBodega, $identificador, $nombre) {
        $select = $this->_db_table->select()
                ->where("idCliente = ?", $idCliente)
                ->where("idBodega = ?", $idBodega)
                ->where("identificador = ?", $identificador)
                ->where("nombre = ?", $nombre);
        $stmt = $this->_db_table->fetchRow($select);
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function buscarProveedor($idCliente, $name) {
        $sql = $this->_db_table->select()
            ->where("idCliente = ?", $idCliente)
            ->where("nombre LIKE ?", "%" . $name . "%")
            ->order("nombre ASC")
            ->limit(10);
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            $arr = array();
            foreach ($stmt->toArray() as $item) {
                $arr[] = array(
                    'id' => $item['id'],
                    'name' => $item["nombre"]
                );
            }
            return $arr;
        }
        return null;
    }

    public function obtenerProveedor($idBodega, $idCliente, $name) {
        $sql = $this->_db_table->select()
            ->where("idBodega = ?", $idBodega)
            ->where("idCliente = ?", $idCliente)
            ->where("nombre = ?", $name);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

}
