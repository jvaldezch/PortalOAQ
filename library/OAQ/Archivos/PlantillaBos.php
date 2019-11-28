<?php

require_once 'UUID.php';
require_once "PHPExcel/IOFactory.php";
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlantillaBos
 *
 * @author Jaime
 */
class OAQ_Archivos_PlantillaBos {

    protected $_filename;
    protected $_worksheet;
    protected $_objPHPExcel;
    protected $_config;

    function set_filename($_filename) {
        $this->_filename = $_filename;
    }

    public function __construct($filename) {
        $this->_filename = $filename;
        if (file_exists($filename)) {
            $this->_objPHPExcel = PHPExcel_IOFactory::load($this->_filename);
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        }
    }

    public function analizar($idCliente) {
        try {
            $this->_objPHPExcel->setActiveSheetIndex(0);
            $this->_worksheet = $this->_objPHPExcel->getActiveSheet();

            $tmp = array();

            $highestRow = $this->_worksheet->getHighestRow();
            $highestColumn = $this->_worksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

            $rows = array();
            $headers = array();

            $cell = $this->_worksheet->getCellByColumnAndRow(0, 1); // check if first sheet has data

            if (preg_match('/Delivery/', $cell->getValue())) {

                $headers[0] = "delivery";

                for ($col = 1; $col < $highestColumnIndex; ++$col) {
                    if (preg_match('/Bill.Doc./i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "billDocument";
                    }
                    if (preg_match('/Bill(.*)Date/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "billDate";
                    }
                    if (preg_match('/Material/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "material";
                    }
                    if (preg_match('/Customer material/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "customerMaterial";
                    }
                    if (preg_match('/^Description$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "description";
                    }
                    if (preg_match('/Bill.qty/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "billQuantity";
                    }
                    if (preg_match('/SU/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "su";
                    }
                    if (preg_match('/Unit price/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "unitPrice";
                    }
                    if (preg_match('/Net/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "net";
                    }
                    if (preg_match('/^Curr.$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "currency";
                    }
                    if (preg_match('/^WUn$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "wun";
                    }
                    if (preg_match('/Name$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "providerName";
                    }
                    if (preg_match('/PO number/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "poNumber";
                    }
                    if (preg_match('/Sales Doc./i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "salesDocument";
                    }
                    if (preg_match('/Gross/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "gross";
                    }
                    if (preg_match('/Wun/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "unit";
                    }
                    if (preg_match('/SOrg./i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "sorg";
                    }
                    if (preg_match('/DstC/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "dstCountry";
                    }
                    if (preg_match('/Item/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "item";
                    }
                    if (preg_match('/City/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "city";
                    }
                    if (preg_match('/Created by/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "createdBy";
                    }
                    if (preg_match('/^On$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "created";
                    }
                    if (preg_match('/Bill to/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "billTo";
                    }
                    if (preg_match('/Address/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "address";
                    }
                    if (preg_match('/BillT$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "billt";
                    }
                    if (preg_match('/Name(.*)2$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "name";
                    }
                    if (preg_match('/Street/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "street";
                    }
                    if (preg_match('/House No./i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "houseNumber";
                    }
                    if (preg_match('/^Po Box$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "poBox";
                    }
                    if (preg_match('/^Po Box Cty$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "poBoxCountry";
                    }
                    if (preg_match('/^Postl Code$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "postalCode";
                    }
                    if (preg_match('/^Cty$/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "country";
                    }
                    if (preg_match('/Reference/i', $this->_worksheet->getCellByColumnAndRow($col, 1)->getValue())) {
                        $headers[$col] = "reference";
                    }
                }
            }
            //Zend_Debug::dump($headers);
            
            $data = array();

            if (isset($headers) && !empty($headers)) {
                for ($row = 2; $row <= $highestRow; ++$row) {
                    $tmp = array();
                    $tmp["idCliente"] = $idCliente;
                    foreach ($headers as $key => $value) {
                        if (in_array($value, array("created", "billDate"))) {
                            $tmp[$value] = $this->changeDate($this->_worksheet->getCellByColumnAndRow($key, $row)->getValue());
                        } else {
                            $tmp[$value] = $this->cleanValue($this->_worksheet->getCellByColumnAndRow($key, $row)->getValue());
                        }
                    }
                    $data[] = $tmp;
                }
            }
            //Zend_Debug::dump($data);
            //die();
            
            if (!empty($data)) {
                $mppr = new Operaciones_Model_CartaInstruccionesPartes();                
                foreach ($data as $item) {                    
                    if (!($mppr->verificar($item["delivery"], $item["billDocument"]))) {                        
                        $mppr->agregar($item);
                    }                    
                }                
            }

            return true;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function changeDate($value) {
        $d = explode('.', $value);
        return $d[2] . '-' . $d[1] . '-' . $d[0];
    }

    public function cleanValue($value) {
        $val = preg_replace('/\s\s+/', ' ', trim(mb_strtoupper($value)));
        if ((string) $val === "") {
            return null;
        }
        return $val;
    }

}
