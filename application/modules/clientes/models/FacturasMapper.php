<?php

class Clientes_Model_FacturasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_Facturas();
    }

    public function getFacturas($rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from('vucem_facturas', array('Solicitud'))
                    ->where('CteRfc = ?', $rfc)
                    ->orWhere('ProTaxID = ?', $rfc)
                    ->where('Active = 1');
            $result = $this->_db_table->fetchAll($select, array());
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = $item["Solicitud"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function verificarFactura($solicitud, $rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from('vucem_facturas', array('id'))
                    ->where('CteRfc = ?', $rfc)
                    ->orWhere('ProTaxID = ?', $rfc)
                    ->where('Solicitud = ?', $solicitud);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
