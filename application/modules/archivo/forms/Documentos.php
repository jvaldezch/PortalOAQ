<?php

class Archivo_Form_Documentos extends Twitter_Bootstrap_Form_Horizontal {
    
    public function init() {
        $documents = new Archivo_Model_DocumentosMapper();
        $docs = $documents->getAll();
        $data = array('0' => '-- Seleccionar --');
        foreach ($docs as $item) {
            $data[$item["id"]] = $item["nombre"];
        }
        
        $document = new Zend_Form_Element_Select('documento');
        $document->addMultiOptions($data);
        $document->setDecorators(array('ViewHelper'));
        $this->addElement($document);
    }

}
