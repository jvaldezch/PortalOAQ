<?php

class Zend_View_Helper_ImpuestosProrrateo extends Zend_View_Helper_Abstract {

    public function impuestosProrrateo($value = null, $cantidadPartes = null, $totalFactura = null) {
        if (isset($value)) {
            return "<td>" . number_format($value, 4) . "</td>"
                    . "<td>" . number_format((($value / $totalFactura) * $cantidadPartes), 4) . "</td>"
                    . "<td>" . number_format(((($value / $totalFactura) * $cantidadPartes)) / $cantidadPartes, 4) . "</td>";
        } else {
            return "<td></td>"
                    . "<td></td>"
                    . "<td></td>";
        }
    }

}
