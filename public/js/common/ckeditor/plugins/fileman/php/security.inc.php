<?php

/*
  RoxyFileman - web based file manager. Ready to use with CKEditor, TinyMCE.
  Can be easily integrated with any other WYSIWYG editor or CMS.

  Copyright (C) 2013, RoxyFileman.com - Lyubomir Arsov. All rights reserved.
  For licensing, see LICENSE.txt or http://RoxyFileman.com/license

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Contact: Lyubomir Arsov, liubo (at) web-lobby.com
 */
require "Zend/Loader/Autoloader.php";
$baseDir = '/var/www/oaqintranet';
$autoloader = Zend_Loader_Autoloader::getInstance();
require_once $baseDir . DIRECTORY_SEPARATOR . '/library/FirePHPCore/lib/FirePHP.class.php';

function checkAccess($action) {   
    global $baseDir;
    $config = new Zend_Config_Ini($baseDir . DIRECTORY_SEPARATOR . '/application/configs/application.ini', 'production');
    $firephp = FirePHP::getInstance(true);
    $session = new Zend_Session_Namespace($config->app->namespace);
    if ($session->authenticated !== true) {
        exit();
    } else {
        $firephp->info(true,"Authenticated");        
    }
}

?>