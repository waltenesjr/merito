<?php

class Application_Model_DbTable_AutonomosHistorico extends Zend_Db_Table
{

    protected $_name = 'autonomos_historico';
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
            
            $where = $this->getAdapter()->quoteInto('autonomos_historico_id = ?', $dados['autonomos_historico_id']);
            
            unset($dados['autonomos_historico_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('autonomos_historico_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"autonomos_historico_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("autonomos_historico_status != 'Inativo'");
        
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
                'autonomos_historico_id',
                'autonomos_historico_data',
                'autonomos_historico_descricao',
                'autonomos_historico_status'
            );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("autonomos_historico_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["autonomos_historico_id"]) && $parametros["autonomos_historico_id"] > 0 )
                    $select->where("autonomos_historico_id = ?", $parametros["autonomos_historico_id"]);
                //descricao
                if(isset($parametros["autonomos_historico_descricao"]) && $parametros["autonomos_historico_descricao"] != "" )
                    $select->where("autonomos_historico_descricao ILIKE '".$parametros["autonomos_historico_descricao"]."%'");
                //fk_autonomos_id
                if(isset($parametros["fk_autonomos_id"]) && $parametros["fk_autonomos_id"] > 0 )
                    $select->where("fk_autonomos_id = ".$parametros["fk_autonomos_id"]."");
                
            }
            
            //Order by
            if(isset($parametros["orderBy"])){
                $select->order($parametros["orderBy"]);
            }else{
                $select->order("autonomos_historico_data DESC");
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
                    'autonomos_historico_id',
                    'autonomos_historico_data',
                    'autonomos_historico_descricao',
                    'autonomos_historico_status'
                );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("autonomos_historico_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["autonomos_historico_id"]) && $parametros["autonomos_historico_id"] > 0 )
                    $select->where("autonomos_historico_id = ?", $parametros["autonomos_historico_id"]);
                //descricao
                if(isset($parametros["autonomos_historico_descricao"]) && $parametros["autonomos_historico_descricao"] != "" )
                    $select->where("autonomos_historico_descricao ILIKE '".$parametros["autonomos_historico_descricao"]."%'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("autonomos_historico_id DESC");
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
    
    public function getParametroPeloId($autonomos_historico_id, $parametro = 'autonomos_historico_descricao'){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $parametro, $this->_schema)
                ->where("autonomos_historico_status != 'Inativo'")
                ->where("autonomos_historico_id = ?",$autonomos_historico_id);
        
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno[0][$parametro];
    }    
    
    public function getHistoricoPeloId($autonomos_historico_id){
               
        $colunas = array(
            'autonomos_historico_data',
            'autonomos_historico_descricao'
        );

        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("autonomos_historico_status != 'Inativo'")
                        ->where("autonomos_historico_id = ?",$autonomos_historico_id);
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
                
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
}
