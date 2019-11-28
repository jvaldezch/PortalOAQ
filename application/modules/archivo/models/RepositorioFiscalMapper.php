<?php

class Archivo_Model_RepositorioFiscalMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_RepositorioFiscal();
    }
    
    public function archivosCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_fiscal"), array("*"))
                    ->joinLeft(array("d" => "documentos_fiscal"), "d.id = a.tipoArchivo", array("nombre as descripcionArchivo"))
                    ->where("a.idCliente = ?", $idCliente)
                    ->order("a.nombreArchivo ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function buscar($id) {
         try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_fiscal"), array("*"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function buscarArchivo($idCliente, $nombreArchivo) {
         try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_fiscal"), array("id"))
                    ->where("a.idCliente = ?", $idCliente)
                    ->where("a.nombreArchivo = ?", $nombreArchivo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function nuevoArchivo($idCliente, $tipoArchivo, $nombreArchivo, $ubicacion, $observaciones, $usuario) {
        try {
            $data = array(
                "idCliente" => $idCliente,
                "tipoArchivo" => $tipoArchivo,
                "hash" => sha1_file($ubicacion . DIRECTORY_SEPARATOR . $nombreArchivo),
                "nombreArchivo" => $nombreArchivo,
                "ubicacion" => $ubicacion,
                "observaciones" => $observaciones,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $usuario,
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }    
    
    public function tipoArchivo($id) {
         try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_fiscal"), array("id"))
                    ->joinLeft(array("d" => "documentos_fiscal"), "d.id = a.tipoArchivo", array("nombre"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->nombre;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function idTipoArchivo($id) {
         try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_fiscal"), array("tipoArchivo"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->tipoArchivo;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function fechaVencimiento($id) {
         try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_fiscal"), array("fechaVencimiento"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->fechaVencimiento;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function actualizarTipoArchivo($id, $tipoArchivo, $fechaVencimiento) {
        try {
            $arr = array(
                "tipoArchivo" => $tipoArchivo,
                "fechaVencimiento" => $fechaVencimiento,
            );
            $added = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($added) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function borrarArchivo($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
}
