<?php

/**
 * Administración, validación de archivos M3
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Auth {

    protected $_users;
    protected $_username;
    protected $_password;
    protected $_config;
    protected $_webservice;

    function __construct() {
        $this->_users = new Webservice_Model_UsuariosMapper();
    }

    /**
     * Challenge password
     *
     * @param String $username
     * @param String $password
     * @return Array
     */
    public function challengeCredentials($username, $password) {
        $this->_password = $password;
        $this->_username = $username;
        try {
            return $this->_users->challengeCredentials($username, $password);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    /**
     *
     * @param String $username
     * @return array
     */
    public function recoverPassword($username) {
        try {
            return $this->_users->recoverPassword($username);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

}
