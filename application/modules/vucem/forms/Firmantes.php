<?php

class Vucem_Form_Firmantes extends Twitter_Bootstrap_Form_Horizontal {

    protected $_username;

    public function setUsername($username = null) {
        $this->_username = $username;
    }

    public function init() {
        $rfcs = array();
        $sign = new Vucem_Model_VucemFirmanteMapper();
        $rfc = $sign->obtenerFirmantes(APPLICATION_ENV, $this->_username);

        $rfcs[""] = "-- Seleccionar --";
        foreach ($rfc as $r) {
            $rfcs[$r["rfc"]] = $r["rfc"] . " - " . $r["razon"];
        }

        $this->addElement("select", "firmante", array(
            "placeholder" => "Firmante",
            "class" => "traffic-select-large",
            "multiOptions" => $rfcs,
            "required" => true,
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe seleccionar un solicitante"
                        ))),
            ),
        ));

        $this->addElement("text", "Patente", array(
            "placeholder" => "Patente",
            "class" => "focused span2",
            "required" => true,
            "attribs" => array("readonly" => "true"),
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una patente"
                        ))),
            ),
        ));

        $this->addElement("text", "Aduana", array(
            "placeholder" => "Aduana",
            "class" => "focused span2",
            "required" => true,
            "attribs" => array("readonly" => "true"),
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una aduana"
                        ))),
            ),
        ));

        $this->addElement("select", "tipoOperacion", array(
            "placeholder" => "Tipo de Operación",
            "class" => "traffic-select-medium",
            "multiOptions" => array(
                "" => "-- Seleccionar --",
                "TOCE.IMP" => "Importación",
                "TOCE.EXP" => "Exportación",
            ),
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe seleccionar el tipo de operación"
                        ))),
            ),
            "required" => true,
        ));

        $this->addElement("select", "tipoFigura", array(
            "placeholder" => "Tipo de Figura",
            "class" => "traffic-select-medium",
            "multiOptions" => array(
                "" => "-- Seleccionar --",
                "1" => "Agente Aduanal",
                "2" => "Apoderado Aduanal",
                "3" => "Mandatario",
                "4" => "Exportador",
                "5" => "Importador",
            ),
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe el tipo de figura"
                        ))),
            ),
            "required" => true,
        ));
    }

}
