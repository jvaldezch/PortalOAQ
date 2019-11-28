<?php

class Trafico_Model_FactDetalle {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_FactDetalle();
    }

    public function verificar($idFactura, $numFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idFactura = ?", $idFactura)
                    ->where("numFactura = ?", $numFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarDetalle($idFactura, $numFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idFactura = ?", $idFactura)
                    ->where("numFactura = ?", $numFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getRow($idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("idFactura = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("d" => "trafico_factdetalle"), array(
                        "id",
                        "idFactura",
                        "idPro",
                        "numFactura",
                        "cove",
                        "DATE_FORMAT(d.fechaFactura,'%Y-%m-%d') AS fechaFactura",
                        "incoterm",
                        "observaciones",
                        "ROUND(d.valorFacturaUsd, 4) AS valorFacturaUsd",
                        "ROUND(d.valorFacturaMonExt, 4) AS valorFacturaMonExt",
                        "divisa",
                        "paisFactura",
                        "ROUND(d.factorMonExt, 5) AS factorMonExt",
                        "ROUND(d.fletes, 5) AS fletes",
                        "ROUND(d.seguros, 5) AS seguros",
                        "ROUND(d.embalajes, 5) AS embalajes",
                        "ROUND(d.otros, 5) AS otros",
                        new Zend_Db_Expr("CASE WHEN certificadoOrigen IS NULL THEN 0 ELSE certificadoOrigen END AS certificadoOrigen"),
                        new Zend_Db_Expr("CASE WHEN subdivision IS NULL THEN 0 ELSE subdivision END AS subdivision"),
                        new Zend_Db_Expr("CASE WHEN d.relFacturas IS NULL THEN 0 ELSE d.relFacturas END AS relFacturas"),
                        "numExportador"
                    ))
                    ->joinLeft(array("f" => "trafico_facturas"), "f.id = d.idFactura", array("idTrafico"))
                    ->where("d.idFactura = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
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

    public function agregarDetalle($arr) {
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
    
    public function agregarFacturaSimple($idFactura, $numFactura) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idFactura" => $idFactura,
                "numFactura" => $numFactura
            ));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizar($idFactura, $numFactura, $data) {
        try {
            $stmt = $this->_db_table->update($data, array(
                "idFactura = ?" => $idFactura,
                "numFactura = ?" => $numFactura,
            ));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarProveedor($idFactura, $idPro) {
        try {
            $stmt = $this->_db_table->update(array('idPro' => $idPro), array(
                "idFactura = ?" => $idFactura
            ));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function prepareData($data, $idFactura = null) {
        if (isset($data) && $data !== false && !empty($data)) {
            $array = array(
                'numFactura' => isset($data["numFactura"]) ? $data["numFactura"] : null,
                'cove' => isset($data["cove"]) ? $data["cove"] : null,
                'fechaFactura' => isset($data["fechaFactura"]) ? $data["fechaFactura"] : null,
                'incoterm' => isset($data["incoterm"]) ? $data["incoterm"] : null,
                'ordenFactura' => isset($data["ordenFactura"]) ? $data["ordenFactura"] : null,
                'valorFacturaUsd' => isset($data["valorFacturaUsd"]) ? $data["valorFacturaUsd"] : null,
                'valorFacturaMonExt' => isset($data["valorFacturaMonExt"]) ? $data["valorFacturaMonExt"] : null,
                'paisFactura' => isset($data["paisFactura"]) ? $data["paisFactura"] : null,
                'divisa' => isset($data["divisa"]) ? $data["divisa"] : null,
                'factorMonExt' => isset($data["factorMonExt"]) ? $data["factorMonExt"] : null,
            );
            if (isset($idFactura)) {
                $array["idFactura"] = $idFactura;
            }
            return $array;
        }
    }
    
    public function prepareDataFromRest($data, $idFactura = null, $idProv = null) {
        if (isset($data) && $data !== false && !empty($data)) {
            $array = array(
                'consFactura' => isset($data["consFactura"]) ? $data["consFactura"] : null,
                'numFactura' => isset($data["numFactura"]) ? $data["numFactura"] : null,
                'cove' => isset($data["cove"]) ? $data["cove"] : null,
                'fechaFactura' => isset($data["fechaFactura"]) ? date("Y-m-d H:i:s", strtotime($data["fechaFactura"])) : null,
                'incoterm' => isset($data["incoterm"]) ? $data["incoterm"] : null,
                'ordenFactura' => isset($data["ordenFactura"]) ? $data["ordenFactura"] : null,
                'valorFacturaUsd' => isset($data["valorDolares"]) ? $data["valorDolares"] : null,
                'valorFacturaMonExt' => isset($data["valorMonExt"]) ? $data["valorMonExt"] : null,
                'paisFactura' => isset($data["pais"]) ? $data["pais"] : null,
                'divisa' => isset($data["divisa"]) ? $data["divisa"] : null,
                'factorMonExt' => isset($data["factorMonExt"]) ? $data["factorMonExt"] : null,
                'sistema' => isset($data["sistema"]) ? $data["sistema"] : null,
            );
            if (isset($idFactura)) {
                $array["idFactura"] = $idFactura;
            }
            if (isset($idProv)) {
                $array["idPro"] = $idProv;
            }
            return $array;
        }
    }
    
    public function update($idFactura, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("idFactura = ?" => $idFactura));
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
    
    public function borrarIdFactura($idFactura) {
        try {
            $stmt = $this->_db_table->delete(array("idFactura = ?" => $idFactura));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function insert($arr) {
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
    
    public function factorMonExt($idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array(new Zend_Db_Expr("factorMonExt")))
                    ->where("idFactura = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->factorMonExt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
