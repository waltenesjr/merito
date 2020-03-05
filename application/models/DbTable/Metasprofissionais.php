<?php

class Application_Model_DbTable_Metasprofissionais extends Zend_Db_Table
{

    protected $_name = 'metasprofissionais';
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
            
            $where = $this->getAdapter()->quoteInto('metasprofissional_id = ?', $dados['metasprofissional_id']);
            
            unset($dados['metasprofissional_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('metasprofissional_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
			ChromePhp::log($exc); 
            $retorno = false;
        }
        
        return $retorno;
    }
 
     public function getMetasProfissional($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("metasprofissionais_status != 'Inativo'");

            if(sizeof($parametros) > 0){
                if(isset($parametros["fk_profissionais_id"]) && $parametros["fk_profissionais_id"] > 0 )
                    $select->where("fk_profissionais_id = ?", $parametros["fk_profissionais_id"]);

                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("metasprofissional_id DESC");
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
                'metasprofissionais_id',
                'metasprofissionais_nome',
                'metasprofissionais_status'
            );
        }
        
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("metasprofissionais_status != 'Inativo'");
            
            if(sizeof($parametros) > 0){
                if(isset($parametros["metasprofissional_id"]) && $parametros["metasprofissional_id"] > 0 ) {
                    $select->where("metasprofissional_id = ?", $parametros["metasprofissional_id"]);
                   
                } 

                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("metasprofissional_id DESC");
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
