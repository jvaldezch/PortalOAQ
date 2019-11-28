<?php

class Archivo_Form_ArchivosExpediente extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->setMethod("POST");

        $table = new Archivo_Model_DocumentosMapper();
        $result = $table->getAllNormal();
        $docs[""] = "-- Seleccionar --";
        foreach ($result as $item) {
            $docs[$item["id"]] = $item["nombre"];
        }

        $this->addElement("select", "tipo", array(
            "multiOptions" => $docs,
        ));

        $this->addElement("text", "patente", array(
            "required" => true,
            "attribs" => array("style" => "text-align: center; width: 40px"),
            "class" => "traffic-input-tiny",
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una patente"
                        ))),
            ),
        ));

        $this->addElement("text", "aduana", array(
            "required" => true,
            "attribs" => array("style" => "text-align: center; width: 40px"),
            "class" => "traffic-input-tiny",
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una aduana"
                        ))),
            ),
        ));

        $this->addElement("text", "pedimento", array(
            "required" => true,
            "attribs" => array("style" => "text-align: center"),
            "class" => "traffic-input-small",
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar un pedimento"
                        ))),
            ),
        ));

        $this->addElement("text", "rfc_cliente", array(
            "attribs" => array("style" => "text-align: center"),
            "class" => "traffic-input-small",
            "required" => true,
        ));

        $this->addElement("text", "referencia", array(
            "required" => true,
            "attribs" => array("style" => "text-align: center"),
            "class" => "traffic-input-small",
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe especificar una referencia"
                        ))),
            ),
        ));
    }

}
