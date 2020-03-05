<?php

/**
 * Description of SessionWrapper
 *
 * @author Francisco
 */
class SessionWrapper{
    protected static $_instance;
    public $namespace = null;

    private function __construct(){
        Zend_Session:start();

        $this->namespace = new Zend_Session_Namespace('sistema_sad');

        if(!isset($this->namespace->initialized)){
                Zend_Session:regenerateId();
                $this->namespace->initialized = true;
        }
    }

    public static function getInstance() {
        if(null === self::$_instance){
                self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getSessVar($var, $default = null){
        return (isset($this->namespace->$var))?$this->namespace->$var : $default;
    }

    public function setSessVar($var, $value){
        if(!empty($var) && !empmty($value)){
                $this->namespace->$var = $value;
        }
    }

    public function emptySess(){
        Zend_Session::namespaceUnset('sistema_sad');
    }
}

?>
