<?php

class Zend_View_Helper_SelectClientes extends Zend_View_Helper_Abstract {

    public function selectClientes($id) {
        $model = new Trafico_Model_ClientesMapper();
        $arr = $model->obtener();
        $html = new V2_Html();
        if (count($arr)) {
            $html->select("traffic-select-small", $id);
            $html->addSelectOption("", "---");
            foreach ($arr as $item) {
                $html->addSelectOption($item["id"], $item["nombre"]);
            }
            return $html->getHtml();
        }
        return;
    }

}
