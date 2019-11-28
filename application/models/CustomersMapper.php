<?php

class Application_Model_CustomersMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    /**
     * 
     * @param String $rfc
     * @return boolean
     */
    public function checkForCustomerByRfc($rfc) {
        $select = $this->_db->select()
                ->from("clientes", array("id"))
                ->where("rfc LIKE ?", $rfc);

        $result = $this->_db->fetchRow($select, array());

        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param String $rfc
     * @param String $name
     */
    public function addNewCustomer($rfc, $name) {
        $data = array(
            "rfc" => $rfc,
            "nombre" => $name,
        );

        $insert = $this->_db->insert("clientes", $data);

        if ($insert) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @return array
     */
    public function getCustomers() {
        try {
            $select = $this->_db->select()
                    ->from("clientes", array("rfc", "nombre"))
                    ->order("nombre ASC");

            $result = $this->_db->fetchAll($select, array());

            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getCustomerIdByRfc($rfc) {
        try {
            $select = $this->_db->select()
                    ->from("clientes", array("id"))
                    ->where("rfc = ?", $rfc);

            $result = $this->_db->fetchRow($select, array());

            return $result["id"];
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
