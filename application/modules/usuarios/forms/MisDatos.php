<?php

class Usuarios_Form_MisDatos extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $this->setIsArray(true);
        $decorators = array("ViewHelper", "Errors", "Label",);
        $this->addElement("hidden", "id", array("decorators" => $decorators));

        $this->addElement("text", "nombre", array(
            "attribs" => array("class" => "traffic-input-large"),
        ));

        $this->addElement("text", "email", array(
            "attribs" => array("class" => "traffic-input-medium"),
        ));

        $this->addElement("text", "usuario", array(
            "attribs" => array("class" => "traffic-input-small"),
            "validators" => array(
                array("validator" => "NotEmpty", "breakChainOnFailure" => true, "options" => array("messages" => array(
                            "isEmpty" => "Falta nombre de usuario"
                        ))),
            )
        ));

        $empresas = array();
        $com = new Application_Model_CustomsMapper();
        $comps = $com->getAllCompanies();
        foreach ($comps as $co) {
            $empresas[$co["nombre"]] = $co["nombre"];
        }

        $this->addElement("select", "empresa", array(
            "class" => "traffic-select-large",
            "multiOptions" => $empresas,
        ));

        $patente = array();
        $pat = new Application_Model_CustomsMapper();
        $patents = $pat->getAllPatents();
        foreach ($patents as $p) {
            $patente[$p["patente"]] = $p["patente"];
        }

        $this->addElement("select", "patente", array(
            "class" => "traffic-select-small",
            "multiOptions" => $patente,
            "value" => "3589",
        ));

        $aduanas = array();
        $cust = new Application_Model_CustomsMapper();
        $customs = $cust->getAllCustoms();
        foreach ($customs as $c) {
            $aduanas[$c["aduana"]] = $c["aduana"] . " - " . $c["ubicacion"];
        }

        $this->addElement("select", "aduana", array(
            "class" => "traffic-select-small",
            "multiOptions" => $aduanas,
            "value" => "640",
        ));

        $rolesUsuarios = array();
        $rol = new Usuarios_Model_RolesMapper();
        $roles = $rol->getRoles();
        foreach ($roles as $r) {
            if ($r["nombre"] != "super") {
                $rolesUsuarios[$r["id"]] = ucfirst($r["desc"]);
            }
        }

        $this->addElement("select", "rol", array(
            "class" => "traffic-select-medium",
            "multiOptions" => $rolesUsuarios,
        ));

        $deptosUsuarios = array();
        $dep = new Usuarios_Model_DepartamentosMapper();
        $deptos = $dep->getDeptos();
        foreach ($deptos as $d) {
            $deptosUsuarios[$d["nombre"]] = ucfirst($d["nombre"]);
        }

        $this->addElement("select", "departamento", array(
            "class" => "traffic-select-medium",
            "multiOptions" => $deptosUsuarios,
        ));

        $this->addElement("password", "password", array(
            "class" => "traffic-input-small",
        ));

        $this->addElement("password", "rptPassword", array(
            "class" => "traffic-input-small",
        ));
    }

}
