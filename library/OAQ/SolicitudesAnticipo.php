<?php

/**
 * Description of Anexo24
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_SolicitudesAnticipo
{

    protected $_log;
    protected $_notifications;
    protected $_idSolicitud;
    protected $_esquema;
    protected $_proceso;
    protected $_row;
    protected $_mapper;
    protected $_header;
    protected $_prop;
    protected $_username;
    protected $_usernameId;
    protected $_schema;
    protected $_process;
    protected $_rolesEditarTrafico = array("trafico", "super", "trafico_operaciones", "trafico_aero");
    protected $_todosClientes = array("trafico", "super", "trafico_operaciones", "trafico_aero");
    protected $_customs = [];
    protected $_idCustoms = [];
    protected $_customers = [];

    function set_username($_username)
    {
        $this->_username = $_username;
    }

    function set_usernameId($_usernameId)
    {
        $this->_usernameId = $_usernameId;
    }

    function get_esquema()
    {
        return $this->_esquema;
    }

    function set_esquema($_esquema)
    {
        $this->_esquema = $_esquema;
    }

    function set_process($_process)
    {
        $this->_process = $_process;
    }

    function get_row()
    {
        return $this->_row;
    }

    function get_header()
    {
        return $this->_header;
    }

    function get_prop()
    {
        return $this->_prop;
    }

    function get_process()
    {
        return $this->_process;
    }

    public function get_customers()
    {
        return $this->_customers;
    }

    public function get_customs()
    {
        return $this->_customs;
    }

    public function get_idCustoms()
    {
        return $this->_idCustoms;
    }

    public function obtenerPermisos($idUsuario, $role)
    {
        if (in_array($role, $this->_todosClientes)) {
            $model = new Trafico_Model_ClientesMapper();
            $customs = new Trafico_Model_TraficoAduanasMapper();
            $this->_customers = $model->obtenerTodos();
            $this->_customs = $customs->aduanas();
        } else {
            $customs = new Application_Model_UsuariosAduanasMapper();
            $model = new Trafico_Model_TraficoUsuClientesMapper();
            $this->_customers = $model->obtenerClientes($idUsuario);
            $this->_customs = $customs->aduanasDeUsuario($idUsuario);
        }
        if (!empty($this->_customs)) {
            foreach ($this->_customs as $k => $v) {
                if ($k !== '-' && (int) $k !== 0) {
                    $this->_idCustoms[] = $k;
                }
            }
        }
    }

    function __construct($idSolicitud = null)
    {
        $this->_notifications = new Trafico_Model_NotificacionesMapper();
        $this->_log = new Trafico_Model_BitacoraMapper();
        if (isset($idSolicitud)) {
            $this->_idSolicitud = $idSolicitud;
            $this->_mapper = new Trafico_Model_TraficoSolicitudesMapper();
            $this->_row = new Trafico_Model_Table_TraficoSolicitudes();
            $this->_row->setId($idSolicitud);
            $this->_mapper->buscarId($this->_row);
            if (null === ($this->_row->getIdCliente())) {
                return false;
            }
            $this->_header = $this->_mapper->obtener($idSolicitud);
            $this->_prop = $this->_mapper->propietario($idSolicitud);
            return true;
        }
        return;
    }

    public function enviarTramite($esquema = null)
    {
        $this->_row->setAutorizada(2);
        $this->_row->setTramite(1);
        $this->_row->setEsquema($esquema);
        $this->_row->setTramitada(date("Y-m-d H:i:s"));
        if (true === ($this->_mapper->update($this->_row))) {
            $this->_bitacoraEnTramite();
            return true;
        }
        return false;
    }

    public function aprobada($esquema = null)
    {
        $arr = array(
            "autorizada" => 1,
            "esquema" => $esquema,
            "aprobada" => date("Y-m-d H:i:s"),
        );
        if (($this->_mapper->actualizar($this->_idSolicitud, $arr))) {
            $this->_bitacoraAprobacion();
            return true;
        }
        return;
        /*$this->_row->setAutorizada(1);
        $this->_row->setEsquema($esquema);
        $this->_row->setAprobada(date("Y-m-d H:i:s"));
        if (true === ($this->_mapper->update($this->_row))) {
            $this->_bitacoraAprobacion();
            return true;
        }
        return false;*/
    }

    public function cancelar()
    {
        $this->_row->setBorrada(1);
        $this->_row->setActualizada(date("Y-m-d H:i:s"));
        return false;
    }

    public function enviarDepositoMultiple($arr)
    {
        if (($this->_mapper->actualizar($this->_idSolicitud, $arr))) {
            $this->_bitacoraAprobacion();
            if (APPLICATION_ENV == "production") {
                $this->_email();
            }
            return true;
        }
        return;
    }

    public function enviarDeposito($esquema = null)
    {
        $arr = array(
            "autorizada" => 3,
            "esquema" => $esquema,
            "deposito" => 1,
            "depositado" => date("Y-m-d H:i:s"),
        );
        if (($this->_mapper->actualizar($this->_idSolicitud, $arr))) {
            $this->_bitacoraAprobacion();
            if (APPLICATION_ENV == "production") {
                $this->_email();
            }
            return true;
        }
        return;
        /*$this->_row->setAutorizada(3);
        $this->_row->setDeposito(1);
        $this->_row->setEsquema($esquema);
        $this->_row->setDepositado(date("Y-m-d H:i:s"));
        if (true === ($this->_mapper->update($this->_row))) {
            $this->_bitacoraDepositado();
            $this->_email();
            return true;
        }
        return false;*/
    }

    public function autorizarBanamex($idSolicitud, $esquema = null)
    {
        $mppr = new Trafico_Model_TraficoSolicitudesMapper();
        $arr = array(
            "autorizada" => 3,
            "deposito" => 1,
            "autorizadaBanamex" => 1,
            "esquema" => $esquema,
        );
        if ($mppr->updateRequest($idSolicitud, $arr)) {
            return true;
        }
        return;
    }

    public function autorizarHsbc($idSolicitud, $esquema = null)
    {
        $mppr = new Trafico_Model_TraficoSolicitudesMapper();
        $arr = array(
            "autorizada" => 3,
            "deposito" => 1,
            "autorizadaHsbc" => 1,
            "esquema" => $esquema,
        );
        if ($mppr->updateRequest($idSolicitud, $arr)) {
            return true;
        }
        return;
    }

    public function proceso($estatus, $hsbc = null, $banamex = null)
    {
        if ($hsbc) {
            return 5;
        }
        if ($banamex) {
            return 6;
        }
        if ($estatus == 1) {
            return 1;
        }
        if ($estatus == 2) {
            return 2;
        }
        if ($estatus == 3) {
            return 3;
        }
        return;
    }

    protected function _bitacoraAprobacion()
    {
        $log = array(
            "patente" => $this->_header["patente"],
            "aduana" => $this->_header["aduana"],
            "pedimento" => $this->_header["pedimento"],
            "referencia" => $this->_header["referencia"],
            "bitacora" => (isset($this->_esquema)) ? "SE APROBO SOLICITUD DE ANTICIPO [" . $this->_mapper->decripcionEsquemaFondos($this->_esquema) . "]" : null,
            "usuario" => $this->_username,
            "creado" => date("Y-m-d H:i:s"),
        );
        $this->_log->agregar($log);
    }

    protected function _bitacoraEnTramite()
    {
        $log = array(
            "patente" => $this->_header["patente"],
            "aduana" => $this->_header["aduana"],
            "pedimento" => $this->_header["pedimento"],
            "referencia" => $this->_header["referencia"],
            "bitacora" => "LA SOLICITUD SE ENCUENTRA EN TRAMITE",
            "usuario" => $this->_username,
            "creado" => date("Y-m-d H:i:s"),
        );
        $this->_log->agregar($log);
    }

    protected function _bitacoraDepositado()
    {
        $log = array(
            "patente" => $this->_header["patente"],
            "aduana" => $this->_header["aduana"],
            "pedimento" => $this->_header["pedimento"],
            "referencia" => $this->_header["referencia"],
            "bitacora" => "SOLICITUD DEPOSITADA",
            "usuario" => $this->_username,
            "creado" => date("Y-m-d H:i:s"),
        );
        $this->_log->agregar($log);
    }

    protected function _email()
    {
        $array = array(
            "idAduana" => $this->_prop["idAduana"],
            "contenido" => "Se ha realizado el depósito de la solicitud número " . $this->_idSolicitud . " referencia " . $this->_prop["referencia"] . " pedimento " . $this->_prop["aduana"] . "-" . $this->_prop["patente"] . "-" . $this->_prop["pedimento"] . "<br><p></p>",
            "pedimento" => $this->_prop["pedimento"],
            "referencia" => $this->_prop["referencia"],
            "de" => $this->_usernameId,
            "para" => $this->_prop["idUsuario"],
            "tipo" => "deposito-solicitud",
            "estatus" => null,
            "creado" => date("Y-m-d H:i:s")
        );
        $id = $this->_notifications->agregar($array);
        $sender = new OAQ_WorkerSender("emails");
        $sender->enviarEmail($id);
        $misc = new OAQ_Misc();
        $misc->execCurl("enviar-email");
        return true;
    }
}
