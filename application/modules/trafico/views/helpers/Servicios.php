<?php

class Zend_View_Helper_Servicios extends Zend_View_Helper_Abstract
{
    public function servicios($aduana, $name, $selected = null)
    {
        $model = new Trafico_Model_TraficoServiciosMapper();
        $options = $model->obtenerServicios($aduana);
        $html = "<select id=\"{$name}\" name=\"{$name}\">";
        $html .= "<option value=\"\">-- Seleccionar --</option>";
        foreach ($options as $item) {
            if(isset($selected) && $selected != 0 && $selected == $item["id"]) {
                $html .= "<option value=\"{$item["id"]}\" selected=\"selected\">{$item["servicio"]}</option>";
            } else {
                $html .= "<option value=\"{$item["id"]}\">{$item["servicio"]}</option>";
            }
        }        
        return $html . "</select>";
    }
    
}
