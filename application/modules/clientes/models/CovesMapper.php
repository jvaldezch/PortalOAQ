<?php

class Clientes_Model_CovesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_Coves();
    }

    public function getCoves($rfc) {
        try {
            $facturas = new Clientes_Model_FacturasMapper();
            $fact = $facturas->getFacturas($rfc);
            if (!empty($fact)) {
                $sql = $this->_db_table->select()
                        ->setIntegrityCheck(false)
                        ->from(array("s" => "vucem_solicitudes"), array("s.id", "s.relfact", "s.factura", "s.solicitud", "s.patente", "s.aduana", "s.pedimento", "s.referencia", "s.rfc", "s.usuario", "s.cove", "s.estatus", "s.respuesta_vu", "s.actualizado"))
                        ->joinLeft(array("f" => "vucem_facturas"), "s.id = f.idSolicitud AND f.Active = 1", array("idFact"))
                        ->where("f.CteRfc = '{$rfc}' OR f.ProTaxID = '{$rfc}'")
                        ->where("f.Active = 1 AND s.active = 1")
                        ->where("s.estatus = 2")
                        ->order("actualizado DESC");
                $stmt = $this->_db_table->fetchAll($sql);
                if ($stmt) {
                    return $stmt->toArray();
                }
                return null;
            }
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function getReporteCoves($rfc, $fechaIni, $fechaFin) {
        try {
            $facturas = new Clientes_Model_FacturasMapper();
            $fact = $facturas->getFacturas($rfc);
            if (!empty($fact)) {
                $sql = $this->_db_table->select()
                        ->setIntegrityCheck(false)
                        ->from(array("s" => "vucem_solicitudes"), array("s.id", "s.factura", "s.solicitud", "s.patente", "s.aduana", "s.pedimento", "s.referencia", "s.rfc", "s.usuario", "s.cove", "s.actualizado"))
                        ->joinLeft(array("f" => "vucem_facturas"), "s.id = f.idSolicitud AND f.Active = 1", array())
                        ->where("f.CteRfc = '{$rfc}' OR f.ProTaxID = '{$rfc}'")
                        ->where("f.Active = 1 AND s.active = 1")
                        ->where("s.estatus = 2")
                        ->where("s.actualizado >= '{$fechaIni}'")
                        ->where("s.actualizado <= '{$fechaFin}'")
                        ->order("actualizado DESC");
                $stmt = $this->_db_table->fetchAll($sql);
                if ($stmt) {
                    return $stmt->toArray();
                }
                return;
            }
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSolicitudPorId($id, $rfc) {
        try {
            $sql = $this->_db_table->select();
            $sql->from(array("s" => "vucem_solicitudes"), array("solicitud", "xml", "cove", "estatus", "uuid"))
                    ->where("s.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSolicitudPorCove($cove) {
        try {
            $sql = $this->_db_table->select();
            $sql->from(array("s" => "vucem_solicitudes"), array("solicitud", "xml", "cove", "estatus", "uuid"))
                    ->where("s.cove = ?", $cove);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
