<?php

class Automatizacion_Model_CuentasGastosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_CuentasGastos();
    }

    public function checkFor($referencia, $patente, $aduana, $folio = null) {
        try {
            $select = $this->_db_table->select();
            $select->where('referencia = ?', $referencia)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('folio = ?', $folio);
            $result = $this->_db_table->fetchRow($select);
            if (!$result) {
                return null;
            }
            return true;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }
    
    public function checkForInvoiceXml($referencia, $patente, $aduana, $nomArchivo, $folio = null) {
        try {
            $select = $this->_db_table->select();
            $select->where('referencia = ?', $referencia)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('nom_archivo = ?', $nomArchivo)
                    ->where('folio = ?', $folio);
            $result = $this->_db_table->fetchRow($select);
            if (!$result) {
                return null;
            }
            return true;
        } catch (Exception $e) {
            throw new Exception("<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }
    
    public function checkForFiles($referencia, $patente, $aduana, $folio = null) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array('nom_archivo'))
                    ->where('referencia = ?', $referencia)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('folio = ?', $folio);
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return false;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function addNewInvoiceDocument($data) {
        try {
            $data['creado'] = date('Y-m-d H:i:s');
            $this->_db_table->insert($data);
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function checkForPdf($referencia, $patente, $aduana, $folio = null) {
        try {
            $select = $this->_db_table->select();
            $select->where("referencia = ?", $referencia)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("folio = ?", $folio)
                    ->where("nom_archivo LIKE '%.pdf'");
            $result = $this->_db_table->fetchRow($select);
            if (!$result) {
                return null;
            }
            return true;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

}
