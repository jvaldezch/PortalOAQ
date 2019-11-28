<?php

class Trafico_Model_FactPro {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_FactPro();
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules) {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "nombre" && $item->value != "") {
                    $sql->where("nombre LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "identificador" && $item->value != "") {
                    $sql->where("identificador LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "clave" && $item->value != "") {
                    $sql->where("clave LIKE ?", "%" . trim($item->value) . "%");
                }
            }
        }
    }

    protected function _totalProveedores($idCliente, $filterRules = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("count(*) as total"))
                    ->where("idCliente = ?", $idCliente);
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

    public function verificar($idCliente, $identificador) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("identificador = ?", $identificador);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->id;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function proveedoresCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "nombre AS text"))
                    ->where("idCliente = ?", $idCliente)
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorCliente($idCliente, $page = null, $rows = null, $filterRules = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->order("nombre ASC");
            
            if (isset($page) && isset($rows)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            if (isset($filterRules)) {
                $this->_filters($sql, $filterRules);
            }
            
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return array(
                    "total" => (int) $this->_totalProveedores($idCliente, $filterRules),
                    "rows" => $stmt->toArray()
                );
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

    public function obtener($idPro) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $idPro);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
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

    public function prepareData($data, $idCliente = null) {
        if (isset($data) && $data !== false && !empty($data)) {
            $array = array(
                "clave" => $this->_tipoIdentificador($data["taxId"], $data["domicilio"]["pais"]),
                "identificador" => isset($data["taxId"]) ? trim($data["taxId"]) : null,
                "nombre" => isset($data["nomProveedor"]) ? trim($data["nomProveedor"]) : null,
                "calle" => isset($data["domicilio"]["calle"]) ? trim($data["domicilio"]["calle"]) : null,
                "numExt" => isset($data["domicilio"]["numExterior"]) ? $data["domicilio"]["numExterior"] : null,
                "numInt" => isset($data["domicilio"]["numInterior"]) ? $data["domicilio"]["numInterior"] : null,
                "localidad" => isset($data["domicilio"]["localidad"]) ? trim($data["domicilio"]["localidad"]) : null,
                "municipio" => isset($data["domicilio"]["municipio"]) ? trim($data["domicilio"]["municipio"]) : null,
                "ciudad" => isset($data["domicilio"]["ciudad"]) ? $data["domicilio"]["ciudad"] : null,
                "codigoPostal" => isset($data["domicilio"]["codigoPostal"]) ? $data["domicilio"]["codigoPostal"] : null,
                "pais" => isset($data["domicilio"]["pais"]) ? $data["domicilio"]["pais"] : null,
            );
            if (isset($idCliente)) {
                $array["idCliente"] = $idCliente;
            }
            return $array;
        }
    }

    /**
     * Regresa el tipo de identificador de VUCEM [0-TAX_ID, 1-RFC, 2-CURP,3-SIN_TAX_ID]
     * 
     * @param string $identificador
     * @param string $pais
     * @return int
     */
    protected function _tipoIdentificador($identificador, $pais) {
        $regRfc = "/^[A-Z]{3,4}([0-9]{2})(1[0-2]|0[1-9])([0-3][0-9])([A-Z0-9]{3,4})$/";
        if (($pais == "MEX" || $pais == "MEXICO") && preg_match($regRfc, str_replace(" ", "", trim($identificador)))) {
            if ($identificador != "EXTR920901TS4") {
                if (strlen($identificador) > 12) {
                    return 2;
                }
                return 1;
            } else {
                return 0;
            }
        }
        if (($pais == "MEX" || $pais == "MEXICO") && !preg_match($regRfc, str_replace(" ", "", trim($identificador)))) {
            return 0;
        }
        if ($pais != "MEX" && trim($identificador) != "") {
            return 0;
        }
        if ($pais != "MEX" && trim($identificador) == "") {
            return 0;
        }
    }
    
    public function verificarProveedor($idCliente, $cvePro, $identificador) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("clave = ?", $cvePro)
                    ->where("identificador = ?", $identificador);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->id;
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

}
