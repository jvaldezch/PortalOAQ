<?php

class Application_View_Helper_Menus {

    protected $_roles;
    protected $_menus;
    protected $_modules;

    function __construct() {
        $this->_roles = new Application_Model_RolesMapper();
        $this->_menus = new Application_Model_MenusMapper();
        $this->_modules = new Application_Model_ModuleMapper();
    }

    public function topMenu($rol, $module) {
        try {
            $roleId = $this->_roles->checkForRole($rol);
            $menus = $this->_menus->getTopMenu($roleId);
            $html = '';
            if ($roleId != 6) {
                if ($module == 'principal') {
                    $html .= '<li class="current_page_item"><a class="current" href="/principal/index/index">INICIO</a></li>';
                } else {
                    $html .='<li><a href="/principal/index/index">INICIO</a></li>';
                }
            }
            foreach ($menus as $menu) {
                $url = '/' . $menu['modulo'] . '/' . $menu['controlador'] . '/' . $menu['accion'];
                if ($module == $menu['modulo']) {
                    $html .= "<li class=\"current_page_item\"><a class=\"current\" href=\"{$url}\">{$menu['menu']}</a></li>";
                } else {
                    $html .= "<li><a href=\"{$url}\">{$menu['menu']}</a></li>";
                }
            }
            $html .= "<li class=\"right\"><a href=\"/default/index/logout\">SALIR</a></li>";
            return $html;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function mainMenu($rol) {
        try {
            $modules = $this->_roles->checkModules($rol);
            $roleId = $this->_roles->checkForRole($rol);
            $explode = explode(',', $modules);
            $html = '';
            foreach ($explode as $mod):
                $menus = $this->_menus->getMainMenu($roleId, $mod);
                if ($menus) {
                    $html .= '<div class="col">
                        <h4>' . $this->_modules->getModuleName($mod) . '</h4>
                        <ul class="options">';
                    foreach ($menus as $menu):
                        $url = '/' . $menu['modulo'] . '/' . $menu['controlador'] . '/' . $menu['accion'];
                        $html .="<li><a href=\"{$url}\">{$menu['menu']}</a></li>";
                    endforeach;
                    $html .= '</ul>
                        </div>';
                }
            endforeach;

            return $html;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function navList($rol) {
        try {
            $modules = $this->_roles->checkModules($rol);
            $roleId = $this->_roles->checkForRole($rol);
            $explode = explode(',', $modules);
            $html = '';
            foreach ($explode as $mod):
                $menus = $this->_menus->getMainMenu($roleId, $mod);
                if ($menus) {
                    $html .= '<div class="well" style="max-width: 340px; min-height: 200px; padding: 8px 0; float:left; margin: 0 5px 5px 0">
                        <ul class="nav nav-list">
                        <li class="nav-header">' . $this->_modules->getModuleName($mod) . '</li>';
                    foreach ($menus as $menu):
                        $html .="<li><a href=\"/{$menu['modulo']}/{$menu['controlador']}/{$menu['accion']}\">{$menu['menu']}</a></li>";
                    endforeach;
                    $html .= '</ul>
                        </div>';
                }
            endforeach;
            return $html;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function leftMenu($rol, $module, $action) {
        try {
            $roleId = $this->_roles->checkForRole($rol);
            $modId = $this->_modules->getModuleId($module);
            $menus = $this->_menus->getLeftMenu($roleId, $modId);
            $html = '<ul class="nav nav-list">
                    <li class="nav-header">MENÚ</li>
                    <li class="divider"></li>';
            if ($menus) {
                if ($roleId != 6) {
                    if ($action == 'index' && $module != 'dashboard') {
                        $html .= '<li class="active"><a href="/' . $module . '/index/index">Inicio</a></li>';
                    } else if ($action != 'index' && $module != 'dashboard') {
                        $html .= '<li><a href="/' . $module . '/index/index">Inicio</a></li>';
                    }
                }
                foreach ($menus as $menu):
                    $url = '/' . $menu['modulo'] . '/' . $menu['controlador'] . '/' . $menu['accion'];
                    if ($action == $menu['accion'] && $module == $menu['modulo']) {
                        $html .= '<li class="active"><a href="' . $url . '">' . $menu['menu'] . '</a></li>';
                    } else {
                        $html .= '<li><a href="' . $url . '">' . $menu['menu'] . '</a></li>';
                    }
                endforeach;
            }
            $html .= '<li class="divider"></li>
                    <li><a href="/default/index/logout">Salir</a></li>
                </ul>';
            return $html;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function workMenu($rol, $module, $action) {
        try {
            $roleId = $this->_roles->checkForRole($rol);
            $modId = $this->_modules->getModuleId($module);
            $menus = $this->_menus->getLeftMenu($roleId, $modId);
            $html = '<ul>';
            if ($menus) {
                foreach ($menus as $menu):
                    $url = '/' . $menu['modulo'] . '/' . $menu['controlador'] . '/' . $menu['accion'];
                    if ($action == $menu['accion'] && $module == $menu['modulo']) {
                        $html .= '<li class="active"><a href="' . $url . '">&raquo; ' . strtoupper($menu['menu']) . '</a></li>';
                    } else {
                        $html .= '<li><a href="' . $url . '">&raquo; ' . strtoupper($menu['menu']) . '</a></li>';
                    }
                endforeach;
            }
            $html .= '</ul>';
            return $html;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
