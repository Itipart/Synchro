<?php
/**
 * Created by Marouen Ben Ahmed.
 * User: itipart
 * Date: 11/03/15
 * Time: 03:19 م
 */
namespace Itipart\SynchroBundle\Services\dao;

use \Mysqli;
class Session {

const HOST="192.168.1.2";
const USERNAME="dev";
const PASSWORD ="dev";
const DB_NAME="Dev_deroche_tstcharge";


    /**
     * Private ctor so nobody else can instance it
     *
     */
    private $connexion;
    private function __construct()
    {
        $this->connexion= new mysqli(self::HOST,self::USERNAME,self::PASSWORD,self::DB_NAME);
    }


    /**
     * Call this method to get singleton
     *
     * @return connexion
     */
    public static function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Session();
        }
        return $inst->connexion;
    }



}