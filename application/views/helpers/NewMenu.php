<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
//class MainMenu {
class Application_View_Helper_NewMenu {

    public function NewMenu($role, $module) {
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
        $mainMenu .= "<span class=\"label label-success\" style=\"margin-top: 7px; margin-left: 5px\"><a href=\"https://oaq.dnsalias.net/soporte/?lang=es_MX\" target=\"_blank\" style=\"color: #fff;\">Tickets</a></span>";
        return $mainMenu;
    }

    public function current($module, $action, $nombre, $email) {
        return "<div style=\"float:left; margin-left: 3px\"><span class=\"label\">" . strtoupper($module) . "</span> | <span class=\"label\">" . str_replace("-", "&nbsp;", ucfirst($action)) . "</span> &mdash; {$nombre},{$email}</div>";
    }

}
