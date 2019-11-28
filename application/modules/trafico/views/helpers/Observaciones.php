<?php

class Zend_View_Helper_Observaciones extends Zend_View_Helper_Abstract {

    public function observaciones($aduana, $name, $selected = null) {
        $model = new Trafico_Model_TraficoObservacionesMapper();
        $options = $model->obtenerObservaciones($aduana);
        $html = "<select id=\"{$name}\" name=\"{$name}\">";
        $html .= "<option value=\"\">-- Seleccionar --</option>";
        foreach ($options as $item) {
//            $html .= "<option value=\"{$item["id"]}\">{$item["observacion"]}</option>";
            if(isset($selected) && $selected != 0 && $selected == $item["id"]) {
                $html .= "<option value=\"{$item["id"]}\" selected=\"selected\">{$item["observacion"]}</option>";
            } else {
                $html .= "<option value=\"{$item["id"]}\">{$item["observacion"]}</option>";
            }
        }
        return $html . "</select>";
    }

}
