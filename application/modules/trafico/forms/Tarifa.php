<?php

class Trafico_Form_Tarifa extends Twitter_Bootstrap_Form_Horizontal {

    protected $id;
    protected $idCliente;
    protected $vigencias;
    protected $aereas;
    protected $maritimas;
    protected $terrestres;
    protected $especiales;
    protected $conceptos;
    protected $otros;

    function setId($id) {
        $this->id = $id;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setAereas($aereas) {
        $this->aereas = $aereas;
    }

    function setMaritimas($maritimas) {
        $this->maritimas = $maritimas;
    }

    function setTerrestres($terrestres) {
        $this->terrestres = $terrestres;
    }

    function setEspeciales($especiales) {
        $this->especiales = $especiales;
    }

    function setConceptos($conceptos) {
        $this->conceptos = $conceptos;
    }

    function setOtros($otros) {
        $this->otros = $otros;
    }

    function setVigencias($vigencias) {
        $this->vigencias = $vigencias;
    }

    public function init() {

        $decorators = array("ViewHelper", "Errors", "Label");

        $aereas = array("" => "---");
        if (isset($this->aereas)) {
            foreach ($this->aereas as $item) {
                $aereas[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
            }
        }

        $terrestres = array("" => "---");
        if (isset($this->terrestres)) {
            foreach ($this->terrestres as $item) {
                $terrestres[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
            }
        }

        $maritimas = array("" => "---");
        if (isset($this->maritimas)) {
            foreach ($this->maritimas as $item) {
                $maritimas[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
            }
        }

        $especiales = array("" => "---");
        if (isset($this->especiales)) {
            foreach ($this->especiales as $item) {
                $especiales[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
            }
        }

        $conceptos = array("" => "---");
        if (isset($this->conceptos)) {
            foreach ($this->conceptos as $item) {
                $conceptos[$item["id"]] = $item["concepto"];
            }
        }

        $otros = array("" => "---");
        if (isset($this->otros)) {
            foreach ($this->otros as $item) {
                $otros[$item["id"]] = $item["concepto"];
            }
        }
        
        $vigencias = array("" => "---");
        if (isset($this->vigencias)) {
            foreach ($this->vigencias as $item) {
                $vigencias[$item["id"]] = $item["tipoVigencia"];
            }
        }

        $this->addElement("hidden", "id", array(
            "decorators" => $decorators,
            "value" => $this->id
        ));

        $this->addElement("select", "idCliente", array(
            "attribs" => array("tabindex" => "1", "class" => "traffic-select-large"),
            "decorators" => $decorators,
            "multioptions" => $this->idCliente
        ));

        $this->addElement("select", "aereas", array(
            "attribs" => array("tabindex" => "1", "class" => "traffic-select-large"),
            "decorators" => $decorators,
            "multioptions" => $aereas
        ));

        $this->addElement("select", "tipoVigencia", array(
            "attribs" => array("tabindex" => "1", "class" => "traffic-select-medium"),
            "decorators" => $decorators,
            "multioptions" => $vigencias
        ));

        $this->addElement("select", "terrestres", array(
            "attribs" => array("tabindex" => "2", "class" => "traffic-select-large"),
            "decorators" => $decorators,
            "multioptions" => $terrestres
        ));

        $this->addElement("select", "maritimas", array(
            "attribs" => array("tabindex" => "3", "class" => "traffic-select-large"),
            "decorators" => $decorators,
            "multioptions" => $maritimas
        ));

        $this->addElement("select", "especiales", array(
            "attribs" => array("tabindex" => "4", "class" => "traffic-select-large"),
            "decorators" => $decorators,
            "multioptions" => $especiales
        ));

        $this->addElement("select", "conceptos", array(
            "attribs" => array("tabindex" => "4", "class" => "traffic-select-large"),
            "decorators" => $decorators,
            "multioptions" => $conceptos
        ));

        $this->addElement("select", "otros", array(
            "attribs" => array("tabindex" => "4", "class" => "traffic-select-large"),
            "decorators" => $decorators,
            "multioptions" => $otros
        ));
    }

}
