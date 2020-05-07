<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_TraficoPedimento {

    protected $id;
    protected $idTrafico;
    protected $pedimentos;
    protected $_firephp;

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }

    function setId($id) {
        $this->id = $id;
    }

    public function __construct(array $options = null) {
        $this->_firephp = Zend_Registry::get("firephp");
        $this->pedimentos = new Pedimento_Model_Pedimento();
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function buscar($usuario) {
        if ((!$id = $this->pedimentos->buscar($this->idTrafico))) {
            $id = $this->pedimentos->agregar($this->idTrafico, $usuario);
            return $this->pedimentos->obtener($id);
        }
        return $id;
    }

    public function detalle() {
        $m = new Pedimento_Model_PedimentoDetalle();
        return $m->obtener($this->idTrafico);
    }

    public function actualizar($arr) {
        if (($this->pedimentos->actualizar($this->id, $arr))) {
            return true;
        }
        return null;
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function procesarProductos($idPedimento, $tipoCambio, $productos, $overwrite=null) {
        $m = new Pedimento_Model_PedimentoPartidas();
        if (($m->total($idPedimento) > 0) && !$overwrite) {
            return $m->obtener($idPedimento);
        }
        $i = 1;
        $m->borrarTodo($idPedimento);
        foreach ($productos as $p) {
            $arr = array(
                "idPedimento" => $idPedimento,
                "secuencia" => $i,
                "fraccion" => $p['fraccion'],
                "descripcion" => $p['descripcion'],
                "cantidadUmc" => $p['cantidadFactura'],
                "umc" => $p['umc'],
                "cantidadUmt" => $p['cantidadTarifa'],
                "umt" => $p['umt'],
                "paisOrigen" => $p['paisOrigen'],
                "paisVendedor" => $p['paisVendedor'],
                "valorAduana" => $tipoCambio * $p['valorComercial'],
                "valorUsd" => $p['valorUsd'],
                "valorComercial" => $p['valorComercial'],
                "precioUnitario" => $p['precioUnitario'],
                "creado" => date("Y-m-D H:i:s"),
            );
            $i++;
            $m->agregar($arr);
        }
        return $m->obtener($idPedimento);
    }

    public function obtenerFacturasProveedor($idPedimento) {
        $m = new Pedimento_Model_PedimentoFacturas();
        $proveedores = $m->obtenerProveedores($idPedimento);

        $arr = array();

        foreach($proveedores as $p) {
            $arr[] = array(
                "proveedor" => $m->datosProveedor($idPedimento, $p['idFiscal'], $p['razonSocial'], $p['pais']),
                "facturas" => $m->facturasProveedor($idPedimento, $p['idFiscal'], $p['razonSocial'], $p['pais']),
            );
        }

        return $arr;
    }

    public function procesarFacturas($idPedimento, $facturas, $overwrite=null) {
        $m = new Pedimento_Model_PedimentoFacturas();
        $i = 1;
        $m->borrarTodo($idPedimento);
        foreach ($facturas as $f) {
            $arr = array(
                "idPedimento" => $idPedimento,
                "idProveedor" => $f['idPro'],
                "idFactura" => $f['idFactura'],
                "vinculacion" => null,
                "numFactura" => $f['numFactura'],
                "edocument" => $f['cove'],
                "fecha" => $f['fechaFactura'],
                "incoterm" => $f['incoterm'],
                "monedaFactura" => $f['divisa'],
                "valorMonedaFactura" => $f['valorFacturaMonExt'],
                "factorMonedaFactura" => $f['factorMonExt'],
                "valorDolares" => $f['valorFacturaUsd'],
                "idFiscal" => $f['identificador'],
                "razonSocial" => $f['nombre'],
                "calle" => $f['calle'],
                "numExterior" => $f['numExt'],
                "numInterior" => $f['numInt'],
                "colonia" => $f['colonia'],
                "localidad" => $f['localidad'],
                "ciudad" => $f['cuidad'],
                "municipio" => $f['municipio'],
                "codigoPostal" => $f['codigoPostal'],
                "estado" => $f['estado'],
                "pais" => $f['pais'],
                "creado" => date("Y-m-D H:i:s"),
            );
            $i++;
            $m->agregar($arr);
        }
        return $m->obtener($idPedimento);
    }

}
