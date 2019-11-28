<?php

class Application_Model_AclResource extends Application_Model_Mapper {

    // http://www.zenddeveloper.com/creating-acl-with-database-in-zend-framework/
    protected $Model_DbTable = 'Application_Model_DbTable_Acl';
    protected $Model_DbView = null;
    protected $id;
    protected $controller;
    protected $action;

    public function __set($name, $value) {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Error __set() function, Invalid  property');
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Error __get() function, Invalid  property');
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function getModel_DbTable() {
        return $this->Model_DbTable;
    }

    public function getModel_DbView() {
        return $this->Model_DbView;
    }

    public function getController() {
        return $this->controller;
    }

    public function setController($text) {
        $this->controller = (string) $text;
        return $this;
    }

    public function setAction($text) {
        $this->action = (string) $text;
        return $this;
    }

    public function getAction() {
        return $this->action;
    }

    public function setId($text) {
        $this->id = (string) $text;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public static function getByColumn($column = null, $value = null) {
        $mapper = new self();
        $out = $mapper->MapperGetByColumn($column, $value);
        return $out;
    }

    public static function getBySQLCondition(array $conditions) {
        $mapper = new self();
        $out = $mapper->MapperGetBySQLCondition($conditions);
        return $out;
    }

    public static function select($sql = null, $params = null) {
        $mapper = new self();
        $out = $mapper->MapperSelect($sql, $params);
        return $out;
    }

    public static function save(array $data) {
        $mapper = new self();
        $out = $mapper->MapperSave($data);
        return $out;
    }

    public static function resourceExists($controller = null, $action = null) {
        if (!$controller || !$action) {
            throw new Exception("Error resourceExists(), the controller/action is empty");
        }
        $result = self::getBySQLCondition(array('controller=?' => $controller, 'action=?' => $action));
        if (count($result)) {
            return true;
        }
        return false;
    }

    public static function resourceValid($request) {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        if (!$dispatcher->isDispatchable($request)) {
            return false;
        }
        $front = Zend_Controller_Front::getInstance();
        $dispatcher = $front->getDispatcher();
        $controllerClass = $dispatcher->getControllerClass($request);
        $controllerclassName = $dispatcher->loadClass($controllerClass);
        $actionName = $dispatcher->getActionMethod($request);
        $controllerObject = new ReflectionClass($controllerclassName);
        if (!$controllerObject->hasMethod($actionName)) {
            return false;
        }
        return true;
    }

    public function createResource($controller = null, $action = null) {
        if (!$controller || !$action) {
            throw new Exception("Error resourceExists(), the controller/action is empty");
        }
        $data = array('controller' => $controller, 'action' => $action);
        return self::save($data);
    }

    public function getCurrentRoleAllowedResources($role_id = null) {
        if (!$role_id) {
            throw new Exception("Error getCurrentUserPermissions(), the role_id is empty");
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = 'SELECT A.module, A.controller,A.action FROM acl_roles ATR INNER JOIN acl A ON A.id = ATR.acl_id WHERE role_id = ? ORDER BY A.controller';
        $stmt = $db->query($sql, $role_id);
        $out = $stmt->fetchAll();
        $controller = '';
        $resources = array();
        foreach ($out as $value) {
            if ($value['controller'] != $controller) {
                $controller = $value['controller'];
            }
            $resources[$controller][] = $value['action'];
        }
        return $resources;
    }

    public static function getAll() {
        $mapper = new self();
        $out = $mapper->MapperFetchAll();
        return $out;
    }

    public function getAllResources() {
        $mapper = new self();
        $sql = 'SELECT controller FROM acl GROUP BY controller';
        $out = $mapper->select($sql);
        return $out;
    }

}
