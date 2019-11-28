<?php

class Usuarios_Form_NuevoUsuario extends Twitter_Bootstrap_Form_Horizontal {

    protected $_edit = null;
    protected $_patente = null;

    protected function setEdit($edit) {
        $this->_edit = $edit;
    }

    public function setPatente($patente) {
        $this->_patente = $patente;
    }

    public function init() {
        $this->setIsArray(true);

        $this->addElement("text", "nombre", array(
            "class" => "traffic-input-large",
            "required" => true,
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Nombre del usuario no especificado"
                        ))),
            ),
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("text", "email", array(
            "class" => "traffic-input-large",
            "required" => true,
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Falta correo eletrónico"
                        ))),
            ),
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("text", "usuario", array(
            "class" => "traffic-input-medium",
            "required" => true,
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Falta nombre de usuario"
                        ))),
            ),
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $arr = array("" => "---");
        $mapper = new Application_Model_CustomsMapper();
        $rows = $mapper->getAllCompanies();
        foreach ($rows as $item) {
            $arr[$item["rfc"]] = $item["nombre"];
        }
        $this->addElement("select", "empresa", array(
            "class" => "traffic-select-large",
            "multiOptions" => $arr,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $patente = array("" => "---");
        $adus = new Trafico_Model_TraficoAduanasMapper();
        foreach ($adus->obtenerPatentes() as $item) {
            $patente[$item["patente"]] = $item["patente"];
        }

        $this->addElement("select", "patenteUsuario", array(
            "class" => "traffic-select-small",
            "decorators" => array("ViewHelper", "Errors", "Label",),
            "multiOptions" => $patente,
        ));

        $this->addElement("select", "aduanaUsuario", array(
            "class" => "traffic-select-large",
            "multiOptions" => array("" => "---"),
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $rolesUsuarios = array("" => "---");
        $rol = new Usuarios_Model_RolesMapper();
        $roles = $rol->getRoles();
        foreach ($roles as $r) {
            if ($r["nombre"] != "super") {
                $rolesUsuarios[$r["id"]] = ucfirst($r["desc"]);
            } else {
                $rolesUsuarios[$r["id"]] = "Generico";
            }
        }
        $this->addElement("select", "rol", array(
            "class" => "traffic-select-medium",
            "multiOptions" => $rolesUsuarios,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $deptosUsuarios = array("" => "---");
        $dep = new Usuarios_Model_DepartamentosMapper();
        $deptos = $dep->getDeptos();
        foreach ($deptos as $d) {
            $deptosUsuarios[$d["nombre"]] = ucfirst($d["nombre"]);
        }

        $this->addElement("select", "departamento", array(
            "class" => "traffic-select-large",
            "multiOptions" => $deptosUsuarios,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("password", "password", array(
            "class" => "traffic-input-small",
            "required" => true,
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Debe establecer una contraseña"
                        ))),
                array("validator" => "StringLength", "options" => array(6, 25, "messages" => array(
                            "stringLengthTooShort" => "Contraseña muy corta - debe ser de al menos %min% caracteres",
                            "stringLengthTooLong" => "Contraseña muy larga - no más de %max% caracteres"
                        )))
            )
        ));

        $this->addElement("password", "confirm", array(
            "class" => "traffic-input-small",
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));
        $sysPed = array("" => "---");
        $sysPed[0] = "Ninguno";
        $sped = new Usuarios_Model_SisPedimentosMapper();
        $speds = $sped->getSystems();
        foreach ($speds as $sp) {
            $sysPed[$sp["id"]] = ucfirst($sp["nombre"]) . " - " . $sp["ubicacion"];
        }

        $this->addElement("select", "sispedimentos", array(
            "class" => "traffic-select-large",
            "multiOptions" => $sysPed,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("checkbox", "estatus", array(
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));
    }

}
