<?php

class Trafico_Model_TraficoSolDetalleMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoSolDetalle();
    }

    /**
     * 
     * @param array $data
     * @return boolean
     * @throws Exception
     */
    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $idSolicitud
     * @return boolean
     * @throws Exception
     */
    public function buscar($idSolicitud) {
        try {
            $sql = $this->_db_table->select(array("id"))
                    ->where("idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["id"];
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param array $arr
     * @return boolean
     * @throws Exception
     */
    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * Obtener el detalle de la solicitud.
     * 
     * @param int $idSolicitud
     * @return array|boolean
     * @throws Exception
     */
    public function obtener($idSolicitud) {
        try {
            $sql = $this->_db_table->select(array("*"))
                    ->where("idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * Verifica si tiene detalle.
     * 
     * @param int $idSolicitud
     * @return array|boolean
     * @throws Exception
     */
    public function tieneDetalle($idSolicitud) {
        try {
            $sql = $this->_db_table->select(array("id"))
                    ->where("idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->_db_table->delete(array("idSolicitud = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function find(Trafico_Model_DbTable_TraficoSolDetalle $tbl) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $tbl->getId())
            );
            if (0 == count($stmt)) {
                return;
            }
            $tbl->setId($stmt->id);
            $tbl->setIdSolicitud($stmt->idSolicitud);
            $tbl->setIdAduana($stmt->idAduana);
            $tbl->setCvePed($stmt->cvePed);
            $tbl->setFechaArribo($stmt->fechaArribo);
            $tbl->setFechaAlmacenaje($stmt->fechaAlmacenaje);
            $tbl->setFechaEta($stmt->fechaEta);
            $tbl->setTipoFacturacion($stmt->tipoFacturacion);
            $tbl->setTipoCarga($stmt->tipoCarga);
            $tbl->setBl($stmt->bl);
            $tbl->setPeso($stmt->peso);
            $tbl->setNumFactura($stmt->numFactura);
            $tbl->setValorMercancia($stmt->valorMercancia);
            $tbl->setPeca($stmt->peca);
            $tbl->setBanco($stmt->banco);
            $tbl->setAlmacen($stmt->almacen);
            $tbl->setMercancia($stmt->mercancia);
            $tbl->setCreado($stmt->creado);
            $tbl->setActualizado($stmt->actualizado);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $ex->getMessage());
        }
    }

    public function save(Trafico_Model_DbTable_TraficoSolDetalle $tbl) {
        try {
            $data = array(
                "id" => $tbl->getId(),
                "idSolicitud" => $tbl->getIdSolicitud(),
                "idAduana" => $tbl->getIdAduana(),
                "cvePed" => $tbl->getCvePed(),
                "fechaArribo" => $tbl->getFechaArribo(),
                "fechaAlmacenaje" => $tbl->getFechaAlmacenaje(),
                "fechaEta" => $tbl->getFechaEta(),
                "tipoFacturacion" => $tbl->getTipoFacturacion(),
                "tipoCarga" => $tbl->getTipoCarga(),
                "bl" => $tbl->getBl(),
                "peso" => $tbl->getPeso(),
                "numFactura" => $tbl->getNumFactura(),
                "valorMercancia" => $tbl->getValorMercancia(),
                "peca" => $tbl->getPeca(),
                "banco" => $tbl->getBanco(),
                "almacen" => $tbl->getAlmacen(),
                "mercancia" => $tbl->getMercancia(),
                "creado" => $tbl->getCreado(),
                "actualizado" => $tbl->getActualizado(),
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $ex->getMessage());
        }
    }

}
