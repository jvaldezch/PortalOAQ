<?php

class Plugin_Acl extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $auth = Zend_Auth::getInstance();
        $authModel = new Application_Model_Auth();
        if (!$auth->hasIdentity()) {
            $authModel->authenticate(array('login' => 'Guest', 'password' => 'shocks'));
        }

        $request = $this->getRequest();
        $aclResource = new Application_Model_AclResource();
        if (!$aclResource->resourceValid($request)) {
            $request->setControllerName('error');
            $request->setActionName('error');
            return;
        }

        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if (!$aclResource->resourceExists($controller, $action)) {
            $aclResource->createResource($controller, $action);
        }
        $role_id = $auth->getIdentity()->role_id;
        $role = Application_Model_Role::getById($role_id);
        $role = $role[0]->role;
        $acl = new Zend_Acl();
        $acl->addRole(new Zend_Acl_Role($role));
        if ($role_id == 3) {//If role_id=3 "Admin" don't need to create the resources
            $acl->allow($role);
        } else {
            $resources = $aclResource->getAllResources();
            foreach ($resources as $resource) {
                $acl->add(new Zend_Acl_Resource($resource->getController()));
            }
            $userAllowedResources = $aclResource->getCurrentRoleAllowedResources($role_id);
            foreach ($userAllowedResources as $controllerName => $allowedActions) {
                $arrayAllowedActions = array();
                foreach ($allowedActions as $allowedAction) {
                    $arrayAllowedActions[] = $allowedAction;
                }
                $acl->allow($role, $controllerName, $arrayAllowedActions);
            }
        }
        if (!$acl->isAllowed($role, $controller, $action)) {
            $request->setControllerName('error');
            $request->setActionName('access-denied');
            return;
        }
    }

}
