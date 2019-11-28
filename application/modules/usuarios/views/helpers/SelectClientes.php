<?php

class Zend_View_Helper_SelectClientes extends Zend_View_Helper_Abstract {

    public function selectClientes($id) {
        $model = new Trafico_Model_ClientesMapper();
        $rows = $model->obtener();
        if (isset($rows) && $rows !== false && !empty($rows)) {
            $html = "<select id=\"{$id}\" name=\"{$id}\" class=\"traffic-select-large\">";
            $html .= "<option value=\"\">---</option>";
            foreach ($rows as $item) {
                $html .= "<option value=\"{$item["id"]}\">{$item["nombre"]}</option>";
            }
            $html .= "</select>";
        }
        return $html;
    }

}
