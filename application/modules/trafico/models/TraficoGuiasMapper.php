<?php

class Trafico_Model_TraficoGuiasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoGuias();
    }

    public function verificarGuia($idTrafico, $tipo, $guia) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("tipo = ?", $tipo)
                    ->where("guia = ?", $guia);
            $found = $this->_db_table->fetchRow($sql);
            if ($found) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarId($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $found = $this->_db_table->fetchRow($sql);
            if ($found) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarGuia($id, $idTransportista, $tipo, $guia, $idUsuario) {
        try {
            $data = array(
                "idTransportista" => $idTransportista,
                "tipo" => $tipo,
                "guia" => $guia,
                "idUsuarioModif" => $idUsuario,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "id = ?" => $id
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * @param $idTrafico
     * @param $tipo Tipo de guia
     * @param $guia Numero de guia
     * @param $idUsuario
     * @param null $idTransportista
     * @return bool
     * @throws Exception
     */
    public function agregarGuia($idTrafico, $tipo, $guia, $idUsuario, $idTransportista = null) {
        try {
            $data = array(
                "idTrafico" => $idTrafico,
                "idUsuario" => $idUsuario,
                "idTransportista" => isset($idTransportista) ? $idTransportista : null,
                "guia" => trim($guia),
                "tipo" => $tipo,
                "creado" => date("Y-m-d H:i:s"),
            );
            $inserted = $this->_db_table->insert($data);
            if ($inserted) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerGuias($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("g" => "trafico_guias"), array("*"))
                    ->joinLeft(array("t" => "trafico_transportistas"), "t.id = g.idTransportista", array("nombre"))
                    ->where("g.idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerGuia($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarGuia($guia) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("g" => "trafico_guias"), array(""))
                    ->joinInner(array("t" => "traficos"), "t.id = g.idTrafico", array("patente", "aduana", "pedimento", "referencia", "rfcCliente"))
                    ->where("REPLACE(g.guia, ' ', '') LIKE ?", "%" . $guia . "%");
            $stmt = $this->_db_table->fetchRow($sql);
            //echo $sql->assemble();
            if (count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarNumGuia($guia) {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), array("patente", "aduana", "pedimento", "referencia", "rfcCliente"))
                ->where("REPLACE(t.blGuia, ' ', '') LIKE ?", "%" . $guia . "%");
            $stmt = $this->_db_table->fetchRow($sql);
            if (count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function delete($idFactura) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $idFactura));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscar($search) {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("g" => "trafico_guias"), array("*"))
                ->joinLeft(array("t" => "traficos"), "g.idTrafico = t.id", array("id", "patente", "aduana", "referencia", "pedimento"))
                ->joinLeft(array("u" => "usuarios"), "g.idUsuario = u.id", array("usuario"))
                ->where("REPLACE(g.guia, ' ', '') LIKE ?", "%" . $search . "%");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
