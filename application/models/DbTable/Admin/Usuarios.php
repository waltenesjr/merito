<?php

class Application_Model_DbTable_Admin_Usuarios extends Zend_Db_Table
{

    protected $_name = 'usuarios';
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
            $where = $this->getAdapter()->quoteInto('admin_id = ?', $dados['admin_id']);
            unset($dados['admin_id']);
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
            $where = $this->getAdapter()->quoteInto('admin_id = ?', $dados['id']);
            unset($dados['id']);
            $retorno = $this->update($dados, $where);
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }

    public function validarLogin($usuario, $senha){
        
        //ChromePhp::log(sha1($senha));
        
        try{                    
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            $query  = $db
                        ->select()
                        ->from($this->_name, array("admin_id","admin_nome","admin_email") , $this->_schema)
                        ->where("admin_usuario = ?", $usuario)
                        ->where("admin_senha = ?", sha1($senha))
                        ->where("admin_status = 'Ativo'");
            
            $dados = $query
                        ->query()
                        ->fetchAll();
                
            return $dados;
        }catch(Exception $e){
            ChromePhp::log($e);
            return false;
        }
        
    }

    public function getRegistros($parametros = array()){
        
        $retorno = true;
        try{            
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            $query  = $db
                        ->select()
                        ->from($this->_name, '*', $this->_schema)
                        ->where("admin_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["admin_id"]) && $parametros["admin_id"] > 0 )
                    $query->where("admin_id = ?", $parametros["admin_id"]);
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $query->order($parametros["orderBy"]);
                }else{
                    $query->order("admin_id DESC");
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
