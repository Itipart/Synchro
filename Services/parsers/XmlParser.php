<?php
/**
 * Created by PhpStorm.
 * User: itipart
 * Date: 12/03/15
 * Time: 08:57 ص
 */

namespace Itipart\SynchroBundle\Services\parsers;

use \DOMDocument;
use Itipart\SynchroBundle\Services\parsers\LogWriter;
class XmlParser {

    private $filename;
    private $document;
    private $parent_path;
    private $confName;
    private $toparse;
    private $accepted_roots=["Article","Customer","Encours","Facture","LigneFact","Cstmr"];
    private function check_root_file_in_list($root_name){

        return  in_array($root_name,$this->accepted_roots);
    }
    function __construct($parent_path,$xml_file_name){
        $this->filename=$xml_file_name;
        $this->parent_path=$parent_path;
        $this->document=new DomDocument();
        //echo $parent_path.'/repos/config/'.$xml_file_name."\n";
        //die($parent_path.'/repos/config/'.$xml_file_name);
        //echo "\n";
        $this->document->load($parent_path.'/repos/config/'.$xml_file_name);
        $this->confName=$this->document->childNodes->item(1)->nodeName;
        if ($this->check_root_file_in_list($this->confName)==false){
            LogWriter::write($xml_file_name,$parent_path,$xml_file_name." ".'root not found exception check root list');
            exit;
        }else{
           // var_dump("success");
        }
    }


    public  function  getModel(){
        $attributes=[];
        $content=$this->document->getElementsByTagName("name");
        $fields_nubmer=$content->length;
        for($i=0;$i<$fields_nubmer;$i++){
            array_push($attributes ,preg_replace('/\s+/', '',$content->item($i)->nodeValue));
        }

        return array(
            "modelName"=>$this->confName,
            "attributes"=>$attributes
        );
    }


    public function getToparse(){
        return preg_replace('/\s+/', '',$this->document->getElementsByTagName('fileTitle')->item(0)->nodeValue);

    }

    public function getLogTable(){
        return preg_replace('/\s+/', '',$this->document->getElementsByTagName('logTable')->item(0)->nodeValue);

    }
    public function getLogFile(){
        return preg_replace('/\s+/', '',$this->document->getElementsByTagName('logFile')->item(0)->nodeValue);

    }
    
    public function getParseConfig(){
        $configs=[];
        $model=$this->getModel();
        $name=$this->document->getElementsByTagName('name');
        $begins=$this->document->getElementsByTagName('begin');
        $lengths=$this->document->getElementsByTagName('length');
        $fields_nubmer=$begins->length;
        for($i=0;$i<$fields_nubmer;$i++) {
            array_push(
                $configs,
                array(
                        "name"=>preg_replace('/\s+/', '',$name->item($i)->nodeValue),
                        "begin"=>preg_replace('/\s+/', '',$begins->item($i)->nodeValue),
                        "length"=>preg_replace('/\s+/', '',$lengths->item($i)->nodeValue)
                    
                )

            );
        }
        return array(
            "modelName"=>$this->confName,
            "configs"=>$configs
        );

    }



}