<?php

class Zend_View_Helper_ImpuestosTitle extends Zend_View_Helper_Abstract {

    public function impuestosTitle($concept) {
        return "<th>{$concept} MXN</th>"
        . "<th>{$concept} Assessment by Invoice and AWB</th>"
        . "<th>{$concept} Assessment by P.N.</th>";
    }

}
