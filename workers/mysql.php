<?php

require "Zend/Loader/Autoloader.php";
require_once __DIR__ . "/../library/OAQ/Sitawin.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
ini_set("soap.wsdl_cache_enabled", 0);
defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));

class Db {

    protected $_config;
    protected $_db;
    protected $_key = "5203bfec0c3db@!b2295";

    function __construct() {
        $this->_config = new Zend_Config_Ini(realpath(dirname(__FILE__) . "/../application/configs/application.ini"), APPLICATION_ENV);
        $this->_db = Zend_Db::factory("Pdo_Mysql", array(
                    "host" => $this->_config->resources->multidb->oaqintranet->host,
                    "username" => $this->_config->resources->multidb->oaqintranet->username,
                    "password" => $this->_config->resources->multidb->oaqintranet->password,
                    "dbname" => $this->_config->resources->multidb->oaqintranet->dbname
        ));
    }

    public function testing() {
        echo "This is a test!";
    }

    public function buscarReferencia($patante, $aduana, $referencia) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio", array("nom_archivo"))
                    ->where("patente = ?", $patante)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
                    ->where("rfc_cliente IS NOT NULL");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nombreArchivo($id) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio", array("referencia", "nom_archivo", "ubicacion"))
                    ->where("id = ?", $id);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ftp($id) {
        try {
            $sql = $this->_db->select()
                    ->from("ftp")
                    ->where("id = ?", $id);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function initJob($archivoId, $referencia, $nomArchivo, $ftpIp, $rfc = null, $filesize = null) {
        try {
            $data = array(
                "archivoId" => $archivoId,
                "referencia" => $referencia,
                "nombreArchivo" => $nomArchivo,
                "ftpIp" => $ftpIp,
                "rfc" => isset($rfc) ? $rfc : null,
                "filesize" => isset($filesize) ? $filesize : null,
                "iniciado" => date("Y-m-d H:i:s"),
            );
            $inserted = $this->_db->insert("envios_ftp", $data);
            if ($inserted) {
                return $this->_db->lastInsertId();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateJob($id, $estatus) {
        try {
            $data = array(
                "estatus" => $estatus,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "id = ?" => $id,
            );
            $inserted = $this->_db->update("envios_ftp", $data, $where);
            if ($inserted) {
                return;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function missing($id) {
        try {
            $data = array(
                "borrado" => 1,
            );
            $where = array(
                "id = ?" => $id,
            );
            $inserted = $this->_db->update("repositorio", $data, $where);
            if ($inserted) {
                return;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateRepo($id) {
        try {
            $data = array(
                "ftp" => 1,
            );
            $where = array(
                "id = ?" => $id,
            );
            $inserted = $this->_db->update("repositorio", $data, $where);
            if ($inserted) {
                return;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdoc($uuid, $solicitud, $estatus, $respuestaVu, $edoc = null, $numTramite = null) {
        try {
            $data = array(
                "edoc" => $edoc,
                "numTramite" => $numTramite,
                "estatus" => $estatus,
                "respuesta_vu" => $respuestaVu,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "uuid = ?" => $uuid,
                "solicitud = ?" => $solicitud,
            );
            $updated = $this->_db->update("vucem_edoc", $data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarRespuestaEdoc($id, $estatus, $respuestaVu, $edoc = null, $numTramite = null) {
        try {
            $data = array(
                "edoc" => $edoc,
                "numTramite" => $numTramite,
                "estatus" => $estatus,
                "respuesta_vu" => $respuestaVu,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db->update("vucem_edoc", $data, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEdocPorId($id) {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_edoc", array("rfc", "patente", "aduana", "pedimento", "referencia", "solicitud", "cadena", "firma", "tipoDoc", "nomArchivo", "rfcConsulta", "hash", "edoc", "numTramite", "enviado", "actualizado"));
            $sql->where("id = ?", $id);
            if (($stmt = $this->_db->fetchRow($sql))) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function checkIfFileExists($ref, $patente, $aduana, $nom) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio")
                    ->where("referencia = ?", $ref)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("nom_archivo = ?", $nom);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDetalleFirmante($rfc, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db->select();
            $sql->from("vucem_firmante", array(
                        "razon",
                        "rfc",
                        "figura",
                        "patente",
                        new Zend_Db_Expr("AES_DECRYPT(spem,'{$this->_key}') AS spem"),
                        new Zend_Db_Expr("AES_DECRYPT(certificado,'{$this->_key}') AS certificado"),
                        new Zend_Db_Expr("AES_DECRYPT(password_spem,'{$this->_key}') AS password_spem"),
                        new Zend_Db_Expr("AES_DECRYPT(password_ws,'{$this->_key}') AS password_ws"),
                        "sha",
                    ))
                    ->where("rfc = ?", $rfc)
                    ->limit(1);

            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                $data = array(
                    "razon" => $stmt["razon"],
                    "figura" => $stmt["figura"],
                    "patente" => $stmt["patente"],
                    "rfc" => $stmt["rfc"],
                    "cer" => $stmt["certificado"],
                    "spem" => $stmt["spem"],
                    "spem_pswd" => $stmt["password_spem"],
                    "ws_pswd" => $stmt["password_ws"],
                    "sha" => $stmt["sha"],
                );
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevaSolicitud($rfc, $patente, $aduana, $pedimento, $referencia, $uuid, $solicitud, $certificado, $firma, $cadena, $base64, $tipoDoc, $subTipoArchivo, $nomArchivo, $hash, $usuario, $email) {
        try {
            $data = array(
                "rfc" => $rfc,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "uuid" => $uuid,
                "solicitud" => $solicitud,
                "certificado" => $certificado,
                "cadena" => $cadena,
                "firma" => $firma,
                "tipoDoc" => $tipoDoc,
                "subTipoArchivo" => $subTipoArchivo,
                "nomArchivo" => $nomArchivo,
                "hash" => $hash,
                "usuario" => $usuario,
                "email" => $email,
                "estatus" => 1,
                "edoc" => null,
                "archivo" => $base64,
                "respuesta" => null,
                "enviado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("vucem_edoc", $data);
            if ($added) {
                return $this->_db->lastInsertId();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function veriricarEdoc($rfc, $patente, $aduana, $cadena, $hash) {
        try {
            $sql = $this->_db->select();
            $sql->from("vucem_edoc", array("solicitud"))
                    ->where("rfc = ?", $rfc)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("cadena = ?", $cadena)
                    ->where("hash = ?", $hash);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevaSolicitudEdoc($rfc, $patente, $aduana, $pedimento, $referencia, $uuid, $solicitud, $certificado, $firma, $cadena, $base64, $tipoDoc, $subTipoArchivo, $nomArchivo, $hash, $usuario, $email, $rfcConsulta = null) {
        try {
            $data = array(
                "rfc" => $rfc,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "uuid" => $uuid,
                "solicitud" => $solicitud,
                "certificado" => $certificado,
                "cadena" => $cadena,
                "firma" => $firma,
                "rfcConsulta" => isset($rfcConsulta) ? $rfcConsulta : null,
                "tipoDoc" => $tipoDoc,
                "subTipoArchivo" => $subTipoArchivo,
                "nomArchivo" => $nomArchivo,
                "hash" => $hash,
                "usuario" => $usuario,
                "email" => $email,
                "estatus" => 1,
                "edoc" => null,
                "archivo" => $base64,
                "respuesta" => null,
                "enviado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("vucem_edoc", $data);
            if ($added) {
                return $this->_db->lastInsertId();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function indexEdoc($id, $rfc, $patente, $aduana, $pedimento, $referencia, $solicitud, $tipoDoc, $subTipoArchivo, $nomArchivo, $size, $usuario) {
        try {
            $arr = array(
                "id" => $id,
                "rfc" => $rfc,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "solicitud" => $solicitud,
                "tipoDoc" => $tipoDoc,
                "subTipoArchivo" => $subTipoArchivo,
                "nomArchivo" => $nomArchivo,
                "usuario" => $usuario,
                "estatus" => 1,
                "size" => $size,
                "enviado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db->insert("vucem_edoc_index", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function indexEdocSolicitud($id, $solicitud) {
        try {
            $stmt = $this->_db->update("vucem_edoc_index", array("solicitud" => $solicitud), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function indexEdocEnPedimento($referencia, $edocument) {
        try {
            $stmt = $this->_db->update("vucem_edoc_index", array("enPedimento" => 1), array("referencia = ?" => $referencia, "edoc" => $edocument));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function indexEdocEstatus($id, $estatus) {
        try {
            $stmt = $this->_db->update("vucem_edoc_index", array("estatus" => $estatus), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function indexEdocRespuesta($id, $estatus, $edocument = null) {
        try {
            $arr = array(
                "edoc" => $edocument,
                "estatus" => $estatus,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db->update("vucem_edoc_index", $arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocRespuesta($id, $xmlRespuesta) {
        try {
            $stmt = $this->_db->update("vucem_edoc", array("respuesta" => $xmlRespuesta), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocSolicitud($id, $solicitud) {
        try {
            $stmt = $this->_db->update("vucem_edoc", array("solicitud" => $solicitud), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocEstatus($id, $estatus) {
        try {
            $stmt = $this->_db->update("vucem_edoc", array("estatus" => $estatus), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocRes($id, $xmlRespuesta) {
        try {
            $stmt = $this->_db->update("vucem_edoc", array("respuesta" => $xmlRespuesta), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarRfcReferencia($patente, $referencia, $rfcCliente, $pedimento) {
        try {
            $data = array(
                "rfc_cliente" => $rfcCliente,
                "pedimento" => $pedimento,
            );
            $where = array(
                "referencia = ?" => $referencia,
                "patente = ?" => $patente,
            );
            $updated = $this->_db->update("repositorio", $data, $where);
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarWsdl($patente, $aduana) {
        try {
            $sql = $this->_db->select();
            $sql->from("ws_wsdl", array("wsdl"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("habilitado = 1")
                    ->where("sistema = 'casa'")
                    ->limit(1);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["wsdl"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarWsdlSistema($patente, $aduana, $sistema) {
        try {
            $sql = $this->_db->select();
            $sql->from("ws_wsdl", array("wsdl"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("sistema = ?", $sistema)
                    ->where("habilitado = 1")
                    ->limit(1);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["wsdl"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarWsdlSlam($patente, $aduana) {
        try {
            $sql = $this->_db->select();
            $sql->from("ws_wsdl", array("wsdl"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("habilitado = 1")
                    ->where("sistema = 'slam'")
                    ->limit(1);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["wsdl"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarDetalle($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db->select()
                    ->from("ws_detalle_pedimentos")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarAnexo($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db->select()
                    ->from("ws_anexo_pedimentos")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEdocDigitalizado($id) {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_edoc", array("nomArchivo", "archivo", "tipoDoc", "subTipoArchivo"))
                    ->where("id = ?", $id);
            if (($stmt = $this->_db->fetchRow($sql))) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function sistemaPedimentos($username) {
        try {
            $sql = $this->_db->select()
                    ->from(array("u" => "usuarios"), array())
                    ->joinLeft(array("s" => "sispedimentos"), "u.sispedimentos = s.id")
                    ->where("u.usuario = ?", $username);
            if (($stmt = $this->_db->fetchRow($sql))) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarDetalle($operacion, $tipoOperacion, $patente, $aduana, $pedimento, $referencia, $data) {
        try {
            $arr = array(
                "operacion" => $operacion,
                "tipoOperacion" => $tipoOperacion,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "rfcCliente" => $data["RFCCliente"],
                "nomCliente" => $data["NomCliente"],
                "transporteEntrada" => $data["TransporteEntrada"],
                "transporteArribo" => $data["TransporteArribo"],
                "transporteSalida" => $data["TransporteSalida"],
                "fechaEntrada" => date("Y-m-d H:i:s", strtotime($data["FechaEntrada"])),
                "fechaPago" => date("Y-m-d H:i:s", strtotime($data["FechaPago"])),
                "firmaValidacion" => $data["FirmaValidacion"],
                "firmaBanco" => $data["FirmaBanco"],
                "tipoCambio" => $data["TipoCambio"],
                "cvePed" => $data["CvePed"],
                "regimen" => $data["Regimen"],
                "consolidado" => isset($data["Consolidado"]) ? $data["Consolidado"] : null,
                "aduanaEntrada" => isset($data["SeccionEntrada"]) ? $data["AduanaEntrada"] . $data["SeccionEntrada"] : null,
                "rectificacion" => isset($data["Rectificacion"]) ? $data["Rectificacion"] : null,
                "valorDolares" => $data["ValorDolares"],
                "valorAduana" => $data["ValorAduana"],
                "fletes" => $data["Fletes"],
                "seguros" => $data["Seguros"],
                "embalajes" => $data["Embalajes"],
                "otrosIncrementales" => $data["OtrosIncrementales"],
                "dta" => $data["DTA"],
                "iva" => $data["IVA"],
                "igi" => $data["IGI"],
                "prev" => $data["PREV"],
                "cnt" => $data["CNT"],
                "totalEfectivo" => $data["TotalEfectivo"],
                "pesoBruto" => $data["PesoBruto"],
                "bultos" => $data["Bultos"],
                "usuarioAlta" => isset($data["UsuarioAlta"]) ? $data["UsuarioAlta"] : null,
                "usuarioModif" => isset($data["UsuarioModif"]) ? $data["UsuarioModif"] : null,
                "guias" => isset($data["Guias"]) ? $data["Guias"] : null,
                "bl" => isset($data["Bl"]) ? $data["Bl"] : null,
                "talon" => isset($data["Talon"]) ? $data["Talon"] : null,
                "candados" => isset($data["Candados"]) ? $data["Candados"] : null,
                "contenedores" => isset($data["Contenedores"]) ? $data["Contenedores"] : null,
                "observaciones" => isset($data["Observaciones"]) ? $data["Observaciones"] : null,
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("ws_detalle_pedimentos", $arr);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarAnexo($operacion, $tipoOperacion, $patente, $aduana, $pedimento, $referencia, $data) {
        try {
            $insert = array(
                "operacion" => $operacion,
                "tipoOperacion" => $tipoOperacion,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "numFactura" => $this->value(array("numFactura", "NumFactura"), $data),
                "cove" => $this->value(array("cove", "Cove"), $data),
                "ordenFactura" => $this->value(array("ordenFactura", "OrdenFactura"), $data),
                "ordenCaptura" => $this->value(array("ordenCaptura", "OrdenCaptura"), $data),
                "fechaFactura" => $this->value(array("fechaFactura", "FechaFactura"), $data, true),
                "incoterm" => $this->value(array("incoterm", "Incoterm"), $data),
                "valorFacturaUsd" => $this->value(array("valorFacturaUsd", "ValorFacturaUsd"), $data),
                "valorFacturaMonExt" => $this->value(array("valorFacturaMonExt", "ValorFacturaMonExt"), $data),
                "cveProveedor" => $this->value(array("cveProveedor", "CveProveedor"), $data),
                "nomProveedor" => $this->value(array("nomProveedor", "NomProveedor"), $data),
                "paisFactura" => $this->value(array("paisFactura", "PaisFactura"), $data),
                "factorMonExt" => $this->value(array("factorMonExt", "FactorMonExt"), $data),
                "divisa" => $this->value(array("divisa", "Divisa"), $data),
                "taxId" => $this->value(array("taxId", "TaxId"), $data),
                "numParte" => $this->value(array("numParte", "NumParte"), $data),
                "descripcion" => $this->value(array("descripcion", "Descripcion"), $data),
                "fraccion" => $this->value(array("fraccion", "Fraccion"), $data),
                "ordenFraccion" => $this->value(array("ordenFraccion", "OrdenFraccion", "OrdenPedimento"), $data),
                "valorMonExt" => $this->value(array("valorMonExt", "ValorMonExt"), $data),
                "valorAduanaMXN" => $this->value(array("valorAduanaMXN"), $data),
                "cantUMC" => $this->value(array("cantUMC", "CantUMC"), $data),
                "abrevUMC" => $this->value(array("abrevUMC"), $data),
                "cantUMT" => $this->value(array("cantUMT", "CantUMT"), $data),
                "umc" => $this->value(array("umc", "UMC"), $data),
                "umt" => $this->value(array("umt", "UMT"), $data),
                "abrevUMT" => $this->value(array("abrevUMT"), $data),
                "cantOMA" => $this->value(array("cantOMA"), $data),
                "oma" => $this->value(array("oma"), $data),
                "umc" => $this->value(array("umc", "UMC"), $data),
                "paisOrigen" => $this->value(array("paisOrigen", "PaisOrigen"), $data),
                "paisVendedor" => $this->value(array("paisVendedor", "PaisVendedor"), $data),
                "tasaAdvalorem" => $this->value(array("tasaAdvalorem", "TasaAdvalorem"), $data),
                "formaPagoAdvalorem" => $this->value(array("formaPagoAdvalorem"), $data),
                "umc" => $this->value(array("umc", "UMC"), $data),
                "iva" => $this->value(array("iva", "IVA"), $data),
                "ieps" => $this->value(array("ieps", "IEPS"), $data),
                "isan" => $this->value(array("isan", "ISAN"), $data),
                "tlc" => $this->value(array("tlc", "TLC"), $data),
                "tlcan" => $this->value(array("tlcan", "TLCAN"), $data),
                "tlcue" => $this->value(array("tlcue", "TLCUE"), $data),
                "prosec" => $this->value(array("prosec", "PROSEC"), $data),
                "observacion" => $this->value(array("observacion", "Observacion"), $data),
                "patenteOrig" => $this->value(array("patenteOrig", "PatenteOriginal"), $data),
                "aduanaOrig" => $this->value(array("aduanaOrig", "AduanaOriginal"), $data),
                "pedimentoOrig" => $this->value(array("pedimentoOrig", "PedimentoOriginal"), $data),
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("ws_anexo_pedimentos", $insert);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarArchivoRepositorio($tipoArchivo, $subTipoArchivo, $referencia, $patente, $aduana, $nom, $ubi, $username, $edoc = null, $rfcCliente = null, $pedimento = null) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "tipo_archivo" => $tipoArchivo,
                "sub_tipo_archivo" => $subTipoArchivo,
                "referencia" => $referencia,
                "pedimento" => $pedimento,
                "nom_archivo" => $nom,
                "ubicacion" => $ubi,
                "edocument" => $edoc,
                "rfc_cliente" => $rfcCliente,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $added = $this->_db->insert("repositorio", $data);
            if ($added) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function value($values, $array, $date = null) {
        try {
            if (is_array($values)) {
                foreach ($values as $value) {
                    if (isset($array[$value]) && !is_array($array[$value])) {
                        if (isset($date) && $date === true) {
                            return date("Y-m-d H:i:s", strtotime($array[$value]));
                        }
                        return $array[$value];
                    }
                }
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function addLog($origen, $mensaje, $ip, $usuario) {
        try {
            $data = array(
                "origen" => $origen,
                "mensaje" => $mensaje,
                "ip" => $ip,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $usuario,
            );
            $this->_db->insert("log", $data);
            return true;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarFactTerceroPorReferencia($referencia) {
        try {
            $sql = $this->_db->select();
            $sql->from("repositorio", array("id", "ubicacion", "nom_archivo"))
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo = 29");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function formatURL($source_file, $space = null) {
        $search[] = " ";
        $search[] = ".";
        $search[] = "&";
        $search[] = "$";
        $search[] = ",";
        $search[] = "!";
        $search[] = "@";
        $search[] = "#";
        $search[] = "^";
        $search[] = "(";
        $search[] = ")";
        $search[] = "+";
        $search[] = "=";
        $search[] = "[";
        $search[] = "]";
        $search[] = "ñ";
        $search[] = "Ñ";
        $search[] = "[aâàá]";
        $search[] = "[eèêé]";
        $search[] = "[iìí]";
        $search[] = "[oôòó]";
        $search[] = "[uûùú]";

        $replace[] = (isset($space)) ? " " : "_";
        $replace[] = "";
        $replace[] = "and";
        $replace[] = "S";
        $replace[] = "_";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "n";
        $replace[] = "N";
        $replace[] = "a";
        $replace[] = "e";
        $replace[] = "i";
        $replace[] = "o";
        $replace[] = "u";
        $basic = str_replace($search, $replace, $source_file);
        $search = array("À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "ÿ", "A", "a", "A", "a", "A", "a", "C", "c", "C", "c", "C", "c", "C", "c", "D", "d", "Ð", "d", "E", "e", "E", "e", "E", "e", "E", "e", "E", "e", "G", "g", "G", "g", "G", "g", "G", "g", "H", "h", "H", "h", "I", "i", "I", "i", "I", "i", "I", "i", "I", "i", "?", "?", "J", "j", "K", "k", "L", "l", "L", "l", "L", "l", "?", "?", "L", "l", "N", "n", "N", "n", "N", "n", "?", "O", "o", "O", "o", "O", "o", "Œ", "œ", "R", "r", "R", "r", "R", "r", "S", "s", "S", "s", "S", "s", "Š", "š", "T", "t", "T", "t", "T", "t", "U", "u", "U", "u", "U", "u", "U", "u", "U", "u", "U", "u", "W", "w", "Y", "y", "Ÿ", "Z", "z", "Z", "z", "Ž", "ž", "?", "ƒ", "O", "o", "U", "u", "A", "a", "I", "i", "O", "o", "U", "u", "U", "u", "U", "u", "U", "u", "U", "u", "?", "?", "?", "?", "?", "?");
        $replace = array("A", "A", "A", "A", "A", "A", "AE", "C", "E", "E", "E", "E", "I", "I", "I", "I", "D", "N", "O", "O", "O", "O", "O", "O", "U", "U", "U", "U", "Y", "s", "a", "a", "a", "a", "a", "a", "ae", "c", "e", "e", "e", "e", "i", "i", "i", "i", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "y", "A", "a", "A", "a", "A", "a", "C", "c", "C", "c", "C", "c", "C", "c", "D", "d", "D", "d", "E", "e", "E", "e", "E", "e", "E", "e", "E", "e", "G", "g", "G", "g", "G", "g", "G", "g", "H", "h", "H", "h", "I", "i", "I", "i", "I", "i", "I", "i", "I", "i", "IJ", "ij", "J", "j", "K", "k", "L", "l", "L", "l", "L", "l", "L", "l", "l", "l", "N", "n", "N", "n", "N", "n", "n", "O", "o", "O", "o", "O", "o", "OE", "oe", "R", "r", "R", "r", "R", "r", "S", "s", "S", "s", "S", "s", "S", "s", "T", "t", "T", "t", "T", "t", "U", "u", "U", "u", "U", "u", "U", "u", "U", "u", "U", "u", "W", "w", "Y", "y", "Y", "Z", "z", "Z", "z", "Z", "z", "s", "f", "O", "o", "U", "u", "A", "a", "I", "i", "O", "o", "U", "u", "U", "u", "U", "u", "U", "u", "U", "u", "A", "a", "AE", "ae", "O", "o");
        $advanced = str_replace($search, $replace, $basic);
        return strtoupper(str_replace(array(" ", "&", "\r\n", "\n", "+", ",", "//"), "", $advanced));
    }

    public function crearDirectorio($patente, $aduana, $referencia) {
        $folder = "/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente;
        if (!file_exists("/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente)) {
            mkdir("/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente);
        }
        if (!file_exists("/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
            mkdir("/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
        }
        if (!file_exists("/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            mkdir("/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia);
        }
        $folder = "/home/samba-share/expedientes" . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
        if (file_exists($folder)) {
            return $folder;
        } else {
            return false;
        }
    }

    public function renombrarArchivo($path, $sourceFile, $newFile) {
        if (!rename($path . DIRECTORY_SEPARATOR . $sourceFile, $path . DIRECTORY_SEPARATOR . $newFile)) {
            return false;
        }
        return true;
    }

    public function verificarPedimento($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_pedimentos")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento);
            if ($this->_db->fetchRow($sql)) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevoPedimento($rfc, $curp, $patente, $aduana, $pedimento, $pagado, $xml, $rfcSociedad, $numOperacion) {
        try {
            $inserted = $this->_db->insert("vucem_pedimentos", array(
                "rfc" => $rfc,
                "curp" => $curp,
                "rfcSociedad" => $rfcSociedad,
                "numOperacion" => $numOperacion,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "pagado" => $pagado,
                "xml" => $xml,
                "estado" => 0,
                "creado" => date("Y-m-d H:i:s"),
            ));
            if ($inserted) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarPartida($patente, $aduana, $pedimento, $partida) {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_partidas")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento)
                    ->where("partida = ?", $partida);
            if ($this->_db->fetchRow($sql)) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevaPartida($rfcAgente, $numOperacion, $patente, $aduana, $pedimento, $partida, $xml) {
        try {
            $data = array(
                "rfc" => $rfcAgente,
                "estado" => 0,
                "numOperacion" => $numOperacion,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "partida" => $partida,
                "xml" => $xml,
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("vucem_partidas", $data);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function archivoValidacionEnviado($id) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_enviados", array("patente", "aduana", "nomArchivo"))
                    ->where("id = ?", $id);
            if (($stmt = $this->_db->fetchRow($sql))) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevoArchivoValidacion($id, $patente, $aduana, $nomArchivo, $contenido, $hash, $error, $usuario) {
        try {
            $data = array(
                "idSelf" => $id,
                "patente" => $patente,
                "aduana" => $aduana,
                "nomArchivo" => $nomArchivo,
                "contenido" => $contenido,
                "hash" => $hash,
                "error" => $error,
                "usuario" => $usuario,
                "respuesta" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("validador_enviados", $data);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validador($patente, $aduana) {
        try {
            $sql = $this->_db->select()
                    ->from("validador")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("habilitado = 1");
            if (($stmt = $this->_db->fetchRow($sql))) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function directorioValidador($patente, $aduana) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_directorio")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            if (($stmt = $this->_db->fetchRow($sql))) {
                return $stmt["directorio"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarArchivoValidacion($id, $error) {
        try {
            $updated = $this->_db->update("validador_enviados", array("respuesta" => date("Y-m-d H:i:s"), "error" => $error), array("id = ?" => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarFirmas($idArchivo) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_firmas")
                    ->where("idArchivo = ?", $idArchivo);
            if ($this->_db->fetchRow($sql)) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarFrima($idArchivo, $pedimento, $firma) {
        try {
            $data = array(
                "idArchivo" => $idArchivo,
                "pedimento" => $pedimento,
                "firma" => $firma,
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("validador_firmas", $data);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarPago($idArchivo, $patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_pagos", array("id"))
                    ->where("idArchivo = ?", $idArchivo)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["id"];
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            return "DB Exception at " . __METHOD__ . ": " . $e->getMessage();
        } catch (Exception $e) {
            return "Exception at " . __METHOD__ . ": " . $e->getMessage();
        }
    }

    public function agregarPago($idArchivo, $data) {
        try {
            $insert = array(
                "idArchivo" => $idArchivo,
                "patente" => $data["patente"],
                "aduana" => $data["aduana"],
                "pedimento" => $data["pedimento"],
                "rfcImportador" => isset($data["rfcImportador"]) ? $data["rfcImportador"] : null,
                "caja" => isset($data["caja"]) ? $data["caja"] : null,
                "numOperacion" => isset($data["numOperacion"]) ? $data["numOperacion"] : null,
                "firmaBanco" => isset($data["firmaBanco"]) ? $data["firmaBanco"] : null,
                "fechaPago" => isset($data["fechaPago"]) ? $data["fechaPago"] : null,
                "fecha" => isset($data["fecha"]) ? $data["fecha"] : null,
                "hora" => isset($data["hora"]) ? $data["hora"] : null,
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("validador_pagos", $insert);
            if ($added) {
                return true;
            }
            return null;
        } catch (Zend_Db_Exception $e) {
            return "DB Exception at " . __METHOD__ . ": " . $e->getMessage();
        } catch (Exception $e) {
            return "Exception at " . __METHOD__ . ": " . $e->getMessage();
        }
    }

    public function verificarArchivoPago($nomArchivo, $hash) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_enviados", array("id"))
                    ->where("nomArchivo = ?", $nomArchivo)
                    ->where("hash = ?", $hash);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["id"];
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            return "DB Exception at " . __METHOD__ . ": " . $e->getMessage();
        } catch (Exception $e) {
            return "Exception at " . __METHOD__ . ": " . $e->getMessage();
        }
    }

    public function agregarArchivoPago($patente, $aduana, $nomArchivo, $contenido, $hash) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "nomArchivo" => $nomArchivo,
                "contenido" => $contenido,
                "hash" => $hash,
                "usuario" => "gearman",
                "enviado" => null,
                "error" => 0,
                "respuesta" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("validador_enviados", $data);
            if ($added) {
                return $this->_db->lastInsertId();
            }
            return null;
        } catch (Zend_Db_Exception $e) {
            return "DB Exception at " . __METHOD__ . ": " . $e->getMessage();
        } catch (Exception $e) {
            return "Exception at " . __METHOD__ . ": " . $e->getMessage();
        }
    }

    public function validadorLog($id) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_log", array("contenido", "archivo", "patente", "aduana", "pedimento", "referencia"))
                    ->where("id =? ", $id);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarValidadorLog($patente, $aduana, $pedimento, $archivo) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_log")
                    ->where("patente = ? ", $patente)
                    ->where("aduana = ? ", $aduana)
                    ->where("pedimento = ? ", $pedimento)
                    ->where("archivo = ? ", $archivo);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function enviadoValidadorLog($id) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_log")
                    ->where("id = ? ", $id)
                    ->where("enviado = 1");
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function enviado($id) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_log", array("enviado"))
                    ->where("id = ? ", $id);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["enviado"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarNoEnviado($id) {
        try {
            $sql = $this->_db->select()
                    ->from("validador_log")
                    ->where("id = ? ", $id)
                    ->where("enviado = 0");
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarValidadorLog($patente, $aduana, $pedimento, $referencia, $archivo, $contenido, $username) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "archivo" => $archivo,
                "contenido" => $contenido,
                "usuario" => $username,
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("validador_log", $data);
            if ($added) {
                return $added;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validadorLogError($id) {
        try {
            $updated = $this->_db->update("validador_log", array("error" => 1), array("id = ?" => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validadorLogValidado($id) {
        try {
            $updated = $this->_db->update("validador_log", array("validado" => 1), array("id = ?" => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validadorLogEnviado($id) {
        try {
            $updated = $this->_db->update("validador_log", array("enviado" => 1), array("id = ?" => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validadorLogAgotado($id) {
        try {
            $updated = $this->_db->update("validador_log", array("agotado" => 1), array("id = ?" => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validadorLogPagado($id) {
        try {
            $updated = $this->_db->update("validador_log", array("pagado" => 1), array("id = ?" => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validadorActividad($patente, $aduana, $pedimento, $referencia, $mensaje) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "mensaje" => $mensaje,
                "creado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db->insert("validador_actividad", $data);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function cofidiEmails($idCofidi) {
        try {
            $sql = $this->_db->select()
                    ->from("cofidi_emails", array("email", "nombre"))
                    ->where("estatus = 1")
                    ->where("idCofidi = ?", $idCofidi);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    protected function _pedimentos($patente, $aduana) {
        try {
            $sql = $this->_db->select()
                    ->from("sispedimentos", array("direccion_ip", "usuario", "pwd", "dbname", "puerto", "tipo"))
                    ->where("env = 'prod'")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function sitawin($patente, $aduana) {
        if(($s = $this->_pedimentos($patente, $aduana))) {
            return new OAQ_Sitawin(false, $s["direccion_ip"], $s["usuario"], $s["pwd"], $s["dbname"], $s["puerto"], $s["tipo"]);
        }
        return;
    }
    
    public function buscarRepositorio($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio", array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo = 9999");
            $stmt = $this->_db->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function buscarRepositorioIndex($idAduana, $patente, $aduana, $referencia) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_index", array("id"))
                    ->where("idAduana = ?", $idAduana)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function buscarEnRepositorio($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio", array("pedimento", "rfc_cliente as rfcCliente"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
                    ->where("pedimentos IS NOT NULL AND rfc_cliente IS NOT NULL");
            $stmt = $this->_db->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function buscarEnSolicitudes($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("s" => "trafico_solicitudes"), array("pedimento"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("rfc as rfcCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array(""))
                    ->where("a.patente = ?", $patente)
                    ->where("a.aduana = ?", $aduana)
                    ->where("s.referencia = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function buscarEnTrafico($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db->select()
                    ->from("traficos", array("pedimento", "rfcCliente"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @param string $referencia
     * @param string $rfcCliente
     * @param string $usuario
     * @return boolean
     * @throws Exception
     */
    public function nuevoRepositorio($patente, $aduana, $pedimento, $referencia, $rfcCliente, $usuario) {
        try {
            $arr = array(
                "tipo_archivo" => 9999,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "rfc_cliente" => $rfcCliente,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $usuario,
            );
            $stmt = $this->_db->insert("repositorio", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function nuevoRepositorioIndex($idAduana, $patente, $aduana, $pedimento, $referencia, $rfcCliente, $usuario) {
        try {
            $arr = array(
                "idAduana" => $idAduana,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "rfcCliente" => $rfcCliente,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            );
            $stmt = $this->_db->insert("repositorio_index", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $referencia
     * @param string $usuario
     * @return type
     */
    public function crearRepositorioSitawin($patente, $aduana, $referencia, $usuario = null) {
        $db = $this->sitawin($patente, $aduana);
        if (isset($db) && !empty($db)) {
            $arr = $db->datosPedimento($referencia);
            if (count($arr)) {
                if ($this->buscarRepositorio($patente, $aduana, $referencia) !== true) {
                    $this->nuevoRepositorio($patente, $aduana, $arr["pedimento"], $referencia, $arr["rfcCliente"], "VucemWorker");
                }
                $this->repositorioIndex($patente, $aduana, $arr["pedimento"], $referencia, $arr["rfcCliente"]);
                return array("pedimento" => $arr["pedimento"], "rfcCliente" => $arr["rfcCliente"]);
            } else {
                return;
            }
        }
        return;
    }
    
    public function idAduana($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from("trafico_aduanas", array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function repositorioIndex($patente, $aduana, $pedimento, $referencia, $rfcCliente) {
        $idAduana = $this->idAduana($patente, $aduana);
        if ($this->buscarRepositorioIndex($idAduana, $patente, $aduana, $referencia) !== true) {
            $this->nuevoRepositorioIndex($idAduana, $patente, $aduana, $pedimento, $referencia, $rfcCliente, "VucemWorker");
        }
        return true;
    }

}
