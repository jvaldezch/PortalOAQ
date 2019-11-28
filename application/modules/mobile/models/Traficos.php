<?php

class Mobile_Model_Traficos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Traficos();
    }

    public function obtenerTraficos($page = 1, $size = 20, $fecha = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array(
                        "id",
                        "patente",
                        "aduana",
                        "pedimento",
                        "referencia",
                    ))
                    ->where("t.estatus <> 4")
                    ->where("t.fechaEta > ?", date('Y-m-d', strtotime('-15 days', strtotime($fecha))))
                    ->order("t.idAduana ASC");
            if (isset($size)) {
                $sql->limit($size, ((int) $size * ((int) $page - 1 )));
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _filters(Zend_Db_Select $sql, $page = 1, $size = 20, $idUsuario = null, $search = null) {
        if (isset($search)) {
            $sql->where("t.referencia LIKE ?", "%" . trim($search) . "%");
        }
        if (isset($size)) {
            $sql->limit($size, ((int) $size * ((int) $page - 1 )));
        }
        if (isset($idUsuario) && !isset($search)) {
            $sql->where("t.idUsuario = ?", $idUsuario)
                    ->orWhere("t.idUsuarioModif = ?", $idUsuario);
        }
    }

    public function getSelect($page = 1, $size = 20, $idUsuario = null, $search = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array(
                        "id",
                        "patente",
                        "aduana",
                        "pedimento",
                        "referencia",
                    ))
                    ->where("t.estatus <> 4")
                    ->where("t.idAduana IS NOT NULL")
                    ->order("t.referencia ASC");
            $this->_filters($sql, $page, $size, $idUsuario, $search);
            return $sql;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function getSelectWarehouse($page = 1, $size = 20, $idUsuario = null, $search = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array(
                        "id",
                        "patente",
                        "aduana",
                        "pedimento",
                        "referencia",
                    ))
                    ->where("t.estatus <> 4")
                    ->where("t.idBodega IS NOT NULL")
                    ->order("t.referencia ASC");
            $this->_filters($sql, $page, $size, $idUsuario, $search);
            return $sql;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("*"))
                    ->joinInner(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                    ->joinInner(array("u" => "usuarios"), "u.id = t.idUsuario", array("nombre"))
                    ->where("t.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
