<?php
/**
 * Created by PhpStorm.
 * User: itipart
 * Date: 12/03/15
 * Time: 12:27 م
 */

namespace Itipart\SynchroBundle\Services\parsers;

use Itipart\SynchroBundle\Services\dao\Session;

class FileParser {
    private $file;
    private $dir;
    private  function look_for_file($xml,$dir){
        $filename=$xml->getToParse();
        ///scanning directory content

        if (is_dir($dir)) {
            $files = scandir($dir, 0);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (strpos($file, $filename) > -1) {
                        $this->file=$file;
                        echo "\t" . $file . " is a customer file \n";
                        $aLines = file($dir . "/" . $file); // Source file for use
                        $file_to_move = $file;
                        break;
                    } else {

                        echo "\t Not a Customer file \n";
                    }
                }
            } else {
                echo "\033[31m =====> no files";
            }
        } else {
            echo "\033[31m =====> no dir";
        }



    }
    public function __construct($xmlParser,$dir)
    {
        if($this->look_for_file($xmlParser,$dir)==false){


        }else{


        }

    }
    public function  toArchive(){
    }
    public function getFile(){
        return $this->file;

    }
    public function setFile($file){

        $this->file=$file;

    }




}