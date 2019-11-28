<?php

class Trafico_Model_FactDest {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_FactDest();
    }

    /**
     * 
     * @param int $idCliente
     * @param string $identificador
     * @return boolean
     * @throws Exception
     */
    public function verificar($idCliente, $identificador) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->where("identificador = ?", $identificador);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param array $data
     * @return boolean
     * @throws Exception
     */
    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param array $data
     * @param int $idCliente
     * @return type
     */
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
    
    public function verificarDestinatario($idCliente, $cvePro, $identificador) {
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

    public function destinatariosCliente($idCliente) {
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

}
