<?php

class Application_Model_DbTable_Atribuicoes extends Zend_Db_Table
{

    protected $_name = 'atribuicoes';
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
            $retorno = false;
        }
        return $retorno;
    }
    
    
    public function atualizar($dados)
    {
        $retorno = false;
        
        try {
            
            $where = $this->getAdapter()->quoteInto('atribuicoes_id = ?', $dados['atribuicoes_id']);
            
            unset($dados['atribuicoes_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('atribuicoes_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    public function verificaDependencias($parametros){
        try{
            $select = $this
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from("funcoes_atribuicoes", "*", $this->_schema)
                    ->where("fk_atribuicoes_id = ?", $parametros['id'])
                    ->where("funcoes_atribuicoes_status = ?", "Ativo");
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
            
        }catch(Exception $exc){
            $retorno = false;
        }
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"atribuicoes_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = NULL){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("atribuicoes_status != 'Inativo'");
        
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
    
    public function getRegistrosFiltradosGrid($colunas = array(),$parametros = array())
    {
        if(sizeof($colunas) == 0){
            $colunas = array(
                'atribuicoes_id',
                'atribuicoes_nome',
                'atribuicoes_descricao',
                'atribuicoes_status'
            );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("atribuicoes_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["atribuicoes_id"]) && $parametros["atribuicoes_id"] > 0 )
                    $select->where("atribuicoes_id = ?", $parametros["atribuicoes_id"]);
                //descricao
                if(isset($parametros["atribuicoes_descricao"]) && $parametros["atribuicoes_descricao"] != "" )
                    $select->where("atribuicoes_descricao ILIKE '".$parametros["atribuicoes_descricao"]."%'");
                
                
            }
            
            //Order by
            if(isset($parametros["orderBy"])){
                $select->order($parametros["orderBy"]);
            }else{
                $select->order("atribuicoes_nome");
            }
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getRegistros($colunas = array(),$parametros = array())
   {
       if(sizeof($colunas) == 0){                      
            $colunas = array(
                'atribuicoes_id',
                'atribuicoes_nome',
                'atribuicoes_descricao',
                'atribuicoes_status'
            );
       }
       
       $retorno = true;
       try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("atribuicoes_status != 'Inativo'")
                        ->order("atribuicoes_nome");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["atribuicoes_id"]) && $parametros["atribuicoes_id"] > 0 )
                    $select->where("atribuicoes_id = ?", $parametros["atribuicoes_id"]);
                //descricao
                if(isset($parametros["atribuicoes_descricao"]) && $parametros["atribuicoes_descricao"] != "" )
                    $select->where("atribuicoes_descricao ILIKE '".$parametros["atribuicoes_descricao"]."%'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("atribuicoes_id DESC");
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
