<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class V2_Html {

    protected $dom;
    protected $select;

    function __construct() {
        $this->dom = new DOMDocument("1.0");
    }

    public function createElement($tagName, $value = NULL, $attributes = NULL) {
        $element = ($value != NULL ) ? $this->dom->createElement($tagName, $value) : $this->dom->createElement($tagName);
        if ($attributes != NULL) {
            foreach ($attributes as $attr => $val) {
                $element->setAttribute($attr, $val);
            }
        }
        return $element;
    }

    public function getHtml() {
        return $this->dom->saveHTML();
    }

    public function nuevosTraficos($referencia, $nombreCliente, $tipoOperacion) {
        $div = $this->dom->createElement("div");
        $ul = $this->dom->createElement("ul");
        $div->setAttribute("class", "newTraffic");
        $ul->appendChild($this->createElement("li", $referencia, array("style" => "width: 100px")));
        $ul->appendChild($this->createElement("li", $nombreCliente, array("style" => "width: 100px")));
        $ul->appendChild($this->createElement("li", (($tipoOperacion == "1") ? "IMP" : "EXP"), array("style" => "width: 40px")));
        $li = $this->createElement("li", null, array("style" => "width: 40px"));
        $li->appendChild($this->createElement("input", null, array("id" => "rectificacion", "type" => "checkbox")));
        $ul->appendChild($li);
        $li = $this->createElement("li", null, array("style" => "width: 40px"));
        $li->appendChild($this->createElement("input", null, array("id" => "consolidado", "type" => "checkbox")));
        $ul->appendChild($li);
        $li = $this->createElement("li", null, array("style" => "width: 40px"));
        $li->appendChild($this->createElement("button", "&cross;", array("id" => "addMore", "class" => "button-red button-plus small")));
        $ul->appendChild($li);
        $div->appendChild($ul);
        $this->dom->appendChild($div);
    }

    public function select($class = null, $id = null, $style = null) {
        $this->dom = new DOMDocument("1.0");
        $this->select = $this->dom->createElement("select");
        if (isset($class)) {
            $this->select->setAttribute("class", $class);
        }
        if (isset($id)) {
            $this->select->setAttribute("id", $id);
            $this->select->setAttribute("name", $id);
        }
        if (isset($style)) {
            $this->select->setAttribute("style", $style);
        }
        $this->dom->appendChild($this->select);
    }
    
    public function setStyle($style) {
        $this->select->setAttribute("style", $style);
    }
    
    public function setMultiple() {
        $this->select->setAttribute("multiple", "true");
    }
    
    public function setDisabled() {
        $this->select->setAttribute("disabled", "disabled");
    }
    
    public function setSelectDisabled() {
        $this->select->setAttribute("disabled", "true");
    }
    
    public function setSelectReadonly() {
        $this->select->setAttribute("readonly", "true");
    }

    public function addSelectOption($attr, $value, $selected = null) {
        $option = $this->dom->createElement("option", $value);
        $option->setAttribute("value", $attr);
        if ($selected) {
            $option->setAttribute("selected", "selected");
        }
        $this->select->appendChild($option);
    }

    public function trafficIconEdit($value, $function) {
        return "<div onclick=\"{$function}({$value})\" class=\"traffic-icon traffic-icon-edit\"></div>";
    }

    public function trafficIconCancel($value, $function) {
        return "<div onclick=\"{$function}({$value})\" class=\"traffic-icon traffic-icon-cancel\"></div>";
    }

    public function trafficIconSave($value, $function) {
        return "<div onclick=\"{$function}({$value})\" class=\"traffic-icon traffic-icon-save\"></div>";
    }

    public function trafficIconDelete($value, $function) {
        return "<div onclick=\"{$function}({$value})\" class=\"traffic-icon traffic-icon-delete\"></div>";
    }

    public function dateInput($id, $value) {
        $this->dom = new DOMDocument("1.0");
        $div = $this->dom->createElement("div");
        $div->setAttribute("style", "width: 120px");
        $div->appendChild($this->createElement("input", null, array("class" => "fecha", "name" => $id, "id" => $id, "value" => $value, "style" => "float: left; text-align: center; width: 80px; border-radius: 2px !important; border: 1px solid #bbbbbb !important")));
        $div->appendChild($this->createElement("div", null, array("class" => "traffic-icon traffic-icon-calendar")));
        $this->dom->appendChild($div);
    }

}
