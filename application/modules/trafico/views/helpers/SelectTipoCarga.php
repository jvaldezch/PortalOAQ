<?php

class Zend_View_Helper_SelectTipoCarga extends Zend_View_Helper_Abstract {

    public function selectTipoCarga($idAduana, $id, $value = null) {
        $rows = array(
            '' => '--',
            'CARGA SUELTA' => 'CARGA SUELTA',
            'CONTENEDOR' => 'CONTENEDOR',
        );
        if (isset($rows) && $rows !== false && !empty($rows)) {
            $html = '<select id="' . $id . '" name="' . $id . '" class="traffic-select-small">';
            foreach ($rows as $k => $v) {
                if (isset($value)) {
                    if ($value == $k) {
                        $html .= '<option value="' . $k . '" selected="selected">' . $v . '</option>';
                        continue;
                    }
                }
                $html .= '<option value="' . $k . '">' . $v . '</option>';
            }
            $html .= '</select>';
        }
        return $html;
    }

}
