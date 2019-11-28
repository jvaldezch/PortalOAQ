<?php

class OAQ_gm
{
    public static $servers  = array ('127.0.0.1:4730');
    public static $job_path = '/path/to/gm_example';
    public static $log_dir  = '/tmp/gm.log';
    
    public static function log_msg ($msg)
    {
        print "$msg\n";
        
        $dt = date("Y-m-d H:i:s (T)");
        error_log ("$dt\t$msg\n", 3, self::$log_dir);
    }   
}
define ('NET_GEARMAN_JOB_PATH', gm::$job_path);