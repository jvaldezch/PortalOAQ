<?php

class Vucem_Model_VucemTmpProductosMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemTmpProductos();
    }

    public function verify($idFact, $idProd, $usuario) {
        try {
            $select = $this->_db_table->select()
                    ->where("ID_FACT = ?", $idFact)
                    ->where("ID_PROD = ?", $idProd)
                    ->where("USUARIO = ?", $usuario);
            if (($this->_db_table->fetchRow($select))) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function save(Vucem_Model_Table_TmpProductos $table) {
        try {
            $arr = array(
                'id' => $table->getId(),
                'IDFACTURA' => $table->getIDFACTURA(),
                'ID_FACT' => $table->getID_FACT(),
                'ID_PROD' => $table->getID_PROD(),
                'PATENTE' => $table->getPATENTE(),
                'ADUANA' => $table->getADUANA(),
                'PEDIMENTO' => $table->getPEDIMENTO(),
                'REFERENCIA' => $table->getREFERENCIA(),
                'SUB' => $table->getSUB(),
                'ORDEN' => $table->getORDEN(),
                'CODIGO' => $table->getCODIGO(),
                'SUBFRA' => $table->getSUBFRA(),
                'DESC1' => $table->getDESC1(),
                'PREUNI' => $table->getPREUNI(),
                'VALCOM' => $table->getVALCOM(),
                'MONVAL' => $table->getMONVAL(),
                'VALCEQ' => $table->getVALCEQ(),
                'VALMN' => $table->getVALMN(),
                'VALDLS' => $table->getVALDLS(),
                'CANTFAC' => $table->getCANTFAC(),
                'CANTTAR' => $table->getCANTTAR(),
                'UMC' => $table->getUMC(),
                'UMT' => $table->getUMT(),
                'PAIORI' => $table->getPAIORI(),
                'PAICOM' => $table->getPAICOM(),
                'FACTAJU' => $table->getFACTAJU(),
                'CERTLC' => $table->getCERTLC(),
                'PARTE' => $table->getPARTE(),
                'CAN_OMA' => $table->getCAN_OMA(),
                'UMC_OMA' => $table->getUMC_OMA(),
                'DESC_COVE' => $table->getDESC_COVE(),
                'OBS' => $table->getOBS(),
                'MARCA' => $table->getMARCA(),
                'MODELO' => $table->getMODELO(),
                'SUBMODELO' => $table->getSUBMODELO(),
                'NUMSERIE' => $table->getNUMSERIE(),
                'CREADO' => $table->getCREADO(),
                'MODIFICADO' => $table->getMODIFICADO(),
                'USUARIO' => $table->getUSUARIO(),
                'ACTIVE' => $table->getACTIVE(),
            );
            if (null === ($id = $table->getId())) {
                $id = $this->_db_table->insert($arr);
                $table->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }            
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevoProducto($idFactura, $factId, $patente, $aduana, $pedimento, $referencia, $prod, $usuario) {
        try {            
            if (isset($prod["id"])) {
                unset($prod["id"]);
            }
            $data = $prod;
            $data["DESC1"] = isset($prod["DESC1"]) ? $prod["DESC1"] : isset($prod["DESC_COVE"]) ? $prod["DESC_COVE"] : null;
            $data["DESC_COVE"] = isset($prod["DESC_COVE"]) ? $prod["DESC_COVE"] : null;
            $data["IDFACTURA"] = $idFactura;
            if (isset($data["SOLICITUD"])) {
                unset($data["SOLICITUD"]);
            }
            $data["PATENTE"] = $patente;
            $data["ADUANA"] = $aduana;
            $data["REFERENCIA"] = $referencia;
            $data["PEDIMENTO"] = $pedimento;
            $data["ID_FACT"] = $factId;
            $data["CREADO"] = date("Y-m-d H:i:s");
            $data["USUARIO"] = $usuario;
            $data["VALCEQ"] = ($prod["VALCEQ"] == "") ? 0 : $prod["VALCEQ"];
            $data["FACTAJU"] = (isset($prod["FACTAJU"]) && $prod["FACTAJU"] == "") ? $prod["FACTAJU"] : 0;
            if (!isset($data["VALMN"])) {
                $data["VALMN"] = 0;
            } else {
                $data["VALMN"] = $prod["VALMN"];
            }
            $data["CAN_OMA"] = ($prod["CAN_OMA"] == "") ? 0 : $prod["CANTFAC"];
            $added = $this->_db_table->insert($data);
            if ($added) {
                return $added;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerProductos($idfact, $usuario = null) {
        try {
            $select = $this->_db_table->select();
            $select->where("ID_FACT = ?", $idfact)
                    ->order("ORDEN ASC");
            if (isset($usuario)) {
                $select->where("USUARIO = ?", $usuario);
            }
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerProductosId($idfact, $usuario = null) {
        try {
            $select = $this->_db_table->select();
            $select->where("IDFACTURA = ?", $idfact)
                    ->order("ORDEN ASC");
            if (isset($usuario)) {
                $select->where("USUARIO = ?", $usuario);
            }
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerProducto($idfact, $idprod, $usuario) {
        try {
            $select = $this->_db_table->select();
            $select->where("ID_FACT = ?", $idfact)
                    ->where("ID_PROD = ?", $idprod)
                    ->where("USUARIO = ?", $usuario);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarDetalleProducto($idfact, $idprod, $usuario, $data) {
        try {
            $data["CAN_OMA"] = $data["CANTFAC"];
            if (isset($data["ID_FACT"])) {
                unset($data["ID_FACT"]);
            }
            if (isset($data["ID_PROD"])) {
                unset($data["ID_PROD"]);
            }
            $where = array(
                "ID_FACT = ?" => $idfact,
                "ID_PROD = ?" => $idprod,
                "USUARIO = ?" => $usuario,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarProductos($id, $IdFact, $usuario) {
        try {
            $where = array(
                "IDFACTURA = ?" => $id,
                "ID_FACT = ?" => $IdFact,
                "USUARIO = ?" => $usuario,
            );
            if (($this->_db_table->delete($where))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarProductosId($id, $usuario) {
        try {
            $where = array(
                "IDFACTURA = ?" => $id,
                "USUARIO = ?" => $usuario,
            );
            if (($this->_db_table->delete($where))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarProducto($IdFact, $IdProd, $usuario) {
        try {
            $where = array(
                "ID_FACT = ?" => $IdFact,
                "ID_PROD = ?" => $IdProd,
                "USUARIO = ?" => $usuario,
            );
            if (($this->_db_table->delete($where))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
