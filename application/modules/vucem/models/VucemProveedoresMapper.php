<?php

class Vucem_Model_VucemProveedoresMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemProveedores();
    }

    public function getProviders($patente, $aduana, $cvecli = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_proveedores", array("cvepro", "rfc", "razon_soc"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            if (isset($cvecli)) {
                $sql->where("cvecli = ?", $cvecli);
            }
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getProviderDetail($patente, $aduana, $cvepro) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("cvepro = ?", $cvepro);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerProveedores($patente, $aduana, $cve, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_proveedores", array("cvepro", "rfc", "razon_soc"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("cvecli = ?", $cve)
                    ->where("rfc = ?", $rfc);
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarProveedor($patente, $aduana, $cvecte, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table)
                    ->where("rfc = ?", $rfc)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("cvecli = ?", $cvecte);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarNuevoProveedor($patente, $aduana, $cvecli, $cvepro, $identificador, $rfc, $razon_soc, $calle, $numext, $numint, $colonia, $localidad, $cp, $municipio, $estado, $pais, $usuario) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "cvecli" => $cvecli,
                "cvepro" => $cvepro,
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

    public function searchByTaxId($patente, $aduana, $rfc, $cveCli) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc"))
                    ->where("rfc LIKE ?", "%" . $rfc . "%")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("cvecli = ?", $cveCli);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function detalleProveedor($patente, $aduana, $rfc, $cveCli) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table)
                    ->where("rfc = ?", $rfc)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("cvecli = ?", $cveCli);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function datosProveedor($razonSocial, $cveCli) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table)
                    ->where("razon_soc = ?", $razonSocial)
                    ->where("cvecli = ?", $cveCli);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function searchProvByRfcEnh($query, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("razon_soc"))
                    ->where("razon_soc LIKE ?", "%" . $query . "%")
                    ->where("cvecli = ?", $rfc);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
