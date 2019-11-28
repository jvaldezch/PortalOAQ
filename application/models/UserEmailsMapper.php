<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_UserEmailsMapper {

    protected $_db;
    protected $_passKey = 'ASsDLCkj#$"#$"#$38478942312%!';

    public function __construct() {
        $this->_db = Zend_Registry::get('oaqintranet');
    }

    public function getUserEmailCredentials($username, $email) {
        try {
            $select = $this->_db->select()
                    ->from('usuarios_emails', array(new Zend_Db_Expr("AES_DECRYPT(password, '{$this->_passKey}') AS password"), "smtp"))
                    ->where('usuario LIKE ?', $username)
                    ->where('email LIKE ?', $email);

            $result = $this->_db->fetchRow($select, array());

            if ($result) {
                return array(
                    'password' => $result['password'],
                    'smtp' => $result['smtp'],
                );
            }
            return null;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
