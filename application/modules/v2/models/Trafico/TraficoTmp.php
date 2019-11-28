<?php

class V2_Model_Trafico_TraficoTmp {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new V2_Model_Trafico_DbTable_TraficoTmp();
    }

    public function guardar(V2_Model_Trafico_Table_TraficoTmp $t) {
        try {
            $arr = array(
                "idAduana" => $t->getIdAduana(),
                "idCliente" => $t->getIdCliente(),
                "idUsuario" => $t->getIdUsuario(),
                "pedimento" => $t->getPedimento(),
                "referencia" => $t->getReferencia(),
                "cvePedimento" => $t->getCvePedimento(),
                "tipoOperacion" => $t->getTipoOperacion(),
                "rectificacion" => $t->getRectificacion(),
                "consolidado" => $t->getConsolidado(),
                "creado" => $t->getCreado(),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function referenciasTemporales($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario)
                    ->order("creado DESC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
