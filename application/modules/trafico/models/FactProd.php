<?php

class Trafico_Model_FactProd
{

    protected $_db_table;
    protected $_firephp;

    public function __construct()
    {
        $this->_db_table = new Trafico_Model_DbTable_FactProd();
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function agregar($arr)
    {
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

    public function actualizar($idProducto, $arr)
    {
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

    public function obtenerProducto($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array(
                    "id",
                    "idFactura",
                    "orden",
                    "numParte",
                    "fraccion",
                    "subFraccion",
                    "descripcion",
                    "descripcionIngles",
                    "ROUND(precioUnitario, 5) AS precioUnitario",
                    "ROUND(valorComercial, 4) AS valorComercial",
                    "valorUsd",
                    "ROUND(cantidadFactura, 4) AS cantidadFactura",
                    "cantidadTarifa",
                    "umc",
                    "umt",
                    "cantidadOma",
                    "oma",
                    "observaciones",
                    "marca",
                    "modelo",
                    "subModelo",
                    "numSerie",
                    "tlc",
                    "tlcue",
                    "prosec",
                    "iva",
                    "advalorem",
                    "paisOrigen",
                    "paisVendedor",
                ))
                ->where('id = ?', $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPartidas($idFacturas)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("p" => "trafico_factprod"), array(
                    "id",
                    "idFactura",
                    "orden",
                    "numParte",
                    "fraccion",
                    "subFraccion",
                    "descripcion",
                    "descripcionIngles",
                    "ROUND(precioUnitario, 5) AS precioUnitario",
                    "ROUND(valorComercial, 4) AS valorComercial",
                    "valorUsd",
                    "ROUND(cantidadFactura, 4) AS cantidadFactura",
                    "cantidadTarifa",
                    "umc",
                    "umt",
                    "cantidadOma",
                    "oma",
                    "CONCAT('F:', f.numFactura, ' ', p.observaciones) AS observacion",
                    "marca",
                    "modelo",
                    "subModelo",
                    "numSerie",
                    "tlc",
                    "tlcue",
                    "prosec",
                    "iva",
                    "advalorem",
                    "paisOrigen",
                    "paisVendedor",
                ))
                ->joinLeft(array("f" => "trafico_facturas"), "p.idFactura = f.id", array())
                ->where('p.idFactura IN (?)', $idFacturas);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPartidasAgrupadas($idFacturas)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("p" => "trafico_factprod"), array(
                    "fraccion",
                    "descripcion",
                    "ROUND(SUM(valorComercial)/SUM(cantidadFactura), 5) AS precioUnitario",
                    "ROUND(SUM(valorComercial), 4) AS valorComercial",
                    "ROUND(SUM(valorUsd), 4) AS valorUsd",
                    "ROUND(SUM(cantidadFactura), 4) AS cantidadFactura",
                    "ROUND(SUM(cantidadTarifa), 4) AS cantidadTarifa",
                    "umc",
                    "umt",
                    "marca",
                    "modelo",
                    "subModelo",
                    "numSerie",
                    "tlc",
                    "tlcue",
                    "prosec",
                    "iva",
                    "advalorem",
                    "paisOrigen",
                    "paisVendedor",
                    "GROUP_CONCAT(CONCAT('N/P: ', numParte) SEPARATOR ', ') AS observacion",
                    "CONCAT('F: ', f.numFactura, ' ', GROUP_CONCAT(CONCAT('N/P: ', p.numParte) SEPARATOR ', ')) AS observacion"
                ))
                ->joinLeft(array("f" => "trafico_facturas"), "p.idFactura = f.id", array())
                ->where('p.idFactura IN (?)', $idFacturas)
                ->group(array("p.fraccion", "p.umc", "p.umt", "p.paisOrigen", "p.paisVendedor"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($idFactura)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array(
                    "id",
                    "idFactura",
                    "orden",
                    "ordenProducto",
                    "numParte",
                    "fraccion",
                    "subFraccion",
                    "descripcion",
                    "ROUND(precioUnitario, 6) AS precioUnitario",
                    "ROUND(cantidadFactura * precioUnitario, 4) AS valorComercial",
                    "valorUsd",
                    "ROUND(cantidadFactura, 4) AS cantidadFactura",
                    "cantidadTarifa",
                    "umc",
                    "umt",
                    "paisOrigen",
                    "paisVendedor",
                    "tlc",
                    "prosec",
                    "cantidadOma",
                    "oma",
                    "observaciones",
                    "marca",
                    "modelo",
                    "subModelo",
                    "numSerie",
                    "fraccion_2020",
                    "nico"
                ))
                ->where('idFactura = ?', $idFactura)
                ->order("orden ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrar($id)
    {
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

    public function borrarIdFactura($idFactura)
    {
        try {
            $stmt = $this->_db_table->delete(array("idFactura = ?" => $idFactura));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function prepareData($data, $idFactura = null)
    {
        if (isset($data) && $data !== false && !empty($data)) {
            $array = array(
                'numParte' => isset($data["numParte"]) ? $data["numParte"] : null,
                'orden' => isset($data["ordenFraccion"]) ? $data["ordenFraccion"] : null,
                'fraccion' => isset($data["fraccion"]) ? $data["fraccion"] : null,
                'subFraccion' => isset($data["subFraccion"]) ? $data["subFraccion"] : null,
                'descripcion' => isset($data["descripcion"]) ? $data["descripcion"] : null,
                'precioUnitario' => isset($data["precioUnitario"]) ? $data["precioUnitario"] : null,
                'valorComercial' => isset($data["valorMonExt"]) ? $data["valorMonExt"] : null,
                'valorUsd' => isset($data["valorUsd"]) ? $data["valorUsd"] : null,
                'cantidadFactura' => isset($data["cantUmc"]) ? $data["cantUmc"] : null,
                'cantidadTarifa' => isset($data["cantUmt"]) ? $data["cantUmt"] : null,
                'cantidadOma' => isset($data["cantidadOma"]) ? $data["cantidadOma"] : null,
                'prosec' => isset($data["prosec"]) ? $data["prosec"] : null,
                'paisOrigen' => isset($data["paisOrigen"]) ? $data["paisOrigen"] : null,
                'paisVendedor' => isset($data["paisVendedor"]) ? $data["paisVendedor"] : null,
                'oma' => isset($data["oma"]) ? $data["oma"] : null,
                'tlc' => isset($data["tlc"]) ? $data["tlc"] : null,
                'umc' => isset($data["umc"]) ? $data["umc"] : null,
                'umt' => isset($data["umt"]) ? $data["umt"] : null,
            );
            if (!isset($array["precioUnitario"]) && (isset($array["cantidadFactura"]) && isset($array["umc"]))) {
                $array["precioUnitario"] = $array["valorComercial"] / $array["cantidadFactura"];
            }
            if (!isset($array["cantidadOma"]) && isset($array["cantidadFactura"])) {
                $array["cantidadOma"] = $array["cantidadFactura"];
            }
            if (!isset($array["oma"]) && isset($array["umc"])) {
                $tbl = new Vucem_Model_VucemUnidadesMapper();
                $array["oma"] = $tbl->getOma($array["umc"]);
            }
            if (isset($idFactura)) {
                $array["idFactura"] = $idFactura;
            }
            return $array;
        }
    }

    public function prepareDataFromRest($data, $idFactura = null)
    {
        $mppr = new Trafico_Model_Nicos();

        if (isset($data) && $data !== false && !empty($data)) {

            $t = null;
            $n = null;

            $f = $mppr->buscar($data["fraccion"]);
            if ($f) {
                $t = $f['tigie_2020'];
                $n = $f['nico'];
            }

            $array = array(
                'numParte' => isset($data["numParte"]) ? $data["numParte"] : null,
                'orden' => isset($data["ordenFraccion"]) ? $data["ordenFraccion"] : null,
                'consFactura' => isset($data["consFactura"]) ? $data["consFactura"] : null,
                'ordenProducto' => isset($data["ordenProducto"]) ? $data["ordenProducto"] : null,
                'fraccion' => isset($data["fraccion"]) ? $data["fraccion"] : null,
                'fraccion_2020' => $t,
                'nico' => $n,
                'subFraccion' => isset($data["subFraccion"]) ? $data["subFraccion"] : null,
                'descripcion' => isset($data["descripcion"]) ? $data["descripcion"] : null,
                'precioUnitario' => isset($data["precioUnitario"]) ? (float) $data["precioUnitario"] : null,
                'valorComercial' => isset($data["valorMonExt"]) ? (float) $data["valorMonExt"] : null,
                'valorUsd' => isset($data["valorUsd"]) ? (float) $data["valorUsd"] : null,
                'cantidadFactura' => isset($data["cantidadFactura"]) ? (float) $data["cantidadFactura"] : null,
                'cantidadTarifa' => isset($data["cantidadTarifa"]) ? (float) $data["cantidadTarifa"] : null,
                'cantidadOma' => isset($data["cantidadOma"]) ? (float) $data["cantidadOma"] : null,
                'prosec' => isset($data["prosec"]) ? $data["prosec"] : null,
                'advalorem' => isset($data["advalorem"]) ? (float) $data["advalorem"] : null,
                'iva' => isset($data["iva"]) ? (float) $data["iva"] : null,
                'paisOrigen' => isset($data["paisOrigen"]) ? $data["paisOrigen"] : null,
                'paisVendedor' => isset($data["paisVendedor"]) ? $data["paisVendedor"] : null,
                'oma' => isset($data["oma"]) ? $data["oma"] : null,
                'tlc' => isset($data["tlc"]) ? $data["tlc"] : null,
                'umc' => isset($data["umc"]) ? $data["umc"] : null,
                'umt' => isset($data["umt"]) ? $data["umt"] : null,
                'marca' => isset($data["marca"]) ? $data["marca"] : null,
                'modelo' => isset($data["modelo"]) ? $data["modelo"] : null,
                'subModelo' => isset($data["subModelo"]) ? $data["subModelo"] : null,
                'numSerie' => isset($data["numSerie"]) ? $data["numSerie"] : null,
                'creado' => date("Y-m-d H:i:s"),
            );
            if (!isset($array["precioUnitario"]) && (isset($array["cantidadFactura"]) && isset($array["umc"]))) {
                $array["precioUnitario"] = $array["valorComercial"] / $array["cantidadFactura"];
            }
            if (!isset($array["cantidadOma"]) && isset($array["cantidadFactura"])) {
                $array["cantidadOma"] = $array["cantidadFactura"];
            }
            if (!isset($array["oma"]) && isset($array["umc"])) {
                $tbl = new Vucem_Model_VucemUnidadesMapper();
                $array["oma"] = $tbl->getOma($array["umc"]);
            }
            if (isset($idFactura)) {
                $array["idFactura"] = $idFactura;
            }
            return $array;
        }
    }

    public function getRows($idPro)
    {
        try {
            $sql = $this->_db_table->select()
                ->where("idFactura = ?", $idPro);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function insert($arr)
    {
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

    public function sumarValorComercial($idFactura)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array(new Zend_Db_Expr("sum(valorComercial) as valorComercial")))
                ->where("idFactura = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->valorComercial;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
