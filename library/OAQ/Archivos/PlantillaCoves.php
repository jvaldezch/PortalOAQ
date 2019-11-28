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
class OAQ_Archivos_PlantillaCoves {

    protected $_filename;
    protected $_worksheet;
    protected $_invoices = array();
    protected $_products = array();
    protected $_objPHPExcel;
    protected $_mpprCust;
    protected $_vucem;
    protected $_config;
    protected $_tmpFact;
    protected $_tmpProd;
    protected $_solicitante;
    protected $_tipoFigura;
    protected $_patente;
    protected $_aduana;
    protected $_usuario;

    function get_filename() {
        return $this->_filename;
    }

    function set_filename($_filename) {
        $this->_filename = $_filename;
    }

    function set_solicitante($_solicitante) {
        $this->_solicitante = $_solicitante;
    }

    function set_tipoFigura($_tipoFigura) {
        $this->_tipoFigura = $_tipoFigura;
    }

    function set_patente($_patente) {
        $this->_patente = $_patente;
    }

    function set_aduana($_aduana) {
        $this->_aduana = $_aduana;
    }

    function set_usuario($_usuario) {
        $this->_usuario = $_usuario;
    }

    public function __construct($filename) {
        $this->_filename = $filename;
        if (file_exists($filename)) {
            $this->_objPHPExcel = PHPExcel_IOFactory::load($this->_filename);
            $this->_mpprCust = new Vucem_Model_VucemClientesMapper();
            $this->_vucem = new OAQ_VucemEnh();
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
            $this->_tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            $this->_tmpProd = new Vucem_Model_VucemTmpProductosMapper();
        }
    }

    public function analizar() {
        try {
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
                    $arr = $this->_insertFactura($factura);
                    if (!empty($arr)) {
                        if (($id = $this->_tmpFact->verificar($arr["NumFactura"], $this->_usuario))) {
                            $this->_tmpFact->borrarFacturaId($id, $this->_usuario);
                            unset($id);
                        }
                        if(($id = $this->_tmpFact->nuevaFactura($this->_solicitante, $this->_tipoFigura, $this->_patente, $this->_aduana, $arr, $this->_usuario, isset($arr["Manual"]) ? 1 : null))) {
                            if (!empty($factura["PRODUCTOS"])) {
                                foreach ($factura["PRODUCTOS"] as $producto) {
                                    $arrp = $this->_insertProducto($producto);
                                    if (!empty($arrp)) {
                                        $this->_tmpProd->nuevoProducto($id, $arr["IdFact"], $this->_patente, $this->_aduana, $arr["Pedimento"], $arr["Referencia"], $arrp, $this->_usuario);
                                    }
                                }
                            }
                        }
                    }
                }
                return true;
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

    protected function _insertProducto($producto) {
        $arr["ID_PROD"] = $this->_getUuid($producto["NUM_FACTURA"] . $producto["PARTE"] . $producto["FRACCION"] . microtime());
        $arr["CODIGO"] = $producto["FRACCION"];
        $arr["PARTE"] = $this->_trimArray($producto["PARTE"]);
        $arr["DESC_COVE"] = $this->_stripAccents($this->_trimArray($producto["DESCRIPCION"]));
        $arr["PAIORI"] = $producto["PAIORI"];
        $arr["PAICOM"] = $producto["PAICOM"];
        $arr["SUB"] = (isset($producto["SUBDIVISION"])) ? $producto["SUBDIVISION"] : null;
        $arr["CERTLC"] = (isset($producto["CERTLC"])) ? $producto["CERTLC"] : null;
        $arr["PREUNI"] = $producto["PRECIO_UNI"];
        $arr["VALCOM"] = $producto["VAL_COM"];
        $arr["MONVAL"] = $producto["MONEDA"];
        $arr["VALCEQ"] = $producto["VAL_EQUI"];
        $arr["FACTAJU"] = (isset($producto["FAC_AJU"])) ? (float) $producto["FAC_AJU"] : null;
        $arr["VALMN"] = (float) $producto["VAL_MXN"];
        $arr["VALDLS"] = (float) $producto["VAL_DLS"];
        $arr["UMC"] = $producto["UMC"];
        $arr["UMT"] = $producto["UMT"];
        $arr["CANTFAC"] = (float) $producto["CANT_FAC"];
        $arr["CAN_OMA"] = $producto["CAN_OMA"];
        $arr["UMC_OMA"] = $producto["UMC_OMA"];
        $arr["MARCA"] = isset($producto["MARCA"]) ? $producto["MARCA"] : null;
        $arr["MODELO"] = isset($producto["MODELO"]) ? $producto["MODELO"] : null;
        $arr["SUBMODELO"] = isset($producto["SUBMODELO"]) ? $producto["SUBMODELO"] : null;
        $arr["NUMSERIE"] = isset($producto["NUMSERIE"]) ? $producto["NUMSERIE"] : null;
        return $arr;
    }
    
    protected function _cambiarFecha($fecha) {
        $exp = explode('/', $fecha);
        return date("Y-m-d H:i:s", strtotime($exp[2] . '-' . $exp[1] . '-' . $exp[0]));
    }

    protected function _insertFactura($factura) {
        try {
            if (!isset($factura["RFC"])) {
                throw new Exception("No se establecio RFC.");
            }
            if ($factura["RFC"] == "") {
                throw new Exception("No se puede leer RFC.");
            }
            $cliente = $this->_mpprCust->verificar(trim($factura["RFC"]));
            if (!empty($cliente)) {
                $arr["NumFactura"] = $factura["NUM_FACTURA"];
                $arr["Manual"] = ($factura["TIPO_OP"] == 'TOCE.IMP') ? 1 : 0;
                $arr["IdFact"] = $this->_getUuid($factura["PATENTE"] . $factura["PEDIMENTO"] . $factura["ADUANA"] . $factura["NUM_FACTURA"] . $factura["NUM_FACTURA"] . time());
                $arr["CertificadoOrigen"] = $factura["CERT_ORIGEN"];
                $arr["Subdivision"] = $factura["SUBDIVISION"];
                $arr["NumExportador"] = $factura["NUM_EXPORTADOR"];
                $arr["TipoOperacion"] = $factura["TIPO_OP"];
                $arr["Patente"] = (int) $factura["PATENTE"];
                $arr["Aduana"] = (int) $factura["ADUANA"];
                $arr["Pedimento"] = (int) $factura["PEDIMENTO"];
                $arr["Referencia"] = $factura["REFERENCIA"];
                $arr["FechaFactura"] = $this->_cambiarFecha($factura["FECHA_FACTURA"]);
                $arr["Observaciones"] = "";
                $arr["ProIden"] = $this->_vucem->tipoIdentificador($factura["TAXID"], $factura["PRO_PAIS"]);
                $arr["ProTaxID"] = $factura["TAXID"];
                $arr["CvePro"] = "PLAN";
                $arr["ProNombre"] = trim($factura["NOM_PRO"]);
                $arr["ProCalle"] = trim($factura["PRO_CALLE"]);
                $arr["ProNumExt"] = trim($factura["PRO_NUME"]);
                $arr["ProNumInt"] = trim($factura["PRO_NUMI"]);
                $arr["ProColonia"] = trim($factura["PRO_COLONIA"]);
                $arr["ProLocalidad"] = isset($factura["PRO_LOCALIDAD"]) ? trim($factura["PRO_LOCALIDAD"]) : null;
                $arr["ProCP"] = $factura["PRO_CP"];
                $arr["ProMun"] = trim($factura["PRO_MUN"]);
                $arr["ProEdo"] = trim($factura["PRO_EDO"]);
                $arr["ProPais"] = trim($factura["PRO_PAIS"]);
                $arr["CteIden"] = trim($cliente["identificador"]);
                $arr["CteRfc"] = $cliente["rfc"];
                $arr["CteNombre"] = $cliente["razon_soc"];
                $arr["CteCalle"] = $cliente["calle"];
                $arr["CteNumExt"] = $cliente["numext"];
                $arr["CteNumInt"] = $cliente["numint"];
                $arr["CteColonia"] = $cliente["colonia"];
                $arr["CteLocalidad"] = $cliente["localidad"];
                $arr["CteCP"] = $cliente["cp"];
                $arr["CteMun"] = $cliente["municipio"];
                $arr["CteEdo"] = $cliente["estado"];
                $arr["CtePais"] = $cliente["pais"];
                $arr["Observaciones"] = isset($factura["OBSERVACIONES"]) ? $factura["OBSERVACIONES"] : null;
                $arr["FactorEquivalencia"] = isset($factura["FACTOR_AJUSTE"]) ? $factura["FACTOR_AJUSTE"] : null;
            }
            if (isset($arr)) {
                return $arr;
            }
            throw new Exception("No se encuentra cliente con RFC " . $factura["RFC"] . " en la base de datos de VUCEM. Favor de solicitar ayuda al equipo de Comercialización para colocar los datos de VUCEM correctamente.");
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _analizarProductos() {
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
    }

    protected function _analizarFacturas() {
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
            if (!isset($this->_invoices[$tmp["NUM_FACTURA"]]) && $tmp["RFC"] != "") {
                $tmp["PRODUCTOS"] = array();
                $this->_invoices[$tmp["NUM_FACTURA"]] = $tmp;
            }
            unset($tmp);
        }
        return;
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
