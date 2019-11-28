<?php

class Vucem_Model_VucemFacturasMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemFacturas();
    }

    /**
     * Agrega una nueva factura para recrear el proceso del COVE y su impresión.
     * 
     * @param String $numSolicitud
     * @param String $fact
     * @param String $usuario
     * @return null
     */
    public function nuevaFactura($idSolicitud, $fact, $usuario, $manual = null) {
        try {
            $data = $fact;
            /** FIX PARA UTF8 * */
            $data["Consolidado"] = (isset($data["Consolidado"]) && $data["Consolidado"] == 'S') ? 1 : 0;
            $data["CteCalle"] = utf8_decode($fact["CteCalle"]);
            $data["CteNombre"] = $fact["CteNombre"];
            $data["CteColonia"] = $fact["CteColonia"];
            $data["CteLocalidad"] = isset($fact["CteLocalidad"]) ? $fact["CteLocalidad"] : null;
            $data["CteMun"] = $fact["CteMun"];
            $data["CteEdo"] = $fact["CteEdo"];
            $data["ProNombre"] = $fact["ProNombre"];
            $data["ProCalle"] = $fact["ProCalle"];
            $data["ProColonia"] = $fact["ProColonia"];
            $data["ProLocalidad"] = isset($fact["ProLocalidad"]) ? $fact["ProLocalidad"] : null;
            $data["ProMun"] = $fact["ProMun"];
            $data["ProEdo"] = $fact["ProEdo"];
            $data["NumParte"] = $fact["NumParte"];
            $data["Observaciones"] = $fact["Observaciones"];
            $data["Manual"] = isset($manual) ? 1 : 0;
            if (isset($data["id"])) {
                unset($data["id"]);
            }
            if (isset($data["firmante"])) {
                unset($data["firmante"]);
            }
            if (isset($data["figura"])) {
                unset($data["figura"]);
            }
            if (isset($data["enviar"])) {
                unset($data["enviar"]);
            }
            unset($data["Reenvio"]);
            unset($data["adenda"]);
            unset($data["CvePro"]);
            unset($data["CvePro"]);
            unset($data["CveCli"]);
            /*             * **************** */
            unset($data["Productos"]); // REMOVER EL ARRAY DE PRODUCTOS PARA EVITAR PROBLEMAS DE INSERCIÓN
            $data["idSolicitud"] = $idSolicitud;
            $data["Creado"] = date('Y-m-d H:i:s');
            $data["Usuario"] = $usuario;
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return ;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Agrega una nueva factura para recrear el proceso del COVE y su impresión.
     * 
     * @param String $numSolicitud
     * @param String $fact
     * @param String $usuario
     * @return null
     */
    public function nuevaFacturaRel($idSolicitud, $fact, $usuario, $manual = null) {
        try {
            $data = $fact;
            /** FIX PARA UTF8 * */
            $data["Consolidado"] = (isset($data["Consolidado"]) && $data["Consolidado"] == 'S') ? 1 : 0;
            $data["CteCalle"] = $fact["CteCalle"];
            $data["CteNombre"] = $fact["CteNombre"];
            $data["CteColonia"] = $fact["CteColonia"];
            $data["CteLocalidad"] = isset($fact["CteLocalidad"]) ? $fact["CteLocalidad"] : null;
            $data["CteMun"] = $fact["CteMun"];
            $data["CteEdo"] = $fact["CteEdo"];
            $data["ProNombre"] = $fact["ProNombre"];
            $data["ProCalle"] = $fact["ProCalle"];
            $data["ProColonia"] = $fact["ProColonia"];
            $data["ProLocalidad"] = isset($fact["ProLocalidad"]) ? $fact["ProLocalidad"] : null;
            $data["ProMun"] = $fact["ProMun"];
            $data["ProEdo"] = $fact["ProEdo"];
            $data["NumParte"] = $fact["NumParte"];
            $data["Observaciones"] = $fact["Observaciones"];
            $data["Manual"] = isset($manual) ? 1 : 0;
            $data["RelFact"] = 1;
            /** FIX PARA FACTURAS MANUALES * */
            if (isset($data["id"])) {
                unset($data["id"]);
            }
            if (isset($data["firmante"])) {
                unset($data["firmante"]);
            }
            if (isset($data["figura"])) {
                unset($data["figura"]);
            }
            unset($data["Reenvio"]);
            unset($data["adenda"]);
            unset($data["CvePro"]);
            unset($data["CvePro"]);
            unset($data["CveCli"]);
            /*             * **************** */
            unset($data["Productos"]); // REMOVER EL ARRAY DE PRODUCTOS PARA EVITAR PROBLEMAS DE INSERCIÓN
            $data["idSolicitud"] = $idSolicitud;
            $data["Creado"] = date('Y-m-d H:i:s');
            $data["Usuario"] = $usuario;
            $added = $this->_db_table->insert($data);
            if ($added) {
                return $added;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFactura($solicitud, $numFactura) {
        try {
            $sql = $this->_db_table->select();
            $sql->where("solicitud = ?", $solicitud)
                    ->where("NumFactura = ?", $numFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFacturaPorIdSolicitud($uuid) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idSolicitud = ?", $uuid);
            $stmt = $this->_db_table->fetchRow($sql);
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

    public function actualizarFactura($idSolicitud, $solicitud) {
        try {
            $data = array(
                'Solicitud' => $solicitud
            );
            $where = array(
                'idSolicitud = ?' => $idSolicitud,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function removerFactura($idSolicitud) {
        try {
            $data = array(
                'Active' => 0,
            );
            $where = array(
                'idSolicitud = ?' => $idSolicitud,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerIdFactura($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_facturas", array("id"))
                    ->where("idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["id"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerUuidFactura($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_facturas", array("idFact"))
                    ->where("idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["idFact"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function find($idFact) {
        try {
            $sql = $this->_db_table->select()
                    ->where('IdFact LIKE ?', $idFact);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getInvoiceData($uuid) {
        try {
            $mapper = new Vucem_Model_VucemProductosMapper();
            $sql = $this->_db_table->select()
                    ->where("IdFact = ?", $uuid);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                $data["PRODUCTOS"] = $mapper->obtainProducts($uuid);
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarNumSolicitud($idSolicitud, $solicitud) {
        try {
            $data = array(
                'Solicitud' => $solicitud,
            );
            $where = array(
                'idSolicitud = ?' => $idSolicitud,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
