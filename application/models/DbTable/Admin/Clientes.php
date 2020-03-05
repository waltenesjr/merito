<?php

class Application_Model_DbTable_Admin_Clientes extends Zend_Db_Table
{

    protected $_name = 'clientes';
    protected $_schema = 'main';

    
    public function inserir($dados)
    {
        $retorno = false;
        
        try {
            $retorno = $this->insert($dados);
            
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    
    public function atualizar($dados)
    {
        $retorno = false;
        try {
            $where = $this->getAdapter()->quoteInto('clientes_id = ?', $dados['clientes_id']);
            unset($dados['clientes_id']);
            $retorno = $this->update($dados, $where);
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    
    public function excluir($dados)
    {
        $retorno = false;
        try {
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');

            $client_schema = $this->getRegistros(array('clientes_id' => $dados['id']));
            $client_schema = $client_schema[0]['clientes_cnpj'];

            $result = $db->query('DROP SCHEMA data_'.$client_schema.' CASCADE;');

            $where = $this->getAdapter()->quoteInto('clientes_id = ?', $dados['id']);
            unset($dados['id']);
            $retorno = $this->delete($where);
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }

    public function getRegistros($parametros = array()){
        
        $retorno = true;
        try{            
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            $query  = $db
                        ->select()
                        ->from($this->_name, '*', $this->_schema);
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["clientes_id"]) && $parametros["clientes_id"] > 0 )
                    $query->where("clientes_id = ?", $parametros["clientes_id"]);

                if(isset($parametros["clientes_identificador"]))
                    $query->where("clientes_identificador = ?", $parametros["clientes_identificador"]);
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $query->order($parametros["orderBy"]);
                }else{
                    $query->order("clientes_id DESC");
                }
            }
            
            $retorno = $query
                        ->query()
                        ->fetchAll();
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
    
}
