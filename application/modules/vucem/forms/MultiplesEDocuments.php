<?php

class Vucem_Form_MultiplesEDocuments extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->setMethod("POST");

        $rfcs = array();
        $sign = new Vucem_Model_VucemFirmanteMapper();
        $rfc = $sign->obtenerFirmantes(APPLICATION_ENV);
        $rfcs[""] = "-- Seleccionar --";
        foreach ($rfc as $r) {
            $rfcs[$r["rfc"]] = $r["rfc"] . " - " . $r["razon"];
        }

        $this->addElement("select", "firmante", array(
            "label" => "Firmante: *",
            "placeholder" => "firmante",
            "multiOptions" => $rfcs,
            "required" => true,
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe seleccionar un solicitante"
                        ))),
            ),
        ));
        
        $table = new Archivo_Model_DocumentosMapper();
        $result = $table->getAllEdocument();
        $docs[""] = "-- Seleccionar --";
        foreach ($result as $item) {
            $docs[$item["id"]] = $item["id"] . " - " . $item["nombre"];
        }
        
        $this->addElement("select", "tipo", array(
            "multiOptions" => $docs,
            "attribs" => array("style" => "width: 150px"),
        ));

        $this->addElement("text", "patente", array(
            "label" => "Patente:",
            "placeholder" => "Patente",
            "required" => true,
            "class" => "traffic-input-small",
            "attribs" => array("readonly" => "true"),
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una patente"
                        ))),
            ),
        ));

        $this->addElement("text", "aduana", array(
            "label" => "Aduana:",
            "placeholder" => "Aduana",
            "required" => true,
            "class" => "traffic-input-small",
            "attribs" => array("readonly" => "true"),
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una aduana"
                        ))),
            ),
        ));

        $this->addElement("text", "pedimento", array(
            "label" => "Pedimento: *",
            "placeholder" => "Pedimento",
            "required" => true,
            "class" => "traffic-input-medium",
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar un pedimento"
                        ))),
            ),
        ));

        $this->addElement("text", "rfc", array(
            "label" => "RFC de consulta: *",
            "class" => "traffic-input-medium",
            "placeholder" => "RFC de consulta",
        ));

        $this->addElement("text", "referencia", array(
            "label" => "Referencia: *",
            "placeholder" => "Referencia",
            "required" => true,
            "class" => "traffic-input-medium",
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una referencia"
                        ))),
            ),
        ));
        
        $this->addElement("text", "nombre", array(
            "label" => "Nombre de la empresa:",
            "placeholder" => "Nombre de la empresa",
            "required" => true,
            "class" => "traffic-input-large",
            "attribs" => array("autocomplete" => "off"),
        ));
        
        $this->addElement("checkbox", "misma", array(
            "label" => "¿Referencia única?",
            "placeholder" => "Referencia unica"
        ));
        
        
    }

}
