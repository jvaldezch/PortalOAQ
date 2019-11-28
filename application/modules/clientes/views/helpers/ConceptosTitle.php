<?php

class Zend_View_Helper_ConceptosTitle extends Zend_View_Helper_Abstract {

    public function conceptosTitle($concept, $lang = null) {
        if (isset($lang) && $lang === 'en') {
            return "<th>Invoice {$concept}</th>"
                    . "<th>Total without IVA</th>"
                    . "<th>Total with IVA</th>"
                    . "<th>{$concept} Assessment by Invoice and AWB</th>"
                    . "<th>{$concept} Assessment by P.N.</th>";
        } else {
            return "<th>#Factura Terminal {$concept}</th>"
                    . "<th>Total {$concept} MXN  sin   IVA </th>"
                    . "<th>Total {$concept} MXN  con  IVA </th>"
                    . "<th>Prorrateo por lote {$concept} MXN</th>"
                    . "<th>Prorrateo por np {$concept} MXN</th>";            
        }
    }

}
