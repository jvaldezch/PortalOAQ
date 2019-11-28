<?php

class Zend_View_Helper_SelectValidador extends Zend_View_Helper_Abstract {

    public function selectValidador($id) {
        $model = new Application_Model_DirectoriosValidacion();
        $arr = $model->obtenerDirectorios();
        $html = new V2_Html();
        $html->select("traffic-select-large", $id);
        $html->addSelectOption("", "---", true);
        foreach ($arr as $item) {
            $html->addSelectOption($item["id"], $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"]);
        }
        return $html->getHtml();
    }

}
