<?php

class Trafico_Model_TraficoCvePedMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoCvePed();
    }

    public function buscarRegimen($cvePedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from("trafico_cveped", array("REGIMENI AS regimenImportacion", "REGIMENE AS regimenExportacion"))
                    ->where("clave = ?", $cvePedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function find(Trafico_Model_Table_TraficoCvePed $table) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("clave = ?", $table->getClave())
            );
            if (0 == count($stmt)) {
                return;
            }
            $table->setId($stmt->id);
            $table->setClave($stmt->clave);
            $table->setDescripcion($stmt->descripcion);
            $table->setIMP($stmt->IMP);
            $table->setADVAI($stmt->ADVAI);
            $table->setDTAAI($stmt->DTAAI);
            $table->setIVAAI($stmt->IVAAI);
            $table->setISANAI($stmt->ISANAI);
            $table->setIEPSAI($stmt->IEPSAI);
            $table->setCCAI($stmt->CCAI);
            $table->setP_DTAI($stmt->P_DTAI);
            $table->setDTAI($stmt->DTAI);
            $table->setIVAI($stmt->IVAI);
            $table->setCONSI($stmt->CONSI);
            $table->setEXP($stmt->EXP);
            $table->setADVAE($stmt->ADVAE);
            $table->setDTAAE($stmt->DTAAE);
            $table->setIVAAE($stmt->IVAAE);
            $table->setISANAE($stmt->ISANAE);
            $table->setIEPSAE($stmt->IEPSAE);
            $table->setCCAE($stmt->CCAE);
            $table->setP_DTAE($stmt->P_DTAE);
            $table->setDTAE($stmt->DTAE);
            $table->setIVAE($stmt->IVAE);
            $table->setCONSE($stmt->CONSE);
            $table->setCANDADOS($stmt->CANDADOS);
            $table->setTRANSITO($stmt->TRANSITO);
            $table->setPREVIOS($stmt->PREVIOS);
            $table->setEXTRACCION($stmt->EXTRACCION);
            $table->setSECTORIAL($stmt->SECTORIAL);
            $table->setREGIMENI($stmt->REGIMENI);
            $table->setREGIMENE($stmt->REGIMENE);
            $table->setACTUADTA($stmt->ACTUADTA);
            $table->setACTUAIVA($stmt->ACTUAIVA);
            $table->setACTUACCMT($stmt->ACTUACCMT);
            $table->setACTUAISIE($stmt->ACTUAISIE);
            $table->setIVA0IMP($stmt->IVA0IMP);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
