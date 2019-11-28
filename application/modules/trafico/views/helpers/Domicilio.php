<?php

class Zend_View_Helper_Domicilio extends Zend_View_Helper_Abstract {

    public function domicilio($cli, $pro) {
        $html = '';
        if (isset($cli) && is_array($cli) && !empty($cli)) {
            $html .= '<tr>'
                    . '<th style="width: 120px; text-align:right">Identificador</th>'
                    . '<td colspan="3">' . $this->_tipoIdentificador($this->_valor($cli["clave"])) . '</td>'
                    . '<th style="width: 120px; text-align:right">Identificador</th>'
                    . '<td colspan="3">' . $this->_tipoIdentificador($this->_valor($pro["clave"])) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">RFC</th>'
                    . '<td colspan="3">' . $this->_valor($cli["identificador"]) . '</td>'
                    . '<th style="text-align: right">TaxId</th>'
                    . '<td colspan="3">' . $this->_valor($pro["identificador"]) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">Razon Social</th>'
                    . '<td colspan="3">' . $this->_valor($cli["nombre"]) . '</td>'
                    . '<th style="text-align: right">Razon Social</th>'
                    . '<td colspan="3">' . $this->_valor($pro["nombre"]) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">Calle</th>'
                    . '<td colspan="3">' . $this->_valor($cli["calle"]) . '</td>'
                    . '<th style="text-align: right">Calle</th>'
                    . '<td colspan="3">' . $this->_valor($pro["calle"]) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">Num Ext.</th>'
                    . '<td style="width: 120px">' . $this->_valor($cli["numExt"]) . '</td>'
                    . '<th style="width: 60px">Num Int.</th>'
                    . '<td>' . $this->_valor($cli["numInt"]) . '</td>'
                    . '<th style="text-align: right">Num Ext.</th>'
                    . '<td style="width: 120px">' . $this->_valor($pro["numExt"]) . '</td>'
                    . '<th style="width: 60px">Num Int.</th>'
                    . '<td>' . $this->_valor($pro["numInt"]) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">Colonia</th>'
                    . '<td colspan="3">' . $this->_valor($cli["colonia"]) . '</td>'
                    . '<th style="text-align: right">Colonia</th>'
                    . '<td colspan="3">' . $this->_valor($pro["colonia"]) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">Municipio</th>'
                    . '<td colspan="3">' . $this->_valor($cli["municipio"]) . '</td>'
                    . '<th style="text-align: right">Municipio</th>'
                    . '<td colspan="3">' . $this->_valor($pro["municipio"]) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">C.P.</th>'
                    . '<td colspan="3">' . $this->_valor($cli["codigoPostal"]) . '</td>'
                    . '<th style="text-align: right">C.P.</th>'
                    . '<td colspan="3">' . $this->_valor($pro["codigoPostal"]) . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th style="text-align: right">País</th>'
                    . '<td colspan="3">' . $this->_valor($cli["pais"]) . '</td>'
                    . '<th style="text-align: right">País</th>'
                    . '<td colspan="3">' . $this->_valor($pro["pais"]) . '</td>'
                    . '</tr>';
        } else {
            
        }
        return $html;
    }

    protected function _valor($value) {
        return ($value != null) ? $value : '';
    }

    protected function _tipoIdentificador($value) {
        if ($value != null && $value != '') {
            switch ((int) $value) {
                case 0:
                    return '0-TAX_ID';
                case 1:
                    return '1-RFC';
                case 2:
                    return '2-CURP';
                case 3:
                    return '3-SIN_TAX_ID';
            }
        } else {
            return '3-SIN_TAX_ID';
        }
    }

}
