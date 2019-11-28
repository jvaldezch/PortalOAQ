<?php

class Trafico_Model_TraficoSolRepoMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoSolRepo();
    }

    protected function _throwException($message, $method, Exception $ex) {
        return $message . " at " . $method . " >> " . $ex->getMessage() . " line: " . $ex->getLine() . " info: " . $ex->getCode() . " trace: " . $ex->getTrace();
    }

    /**
     * 
     * @param Trafico_Model_Table_TraficoSolRepo $t
     * @return type
     * @throws Exception
     */
    public function save(Trafico_Model_Table_TraficoSolRepo $t) {
        try {
            $arr = array(
                "id" => $t->getId(),
                "idSolicitud" => $t->getIdSolicitud(),
                "idRepositorioConta" => $t->getIdRepositorioConta()
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_TraficoSolRepo $t
     * @return type
     * @throws Exception
     */
    public function update(Trafico_Model_Table_TraficoSolRepo $t) {
        try {
            $arr = array(
                "id" => $t->getId(),
                "idSolicitud" => $t->getIdSolicitud(),
                "idRepositorioConta" => $t->getIdRepositorioConta()
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $t->getId()));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param type $idSolicitud Id de la solicitud
     * @return array|boolean
     * @throws Exception
     */
    public function buscarPorSolicitud($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solrepo"), array("idSolicitud"))
                    ->joinLeft(array("r" => "repositorio_conta"), "s.idRepositorioConta = r.id", array("id", "nombreArchivo", "usuario", "creado"))
                    ->where("s.idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
