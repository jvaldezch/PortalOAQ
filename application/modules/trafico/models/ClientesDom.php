<?php

class Trafico_Model_ClientesDom {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ClientesDom();
    }

    public function verificar($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarPersonalizado($idCliente, $clave, $identificador, $nombre, $calle, $numExt, $numInt, $colonia, $localidad, $municipio, $ciudad, $codigoPostal, $pais) {
        try {
            $data = array(
                "idCliente" => $idCliente,
                "clave" => $clave,
                "identificador" => trim($identificador),
                "nombre" => $nombre,
                "calle" => $calle,
                "numExt" => $numExt,
                "numInt" => $numInt,
                "colonia" => $colonia,
                "localidad" => $localidad,
                "municipio" => $municipio,
                "ciudad" => $ciudad,
                "codigoPostal" => $codigoPostal,
                "pais" => $pais,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function prepareData($data, $idCliente = null) {
        if (isset($data) && $data !== false && !empty($data)) {
            $array = array(
                "clave" => 1,
                "identificador" => isset($data["rfc"]) ? trim($data["rfc"]) : null,
                "nombre" => isset($data["razonSocial"]) ? trim($data["razonSocial"]) : null,
                "calle" => isset($data["calle"]) ? trim($data["calle"]) : null,
                "numExt" => isset($data["numext"]) ? $data["numext"] : null,
                "numInt" => isset($data["numint"]) ? $data["numint"] : null,
                "colonia" => isset($data["colonia"]) ? trim($data["colonia"]) : null,
                "localidad" => isset($data["localidad"]) ? trim($data["localidad"]) : null,
                "municipio" => isset($data["municipio"]) ? trim($data["municipio"]) : null,
                "ciudad" => isset($data["ciudad"]) ? $data["ciudad"] : null,
                "estado" => isset($data["estado"]) ? $data["estado"] : null,
                "codigoPostal" => isset($data["cp"]) ? $data["cp"] : null,
                "pais" => isset($data["pais"]) ? $data["pais"] : null,
                "creado" => date("Y-m-d H:i:s"),
            );
            if (isset($idCliente)) {
                $array["idCliente"] = $idCliente;
            }
            return $array;
        }
    }

    public function updateByRfc(Trafico_Model_Table_TraficoCliDom $t) {
        try {
            $arr = array(
                "clave" => $t->getClave(),
                "identificador" => $t->getIdentificador(),
                "nombre" => $t->getNombre(),
                "calle" => $t->getCalle(),
                "numExt" => $t->getNumExt(),
                "numInt" => $t->getNumInt(),
                "colonia" => $t->getColonia(),
                "localidad" => $t->getLocalidad(),
                "municipio" => $t->getMunicipio(),
                "ciudad" => $t->getCiudad(),
                "estado" => $t->getEstado(),
                "codigoPostal" => $t->getCodigoPostal(),
                "pais" => $t->getPais(),
                "creado" => $t->getCreado(),
                "modificado" => $t->getModificado(),
            );
            $stmt = $this->_db_table->update($arr, array("identificador = ?" => $t->getIdentificador()));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function search(Trafico_Model_Table_TraficoCliDom $t) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $t->getId())
            );
            if (0 == count($stmt)) {
                return;
            }
            $t->setId($stmt->id);
            $t->setIdCliente($stmt->idCliente);
            $t->setClave($stmt->clave);
            $t->setIdentificador($stmt->identificador);
            $t->setNombre($stmt->nombre);
            $t->setCalle($stmt->calle);
            $t->setNumExt($stmt->numExt);
            $t->setNumInt($stmt->numInt);
            $t->setColonia($stmt->colonia);
            $t->setLocalidad($stmt->localidad);
            $t->setMunicipio($stmt->municipio);
            $t->setCiudad($stmt->ciudad);
            $t->setEstado($stmt->estado);
            $t->setCodigoPostal($stmt->codigoPostal);
            $t->setPais($stmt->pais);
            $t->setCreado($stmt->creado);
            $t->setModificado($stmt->modificado);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
