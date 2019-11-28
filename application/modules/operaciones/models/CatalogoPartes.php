<?php

class Operaciones_Model_CatalogoPartes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ClientesPartes();
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules) {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "fraccion" && $item->value != "") {
                    $sql->where("fraccion LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "numParte" && $item->value != "") {
                    $sql->where("numParte LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "descripcion" && $item->value != "") {
                    $sql->where("descripcion LIKE ?", "%" . trim($item->value) . "%");
                }
            }
        }
    }

    protected function _totalProductos($idCliente, $idProveedor, $filterRules = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("count(*) as total"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("idPro = ?", $idProveedor);
            $this->_filters($sql, $filterRules);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->total;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idCliente, $idProveedor, $fraccion, $numParte) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->where("idPro = ?", $idProveedor)
                    ->where("fraccion = ?", $fraccion)
                    ->where("numParte = ?", $numParte);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function todos($idCliente, $idProveedor, $page = null, $rows = null, $filterRules = null) {
        try {
            $mppr = new Operaciones_Model_CatalogoPartesImagenes();
            
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->where("idPro = ?", $idProveedor)
                    ->order("numParte ASC");
            
            if (isset($page) && isset($rows)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            if (isset($filterRules)) {
                $this->_filters($sql, $filterRules);
            }
            
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = [];
                foreach ($stmt->toArray() as $item) {
                    $item["imagenes"] = $mppr->obtenerImagenes($item["id"]);
                    $arr[] = $item;
                }
                return array(
                    "total" => (int) $this->_totalProductos($idCliente, $idProveedor, $filterRules),
                    "rows" => $arr
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idCliente, $idProveedor, $fraccion, $numParte, $descripcion, $usuario) {
        try {
            $arr = array(
                "idCliente" => $idCliente,
                "idPro" => $idProveedor,
                "fraccion" => $fraccion,
                "numParte" => $numParte,
                "descripcion" => $descripcion,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
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

    public function borrar($id) {
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

    public function borrarTodoDeProveedor($idProveedor) {
        try {
            $stmt = $this->_db_table->delete(array("idProveedor = ?" => $idProveedor));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
