<?php

class Archivo_Model_RepositorioLog
{

    protected $_db_table;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $referencia;

    function setPatente($patente)
    {
        $this->patente = $patente;
    }

    function setAduana($aduana)
    {
        $this->aduana = $aduana;
    }

    function setPedimento($pedimento)
    {
        $this->pedimento = $pedimento;
    }

    function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Error __set() function, Invalid  property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Error __get() function, Invalid  property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
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
        $this->_db_table = new Archivo_Model_DbTable_RepositorioLog();
    }

    public function agregar($accion, $tipoArchivo, $nombreArchivo, $usuario)
    {
        try {
            $arr = array(
                "patente" => str_pad($this->patente, 4, '0', STR_PAD_LEFT),
                "aduana" => str_pad($this->aduana, 3, '0', STR_PAD_LEFT),
                "pedimento" => str_pad($this->pedimento, 7, '0', STR_PAD_LEFT),
                "referencia" => $this->referencia,
                "accion" => $accion,
                "tipoArchivo" => $tipoArchivo,
                "nombreArchivo" => $nombreArchivo,
                "usuario" => $usuario,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function bitacora()
    {
        try {
            $sql = $this->_db_table->select()
                ->where("patente = ?", $this->patente)
                ->where("aduana = ?", $this->aduana)
                ->where("pedimento = ?", $this->pedimento)
                ->where("referencia = ?", $this->referencia)
                ->order("creado DESC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
}
