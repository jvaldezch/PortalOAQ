<?php

class Zend_View_Helper_Transportistas extends Zend_View_Helper_Abstract {

    public function transportistas($aduana, $tipo, $name, $selected = null) {
        $model = new Trafico_Model_TraficoTransportistasMapper();
        $options = $model->obtenerTransportistas($aduana, $tipo);
        $html = "<select id=\"{$name}\" name=\"{$name}\">";
        $html .= "<option value=\"\">-- Seleccionar --</option>";
        $html .= "<option value=\"0\">N/D</option>";
        foreach ($options as $item) {
//            $html .= "<option value=\"{$item["id"]}\">{$item["nombre"]}</option>";
            if(isset($selected) && $selected != 0 && $selected == $item["id"]) {
                $html .= "<option value=\"{$item["id"]}\" selected=\"selected\">{$item["nombre"]}</option>";
            } else {
                $html .= "<option value=\"{$item["id"]}\">{$item["nombre"]}</option>";
            }
        }
        return $html . "</select>";
    }

}
