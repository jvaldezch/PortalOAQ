<?php

class Trafico_Model_Imagenes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Imagenes();
    }

    public function agregar($idTrafico, $estatus, $carpeta, $imagen, $miniatura = null, $nombre = null) {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "idEstatus" => $estatus,
                "carpeta" => $carpeta,
                "imagen" => $imagen,
                "miniatura" => $miniatura,
                "nombre" => $nombre,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function miniaturas($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("i" => "trafico_imagenes"), array("id", "idTrafico", "imagen", "idEstatus", "carpeta", "miniatura", "nombre"))
                    ->joinLeft(array("e" => "trafico_estatusimg"), "i.idEstatus = e.id", array("descripcion as estatus"))
                    ->where("i.idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerMiniatura($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("carpeta", "miniatura"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["carpeta"] . DIRECTORY_SEPARATOR . $data["miniatura"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerImagen($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("carpeta", "imagen"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["carpeta"] . DIRECTORY_SEPARATOR . $data["imagen"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodas($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("idTrafico = ?", $id);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarImagen($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
