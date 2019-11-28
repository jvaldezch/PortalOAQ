<?php

class Vucem_Model_VucemProductosMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemProductos();
    }

    public function nuevoProducto($idFactura, $factId, $patente, $aduana, $pedimento, $referencia, $prod, $usuario) {
        try {
            if (isset($prod["id"])) {
                unset($prod["id"]);
            }
            $data = $prod;
            /** FIX PARA UTF8 * */
            $data["DESC1"] = $prod["DESC1"];
            $data["DESC_COVE"] = $prod["DESC_COVE"];
            /*             * **************** */
            $data["IDFACTURA"] = $idFactura;
            //$data["SOLICITUD"] = $solicitud;
            $data["PATENTE"] = $patente;
            $data["ADUANA"] = $aduana;
            $data["REFERENCIA"] = $referencia;
            $data["PEDIMENTO"] = $pedimento;
            $data["ID_FACT"] = $factId;
            $data["CREADO"] = date('Y-m-d H:i:s');
            $data["USUARIO"] = $usuario;

            $data["VALCEQ"] = ($prod["VALCEQ"] == '') ? 0 : $prod["VALCEQ"];
            $data["FACTAJU"] = ($prod["FACTAJU"] == '') ? 0 : $prod["FACTAJU"];
            $data["VALMN"] = ($prod["VALMN"] == '') ? 0 : $prod["VALMN"];
            $data["CAN_OMA"] = ($prod["CAN_OMA"] == '') ? 0 : $prod["CAN_OMA"];

            $added = $this->_db_table->insert($data);
            if ($added) {
                return $added;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }
    
    public function obtainProducts($idfact) {
        try {
            $select = $this->_db_table->select()
                    ->where("ID_FACT = ?", $idfact);
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function obtenerProductos($idfact, $solicitud = null) {
        try {
            $select = $this->_db_table->select();
            $select->where("IDFACTURA = ?", $idfact);
            if (isset($solicitud)) {
                $select->where('solicitud = ?', $solicitud);
            }
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function actualizarProducto($idFactura, $solicitud) {
        try {
            $data = array(
                'SOLICITUD' => $solicitud,
            );
            $where = array(
                'ID_FACT = ?' => $idFactura,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function removerProductos($idFact) {
        try {
            $data = array(
                'ACTIVE' => 0,
            );
            $where = array(
                'IDFACTURA = ?' => $idFact,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function actualizarNumSolicitud($idFact, $solicitud) {
        try {
            $data = array(
                'SOLICITUD' => $solicitud,
            );
            $where = array(
                'IDFACTURA = ?' => $idFact,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
