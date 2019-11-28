<?php

/**
 * Description of Vucem_Misc
 * 
 * Clase miscelanea para operaciones previas a la construcción de XML para COVE e EDOCUMENT
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_Conversion {

    function __construct() {    }

    public function crear($email, $sello, $factura, $rfcConsulta) {
        try {
            $vucem = new OAQ_VucemEnh();
            $arr = new OAQ_Arreglos();
            if (APPLICATION_ENV == "development") {
                $arr->setEmail($email);
            } else {
                $arr->setEmail($email);
            }
            $arr->setPatente($factura["Patente"]);
            $arr->setTipoFigura($sello["figura"]);
            $arr->setUsername($sello["rfc"]);
            $arr->setPassword($sello["ws_pswd"]);
            $arr->setCertificado($sello["cer"]);
            $arr->setKey(openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]));
            $arr->setNew(isset($sello["sha"]) ? true : false);
            $arr->setRfcConsulta($rfcConsulta);
            if (APPLICATION_ENV == "development") {
                $factura["Observaciones"] = "FACTURA DE PRUEBA, NO TIENE VALIDÉZ ANTE AUTORIDAD";
            }
            $encabezado = $this->_encabezadoFactura($factura);
            $dirCliente = $this->_direccionCliente($vucem, $factura);
            $dirProveedor = $this->_direccionProveedor($vucem, $factura);
            $productos = $this->_productos($factura["Productos"]);
            $data = $arr->arregloCove($encabezado, $productos, $dirCliente, $dirProveedor);
            if (isset($factura["adenda"]) && trim($factura["adenda"]) != "") {
                $data["factura"]["e-document"] = $factura["adenda"];
            }
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function consultaEdocument($email, $sello, $solicitud) {
        try {
            $arr = new OAQ_Arreglos();
            if (APPLICATION_ENV == "development") {
                $arr->setEmail($email);
            } else {
                $arr->setEmail($email);
            }
            $arr->setTipoFigura($sello["figura"]);
            $arr->setUsername($sello["rfc"]);
            $arr->setPassword($sello["ws_pswd"]);
            $arr->setCertificado($sello["cer"]);
            $arr->setKey(openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]));
            $arr->setNew(isset($sello["sha"]) ? true : false);
            $data = $arr->consultaEdocument($solicitud);
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function crearEdocument($email, $sello, $archivo, $rfcConsulta) {
        try {
            $arr = new OAQ_Arreglos();
            if (APPLICATION_ENV == "development") {
                $arr->setEmail($email);
            } else {
                $arr->setEmail($email);
            }
            $arr->setTipoFigura($sello["figura"]);
            $arr->setUsername($sello["rfc"]);
            $arr->setPassword($sello["ws_pswd"]);
            $arr->setCertificado($sello["cer"]);
            $arr->setKey(openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]));
            $arr->setNew(isset($sello["sha"]) ? true : false);
            $arr->setRfcConsulta($rfcConsulta);
            $arr->setIdTipoDocumento($archivo["idTipoDocumento"]);
            $arr->setNombreDocumento($archivo["nomArchivo"]);
            $arr->setHash($archivo["hash"]);
            $arr->setArchivo($archivo["archivo"]);
            $data = $arr->arregloEdocument();
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function consultaSolicitud($sello, $solicitud) {
        try {
            $arr = new OAQ_Arreglos();
            $arr->setUsername($sello["rfc"]);
            $arr->setPassword($sello["ws_pswd"]);
            $arr->setCertificado($sello["cer"]);
            $arr->setKey(openssl_get_privatekey(base64_decode($sello['spem']), $sello["spem_pswd"]));
            $arr->setNew(isset($sello["sha"]) ? true : false);
            $data = $arr->arregloSolicitudCove($solicitud);
            if (count($data)) {
                return $data;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function rfConsulta(OAQ_VucemEnh $vucem, $factura, $rfcConsulta) {
        $rfcConsulta[] = "OAQ030623UL8";
        if ($factura["Patente"] == 3920) {
            $rfcConsulta[] = "NOGI660213BI0";
        }
        if ($factura["Patente"] == 3574) {
            $rfcConsulta[] = "PEPJ561122765";
        }
        if ($vucem->addRfcsConsulta($factura["CteRfc"], $factura["CtePais"])) {
            if ($factura["firmante"] != $factura["CteRfc"]) {
                $rfcConsulta[] = $factura["CteRfc"];
            }
        }
        if ($vucem->addRfcsConsulta($factura["ProTaxID"], $factura["ProPais"]) && !in_array($factura["ProTaxID"], $rfcConsulta)) {
            if ($factura["firmante"] != $factura["ProTaxID"]) {
                $rfcConsulta[] = $factura["ProTaxID"];
            }
        }
        $gontor = array(
            "PEM930903SH4", "CCE001027PF6", "MAT0903126W0", "FCE1210012TA"
        );
        if (in_array($factura["CteRfc"], $gontor) || in_array($factura["ProTaxID"], $gontor)) {
            $rfcConsulta[] = "GTO910508AM7";
        }
        return $rfcConsulta;
    }

    protected function _encabezadoFactura($factura) {
        return array(
            "tipoOperacion" => $factura["TipoOperacion"],
            "numFactura" => $factura["NumFactura"],
            "patente" => $factura["Patente"],
            "fechaFactura" => $factura["FechaFactura"],
            "certificadoOrigen" => $factura["CertificadoOrigen"],
            "subdivision" => $factura["Subdivision"],
            "divisa" => null,
            "observaciones" => $factura["Observaciones"],
            "factorMonExt" => $factura["FactorMonExt"],
            "tipoFigura" => $factura["Figura"],
        );
    }

    protected function _direccionProveedor(OAQ_VucemEnh $vucem, $factura) {
        return array(
            "idIdentificador" => $vucem->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]),
            "identificador" => $factura["ProTaxID"],
            "razonSocial" => $factura["ProNombre"],
            "domicilio" => array(
                "calle" => $factura["ProCalle"],
                "numExt" => isset($factura["ProNumExt"]) ? $factura["ProNumExt"] : null,
                "numInt" => isset($factura["ProNumInt"]) ? $factura["ProNumInt"] : null,
                "colonia" => isset($factura["ProColonia"]) ? $factura["ProColonia"] : null,
                "localidad" => isset($factura["ProLocalidad"]) ? $factura["ProLocalidad"] : null,
                "municipio" => isset($factura["ProMun"]) ? $factura["ProMun"] : null,
                "estado" => isset($factura["ProEdo"]) ? $factura["ProEdo"] : null,
                "codigoPostal" => isset($factura["ProCP"]) ? $factura["ProCP"] : null,
                "pais" => isset($factura["ProPais"]) ? $factura["ProPais"] : null,
            )
        );
    }

    protected function _direccionCliente(OAQ_VucemEnh $vucem, $factura) {
        return array(
            "idIdentificador" => $vucem->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]),
            "identificador" => $factura["CteRfc"],
            "razonSocial" => $factura["CteNombre"],
            "domicilio" => array(
                "calle" => $factura["CteCalle"],
                "numExt" => isset($factura["CteNumExt"]) ? $factura["CteNumExt"] : null,
                "numInt" => isset($factura["CteNumInt"]) ? $factura["CteNumInt"] : null,
                "colonia" => isset($factura["CteColonia"]) ? $factura["CteColonia"] : null,
                "localidad" => isset($factura["CteLocalidad"]) ? $factura["CteLocalidad"] : null,
                "municipio" => isset($factura["CteMun"]) ? $factura["CteMun"] : null,
                "estado" => isset($factura["CteEdo"]) ? $factura["CteEdo"] : null,
                "codigoPostal" => isset($factura["CteCP"]) ? $factura["CteCP"] : null,
                "pais" => isset($factura["CtePais"]) ? $factura["CtePais"] : null,
            )
        );
    }

    protected function _productos($productos) {
        $array = array();
        foreach ($productos as $prod) {
            $array[] = array(
                "descripcion" => isset($prod["DESC1"]) ? $prod["DESC1"] : $prod["DESC_COVE"],
                "numParte" => isset($prod["PARTE"]) ? $prod["PARTE"] : null,
                "orden" => isset($prod["ORDEN"]) ? $prod["ORDEN"] : null,
                "oma" => isset($prod["UMC_OMA"]) ? strtoupper(trim($prod["UMC_OMA"])) : null,
                "divisa" => isset($prod["MONVAL"]) ? $prod["MONVAL"] : null,
                "cantidadFactura" => isset($prod["CANTFAC"]) ? number_format($prod["CANTFAC"], 3, ".", "") : null,
                "precioUnitario" => isset($prod["VALCOM"]) ? number_format($prod["VALCOM"] / $prod["CANTFAC"], 6, ".", "") : null,
                "valorComercial" => isset($prod["VALCOM"]) ? number_format($prod["VALCOM"], 6, ".", "") : null,
                "valorDolares" => (isset($prod["VALDLS"]) && (float) $prod["VALDLS"] != 0) ? number_format($prod["VALDLS"], 4, ".", "") : number_format(($prod["VALCOM"] * $prod["VALCEQ"]), 4, ".", ""),
            );
        }
        return $array;
    }

    public function agregarNuevaFactura($fct, $xml, $uuid, $firmante, $username) {
        try {
            $sol = new Vucem_Model_VucemSolicitudesMapper();
            $vfct = new Vucem_Model_VucemFacturasMapper();
            $vprd = new Vucem_Model_VucemProductosMapper();
            $id = $sol->nuevaSolicitud(null, null, null, null, null, $xml, $firmante, $uuid, null, $fct["TipoOperacion"], $fct["Patente"], $fct["Aduana"], $fct["Pedimento"], $fct["Referencia"], $fct["NumFactura"], $username, null, isset($fct["Manual"]) ? 1 : null);
            if (isset($id)) {
                $idf = $vfct->nuevaFactura($id, $fct, $username, isset($fct["Manual"]) ? 1 : null);
                foreach ($fct["Productos"] as $prod) {
                    $vprd->nuevoProducto($idf, $fct["IdFact"], $fct["Patente"], $fct["Aduana"], $fct["Pedimento"], $fct["Referencia"], $prod, $username);
                }
                return $id;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
