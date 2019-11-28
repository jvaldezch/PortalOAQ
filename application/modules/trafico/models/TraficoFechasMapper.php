<?php

class Trafico_Model_TraficoFechasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoFechas();
    }

    public function verificarFecha($idTrafico, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("tipo = ?", $tipo);
            $found = $this->_db_table->fetchRow($sql);
            if ($found) {
                return $found["id"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarFecha($id, $fecha, $tipo, $idUsuario) {
        try {
            $data = array(
                "fecha" => date("Y-m-d H:i:s", strtotime($fecha)),
                "tipo" => $tipo,
                "actualizadoPor" => $idUsuario,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "id = ?" => $id,
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

    public function agregarFecha($idTrafico, $fecha, $tipo, $idUsuario) {
        try {
            $data = array(
                "idTrafico" => $idTrafico,
                "creadoPor" => $idUsuario,
                "fecha" => date("Y-m-d H:i:s", strtotime($fecha)),
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

    public function obtenerFecha($idTrafico, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("tipo = ?", $tipo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->fecha;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFechas($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[$item["tipo"]] = $item["fecha"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function find(Trafico_Model_Table_TraficoFechas $t) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $t->getIdTrafico())
                    ->where("tipo = ?", $t->getTipo());
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $t->setId($stmt->id);
                $t->setIdTrafico($stmt->idTrafico);
                $t->setFecha($stmt->fecha);
                $t->setTipo($stmt->tipo);
                $t->setCreado($stmt->creado);
                $t->setCreadoPor($stmt->creadoPor);
                $t->setActualizado($stmt->actualizado);
                $t->setActualizadoPor($stmt->actualizadoPor);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function save(Trafico_Model_Table_TraficoFechas $t) {
        try {
            $arr = array(
                "id" => $t->getId(),
                "idTrafico" => $t->getIdTrafico(),
                "fecha" => $t->getFecha(),
                "tipo" => $t->getTipo(),
                "creado" => $t->getCreado(),
                "creadoPor" => $t->getCreadoPor(),
                "actualizado" => $t->getActualizado(),
                "actualizadoPor" => $t->getActualizadoPor(),
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
    
    public function buscar($idTrafico, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "fecha"))
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("tipo = ?", $tipo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
