<?php

class Zend_View_Helper_Monedas extends Zend_View_Helper_Abstract {

    public function monedas($name) {
        $model = new Vucem_Model_VucemMonedasMapper();
        $options = $model->getAllCurrencies();
        $html = "<select id=\"{$name}\" name=\"{$name}\">";
        $html .= "<option value=\"\">-- Seleccionar --</option>";
        foreach ($options as $item) {
            $html .= "<option value=\"{$item["codigo"]}\">{$item["codigo"]}</option>";
        }
        return $html . "</select>";
    }

}
