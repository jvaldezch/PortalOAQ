<?php

class Zend_View_Helper_SelectYear extends Zend_View_Helper_Abstract {

    public function selectYear($id) {
        foreach (range(date("Y"), 2011, -1) as $item) {
            $array[$item] = $item;
        }
        $html = new V2_Html();
        $html->select("traffic-select-small", $id);
        foreach ($array as $k => $v) {
            $html->addSelectOption($k, $v);
        }
        return $html->getHtml();
    }

}
