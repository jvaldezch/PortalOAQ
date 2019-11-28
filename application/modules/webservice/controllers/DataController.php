<?php

class Webservice_DataController extends Zend_Rest_Controller {

    public function init() {
        // https://www.ahmadnassri.com/blog/restful-services-with-zend-framework/
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction() {
        $this->getResponse()->setBody('Hello World');
        $this->getResponse()->setHttpResponseCode(200);
    }

    public function getAction() {
        $this->getResponse()->setBody(sprintf('Resource #%s', $this->_getParam('id')));
        $this->getResponse()->setHttpResponseCode(200);
    }

    public function postAction() {
        $this->getResponse()->setBody('resource created');
        $this->getResponse()->setHttpResponseCode(200);
    }

    public function putAction() {
        $this->getResponse()->setBody('resource updated');
        $this->getResponse()->setHttpResponseCode(200);
    }

    public function deleteAction() {
        $this->getResponse()->setBody('resource deleted');
        $this->getResponse()->setHttpResponseCode(200);
    }

}
