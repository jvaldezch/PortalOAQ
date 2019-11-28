<?php

class Trafico_Form_NuevoFtp extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setMethod("post");
        $deco = array("ViewHelper", "Errors", "Label",);
        
        $this->addElement("select", "type", array(
            "class" => "traffic-input-medium",
            "multiOptions"  => array(
                "" => "---",
                "m3" => "Archivos M3",
                "expedientes" => "Expedientes",
            ),
            "attribs" => array("tabindex" => "1"),
        ));

        $this->addElement("text", "rfc", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "2"),
            "decorators" => $deco,
        ));

        $this->addElement("text", "url", array(
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "3"),
            "decorators" => $deco,
        ));
        
        $this->addElement("text", "user", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "4"),
            "decorators" => $deco,
        ));
        
        $this->addElement("text", "password", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "5"),
            "decorators" => $deco,
        ));
        
        $this->addElement("text", "port", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "6"),
            "decorators" => $deco,
        ));
        
        $this->addElement("text", "remoteFolder", array(
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "7"),
            "decorators" => $deco,
        ));
        
    }

}
