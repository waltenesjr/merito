<?php

class Application_Model_DbTable_Metasautonomos extends Zend_Db_Table
{

    protected $_name = 'metasautonomos';
    protected $_schema = '';

    public function __construct(){
        $this->_schema = $_SESSION["cliente_schema"];
        parent::__construct();
    }

    
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
            
            $where = $this->getAdapter()->quoteInto('metasautonomos_id = ?', $dados['metasautonomos_id']);
            
            unset($dados['metasautonomos_id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = $exc;
        }
        return $retorno;
    }
    
    
    public function excluir($dados)
    {
     
        $retorno = false;
        
        try {
            
            $where = $this->getAdapter()->quoteInto('metasautonomos_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
			ChromePhp::log($exc); 
            $retorno = false;
        }
        
        return $retorno;
    }
 
     public function getMetasAutonomos($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("metasautonomos_status != 'Inativo'");

            if(sizeof($parametros) > 0){
                if(isset($parametros["fk_autonomos_id"]) && $parametros["fk_autonomos_id"] > 0 )
                    $select->where("fk_autonomos_id = ?", $parametros["fk_autonomos_id"]);

                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("metasautonomos_id DESC");
                }
            }
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }

    public function getRegistros($colunas = array(), $parametros = array())
    {                            
        if(sizeof($colunas) == 0){
            $colunas = array(
                'metasautonomos_id',
                'metasautonomos_nome',
                'metasautonomos_status'
            );
        }
        
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("metasautonomos_status != 'Inativo'");
            
            if(sizeof($parametros) > 0){
                if(isset($parametros["metasautonomos_id"]) && $parametros["metasautonomos_id"] > 0 )
                    $select->where("metasautonomos_id = ?", $parametros["metasautonomos_id"]);

                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("metasautonomos_id DESC");
                }
            }
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
                
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }   

}
