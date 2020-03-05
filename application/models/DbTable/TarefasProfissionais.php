<?php

class Application_Model_DbTable_TarefasProfissionais extends Zend_Db_Table
{

    protected $_name = 'tarefas_profissionais';
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
            var_dump($exc);exit;
            $retorno = false;
        }
        return $retorno;
    }
    
    
    public function atualizar($dados)
    {
        $retorno = false;
        
        try {
            
            $where = $this->getAdapter()->quoteInto('tarefas_profissionais_id = ?', $dados['tarefas_profissionais_id']);
            
            unset($dados['tarefas_profissionais_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('tarefas_profissionais_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    public function getRegistros($parametros = array(), $show_und = null){
        
        $retorno = true;
        try{
            
            $colunas = array(
                'a.*',
                'b.profissionais_nome'
            );
                    
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".profissionais"),"b.profissionais_id = tarefas_profissionais_profissional_id",array());
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                if(isset($parametros["tarefas_profissionais_profissional_id"]) && $parametros["tarefas_profissionais_profissional_id"] > 0 )
                    $select->where("a.tarefas_profissionais_profissional_id = ?", $parametros["tarefas_profissionais_profissional_id"]);
                if(isset($parametros["tarefas_profissionais_tarefa_id"]) && $parametros["tarefas_profissionais_tarefa_id"] > 0 )
                    $select->where("a.tarefas_profissionais_tarefa_id = ?", $parametros["tarefas_profissionais_tarefa_id"]);
                
                
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
    
}
