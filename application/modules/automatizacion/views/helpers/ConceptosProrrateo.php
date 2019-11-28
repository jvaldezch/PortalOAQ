<?php

class Zend_View_Helper_ConceptosProrrateo extends Zend_View_Helper_Abstract {

    public function conceptosProrrateo($array = null, $cantidadPartes = null, $totalFactura = null) {
        if (isset($array) && !empty($array)) {
            return "<td>" . $array["folio"] . "</td>"
                    . "<td>" . number_format(($array["importe"] / 1.16), 4) . "</td>"
                    . "<td>" . number_format($array["importe"], 4) . "</td>"
                    . "<td>" . number_format((($array["importe"] / $totalFactura) * $cantidadPartes), 4) . "</td>"
                    . "<td>" . number_format(((($array["importe"] / $totalFactura) * $cantidadPartes))/$cantidadPartes, 4) . "</td>";
        } else {
            return "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>";
        }
    }

}
