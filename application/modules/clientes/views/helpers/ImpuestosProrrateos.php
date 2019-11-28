<?php

class Zend_View_Helper_ImpuestosProrrateos extends Zend_View_Helper_Abstract {

    public function impuestosProrrateos($key, $array, $cantidadPartes = null, $totalFactura = null) {
        if (isset($array[$key])) {
            $html = "<td>" . number_format($array[$key]["importe"], 4) . "</td>"
                    . "<td>" . number_format((($array[$key]["importe"] / $totalFactura) * $cantidadPartes), 4) . "</td>"
                    . "<td>" . number_format(((($array[$key]["importe"] / $totalFactura) * $cantidadPartes)) / $cantidadPartes, 4) . "</td>";
            return array(
                'html' => $html,
                'pn' => (float)number_format(((($array[$key]["importe"] / $totalFactura) * $cantidadPartes)) / $cantidadPartes, 4)
            );
        } else {
            return array(
                'html' => "<td></td><td></td><td></td>",
                'pn' => 0
            );
        }
    }

}
