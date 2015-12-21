<?php
/**
 * Created by PhpStorm.
 * User: itipart
 * Date: 12/03/15
 * Time: 09:54 ص
 */

namespace Itipart\SynchroBundle\Services\parsers;

class LogWriter {




    public static  function write($file_name,$parent_path,$msg){
        $log_path = $parent_path . '/repos/logs/'.str_replace('.xml','',$file_name).'.log';
        $f = (file_exists($log_path)) ? fopen($log_path, "a+") : fopen($log_path, "w+");
        fwrite($f, "\t" . date('d-m-Y H:i:s') ."  ". $msg);
        chmod($log_path, 0777);
    }



}