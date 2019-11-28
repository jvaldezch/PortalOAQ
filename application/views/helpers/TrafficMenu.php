<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
//class MainMenu {
class Application_View_Helper_TrafficMenu {

    public function TrafficMenu($role, $module) {
        $menus = new Application_Model_MenusMapper();
        $roles = new Application_Model_RolesMapper();
        $modules = new Application_Model_ModuleMapper();

        $main = $menus->getMainMenus($roles->checkForRole($role));
        $mainMenu = "";
        foreach ($main as $m) {
            $subMenu = $menus->getLeftMenu($roles->checkForRole($role), $modules->getModuleId($m["modulo"]));
            $active = ($module == $m["modulo"]) ? " active" : '';
            if ($subMenu) {
                $mainMenu .= "\n<li class=\"dropdown{$active}\">";
                $mainMenu .= "\n\n<a href=\"/{$m["modulo"]}/{$m["controlador"]}/{$m["accion"]}\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">{$m["menu"]}</a>";
                if ($subMenu) {
                    $mainMenu .= "\n<ul class=\"dropdown-menu\">";
                    foreach ($subMenu as $sm) {
                        $mainMenu .= "\n\n<li><a href=\"/{$sm["modulo"]}/{$sm["controlador"]}/{$sm["accion"]}\">{$sm["menu"]}</a></li>";
                    }
                    $mainMenu .= "\n</ul>";
                }
                $mainMenu .= "\n</li>";
            } else {
                $mainMenu .= "\n<li class=\"{$active}\"><a href=\"/{$m["modulo"]}/{$m["controlador"]}/{$m["accion"]}\">{$m["menu"]}</a></li>";
            }
        }
        return $mainMenu;
    }

    public function current($username, $email) {
        return "<div style=\"float:left; margin-left: 3px\"><strong>Usuario conectado: </strong>" . strtoupper($username) . "\n</div>";
    }

}
