<?php

class Application_Model_DbTable_Funcoes extends Zend_Db_Table
{

    protected $_name = 'funcoes';
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
            
            $where = $this->getAdapter()->quoteInto('funcoes_id = ?', $dados['funcoes_id']);
            
            unset($dados['funcoes_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('funcoes_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"funcoes_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("funcoes_status != 'Inativo'");
        
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
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getRegistrosFiltradosGrid($colunas = array(), $parametros = array())
    {
        if(sizeof($colunas) == 0){
            $colunas = array(
                'funcoes_id',
                'funcoes_descricao',
                'funcoes_status'
            );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("funcoes_status != 'Inativo'")
                        ->order("funcoes_descricao");
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["funcoes_id"]) && $parametros["funcoes_id"] > 0 )
                    $select->where("funcoes_id = ?", $parametros["funcoes_id"]);
                //descricao
                if(isset($parametros["funcoes_descricao"]) && $parametros["funcoes_descricao"] != "" )
                    $select->where("funcoes_descricao ILIKE '".$parametros["funcoes_descricao"]."%'");
                if(isset($parametros["funcao_autonomo"]) && $parametros["funcao_autonomo"] != "" )
                    $select->where("funcao_autonomo = ?", $parametros["funcao_autonomo"]);
                
                
                
            }
            
            //Order by
            if(isset($parametros["orderBy"])){
                $select->order($parametros["orderBy"]);
            }else{
                $select->order("funcoes_descricao");
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
    
    public function getRegistros($colunas = array(),$parametros = array())
    {
        if(sizeof($colunas) == 0){                 
            $colunas = array(
                    'funcoes_id',
                    'funcoes_descricao',
                    'funcoes_status'
                );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("funcao_autonomo = 'N'")
                        ->order("funcoes_descricao");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["funcoes_id"]) && $parametros["funcoes_id"] > 0 )
                    $select->where("funcoes_id = ?", $parametros["funcoes_id"]);
                //descricao
                if(isset($parametros["funcoes_descricao"]) && $parametros["funcoes_descricao"] != "" )
                    $select->where("funcoes_descricao ILIKE '".$parametros["funcoes_descricao"]."%'");
                //descricao
                if(isset($parametros["funcoes_status"]) && $parametros["funcoes_status"] != "" )
                    $select->where("funcoes_status = '".$parametros["funcoes_status"]."'");
                    
                if(isset($parametros["funcao_autonomo"]) && $parametros["funcao_autonomo"] != "" )
                    $select->where("funcao_autonomo = '".$parametros["funcao_autonomo"]."'");
                
                
            }
            
            //Order by
            if(isset($parametros["orderBy"])){
                $select->order($parametros["orderBy"]);
            }else{
                $select->order("funcoes_descricao");
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
    
    public function getParametroPeloId($funcoes_id, $parametro = 'funcoes_descricao'){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $parametro, $this->_schema)
                ->where("funcoes_status != 'Inativo'")
                ->where("funcoes_id = ?",$funcoes_id);
        
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno[0][$parametro];
    }

}
