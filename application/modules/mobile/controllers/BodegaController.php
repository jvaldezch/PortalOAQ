<?php

class Mobile_BodegaController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout->setLayout("mobile/traficos");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
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
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");

        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "search" => array("StringToUpper")
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "search" => "NotEmpty"
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("search")) {
            $this->view->search = $input->search;
        }

        $mppr = new Mobile_Model_Traficos();
        $select = $mppr->getSelectWarehouse($input->page, $input->size, $this->_session->id, $input->search);

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage($input->size);
        $paginator->setCurrentPageNumber($input->page);
        $this->view->paginator = $paginator;
    }

    public function entradaAction() {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/bodega/entrada.js?" . time());
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

}
