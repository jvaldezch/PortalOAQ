<?php

class Administracion_Form_Repositorio extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(false);
        $this->_addClassNames('well');
        $this->setAction("/administracion/index/repositorio");
        $this->setMethod('GET');
        
        $this->addElement('text', 'poliza', array(
            'label' => 'Referencia',
            'placeholder' => 'Referencia',
            'class' => 'focused'
        ));
        
    }

}
