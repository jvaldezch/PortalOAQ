<?php

require_once 'UUID.php';
require_once "PHPExcel/IOFactory.php";
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlantillaCoves
 *
 * @author Jaime
 */
class OAQ_Archivos_PlantillaFacturas {

    protected $_filename;
    protected $_worksheet;
    protected $_invoices = array();
    protected $_products = array();
    protected $_objPHPExcel;
    protected $_vucem;
    protected $_config;
    protected $_idTrafico;
    protected $_trafico;
    protected $_usuario;
    protected $_idUsuario;
    protected $_replace = true;
    protected $_firephp;

    function get_filename() {
        return $this->_filename;
    }

    function set_filename($_filename) {
        $this->_filename = $_filename;
    }
    
    function set_idUsuario($_idUsuario) {
        $this->_idUsuario = $_idUsuario;
    }
    
    function set_idTrafico($_idTrafico) {
        $this->_idTrafico = $_idTrafico;
    }
    
    function set_replace($_replace) {
        $this->_replace = $_replace;
    }

    function set_usuario($_usuario) {
        $this->_usuario = $_usuario;
    }

    public function __construct($filename) {
        $this->_firephp = Zend_Registry::get("firephp");
        $this->_filename = $filename;
        if (file_exists($filename)) {
            $this->_objPHPExcel = PHPExcel_IOFactory::load($this->_filename);
            $this->_vucem = new OAQ_VucemEnh();
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        }
    }
    
    // 0-TAX_ID, 1-RFC, 2-CURP,3-SIN_TAX_ID
    protected function _tipoIdentificador($tipo) {
        switch ($tipo) {
            case 'TAXID':
                return 0;
            case 'RFC':
                return 1;
            case 'CURP':
                return 2;
            case 'SINTAXID':
                return 3;
            default:
                return;
        }
    }

    protected function _verificarDestinatario($idCliente, $arr) {
        $mppr = new Trafico_Model_FactDest();
        if (!($id = $mppr->verificarDestinatario($idCliente, $arr["clave"], $arr["identificador"]))) {
            $row = array(
                "idCliente" => $idCliente,
                "clave" => $arr["clave"],
                "tipoIdentificador" => $this->_tipoIdentificador($arr["tipoIdentificador"]),
                "identificador" => $arr["identificador"],
                "nombre" => $arr["nombre"],
                "calle" => $arr["calle"],
                "numExt" => $arr["numExt"],
                "numInt" => $arr["numInt"],
                "municipio" => isset($arr["municipio"]) ? $arr["municipio"] : null,
                "localidad" => isset($arr["localidad"]) ? $arr["localidad"] : null,
                "estado" => $arr["estado"],
                "codigoPostal" => $arr["codigoPostal"],
                "pais" => $arr["pais"],
                "creado" => date("Y-m-d H:i:s"),
            );
            return $mppr->agregar($row);
        }
        return $id;
    }
    
    protected function _verificarProveedor($idCliente, $arr) {
        $mppr = new Trafico_Model_FactPro();
        if (!($id = $mppr->verificarProveedor($idCliente, $arr["clave"], $arr["identificador"]))) {
            $row = array(
                "idCliente" => $idCliente,
                "clave" => $arr["clave"],
                "tipoIdentificador" => $this->_tipoIdentificador($arr["tipoIdentificador"]),
                "identificador" => $arr["identificador"],
                "nombre" => $arr["nombre"],
                "calle" => $arr["calle"],
                "numExt" => $arr["numExt"],
                "numInt" => $arr["numInt"],
                "municipio" => isset($arr["municipio"]) ? $arr["municipio"] : null,
                "localidad" => isset($arr["localidad"]) ? $arr["localidad"] : null,
                "estado" => $arr["estado"],
                "codigoPostal" => $arr["codigoPostal"],
                "pais" => $arr["pais"],
                "creado" => date("Y-m-d H:i:s"),
            );
            return $mppr->agregar($row);
        }
        return $id;
    }
    
    protected function _verificarFactura($arr, $id_prov) {
        $mppr = new Trafico_Model_TraficoFacturasMapper();
        if (!empty($arr) && isset($id_prov)) {
            unset($arr["cove"]);

            if ($this->_replace == false) {
                $id = $mppr->agregar($this->_idTrafico, $arr, $this->_idUsuario);
                return $id;
            }
            if (!($id = $mppr->verificarFactura($this->_idTrafico, $arr["numFactura"]))) {
                $id = $mppr->agregar($this->_idTrafico, $arr, $this->_idUsuario);
            } else {
                $mppr->actualizar($id, $arr);
            }

            return $id;
        }
    }
    
    protected function _verificarDetalleFactura($idFactura, $arr) {
        try {            
            $mppr = new Trafico_Model_FactDetalle();
            if (!empty($arr)) {
                if (!($id = $mppr->verificarDetalle($idFactura, $arr["numFactura"]))) {
                    $id = $mppr->agregarDetalle($arr);
                } else {
                    $mppr->update($idFactura, $arr);
                }
                return $id;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    protected function _productosTrafico($idFactura, $idPro, $arr) {
        $mppr = new Trafico_Model_FactProd();        
        $mppr->borrarIdFactura($idFactura);        
        foreach ($arr as $item) {
            $row = $this->_insertProducto($idFactura, $item);
            if (isset($row["fraccion"]) && isset($row["numParte"])) {
                $mppr->agregar($row);
            }
        }
    }

    public function analizar() {
        try {
            
            $this->_trafico = new OAQ_Trafico(array("idTrafico" => $this->_idTrafico, "usuario" => $this->_usuario, "idUsuario" => $this->_idUsuario));
            
            foreach ($this->_objPHPExcel->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();
                if (preg_match("/facturas/i", $worksheetTitle)) {
                    $this->_worksheet = $worksheet;
                    $this->_analizarFacturas();
                }
                if (preg_match("/productos/i", $worksheetTitle) && !empty($this->_invoices)) {
                    $this->_worksheet = $worksheet;
                    $this->_analizarProductos();
                }
            }
            
            if (!empty($this->_invoices)) {

                foreach ($this->_invoices as $factura) {
                    $prov = $this->_insertProveedor($factura);
                    if (!empty($prov)) {
                        if ($this->_trafico->getTipoOperacion() == "TOCE.IMP") {
                            $id_prov = $this->_verificarProveedor($this->_trafico->getIdCliente(), $prov);
                            $this->tipoOperacion = 1;
                        }
                        if ($this->_trafico->getTipoOperacion() == "TOCE.EXP") {
                            $id_prov = $this->_verificarDestinatario($this->_trafico->getIdCliente(), $prov);
                            $this->tipoOperacion = 2;
                        }
                    }
                    
                    $invoice = $this->_insertFactura($factura);
                    
                    if(($idFactura = $this->_verificarFactura($invoice, $id_prov))) {
                        
                        $detail = $this->_insertFacturaDetalle($idFactura, $id_prov, $factura);
                        if (($idd = $this->_verificarDetalleFactura($idFactura, $detail))) {
                            
                            $this->_productosTrafico($idFactura, $id_prov, $factura["PRODUCTOS"]);
                        }

                    }
                    
                }
            } else {
                throw new Exception("No invoices found.");
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _getUuid($key) {
        return UUID::v5($this->_config->app->uuid, $key);
    }

    protected function _trimArray($value) {
        $pre = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', trim(preg_replace('/[\x00-\x1F\x7f-\xFF]/', '', $value)));
        return trim(preg_replace('/\t/', '', preg_replace('/\s+/', ' ', $pre)));
    }

    protected function _insertProducto($idFactura, $producto) {
        try {
            $arr = array(
                "idFactura" => $idFactura,
                "orden" => isset($producto["ORDEN"]) ? $producto["ORDEN"] : null,
                "consFactura" => isset($producto["MARCA"]) ? $producto["MARCA"] : null,
                "numParte" => $this->_trimArray($producto["PARTE"]),
                "fraccion" => isset($producto["FRACCION"]) ? $producto["FRACCION"] : null,
                "subFraccion" => isset($producto["SUB_FRACCION"]) ? $producto["SUB_FRACCION"] : null,
                "descripcion" => $this->_stripAccents($this->_trimArray($producto["DESCRIPCION"])),
                "precioUnitario" => $producto["PRECIO_UNI"],
                "valorComercial" => $producto["VAL_COM"],
                "valorUsd" => (float) $producto["VAL_DLS"],
                "cantidadFactura" => (float) $producto["CANT_FAC"],
                "umc" => $producto["UMC"],
                "cantidadTarifa" => isset($producto["CANT_TAR"]) ? (float) $producto["CANT_TAR"] : null,
                "umt" => $producto["UMT"],
                "cantidadOma" => $producto["CAN_OMA"],
                "oma" => $producto["UMC_OMA"],
                "paisOrigen" => $producto["PAIORI"],
                "paisVendedor" => $producto["PAICOM"],
                "tlc" => (isset($producto["CERTLC"])) ? $producto["CERTLC"] : null,
                "marca" => isset($producto["MARCA"]) ? $producto["MARCA"] : null,
                "modelo" => isset($producto["MODELO"]) ? $producto["MODELO"] : null,
                "subModelo" => isset($producto["SUBMODELO"]) ? $producto["SUBMODELO"] : null,
                "numSerie" => isset($producto["NUMSERIE"]) ? $producto["NUMSERIE"] : null,
                "creado" => date("Y-m-d H:i:s"),
            );
            return $arr;
        } catch (Exception $ex) {

        }
    }
    
    protected function _cambiarFecha($fecha) {
        $exp = explode('/', $fecha);
        return date("Y-m-d H:i:s", strtotime($exp[2] . '-' . $exp[1] . '-' . $exp[0]));
    }

    protected function _insertProveedor($factura) {
        try {
            
            $arr = array(
                "idCliente" => (int) $this->_trafico->getIdCliente(),
                "clave" => isset($factura["PRO_CVE"]) ? trim($factura["PRO_CVE"]) : '',
                "tipoIdentificador" => isset($factura["PRO_IDEN"]) ? trim($factura["PRO_IDEN"]) : '',
                "identificador" => trim($factura["TAXID"]),
                "nombre" => trim($factura["NOM_PRO"]),
                "calle" => trim($factura["PRO_CALLE"]),
                "numExt" => trim($factura["PRO_NUME"]),
                "numInt" => trim($factura["PRO_NUMI"]),
                "colonia" => trim($factura["PRO_COLONIA"]),
                "localidad" => isset($factura["PRO_LOCALIDAD"]) ? trim($factura["PRO_LOCALIDAD"]) : '',
                "municipio" => isset($factura["PRO_MUN"]) ? trim($factura["PRO_MUN"]) : '',
                "ciudad" => trim($factura["PRO_PAIS"]),
                "estado" => trim($factura["PRO_EDO"]),
                "codigoPostal" => trim($factura["PRO_CP"]),
                "pais" => trim($factura["PRO_PAIS"]),
                "creado" => date("Y-m-d H:i:s"),
            );
            
            if (isset($arr)) {
                return $arr;
            }
            
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    protected function _insertFacturaDetalle($idFactura, $idPro, $factura) {
        try {

            $arr = array(
                "idFactura" => $idFactura,
                "idPro" => $idPro,
                "relFacturas" => 0,
                "consFactura" => 0,
                "numRemesa" => isset($factura["numRemesa"]) ? $factura["numRemesa"] : null,
                "numFactura" => $factura["NUM_FACTURA"],
                "fechaFactura" => $this->_cambiarFecha($factura["FECHA_FACTURA"]),
                "incoterm" => $factura["INCOTERM"],
                "observaciones" => isset($factura["OBSERVACIONES"]) ? $factura["OBSERVACIONES"] : null,
                "subdivision" => isset($factura["SUBDIVISION"]) ? $factura["SUBDIVISION"] : null,
                "ordenFactura" => isset($factura["ORDEN_FACTURA"]) ? $factura["ORDEN_FACTURA"] : null,
                "valorFacturaUsd" => $factura["VAL_DLS"],
                "valorFacturaMonExt" => $factura["VAL_EXT"],
                "divisa" => $factura["DIVISA"],
                "paisFactura" => $factura["PRO_PAIS"],
                "factorMonExt" => $factura["VAL_EQUI"],
                "certificadoOrigen" => isset($factura["CERT_ORIGEN"]) ? $factura["CERT_ORIGEN"] : null,
                "numExportador" => isset($factura["NUM_EXPORTADOR"]) ? $factura["NUM_EXPORTADOR"] : null,
            );
            
            if (isset($arr)) {
                return $arr;
            }
            
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    protected function _insertFactura($factura) {
        try {

            $arr = array(
                "idUsuario" => (int) $this->_idUsuario,
                "idTrafico" => (int) $this->_idTrafico,
                "relFacturas" => 0,
                "numFactura" => $factura["NUM_FACTURA"],
                "fechaFactura" => $this->_cambiarFecha($factura["FECHA_FACTURA"]),
                "incoterm"=> trim($factura["INCOTERM"]),
                "identificador" => trim($factura["TAXID"]),
                "cvePro" => trim($factura["PRO_CVE"]),
                "nombreProveedor" => trim($factura["NOM_PRO"]),
                "divisa" => trim($factura["DIVISA"]),
                "paisFactura" => trim($factura["PRO_PAIS"]),
                "valorDolares" => trim($factura["VAL_DLS"]),
                "valorMonExt" => trim($factura["VAL_EXT"]),
                "factorMonExt" => trim($factura["VAL_EQUI"]),
                "estatus" => 1,
                "creado" => date("Y-m-d H:i:s"),
            );
            
            if (isset($arr)) {
                return $arr;
            }
            
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _analizarProductos() {
        try {
            $highestRow = $this->_worksheet->getHighestRow();
            $highestColumn = $this->_worksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            for ($row = 2; $row <= $highestRow; ++$row) {
                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $this->_worksheet->getCellByColumnAndRow($col, $row);
                    if ($this->_worksheet->getCellByColumnAndRow($col, 1)->getValue() != '') {
                        $tmp[$this->_worksheet->getCellByColumnAndRow($col, 1)->getValue()] = preg_match('/=/', $cell->getValue()) ? $cell->getCalculatedValue() : $cell->getFormattedValue();
                    }
                }
                $tmp["NUM_FACTURA"] = trim($tmp["NUM_FACTURA"]);
                if (isset($this->_invoices[$tmp["NUM_FACTURA"]]["PRODUCTOS"]) && $tmp["NUM_FACTURA"] != "") {
                    $this->_invoices[$tmp["NUM_FACTURA"]]["PRODUCTOS"][] = $tmp;
                }
                unset($tmp);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _analizarFacturas() {
        try {
            $highestRow = $this->_worksheet->getHighestRow();
            $highestColumn = $this->_worksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            for ($row = 2; $row <= $highestRow; ++$row) {
                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $this->_worksheet->getCellByColumnAndRow($col, $row);
                    if ($this->_worksheet->getCellByColumnAndRow($col, 1)->getValue() != '') {
                        if (!preg_match("/fecha_/i", $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                            $tmp[$this->_worksheet->getCellByColumnAndRow($col, 1)->getValue()] = preg_match('/=/', $cell->getValue()) ? $cell->getCalculatedValue() : $cell->getFormattedValue();
                        } else {
                            $tmp[$this->_worksheet->getCellByColumnAndRow($col, 1)->getValue()] = $cell->getFormattedValue();
                            if ($tmp[$this->_worksheet->getCellByColumnAndRow($col, 1)->getValue()] == "") {
                                $tmp[$this->_worksheet->getCellByColumnAndRow($col, 1)->getValue()] = $cell->getValue();
                            }
                        }
                    }
                }
                $tmp["NUM_FACTURA"] = trim($tmp["NUM_FACTURA"]);
                if (!isset($this->_invoices[$tmp["NUM_FACTURA"]])) {
                    $tmp["PRODUCTOS"] = array();
                    $this->_invoices[$tmp["NUM_FACTURA"]] = $tmp;
                }
                unset($tmp);
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _stripAccents($string) {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's'
        );
        return strtr($string, $chars);
    }

}
