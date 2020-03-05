<?php

class Application_Model_DbTable_Funcoesmetas extends Zend_Db_Table
{

    protected $_name = 'funcoes_metas';
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
            
            $where = $this->getAdapter()->quoteInto('funcoes_metas_id = ?', $dados['funcoes_metas_id']);
            
            unset($dados['funcoes_metas_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('funcoes_metas_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
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
                ->where("funcoes_metas_status != 'Inativo'");
        
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
    
    public function getRegistrosFiltradosGrid($colunas = array(), $parametros = array())
    {
        if(sizeof($colunas) == 0){
            $colunas = array(
                'a.funcoes_metas_id',
                'a.fk_funcoes_id',
                'a.fk_metas_id',
                'b.metas_nome',
                'a.funcoes_metas_qtde',
                'a.funcoes_metas_status'
            );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".metas"), "b.metas_id = a.fk_metas_id")
                        ->where("funcoes_metas_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["funcoes_metas_id"]) && $parametros["funcoes_metas_id"] > 0 )
                    $select->where("funcoes_metas_id = ?", $parametros["funcoes_metas_id"]);
                //fk_funcoes_id
                if(isset($parametros["fk_funcoes_id"]) && $parametros["fk_funcoes_id"] > 0 )
                    $select->where("fk_funcoes_id = ?", $parametros["fk_funcoes_id"]);
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
                'funcoes_metas_id',
                'fk_funcoes_id',
                'fk_metas_id',
                'funcoes_metas_qtde',
                'funcoes_metas_status'
            );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("funcoes_metas_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["funcoes_metas_id"]) && $parametros["funcoes_metas_id"] > 0 )
                    $select->where("funcoes_metas_id = ?", $parametros["funcoes_metas_id"]);
            }
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
                
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
    
    public function existeRegistro($fk_funcoes_id, $fk_metas_id, $funcoes_metas_id){
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), '*', $this->_schema)
                        ->where("funcoes_metas_status != 'Inativo'")
                        ->where("fk_funcoes_id = ?",$fk_funcoes_id)
                        ->where("fk_metas_id = ?",$fk_metas_id);
            
            
            if($funcoes_metas_id > 0)
                $select->where('funcoes_metas_id != ?',$funcoes_metas_id);
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
            if(sizeof($retorno) <= 0)
                $retorno = false;
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
}
