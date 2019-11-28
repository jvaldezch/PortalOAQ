<?php

class Archivo_Form_ArchivosValidacion extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setMethod("get");
        $this->setAttrib("id", "form");

        $deco = array("ViewHelper", "Errors", "Label",);

        $this->addElement("text", "fecha", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "1", "style" => "text-align: center"),
            "decorators" => $deco,
        ));

        $this->addElement("text", "pedimento", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "3", "style" => "text-align: center"),
            "decorators" => $deco,
        ));

        $this->addElement("select", "aduana", array(
            "class" => "traffic-select-medium",
            "multiOptions" => array(
                0 => "-- Todas --",
                1 => "3589-640 - Queŕetaro",
                7 => "3589-240 - Nuevo Laredo, Tamps.",
            ),
            "decorators" => $deco,
            "attribs" => array("tabindex" => "2"),
        ));
    }

}
