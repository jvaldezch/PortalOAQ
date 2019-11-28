<?php

class Mobile_TraficosController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout->setLayout("mobile/traficos");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
//                ->appendFile("/mobile/jquery-3.3.1.slim.min.js")
                ->appendFile("/mobile/bootstrap/js/bootstrap.min.js")
                ->appendFile("/mobile/popper.js/dist/umd/popper.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js");
        $this->view->headLink()
                ->appendStylesheet("/mobile/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/mobile/fontawesome/css/all.css")
                ->appendStylesheet("/mobile/common/styles.css");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace("OAQmobile");
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect("/mobile/main/logout");
        }
    }

    public function indexAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");

        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "search" => array("StringToUpper")
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 10),
            "search" => "NotEmpty"
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("search")) {
            $this->view->search = $input->search;
        }

        $mppr = new Mobile_Model_Traficos();
        $select = $mppr->getSelect($input->page, $input->size, $this->_session->id, $input->search);

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage($input->size);
        $paginator->setCurrentPageNumber($input->page);
        $this->view->paginator = $paginator;
    }

    public function traficoAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/trafico.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;
        }
    }

    public function comentariosAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/comentarios.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;

            $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
            $comments = array();
            $comments['comentarios'] = $trafico->obtenerComentarios();
            $comments['archivos'] = $trafico->obtenerArchivosComentarios();

            $this->view->comments = $comments;
        }
    }

    public function bitacoraAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/comentarios.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;

            $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
            $comments = array();
            $comments['bitacora'] = $trafico->obtenerBitacora();
            $comments['archivos'] = $trafico->obtenerArchivosComentarios();

            $this->view->comments = $comments;
        }
    }

    public function fotosAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/loadingoverlay.min.js")
                ->appendFile("/mobile/common/traficos/fotos.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;

            $gallery = new Trafico_Model_Imagenes();
            $thumbs = $gallery->miniaturas($input->id);
            $this->view->gallery = $thumbs;
        }
    }

    public function archivosAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/comentarios.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;

            $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

            $repo = new Archivo_Model_RepositorioMapper();
            $archivos = $repo->obtenerArchivosReferencia($trafico->getReferencia());
            $this->view->archivos = $archivos;
        }
    }

    public function agregarFacturaAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/agregar-factura.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;
        }
    }

    public function editarFacturaAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/agregar-factura.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Trafico_Model_TraficoFacturasMapper();
            $row = $mppr->informacionFactura($input->id);
            
            $mdl = new Mobile_Model_Traficos();
            $arr = $mdl->obtener($row['idTrafico']);
            $this->view->row = $arr;
        }
    }

    public function agregarGuiaAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/agregar-guia.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {                    
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;
        }
    }

    public function editarGuiaAction() {
        $this->view->title = "Tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/traficos/agregar-guia.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits")
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $model = new Trafico_Model_TraficoGuiasMapper();
            $row = $model->obtenerGuia($input->id);
                    
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($row['idTrafico']);
            $this->view->row = $arr;
        }
    }

}
