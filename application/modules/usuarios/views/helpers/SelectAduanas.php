<?php

class Zend_View_Helper_SelectAduanas extends Zend_View_Helper_Abstract {

    public function selectAduanas($id) {
        $model = new Trafico_Model_TraficoAduanasMapper();
        $arr = $model->obtener();
        $html = new V2_Html();
        $html->select("traffic-select-large", $id);
        $html->addSelectOption("", "---", true);
        foreach ($arr as $item) {
            $html->addSelectOption($item["id"], $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"]);
        }
        return $html->getHtml();
    }

}
