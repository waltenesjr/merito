<?php

class Application_Model_DbTable_TabelaAvaliacoesJustificativasAutonomos extends Zend_Db_Table
{

    protected $_name = 'justificativas_autonomos';
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
            
            $where = $this->getAdapter()->quoteInto('justificativas_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = $exc;
        }
        return $retorno;
    }
    
    
    public function excluir($dados)
    {
        
        $retorno = false;
        
        try {
            
            $where = $this->getAdapter()->quoteInto('justificativas_id = ?', $dados['justificativas_id']);
            
            unset($dados['justificativas_id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        
        return $retorno;
    }
    
    public function getRegistros($colunas = array(),$parametros = array())
    {
        if(sizeof($colunas) == 0){                 
            $colunas = array(
                    'justificativas_id',
                    'justificativas_descricao',
                    'justificativas_status'
                );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("justificativas_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["justificativas_id"]) && $parametros["justificativas_id"] > 0 )
                    $select->where("justificativas_id = ?", $parametros["justificativas_id"]);
                //descricao
                if(isset($parametros["justificativas_descricao"]) && $parametros["justificativas_descricao"] != "" )
                    $select->where("justificativas_descricao ILIKE '".$parametros["justificativas_descricao"]."%'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("justificativas_id DESC");
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
    
    public function getId($parametros){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from(array('a'=>$this->_name), array('a.justificativas_id'), $this->_schema)
                ->where("a.fk_tabela_avaliacoes_id_autonomos = ?",$parametros["fk_tabela_avaliacoes_id"]);

            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        
        if(isset($retorno) && sizeof($retorno) > 0)
            return $retorno[0]['justificativas_id'];
        else
            return 0;
    }
    
    public function getDados($fk_tabela_avaliacoes_id){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from(array('a'=>$this->_name), '*', $this->_schema)
                ->where("a.fk_tabela_avaliacoes_id_autonomos = ?",$fk_tabela_avaliacoes_id);

            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        
        return $retorno;
    }

}
