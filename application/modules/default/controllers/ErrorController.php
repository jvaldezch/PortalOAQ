"<?php

class Default_ErrorController extends Zend_Controller_Action {

    public function init() {
        parent::init();
        $this->_helper->layout->setLayout("error");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->title = "OAQ | Error";
        $this->view->headLink()
                ->appendStylesheet("/less/traffic-module.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/jquery.form.js");
    }

    public function indexAction() {
        $errors = $this->_getParam("error_handler");
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = "Usted está en la página de error";
            return;
        }
        $this->view->message = "<div class=\"traffic-icon traffic-icon-warning\" style=\"margin-top: 12px\"></div>";
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
                $this->getResponse()->setHttpResponseCode(403);
                $priority = Zend_Log::NOTICE;
                $this->view->full = true;
                break;
            default:
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                break;
        }
        if ($this->getInvokeArg("displayExceptions") == true) {
            $this->view->exception = $errors->exception;
        }
        $this->view->request = $errors->request;
    }

    public function getLog() {
        $bootstrap = $this->getInvokeArg("bootstrap");
        if (!$bootstrap->hasResource("Log")) {
            return false;
        }
        $log = $bootstrap->getResource("Log");
        return $log;
    }

}
