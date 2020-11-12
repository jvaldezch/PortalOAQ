<?php

class OAQ_Google_Storage {

    function __construct() {
    }

    public function copy($filename) {
        $output = shell_exec("/var/www/pyportal/./storage.sh {$filename} 2>&1");
        if ($output) {
            $arr = explode("\n", trim($output));

            if (isset($arr[2])) {
                $url = explode("=", trim($arr[2]));
                return $url[1];
            }
        }
        return null;
    }
                

}
