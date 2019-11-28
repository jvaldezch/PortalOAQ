<?php

class Zend_View_Helper_SelectPlanta extends Zend_View_Helper_Abstract {

    public function selectPlanta($idCliente, $value = null) {
        $mppr = new Trafico_Model_ClientesPlantas();
        $arr = $mppr->obtener($idCliente);
        $html = new V2_Html();
        $html->select("traffic-select-medium", "idPlanta");
        $html->addSelectOption("", "---");
        if (!empty($arr)) {
            if (count($arr)) {
                foreach ($arr as $item) {
                    if (isset($value) && $item["id"] == $value) {
                        $html->addSelectOption($item["id"], $item["descripcion"], true);
                    } else {
                        $html->addSelectOption($item["id"], $item["descripcion"]);
                    }
                }
            }
        } else {
            $html->setSelectDisabled();
        }
        return $html->getHtml();
    }

}
