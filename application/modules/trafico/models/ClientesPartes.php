<?php

class Trafico_Model_ClientesPartes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ClientesPartes();
    }

    public function buscar($idPro, $tipoOperacion, $fraccion, $numParte, $paisOrigen, $paisVendedor) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idPro = ?", $idPro)
                    ->where("tipoOperacion = ?", $tipoOperacion)
                    ->where("fraccion = ?", $fraccion)
                    ->where("numParte = ?", $numParte)
                    ->where("paisOrigen = ?", $paisOrigen)
                    ->where("paisVendedor = ?", $paisVendedor);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
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
    
    public function obtenerPorCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerDetallePorCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "trafico_clientes_partes"), array('*'))
                    ->joinLeft(array("v" => "trafico_factpro"), "p.idPro = v.id", array('identificador', 'nombre AS nombreProveedor'))
                    ->where("p.idCliente = ?", $idCliente)
                    ->order("v.nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
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
    
    public function actualizar($idProducto, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $idProducto));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function prepareDataFromRest($idCliente, $tipoOperacion, $idProv, $data) {
        if (isset($data) && $data !== false && !empty($data)) {
            $array = array(
                'idCliente' => $idCliente,
                'tipoOperacion' => $tipoOperacion,
                'idPro' => $idProv,
                'numParte' => isset($data["numParte"]) ? $data["numParte"] : null,
                'fraccion' => isset($data["fraccion"]) ? $data["fraccion"] : null,
                'subFraccion' => isset($data["subFraccion"]) ? $data["subFraccion"] : null,
                'descripcion' => isset($data["descripcion"]) ? $data["descripcion"] : null,
                'oma' => isset($data["oma"]) ? $data["oma"] : null,
                'umc' => isset($data["umc"]) ? $data["umc"] : null,
                'umt' => isset($data["umt"]) ? $data["umt"] : null,
                'marca' => isset($data["marca"]) ? $data["marca"] : null,
                'modelo' => isset($data["modelo"]) ? $data["modelo"] : null,
                'subModelo' => isset($data["subModelo"]) ? $data["subModelo"] : null,
                'numSerie' => isset($data["numSerie"]) ? $data["numSerie"] : null,
                'iva' => isset($data["iva"]) ? $data["iva"] : null,
                'tlc' => (isset($data["tlc"]) && $data["tlc"] == 'on') ? 'S' : null,
                'tlcue' => (isset($data["tlcue"]) && $data["tlcue"] == 'on') ? 'S' : null,
                'prosec' => (isset($data["prosec"]) && $data["prosec"] == 'on') ? 'S' : null,
                'advalorem' => isset($data["advalorem"]) ? (float) $data["advalorem"] : null,
                'paisOrigen' => isset($data["paisOrigen"]) ? $data["paisOrigen"] : null,
                'paisVendedor' => isset($data["paisVendedor"]) ? $data["paisVendedor"] : null,
                'observaciones' => isset($data["observaciones"]) ? $data["observaciones"] : null,
                'creado' => date("Y-m-d H:i:s"),
            );
            if (!isset($array["oma"]) && isset($array["umc"])) {
                $tbl = new Vucem_Model_VucemUnidadesMapper();
                $array["oma"] = $tbl->getOma($array["umc"]);
            }
            return $array;
        }
    }

}
