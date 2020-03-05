<?php

class Application_Model_DbTable_Tarefas extends Zend_Db_Table
{

    protected $_name = 'tarefas';
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
            
            $where = $this->getAdapter()->quoteInto('tarefas_id = ?', $dados['tarefas_id']);
            
            unset($dados['tarefas_id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            echo 'aqui';exit;
            $retorno = $exc;
        }
        return $retorno;
    }
    
    
    public function excluir($dados)
    {
        
        $retorno = false;
        
        try {
            
            $where = $this->getAdapter()->quoteInto('tarefas_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"tarefas_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = NULL){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("tarefas_status != 'Inativo'");
        
            if(isset($parametros)){
                if(sizeof($parametros) > 0){
                    foreach($parametros as $arr){                    
                        if($arr["condicao"] == "AND")
                            $select->where($arr["coluna"]." ".$arr["operador"]." (?)",$arr["valor"]);
                        else
                            $select->orWhere($arr["coluna"]." ".$arr["operador"]." (?)",$arr["valor"]);
                    }
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
    
    public function getRegistrosFiltradosGrid($parametros = array()){
		
		
		
        $retorno = true;
        try{
            
            $colunas = array(
                'a.*'
            );
                    
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("a.tarefas_status != 'Inativo'")
                        ->order("a.tarefas_data_criacao DESC, a.tarefas_id");
            
            
            //Tratamento para os filtros passados como parametros
            if(sizeof($parametros) > 0){
                if(isset($parametros["tarefas_id"]) && $parametros["tarefas_id"] > 0 )
                    $select->where("a.tarefas_id = ?", $parametros["tarefas_id"]);
                if(isset($parametros["tarefas_nome"]) && $parametros["tarefas_nome"] != "" )
                    $select->where("a.tarefas_nome ILIKE '".$parametros["tarefas_nome"]."%'");
                if(isset($parametros["tarefas_pontos"]) && $parametros["tarefas_pontos"] != "" )
                    $select->where("a.tarefas_pontos = '".$parametros["tarefas_pontos"]."'");
                if(isset($parametros["gestor_id"]) && $parametros["gestor_id"] != "" && isset($parametros["funcionario_id"]) && $parametros["funcionario_id"] != "")
                    $select->joinInner(array("b"=>$this->_schema.".tarefas_profissionais"), "b.tarefas_profissionais_tarefa_id = a.tarefas_id")->where("a.tarefas_gestor_id = '".$parametros["gestor_id"]."' OR b.tarefas_profissionais_profissional_id = ".$parametros["funcionario_id"]);
                else if(isset($parametros["gestor_id"]) && $parametros["gestor_id"] != "" )
                    $select->where("a.tarefas_gestor_id = '".$parametros["gestor_id"]."'");
                else if(isset($parametros["funcionario_id"]) && $parametros["funcionario_id"] != "" )
                    $select->joinInner(array("b"=>$this->_schema.".tarefas_profissionais"), "b.tarefas_profissionais_tarefa_id = a.tarefas_id AND b.tarefas_profissionais_profissional_id = ".$parametros["funcionario_id"]);
            }

            // $sql = $select->__toString();
            // var_dump($sql);exit;
            
            $retorno = $select
                        ->query()
                        ->fetchAll();

             // $retorno = array_unique($retorno);
            
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getRegistros($parametros = array(), $show_und = null){
        
        $retorno = true;
        try{
            
            $colunas = array(
                'a.*',
                'g.profissionais_nome as tarefas_gestor_nome'
            );
                    
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->joinLeft(array("g"=>$this->_schema.".profissionais"), "g.profissionais_id = a.tarefas_gestor_id", "")
                        ->where("a.tarefas_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                if(isset($parametros["tarefas_id"]) && $parametros["tarefas_id"] > 0 )
                    $select->where("a.tarefas_id = ?", $parametros["tarefas_id"]);
                if(isset($parametros["tarefas_nome"]) && $parametros["tarefas_nome"] != "" )
                    $select->where("a.tarefas_nome ILIKE '".$parametros["tarefas_nome"]."%'");
                if(isset($parametros["tarefas_pontos"]) && $parametros["tarefas_pontos"] != "" )
                    $select->where("a.tarefas_pontos = '".$parametros["tarefas_pontos"]."'");
                
                
            }
            
            if (isset($show_und) && $show_und != null){
				$select->where("a.tarefas_id = ?", $show_und);
			}
            
            //Order by
            if(isset($parametros["orderBy"])){
                $select->order($parametros["orderBy"]);
            }else{
                $select->order("a.tarefas_nome");
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

    
//    public function autocomplete($getParams = array()) {
//        try {
//            $columns = array(
//                'clientes_id',
//                'clientes_nome',
//                'clientes_cpf',
//                'clientes_cnpj'
//            );
//            
//            $select = $this
//                    ->select()
//                    ->setIntegrityCheck(false)
//                    ->from($this->_name, $columns, $this->_schema)
//                    ->where("(LOWER(clientes_nome) LIKE LOWER('%" . $getParams['valor'] . "%'))
//                            OR (clientes_id = '" . (int) substr($getParams['valor'], 0, 5) . "')
//                            OR (REPLACE(REPLACE(clientes_cpf, '-', ''), '.', '') = '" . $getParams['valor'] . "')
//                            OR (REPLACE(REPLACE(REPLACE(clientes_cnpj, '-', ''), '.', ''), '/', '') = '" . $getParams['valor'] . "')
//                          ");
//
//            $select->where('clientes_excluido = FALSE')
//                    ->where('clientes_ativo = TRUE')
//                    ->order('LOWER(clientes_nome) ASC');
//
//            $dados = $select
//                    ->query()
//                    ->fetchAll();
//
//            $retorno = $dados;
//        } catch (Exception $exc) {
//            $retorno = FALSE;
//        }
//
//        return $retorno;
//    }
    
}
