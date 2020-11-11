<?php

class OAQ_Cuentas_Gastos
{

    public function __set($name, $value)
    {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function obtenerCuentas($rows = 20, $page = 1, $filterRules = null)
    {
        $mppr = new Automatizacion_Model_RptCuentas();
        $sql = $mppr->obtener($filterRules);
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
        $paginator->setItemCountPerPage($rows);
        $paginator->setCurrentPageNumber($page);
        return $paginator;
    }

    public function obtenerCuenta($id)
    {
        $mppr = new Automatizacion_Model_RptCuentas();
        $con = new Automatizacion_Model_RptCuentaConceptos();

        $row = $mppr->obtenerDatos($id);
        $row['conceptos'] = $con->conceptos($id);

        return $row;
    }

}
