<?php

class Default_Form_Contacto extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        
        $this->setIsArray(true);
        $this->setElementsBelongTo("bootstrap");
        $this->_addClassNames("well");
        $this->setAction("/index/contacto");
        $this->setMethod("POST");

        $this->addElement("text", "nombre", array(
            "label" => "Nombre",
            "placeholder" => "Nombre",
            "class" => "focused",
            "attribs" => array(
                "autocomplete" => "off",
                "style" => "width: 270px",
            ),
        ));

        $this->addElement("text", "email", array(
            "label" => "Email",
            "placeholder" => "Email",
            "class" => "focused",
            "attribs" => array(
                "autocomplete" => "off",
                "style" => "width: 270px",
            ),
        ));

        $this->addElement("text", "telefono", array(
            "label" => "Teléfono",
            "placeholder" => "Teléfono",
            "class" => "focused",
            "attribs" => array(
                "autocomplete" => "off",
                "style" => "width: 270px",
            ),
        ));

        $this->addElement("text", "empresa", array(
            "label" => "Empresa",
            "placeholder" => "Empresa",
            "class" => "focused",
            "attribs" => array(
                "autocomplete" => "off",
                "style" => "width: 270px",
            ),
        ));

        $this->addElement("textarea", "mensaje", array(
            "label" => "Mensaje",
            "placeholder" => "Mensaje",
            "class" => "focused",
            "attribs" => array(
                "autocomplete" => "off",
                "style" => "height: 178px; width: 270px",
            ),
        ));

        $this->addElement("button", "submit", array(
            "label" => "Enviar",
            "type" => "submit",
            "buttonType" => "primary",
            "decorators" => Array("ViewHelper", "HtmlTag"),
            "attribs" => array("style" => "margin-top:5px"),
        ));
    }

}
