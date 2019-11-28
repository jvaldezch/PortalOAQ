<?php

class Usuarios_Form_AgregarUsuario extends Twitter_Bootstrap_Form_Horizontal {

    protected $_edit = null;

    protected function setEdit($edit) {
        $this->_edit = $edit;
    }

    public function init() {
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        // http://www.zfforums.com/zend-framework-components-13/model-view-controller-mvc-21/form-validator-notempty-without-being-required-3573.html
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');

        $this->addElement('text', 'nombre', array(
            'label' => 'Nombre del usuario',
            'placeholder' => 'Nombre del usuario',
            'class' => 'focused',
            'required' => true,
            'attribs' => array('style' => 'width: 450px'),
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Nombre del usuario no especificado'
                        ))),
            )
        ));

        $this->addElement('text', 'email', array(
            'label' => 'Email',
            'placeholder' => 'Email',
            'class' => 'focused',
            'prepend' => '@',
            'attribs' => array('style' => 'width: 350px'),
            'required' => true,
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Falta correo eletrónico'
                        ))),
            )
        ));

        $this->addElement('text', 'usuario', array(
            'label' => 'Usuario',
            'placeholder' => 'Usuario',
            'class' => 'focused',
            'required' => true,
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Falta nombre de usuario'
                        ))),
            )
        ));

        $empresas = array();
        $com = new Application_Model_CustomsMapper();
        $comps = $com->getAllCompanies();
        foreach ($comps as $co) {
            $empresas[$co['rfc']] = $co["nombre"];
        }
        $this->addElement('select', 'empresa', array(
            'label' => 'Empresa',
            'placeholder' => 'Empresa',
            'class' => 'focused',
            'multiOptions' => $empresas,
            'attribs' => array('style' => 'width: 450px'),
            'value' => 'OAQ030623UL8',
        ));

        $patente = array();
        $pat = new Application_Model_CustomsMapper();
        $patents = $pat->getAllPatents();
        foreach ($patents as $p) {
            $patente[$p['patente']] = $p["patente"];
        }

        $this->addElement('select', 'patente', array(
            'label' => 'Patente',
            'placeholder' => 'Patente',
            'class' => 'focused',
            'multiOptions' => $patente,
            'attribs' => array('style' => 'width: 100px'),
            'value' => '3589',
        ));

        $aduanas = array();
        $cust = new Application_Model_CustomsMapper();
        $customs = $cust->getAllCustoms();
        foreach ($customs as $c) {
            $aduanas[$c['aduana']] = $c["aduana"] . ' - ' . $c['ubicacion'];
        }

        $this->addElement('select', 'aduana', array(
            'label' => 'Aduana',
            'placeholder' => 'Aduana',
            'class' => 'focused',
            'multiOptions' => $aduanas,
            'attribs' => array('style' => 'width: 300px'),
            'value' => '640',
        ));

        if (!isset($this->_edit)) {
            $rolesUsuarios = array();
            $rol = new Usuarios_Model_RolesMapper();
            $roles = $rol->getRoles();
            foreach ($roles as $r) {
                if ($r['nombre'] != 'super') {
                    $rolesUsuarios[$r['id']] = ucfirst($r['desc']);
                } else {
                    $rolesUsuarios[$r['id']] = "Generico";
                }
            }
            $this->addElement('select', 'rol', array(
                'label' => 'Rol',
                'placeholder' => 'Rol',
                'class' => 'focused',
                'multiOptions' => $rolesUsuarios,
                'attribs' => array('style' => 'width: 250px'),
            ));
        }

        $deptosUsuarios = array();
        $dep = new Usuarios_Model_DepartamentosMapper();
        $deptos = $dep->getDeptos();
        foreach ($deptos as $d) {
            $deptosUsuarios[$d['nombre']] = ucfirst($d['nombre']);
        }

        $this->addElement('select', 'departamento', array(
            'label' => 'Departamento',
            'class' => 'focused',
            'multiOptions' => $deptosUsuarios,
            'attribs' => array('style' => 'width: 450px'),
        ));

        $this->addElement('password', 'password', array(
            'label' => 'Contraseña',
            'placeholder' => 'Contraseña',
            'class' => 'focused',
            'required' => true,
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Debe establecer una contraseña'
                        ))),
                array('validator' => 'StringLength', 'options' => array(6, 25, 'messages' => array(
                            'stringLengthTooShort' => 'Contraseña muy corta - debe ser de al menos %min% caracteres',
                            'stringLengthTooLong' => 'Contraseña muy larga - no más de %max% caracteres'
                        )))
            )
        ));

        $this->addElement('password', 'pwd', array(
            'label' => 'Repetir contraseña',
            'placeholder' => 'Repetir contraseña',
            'class' => 'focused',
        ));

        $sysPed = array(
            0 => '-- Ninguno --',
        );
        $sped = new Usuarios_Model_SisPedimentosMapper();
        $speds = $sped->getSystems();
        foreach ($speds as $sp) {
            $sysPed[$sp['id']] = ucfirst($sp['nombre']) . ' - ' . $sp["ubicacion"];
        }

        $this->addElement('select', 'sispedimentos', array(
            'label' => 'Sistema de pedimentos',
            'class' => 'focused span4',
            'attribs' => array('style' => 'margin-top:5px'),
            'multiOptions' => $sysPed,
        ));

        $this->addElement('button', 'submit', array(
            'label' => (isset($this->_edit)) ? 'Editar usuario' : 'Agregar usuario',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px; margin-right: 5px'),
        ));
    }

}
