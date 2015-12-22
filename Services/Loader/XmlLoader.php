<?php
/**
 * Created by PhpStorm.
 * User: itipart
 * Date: 22/12/15
 * Time: 10:45
 */

namespace Itipart\SynchroBundle\Services\Loader;

class XmlLoader {

    private  $domDocument;
    private $entityName;
    static private $accepted_roots;

    public function __construct()
    {
        $this->domDocument = new \DOMDocument();
    }

    public function loadFile($file)
    {
        $this->domDocument->load($file);
    }

    public function getModelName()
    {
        $this->entityName = $this->domDocument->childNodes->item(1)->nodeName;
        if ($this->check_root_file_in_list($this->entityName)==false){
            // LogWriter::write($xml_file_name,$parent_path,$xml_file_name." ".'root not found exception check root list');
            exit;
        }
        return $this->entityName;
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

    public function getModelAttributesConfig(){
        $configs=[];
        $name=$this->domDocument->getElementsByTagName('name');
        $begins=$this->domDocument->getElementsByTagName('begin');
        $lengths=$this->domDocument->getElementsByTagName('length');
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
            "modelName"=>$this->getModelName(),
            "configs"=>$configs
        );

    }

    public function getFileName(){
        return preg_replace('/\s+/', '',$this->domDocument->getElementsByTagName('fileTitle')->item(0)->nodeValue);
    }

    private function check_root_file_in_list($root_name)
    {
        self::$accepted_roots = ['Article', 'Customer'];
        return  in_array($root_name,self::$accepted_roots);
    }
}