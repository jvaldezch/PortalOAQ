<?php

class Zend_View_Helper_Edocuments extends Zend_View_Helper_Abstract {

    public function edocuments($id, $doc) {
        $table = new Archivo_Model_DocumentosMapper();
        $result = $table->getAllEdocument();
        $select = '<option value="0">-- Seleccionar --</option>';
        foreach ($result as $item) {
            if ($item["id"] == $doc) {
                $select .= '<option value="' . $item['id'] . '" selected="selected">' . ($item['id'] . ' - ' . $item['nombre']) . '</option>';
            } else {
                $select .= '<option value="' . $item['id'] . '">' . ($item['id'] . ' - ' . $item['nombre']) . '</option>';
            }
        }
        return '<select class="doctype" id="doctype_' . $id . '">' . $select . '</select>';
    }

}
