<?php

class Application_Model_DbTable_Main extends Zend_Db_Table
{

    protected $_schema = 'dados';

    // public function __construct(){
    //     $this->_schema = $_SESSION["cliente_schema"];
    //     parent::__construct();
    // }
    
    public function createSchemaForClient($cnpj){

        $retorno = false;

        try{
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');

            $client_schema = 'data_'.$cnpj;

            $result = $db->query('CREATE SCHEMA '.$client_schema.';');

            if($result){
                $tables = $db->query('SELECT table_name FROM information_schema.tables WHERE table_schema=\'dados\';')->fetchAll();
                foreach ($tables as $table) {
                    $db->query('CREATE table '.$client_schema.'.'.$table['table_name'].' (like '.$this->_schema.'.'.$table['table_name'].' including defaults including constraints including indexes);');
                    $db->query('insert into '.$client_schema.'.'.$table['table_name'].' select * from '.$this->_schema.'.'.$table['table_name'].';');
                }
                $sqls_seq = $db->query("SELECT 'CREATE SEQUENCE ".$client_schema.".' || sequence_name || ' START ' ||  start_value || ';' as sql from information_schema.sequences where sequence_schema = 'dados';")->fetchAll();
                foreach ($sqls_seq as $sql) {
                    $db->query($sql['sql']);
                }
                $retorno = true;
            }
        }catch(Exception $e){

            ChromePhp::log($e);
            // echo '<pre>'.print_r($e, 1).'</pre>';
        }
        return $retorno;
    }

    public function getCNPJ($identificador){

        $retorno = false;

        try{
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');

            $client_schema = 'data_'.$cnpj;

            $result = $db->query('CREATE SCHEMA '.$client_schema.';');

            if($result){
                $tables = $db->query('SELECT table_name FROM information_schema.tables WHERE table_schema=\'dados\';')->fetchAll();
                foreach ($tables as $table) {
                    $db->query('CREATE table '.$client_schema.'.'.$table['table_name'].' (like '.$this->_schema.'.'.$table['table_name'].' including all);');
                    $db->query('insert into '.$client_schema.'.'.$table['table_name'].' select * from '.$this->_schema.'.'.$table['table_name'].';');
                }
                $retorno = true;
            }
        }catch(Exception $e){
            ChromePhp::log($e);
            // echo '<pre>'.print_r($e, 1).'</pre>';
        }
        return $retorno;
    }
    
    
}
