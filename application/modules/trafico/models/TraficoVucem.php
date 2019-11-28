<?php

class Trafico_Model_TraficoVucem {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoVucem();
    }

    public function find(Trafico_Model_Table_TraficoVucem $t) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("idTrafico = ?", $t->getIdTrafico())
                            ->where("idFactura = ?", $t->getIdFactura())
                            ->where("numFactura = ?", $t->getNumFactura())
            );
            if (0 == count($stmt)) {
                return;
            }
            $t->setId($stmt->id);
            $t->setIdTrafico($stmt->idTrafico);
            $t->setIdFactura($stmt->idFactura);
            $t->setNumFactura($stmt->numFactura);
            $t->setNombreArchivo($stmt->nombreArchivo);
            $t->setTipoDocumento($stmt->tipoDocumento);
            $t->setDescripcionDocumento($stmt->descripcionDocumento);
            $t->setInstruccion($stmt->instruccion);
            $t->setSolicitud($stmt->solicitud);
            $t->setEdoc($stmt->edoc);
            $t->setEnviar($stmt->enviar);
            $t->setError($stmt->error);
            $t->setEnviado($stmt->enviado);
            $t->setRespuesta($stmt->respuesta);
            $t->setCreado($stmt->creado);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function save(Trafico_Model_Table_TraficoVucem $t) {
        try {
            $arr = array(
                "id" => $t->getid(),
                "idTrafico" => $t->getIdTrafico(),
                "idFactura" => $t->getIdFactura(),
                "numFactura" => $t->getNumFactura(),
                "nombreArchivo" => $t->getNombreArchivo(),
                "tipoDocumento" => $t->getTipoDocumento(),
                "descripcionDocumento" => $t->getDescripcionDocumento(),
                "instruccion" => $t->getInstruccion(),
                "numeroOperacion" => $t->getSolicitud(),
                "edocument" => $t->getEdoc(),
                "enviar" => $t->getEnviar(),
                "error" => $t->getError(),
                "enviado" => $t->getEnviado(),
                "respuesta" => $t->getRespuesta(),
                "creado" => $t->getCreado(),
            );
            if (null === ($id = $t->getId())) {
                $id = $this->_db_table->insert($arr);
                $t->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFactura($idFactura) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("idFactura = ?", $idFactura)
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($idVucem, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $idVucem));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarEdocument($edocument) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("edocument = ?", $edocument)
            );
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idTrafico, $idFactura, $numFactura) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("idTrafico = ?", $idTrafico)
                            ->where("idFactura = ?", $idFactura)
                            ->where("numFactura = ?", $numFactura)
            );
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function contarCoves($idTrafico) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->from($this->_db_table, array("count(*) AS cantidad"))
                            ->where("idTrafico = ?", $idTrafico)
                            ->where("edocument IS NOT NULL")
                            ->where("idFactura IS NOT NULL")
            );
            if ($stmt) {
                return $stmt->cantidad;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function contarEdocuments($idTrafico) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->from($this->_db_table, array("count(*) AS cantidad"))
                            ->where("idTrafico = ?", $idTrafico)
                            ->where("edocument IS NOT NULL")
                            ->where("idArchivo IS NOT NULL")
            );
            if ($stmt) {
                return $stmt->cantidad;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimasOperaciones($page, $rows) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "trafico_vucem"), array(
                        "*",
                    ))
                    ->joinLeft(array("a" => "trafico_sellos_agentes"), "a.id = r.idSelloAgente", array("patente AS selloPatente"))
                    ->joinLeft(array("c" => "trafico_sellos_clientes"), "c.id = r.idSelloCliente", array("rfc AS selloRfc"))
                    ->joinLeft(array("t" => "traficos"), "t.id = r.idTrafico", array("patente", "aduana", "pedimento", "referencia"))
                    ->order("r.enviado DESC");
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, $rows * ($page - 1));
            }
            return $sql;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
