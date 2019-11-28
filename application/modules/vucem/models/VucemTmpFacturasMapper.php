<?php

class Vucem_Model_VucemTmpFacturasMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemTmpFacturas();
    }

    public function verificar($numFactura, $usuario) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("NumFactura LIKE ?", $numFactura)
                    ->where("Usuario = ?", $usuario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verify($idFact, $usuario) {
        try {
            $sql = $this->_db_table->select()
                    ->where("IdFact LIKE ?", $idFact)
                    ->where("Usuario = ?", $usuario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function find($idFact) {
        try {
            $sql = $this->_db_table->select()
                    ->where("IdFact LIKE ?", $idFact);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function detectEncoding($str) {
        if (mb_detect_encoding($str, "UTF-8")) {
            return utf8_decode(html_entity_decode($str, ENT_COMPAT, "UTF-8"));
        } else {
            return $str;
        }
    }

    public function nuevaFactura($rfc, $figura, $patente, $aduana, $fact, $usuario, $manual = null, $reenvio = null) {
        try {
            $data = $fact;
            $data["firmante"] = $rfc;
            $data["figura"] = $figura;
            $data["Consolidado"] = (isset($data["Consolidado"]) && ($data["Consolidado"] == "S" || $data["Consolidado"] == 1)) ? 1 : 0;
            $data["Patente"] = $patente;
            $data["Aduana"] = $aduana;
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
            $data["NumParte"] = isset($data["NumParte"]) ? $fact["NumParte"] : null;
            $data["Observaciones"] = $fact["Observaciones"];
            if (isset($data["Manual"]) && $data["Manual"] == 0 && $data["TipoOperacion"] == 'TOCE.EXP')  {
                $data["Manual"] = 0;
            } else {
                $data["Manual"] = isset($manual) ? $manual : null;
            }
            $data["Reenvio"] = isset($reenvio) ? $reenvio : null;
            if (isset($data["CvePro"])) {
                unset($data["CvePro"]);
            }
            if (isset($data["CveCli"])) {
                unset($data["CveCli"]);
            }
            if (isset($data["success"])) {
                unset($data["success"]);
            }
            if (isset($data["FactFacAju"])) {
                unset($data["FactFacAju"]);
            }
            unset($data["Productos"]);

            $data["Creado"] = date("Y-m-d H:i:s");
            $data["Usuario"] = $usuario;
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodas($usuario) {
        try {
            $sql = $this->_db_table->select();
            $sql->where("Usuario = ?", $usuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFactura($IdFact, $usuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("IdFact = ?", $IdFact);
            if (isset($usuario)) {
                $sql->where("Usuario = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getInvoiceData($uuid) {
        try {
            $mapper = new Vucem_Model_VucemTmpProductosMapper();
            $sql = $this->_db_table->select()
                    ->where("IdFact = ?", $uuid);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                $data["PRODUCTOS"] = $mapper->obtenerProductos($uuid);
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarIdentificadorCliente($IdFact, $identificador, $rfc, $usuario) {
        try {
            $arr = array(
                "CteRfc" => $rfc,
                "CteIden" => $identificador,
                "Modificado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "IdFact = ?" => $IdFact,
                "Usuario = ?" => $usuario,
            );
            $stmt = $this->_db_table->update($arr, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarIdentificadorProveedor($IdFact, $identificador, $taxId, $usuario) {
        try {
            $arr = array(
                "ProTaxID" => $taxId,
                "ProIden" => $identificador,
                "Modificado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "IdFact = ?" => $IdFact,
                "Usuario = ?" => $usuario,
            );
            $stmt = $this->_db_table->update($arr, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarDatosFactura($IdFact, $usuario, $data) {
        try {
            $arr = array(
                "NumFactura" => $data["NumFactura"],
                "FechaFactura" => $data["FechaFactura"],
                "Subdivision" => $data["Subdivision"],
                "ValDls" => isset($data["ValDls"]) ? $data["ValDls"] : null,
                "ValExt" => isset($data["ValExt"]) ? $data["ValExt"] : null,
                "CertificadoOrigen" => $data["CertificadoOrigen"],
                "NumExportador" => $data["NumExportador"],
                "CteCalle" => $data["CteCalle"],
                "CteNombre" => $data["CteNombre"],
                "CteColonia" => $data["CteColonia"],
                "CteNumExt" => $data["CteNumExt"],
                "CteNumInt" => $data["CteNumInt"],
                "CteLocalidad" => isset($data["CteLocalidad"]) ? $data["CteLocalidad"] : null,
                "CteMun" => $data["CteMun"],
                "CteEdo" => $data["CteEdo"],
                "CtePais" => $data["CtePais"],
                "CteEdo" => $data["CteEdo"],
                "CteCP" => $data["CteCP"],
                "ProTaxID" => $data["ProTaxID"],
                "ProNombre" => $data["ProNombre"],
                "ProCalle" => $data["ProCalle"],
                "ProNumExt" => $data["ProNumExt"],
                "ProNumInt" => $data["ProNumInt"],
                "ProColonia" => $data["ProColonia"],
                "ProLocalidad" => isset($data["ProLocalidad"]) ? $data["ProLocalidad"] : null,
                "ProMun" => $data["ProMun"],
                "ProEdo" => $data["ProEdo"],
                "ProCP" => $data["ProCP"],
                "ProPais" => $data["ProPais"],
                "Observaciones" => isset($data["Observaciones"]) ? $data["Observaciones"] : null,
                "Manual" => 1,
                "Modificado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($arr, array(
                "IdFact = ?" => $IdFact,
                "Usuario = ?" => $usuario,
            ));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarNumeroFactura($IdFact, $numFactura) {
        try {
            $arr = array(
                "NumFactura" => $numFactura,
                "Modificado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($arr, array("IdFact = ?" => $IdFact));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function removerFactura($idSolicitud) {
        try {
            $stmt = $this->_db_table->update(array("Active" => 0), array("idSolicitud = ?" => $idSolicitud));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function facturaBasico($IdFact) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_tmp_facturas", array("id", "Patente", "Aduana", "Pedimento", "Referencia"))
                    ->where("IdFact = ?", $IdFact);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFacturasParaEnvio($usuario, $id = null) {
        try {
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            $sql = $this->_db_table->select();
            if (!isset($id)) {
                $sql->where("Usuario = ?", $usuario)
                        ->where("RelFact = 0")
                        ->where("enviar = 1");
            } elseif (isset($id)) {
                $sql->where("id = ?", $id)
                        ->where("enviar = 1");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $facturas = array();
                $data = $stmt->toArray();
                foreach ($data as $f) {
                    $f["Productos"] = $tmpProd->obtenerProductos($f["IdFact"], $usuario);
                    $facturas[] = $f;
                }
                return $facturas;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFacturasRelacion($usuario) {
        try {
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            $sql = $this->_db_table->select();
            $sql->where("Usuario = ?", $usuario)
                    ->where("RelFact = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $facturas = array();
                $data = $stmt->toArray();
                foreach ($data as $f) {
                    $f["Productos"] = $tmpProd->obtenerProductos($f["IdFact"], $usuario);
                    $facturas[] = $f;
                }
                return $facturas;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function existenFacturas($usuario) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_tmp_facturas", array("count(*) AS total"))
                    ->where("enviar = 1")
                    ->where("Usuario = ?", $usuario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarFactura($id, $IdFact, $usuario) {
        try {
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            $where = array(
                "id = ?" => $id,
                "IdFact = ?" => $IdFact,
                "Usuario = ?" => $usuario,
            );
            if (($this->_db_table->delete($where))) {
                $prods = $tmpProd->obtenerProductos($IdFact, $usuario);
                if ($prods) {
                    if (($tmpProd->borrarProductos($id, $IdFact, $usuario))) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarFacturaId($id, $usuario) {
        try {
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            $where = array(
                "id = ?" => $id,
                "Usuario = ?" => $usuario,
            );
            if (($this->_db_table->delete($where))) {
                $prods = $tmpProd->obtenerProductosId($id, $usuario);
                if ($prods) {
                    if (($tmpProd->borrarProductosId($id, $usuario))) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getSubdivisionValue($idFact, $usuario) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_tmp_facturas", array("Subdivision"))
                    ->where("Usuario = ?", $usuario)
                    ->where("IdFact = ?", $idFact);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["Subdivision"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getRelfactValue($idFact, $usuario) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_tmp_facturas", array("RelFact"))
                    ->where("Usuario = ?", $usuario)
                    ->where("IdFact = ?", $idFact);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["RelFact"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getSendfactValue($idFact, $usuario) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_tmp_facturas", array("enviar"))
                    ->where("Usuario = ?", $usuario)
                    ->where("IdFact = ?", $idFact);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["enviar"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateSubdivisionValue($idFact, $usuario, $value) {
        try {
            $data = array(
                "Subdivision" => $value,
            );
            $where = array(
                "IdFact = ?" => $idFact,
                "Usuario = ?" => $usuario,
            );
            $stmt = $this->_db_table->update($data, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateRelfactValue($idFact, $usuario, $value) {
        try {
            $data = array(
                "RelFact" => $value,
            );
            $where = array(
                "IdFact = ?" => $idFact,
                "Usuario = ?" => $usuario,
            );
            $stmt = $this->_db_table->update($data, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateSendfactValue($idFact, $usuario, $value) {
        try {
            $data = array(
                "enviar" => $value,
            );
            $where = array(
                "IdFact = ?" => $idFact,
                "Usuario = ?" => $usuario,
            );
            $stmt = $this->_db_table->update($data, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function save(Vucem_Model_Table_TmpFacturas $tbl) {
        try {
            $arr = array(
                "id" => $tbl->getId(),
                "enviar" => $tbl->getEnviar(),
                "firmante" => $tbl->getFirmante(),
                "figura" => $tbl->getFigura(),
                "adenda" => $tbl->getAdenda(),
                "IdFact" => $tbl->getIdFact(),
                "Patente" => $tbl->getPatente(),
                "Aduana" => $tbl->getAduana(),
                "Pedimento" => $tbl->getPedimento(),
                "Referencia" => $tbl->getReferencia(),
                "TipoOperacion" => $tbl->getTipoOperacion(),
                "NumFactura" => $tbl->getNumFactura(),
                "NumParte" => $tbl->getNumParte(),
                "FechaFactura" => $tbl->getFechaFactura(),
                "Observaciones" => $tbl->getObservaciones(),
                "Subdivision" => $tbl->getSubdivision(),
                "RelFact" => $tbl->getRelFact(),
                "Consolidado" => $tbl->getConsolidado(),
                "OrdenFact" => $tbl->getOrdenFact(),
                "OrdenFactCon" => $tbl->getOrdenFactCon(),
                "ValDls" => $tbl->getValDls(),
                "ValExt" => $tbl->getValExt(),
                "CertificadoOrigen" => $tbl->getCertificadoOrigen(),
                "NumExportador" => $tbl->getNumExportador(),
                "CveImp" => $tbl->getCveImp(),
                "CteIden" => $tbl->getCteIden(),
                "CteRfc" => $tbl->getCteRfc(),
                "CteNombre" => $tbl->getCteNombre(),
                "CteCalle" => $tbl->getCteCalle(),
                "CteNumExt" => $tbl->getCteNumExt(),
                "CteNumInt" => $tbl->getCteNumInt(),
                "CteColonia" => $tbl->getCteColonia(),
                "CteLocalidad" => $tbl->getCteLocalidad(),
                "CteCP" => $tbl->getCteCP(),
                "CteMun" => $tbl->getCteMun(),
                "CteEdo" => $tbl->getCteEdo(),
                "CtePais" => $tbl->getCtePais(),
                "CvePro" => $tbl->getCvePro(),
                "ProIden" => $tbl->getProIden(),
                "ProTaxID" => $tbl->getProTaxID(),
                "ProNombre" => $tbl->getProNombre(),
                "ProCalle" => $tbl->getProCalle(),
                "ProNumExt" => $tbl->getProNumExt(),
                "ProNumInt" => $tbl->getProNumInt(),
                "ProColonia" => $tbl->getProColonia(),
                "ProLocalidad" => $tbl->getProLocalidad(),
                "ProCP" => $tbl->getProCP(),
                "ProMun" => $tbl->getProMun(),
                "ProEdo" => $tbl->getProEdo(),
                "ProPais" => $tbl->getProPais(),
                "Creado" => $tbl->getCreado(),
                "Modificado" => $tbl->getModificado(),
                "Usuario" => $tbl->getUsuario(),
                "Active" => $tbl->getActive(),
                "Manual" => $tbl->getManual(),
                "Reenvio" => $tbl->getReenvio(),
                "Reenvio" => $tbl->getReenvio(),
            );
            if (null === ($id = $tbl->getId())) {
                $id = $this->_db_table->insert($arr);
                $tbl->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
