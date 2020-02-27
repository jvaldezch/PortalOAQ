<?php

/**
 * Clase para utilerias diversas o miscelaneas
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Checklist {

    protected $new;
    protected $current;
    protected $admin = array("administracion", "externo_administracion", "digitalizacionadmin");
    protected $ops = array("trafico", "vucem", "corresponsal", "externo_operaciones", "trafico_ejecutivo", "digitalizacionadmin");
    protected $supervisor = array("super", "super_admon", "gerente", "digitalizacionop", "digitalizacion");

    function getNew() {
        return $this->new;
    }

    function getCurrent() {
        return $this->current;
    }

    function setNew($new) {
        $this->new = $new;
    }

    function setCurrent($current) {
        $this->current = $current;
    }

    public function actualizarChecklist() {
        foreach ($this->current as $key => $value) {
            if (isset($this->new[$key])) {
                $this->current[$key] = $this->new[$key];
            }
        }
        foreach ($this->new as $key => $value) {
            if (!isset($this->current[$key])) {
                $this->current[$key] = $value;
            }
        }
        return $this->current;
    }

    public function obtenerChecklist($role = null, $fecha = null) {
        $mapper = new Archivo_Model_Checklist();
        if (isset($fecha)) {
            $v = 1;
            if (strtotime($fecha) > strtotime("2017-04-24")) {
                $v = 2;
            }
            if (strtotime($fecha) > strtotime("2020-02-26")) {
                $v = 3;
            }
        }
        if (isset($role)) {
            if (in_array($role, $this->admin)) {
                return $mapper->getGeneric(array("administracion"), $v);
            } elseif (in_array($role, $this->ops)) {
                return $mapper->getGeneric(array("operacion", "documentacion"), $v);
            } else {
                return $mapper->getGeneric(null, $v);
            }
        } else {
            return $mapper->getGeneric(null, $v);
        }
    }

    public function revision($username, $name, $status, $role) {
        if (in_array($role, $this->admin)) {
            return array("administracion" => array($status => array("username" => $username, "nombre" => $name, "fecha" => date("Y-m-d H:i:s"))));
        } elseif (in_array($role, $this->ops)) {
            return array("operacion" => array($status => array("username" => $username, "nombre" => $name, "fecha" => date("Y-m-d H:i:s"))));
        } elseif (in_array($role, $this->supervisor)) {
            return array("supervision" => array($status => array("username" => $username, "nombre" => $name, "fecha" => date("Y-m-d H:i:s"))));
        } else {
            return false;
        }
    }

    public function actualizarRevision($current, $new) {
        foreach (array("administracion", "operacion", "supervision") as $item) {
            if (isset($current[$item]) && isset($new[$item])) {
                foreach ($new[$item] as $key => $value) {
                    if (isset($current[$item][$key])) {
                        $current[$item][$key] = $value;
                    } else {
                        $current[$item][$key] = $value;
                    }
                }
            } elseif (!isset($current[$item]) && isset($new[$item])) {
                $current[$item] = $new[$item];
            }
        }
        return $current;
    }

}
