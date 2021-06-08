<?php

class Manifestacion_Form_NuevaManifestacion extends Twitter_Bootstrap_Form_Horizontal
{

    protected $_clientes;
    protected $_aduanas;
    protected $_aduana;

    public function setClientes($clientes = null)
    {
        $this->_clientes = $clientes;
    }

    function setAduanas($aduanas = null)
    {
        $this->_aduanas = $aduanas;
    }

    function setAduana($aduana = null)
    {
        $this->_aduana = $aduana;
    }

    public function init()
    {

        $tbl = new Trafico_Model_CvePedimentos();
        $rows = $tbl->obtener();
        if (isset($rows) && !empty($rows)) {
            $data = array();
            $data[""] = "---";
            foreach ($rows as $item) {
                $data[$item["clave"]] = $item["clave"];
            }
        }

        if (isset($this->_aduanas) && !empty($this->_aduanas)) {
            $arrAduanas = array("" => "---");
            foreach ($this->_aduanas as $item) {
                if ((int) $item["patente"] !== 0) {
                    $arrAduanas[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . ": " . $item["nombre"];
                }
            }
        }

        if (isset($this->_aduana) && !empty($this->_aduana)) {
            $mapper = new Trafico_Model_TraficoCliAduanasMapper();
            $rows = $mapper->clientesAduana($this->_aduana);
            foreach ($rows as $item) {
                $this->_clientes[$item["id"]] = $item["nombre"];
            }
        }

        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("select", "idAduana", array(
            "class" => "traffic-select-large",
            "multiOptions" => isset($arrAduanas) ? $arrAduanas : array("" => "---"),
            "attribs" => array("tabindex" => "1"),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));

        $this->addElement("select", "idCliente", array(
            "class" => "traffic-select-large",
            "multiOptions" => isset($this->_clientes) ? $this->_clientes : array("" => "---"),
            "attribs" => array("tabindex" => "2"),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));

        $this->addElement("select", "tipoOperacion", array(
            "class" => "traffic-select-small",
            "multiOptions" => array(
                "" => "---",
                "TOCE.IMP" => "Importación",
                "TOCE.EXP" => "Exportación",
            ),
            "attribs" => array("tabindex" => "3"),
            "validators" => array(array('stringLength', array('min' => 7, 'max' => 8)))
        ));

        $this->addElement("select", "cvePedimento", array(
            "class" => "traffic-select-small",
            "attribs" => array("tabindex" => "4"),
            "decorators" => $decorators,
            "multioptions" => isset($data) ? $data : array(),
        ));

        $this->addElement("text", "pedimento", array(
            "class" => "traffic-input-small",
            "attribs" => array(
                "tabindex" => "5",
            ),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));

        $this->addElement("text", "referencia", array(
            "class" => "traffic-input-small",
            "attribs" => array(
                "tabindex" => "6",
            ),
            "decorators" => $decorators,
        ));
    }
}
