<?php

class Operaciones_Model_ValidadorLog {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_ValidadorLog();
    }

    public function verificar($patente, $aduana, $pedimento, $archivo) {
        try {
            $sql = $this->_db_table->select()
                    ->where('patente = ? ', $patente)
                    ->where('aduana = ? ', $aduana)
                    ->where('pedimento = ? ', $pedimento)
                    ->where('archivo = ? ', $archivo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarArchivo($patente, $aduana, $archivo) {
        try {
            $sql = $this->_db_table->select()
                    ->where('patente = ? ', $patente)
                    ->where('aduana = ? ', $aduana)
                    ->where('archivo = ? ', $archivo)
                    ->where('enviado = 1')
                    ->where('YEAR(creado) = ?', date('Y'))
                    ->where('MONTH(creado) = ?', date('m'))
                    ->where('DAY(creado) = ?', date('d'));
            $stmt = $this->_db_table->fetchRow($sql);
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
            $updated = $this->_db_table->update(array('enviado' => 1), array('id = ?' => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function noAgotado($id) {
        try {            
            $updated = $this->_db_table->update(array('agotado' => 0), array('id = ?' => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function noEnviado($id) {
        try {            
            $updated = $this->_db_table->update(array('enviado' => 0), array('id = ?' => $id));
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    

    public function agregar($patente, $aduana, $pedimento, $referencia, $archivo, $contenido, $usuario) {
        try {
            $arr = array(
                'patente' => $patente,
                'aduana' => $aduana,
                'pedimento' => $pedimento,
                'referencia' => $referencia,
                'archivo' => $archivo,
                'contenido' => $contenido,
                'usuario' => $usuario,
                'creado' => date('Y-m-d H:i:s'),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodos($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('id', 'archivo', 'usuario', 'creado', 'enviado', 'validado', 'error', 'pagado', 'agotado','OCTET_LENGTH(contenido) as size'))
                    ->where('patente =? ', $patente)
                    ->where('aduana =? ', $aduana)
                    ->where('pedimento =? ', $pedimento);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerContenido($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('contenido', 'archivo'))
                    ->where('id = ? ', $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('contenido', 'patente', 'aduana', 'pedimento', 'archivo', 'contenido'))
                    ->where('id = ? ', $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerNombre($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('patente', 'aduana', 'archivo'))
                    ->where('id = ? ', $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
