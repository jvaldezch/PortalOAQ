<?php

class Zend_View_Helper_ImpuestosTitle extends Zend_View_Helper_Abstract {

    public function impuestosTitle($concept) {
        return "<th>{$concept} MXN</th>"
        . "<th>Prorrateo por lote {$concept} MXN</th>"
        . "<th>Prorrateo por NP {$concept} MXN</th>";
    }

}
