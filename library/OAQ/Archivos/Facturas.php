<?php

class OAQ_Archivos_Facturas {

    protected $idFactura;
    protected $detalle;
    protected $facturas;
    protected $productos;

    function setIdFactura($idFactura) {
        $this->idFactura = $idFactura;
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->idFactura = $this->idFactura;
        if (isset($this->idFactura)) {
            $this->facturas = new Trafico_Model_TraficoFacturasMapper();
            $this->detalle = new Trafico_Model_FactDetalle();
            $this->productos = new Trafico_Model_FactProd();
        } else {
            throw new Exception("Id Factura is not set");
        }
    }

    public function copiar() {
        $row = $this->facturas->getRow($this->idFactura);
        $row_d = $this->detalle->getRow($this->idFactura);
        $row_p = $this->productos->getRows($this->idFactura);
        if (!empty($row)) {

            unset($row["id"]);
            unset($row["actualizado"]);
            unset($row["idUsuarioModif"]);

            unset($row_d["id"]);
            unset($row_d["actualizado"]);
            unset($row_d["archivoCove"]);

            $row["idFacturaAdenda"] = $this->idFactura;
            $row["coveAdenda"] = $row["cove"];
            $row["adenda"] = 1;
            $row["creado"] = date("Y-m-d H:i:s");

            $row_d["coveAdenda"] = $row["cove"];

            $row["cove"] = null;
            $row_d["cove"] = null;

            if (($idFactura = $this->facturas->insert($row))) {
                $row_d["idFactura"] = $idFactura;
                if ($this->detalle->insert($row_d)) {
                    if (!empty($row_p)) {
                        foreach ($row_p as $item) {
                            unset($item["id"]);
                            unset($item["idFactura"]);
                            unset($item["modificado"]);
                            $item["idFactura"] = $idFactura;
                            $item["creado"] = date("Y-m-d H:i:s");
                            $this->productos->insert($item);
                        }
                    }
                }
            }
            return true;
        }
    }

    public function log($idTrafico = null, $idFactura = null, $bitacora = null, $usuario = null) {
        $mppr = new Trafico_Model_FacturasLog();
        $mppr->add(array(
            "idTrafico" => $idTrafico,
            "idFactura" => $idFactura,
            "bitacora" => $bitacora,
            "usuario" => $usuario,
            "creado" => date("Y-m-d H:i:s")
        ));
    }

    public function actualizarValorFactura() {
        $valorComercial = $this->productos->sumarValorComercial($this->idFactura);
        $factorMonExt = $this->detalle->factorMonExt($this->idFactura);
        if ($valorComercial) {
            $valorFacturaUsd = $factorMonExt * $valorComercial;
            $this->facturas->actualizar($this->idFactura, array("valorMonExt" => $valorComercial, "valorDolares" => $valorFacturaUsd, "factorMonExt" => $factorMonExt));
            $this->detalle->update($this->idFactura, array("valorFacturaMonExt" => $valorComercial, "factorMonExt" => $factorMonExt, "valorFacturaUsd" => $valorFacturaUsd));
        }
    }
    
    public function obtenerFacturas() {
        
    }

}
