<?php

class Comercializacion_Model_ClientesEmailsMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Comercializacion_Model_DbTable_ClientesEmails();
    }

    public function getAllEmails($idcliente, $tipo) {
        try {
            $select = $this->_db_table->select();
            $select->where("idcliente = ?", $idcliente)
                    ->where("tipo = ?", $tipo);
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function addNewContact($idcliente, $nombre, $email, $tipo, $usuario) {
        try {
            $data = array(
                "idcliente" => $idcliente,
                "tipo" => $tipo,
                "nombre" => $nombre,
                "email" => $email,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $usuario,
            );

            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getContactByid($id) {
        try {
            $select = $this->_db_table->select();
            $select->where("id = ?", $id);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateContact($id, $nombre, $email, $tipo, $usuario) {
        try {
            $data = array(
                "nombre" => $nombre,
                "email" => $email,
                "tipo" => $tipo,
                "modificado" => date("Y-m-d H:i:s"),
                "usuario" => $usuario
            );
            $where = array(
                "id = ?" => $id
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function deleteContact($id) {
        try {
            $where = array(
                "id = ?" => $id
            );
            $deteled = $this->_db_table->delete($where);
            if ($deteled) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
