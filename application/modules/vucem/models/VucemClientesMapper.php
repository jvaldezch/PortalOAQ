<?php

class Vucem_Model_VucemClientesMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemClientes();
    }

    public function getCustomers($patente, $aduana, $rfc = null) {
        try {
            $select = $this->_db_table->select()
                    ->from("vucem_clientes", array("cvecte", "patente", "aduana", "rfc", "razon_soc"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->order("razon_soc ASC");

            if (isset($rfc)) {
                $select->where("rfc_agente = ?", $rfc);
            }
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function detailCustomer($patente, $aduana, $cvecli) {
        try {
            $select = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("cvecte = ?", $cvecli);

            if (($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function detalleCliente($patente, $aduana, $rfcCte) {
        try {
            $select = $this->_db_table->select()
                    ->where("patente = ?", (int) $patente)
                    ->where("aduana = ?", (int) $aduana)
                    ->where("rfc = ?", $rfcCte);
            if (($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function detalleClienteRfc($rfcCte) {
        try {
            $select = $this->_db_table->select()
                    ->where("rfc = ?", $rfcCte);
            if (($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->where("rfc = ?", $rfc);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function detalleClientePorRrfc($patente, $aduana, $rfcCte) {
        try {
            $select = $this->_db_table->select()
                    ->where("patente = ?", (int) $patente)
                    ->where("aduana = ?", (int) $aduana)
                    ->where("rfc = ?", $rfcCte);
            if (($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function detalleClienteCove($patente, $aduana, $rfcCte, $tipoOpe) {
        try {
            $select = $this->_db_table->select()
                    ->where("patente = ?", (int) $patente)
                    ->where("aduana = ?", (int) $aduana)
                    ->where("rfc = ?", $rfcCte);
            if (($result = $this->_db_table->fetchRow($select))) {
                $data = $result->toArray();
                if ($tipoOpe == "TOCE.IMP") {
                    return array(
                        "CteIden" => $data["identificador"],
                        "CveCli" => $data["cvecte"],
                        "CteNombre" => $data["razon_soc"],
                        "CteRfc" => $data["rfc"],
                        "CteCalle" => $data["calle"],
                        "CteNumExt" => $data["numext"],
                        "CteNumInt" => $data["numint"],
                        "CteColonia" => $data["colonia"],
                        "CteLocalidad" => $data["localidad"],
                        "CteCP" => $data["cp"],
                        "CteMun" => $data["municipio"],
                        "CteEdo" => $data["estado"],
                        "CtePais" => $data["pais"],
                    );
                } else {
                    return array(
                        "ProIden" => $data["identificador"],
                        "ProNombre" => $data["razon_soc"],
                        "ProTaxID" => $data["rfc"],
                        "ProCalle" => $data["calle"],
                        "ProNumExt" => $data["numext"],
                        "ProNumInt" => $data["numint"],
                        "ProColonia" => $data["colonia"],
                        "ProLocalidad" => $data["localidad"],
                        "ProCP" => $data["cp"],
                        "ProMun" => $data["municipio"],
                        "ProEdo" => $data["estado"],
                        "ProPais" => $data["pais"],
                    );
                }
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function datosCliente($rfcCte) {
        try {
            $select = $this->_db_table->select()
                    ->where("rfc = ?", $rfcCte);
            if (($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getLastCustomer($firmId) {
        try {
            $select = $this->_db_table->select()
                    ->from("vucem_clientes", array("cvecte"))
                    ->where("cvecte LIKE ?", str_pad($firmId, 3, "0", STR_PAD_LEFT) . "%")
                    ->order("id DESC");

            if (($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarCveCte($newCteId, $patente, $aduana) {
        try {
            $select = $this->_db_table->select()->where("cvecte = ?", $newCteId)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            if (($result = $this->_db_table->fetchRow($select))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarRfc($rfc, $firmId) {
        try {
            $select = $this->_db_table->select()
                    ->where("rfc = ?", $rfc)
                    ->where("cvecte LIKE ?", str_pad($firmId, 3, "0", STR_PAD_LEFT) . "%");
            if (($result = $this->_db_table->fetchRow($select))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarCliente($patente, $aduana, $rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("cvecte"))
                    ->where("rfc = ?", $rfc)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            if (($result = $this->_db_table->fetchRow($select))) {
                $data = $result->toArray();
                return $data["cvecte"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarNuevoCliente($patente, $aduana, $cvecte, $identificador, $rfc, $razon_soc, $calle, $numext, $numint, $colonia, $localidad, $cp, $municipio, $estado, $pais, $usuario) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "cvecte" => $cvecte,
                "identificador" => $identificador,
                "rfc" => $rfc,
                "razon_soc" => $razon_soc,
                "calle" => $calle,
                "numext" => $numext,
                "numint" => $numint,
                "colonia" => $colonia,
                "localidad" => $localidad,
                "cp" => (isset($cp) && $cp != "") ? $cp : null,
                "municipio" => $municipio,
                "estado" => $estado,
                "pais" => $pais,
                "creadopor" => $usuario,
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarCliente($patente, $aduana, $identificador, $rfc, $razon_soc, $calle, $numext, $numint, $colonia, $localidad, $cp, $municipio, $estado, $pais, $usuario) {
        try {
            $where = array(
                "patente = ?" => $patente,
                "aduana = ?" => $aduana,
                "rfc = ?" => $rfc,
            );
            $data = array(
                "identificador" => $identificador,
                "razon_soc" => $razon_soc,
                "calle" => $calle,
                "numext" => $numext,
                "numint" => $numint,
                "colonia" => $colonia,
                "localidad" => $localidad,
                "cp" => (isset($cp) && $cp != "") ? $cp : null,
                "municipio" => $municipio,
                "estado" => $estado,
                "pais" => $pais,
                "modificadopor" => $usuario,
                "modificado" => date("Y-m-d H:i:s"),
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

    public function actualizarDireccion($id, $razon_soc, $calle, $numext, $numint, $colonia, $localidad, $cp, $municipio, $estado, $pais, $usuario) {
        try {
            $where = array(
                "id = ?" => $id,
            );
            $data = array(
                "razon_soc" => $razon_soc,
                "calle" => $calle,
                "numext" => $numext,
                "numint" => $numint,
                "colonia" => $colonia,
                "localidad" => $localidad,
                "cp" => (isset($cp) && $cp != "") ? $cp : null,
                "municipio" => $municipio,
                "estado" => $estado,
                "pais" => $pais,
                "modificadopor" => $usuario,
                "modificado" => date("Y-m-d H:i:s"),
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

    public function searchByRfc($patente, $aduana, $rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc"))
                    ->where("rfc LIKE ?", "%" . $rfc . "%")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function searchByRfcEnh($rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc"))
                    ->where("rfc LIKE ?", "%" . $rfc . "%");
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function update(Vucem_Model_Table_VucemClientes $t) {
        try {
            $arr = array(
                "patente" => $t->getPatente(),
                "aduana" => $t->getAduana(),
                "cvecte" => $t->getCvecte(),
                "identificador" => $t->getIdentificador(),
                "rfc" => $t->getRfc(),
                "razon_soc" => $t->getRazon_soc(),
                "calle" => $t->getCalle(),
                "numext" => $t->getNumext(),
                "numint" => $t->getNumint(),
                "colonia" => $t->getColonia(),
                "localidad" => $t->getLocalidad(),
                "cp" => $t->getCp(),
                "ciudad" => $t->getCiudad(),
                "municipio" => $t->getMunicipio(),
                "estado" => $t->getEstado(),
                "pais" => $t->getPais(),
                "creadopor" => $t->getCreadopor(),
                "modificadopor" => $t->getModificadopor(),
                "creado" => $t->getCreado(),
                "modificado" => $t->getModificado(),
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $t->getId()));
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
    
    public function save(Vucem_Model_Table_VucemClientes $t) {
        try {
            $arr = array(
                "patente" => $t->getPatente(),
                "aduana" => $t->getAduana(),
                "cvecte" => $t->getCvecte(),
                "identificador" => $t->getIdentificador(),
                "rfc" => $t->getRfc(),
                "razon_soc" => $t->getRazon_soc(),
                "calle" => $t->getCalle(),
                "numext" => $t->getNumext(),
                "numint" => $t->getNumint(),
                "colonia" => $t->getColonia(),
                "localidad" => $t->getLocalidad(),
                "cp" => $t->getCp(),
                "ciudad" => $t->getCiudad(),
                "municipio" => $t->getMunicipio(),
                "estado" => $t->getEstado(),
                "pais" => $t->getPais(),
                "creadopor" => $t->getCreadopor(),
                "modificadopor" => $t->getModificadopor(),
                "creado" => $t->getCreado(),
                "modificado" => $t->getModificado(),
            );
            if (null === ($id = $t->getId())) {
                $id = $this->_db_table->insert($arr);
                $t->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function search(Vucem_Model_Table_VucemClientes $t) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $t->getId())
            );
            if (0 == count($stmt)) {
                return;
            }
            $t->setId($stmt->id);
            $t->setPatente($stmt->patente);
            $t->setAduana($stmt->aduana);
            $t->setCvecte($stmt->cvecte);
            $t->setIdentificador($stmt->identificador);
            $t->setRfc($stmt->rfc);
            $t->setRazon_soc($stmt->razon_soc);
            $t->setCalle($stmt->calle);
            $t->setNumext($stmt->numext);
            $t->setNumint($stmt->numint);
            $t->setColonia($stmt->colonia);
            $t->setLocalidad($stmt->localidad);
            $t->setCp($stmt->cp);
            $t->setCiudad($stmt->ciudad);
            $t->setMunicipio($stmt->municipio);
            $t->setEstado($stmt->estado);
            $t->setPais($stmt->pais);
            $t->setCreadopor($stmt->creadopor);
            $t->setModificadopor($stmt->modificadopor);
            $t->setCreado($stmt->creado);
            $t->setModificado($stmt->modificado);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
