<?php

class Webservice_Model_ClientesMapper {

    protected $_db_table;
    protected $_custPassKey = "oaqlkjkj3asdjaksdjqweuiuyyASDQWEksald";

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_Clientes();
    }

    public function challengeCustomer($username) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->joinLeft(array("d" => "trafico_cliente_dbs"), "d.idCliente = c.id", array(""))
                    ->where("d.sistema = 'portal' AND d.identificador = 1")
                    ->where("c.rfc LIKE ?", $username);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function challengeCustomerPassword($id, $password) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array(""))
                    ->joinLeft(array("d" => "trafico_cliente_dbs"), "d.idCliente = c.id", array(new Zend_Db_Expr("AES_DECRYPT(`password`,'{$this->_custPassKey}') AS `password`")))
                    ->where("d.sistema = 'portal' AND d.identificador = 1")
                    ->where("c.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                if ($stmt->password === $password) {
                    return true;
                }
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomerIdentity($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id", "nombre", "rfc"))
                    ->where("c.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return array(
                    "auth" => true,
                    "usuario" => $stmt->rfc,
                    "id" => $stmt->id,
                    "idRol" => 6,
                    "rfc" => $stmt->rfc,
                    "rol" => "cliente",
                    "nombre" => null,
                    "empresa" => NULL,
                    "email" => null,
                    "aduana" => NULL,
                    "patente" => NULL,
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
