<?php

class Application_Model_DbTable_ProfissionaisMetas extends Zend_Db_Table
{

    protected $_name = 'profissionais_metas';
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
            
            $where = $this->getAdapter()->quoteInto('profissionais_metas_id = ?', $dados['profissionais_metas_id']);
            
            unset($dados['profissionais_metas_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('profissionais_metas_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"profissionais_metas_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema);
        
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
    
    public function getRegistrosFiltradosGrid($colunas, $parametros = array())
    {
        if(sizeof($colunas)<= 0){
            $colunas = array(
                'profissionais_metas_id',
                'fk_profissionais_id',
                'fk_metas_id',
                'profissionais_metas_cota',
                'profissionais_metas_total_quinzena',
                'profissionais_metas_total_mes',
                'b.profissionais_nome',
                'c.metas_nome'
            );
        }
        
        $retorno = false;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".profissionais"),"b.profissionais_id = a.fk_profissionais_id",array())
                        ->joinInner(array("c"=>$this->_schema.".metas"),"c.metas_id = a.fk_metas_id",array());
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["profissionais_metas_id"]) && $parametros["profissionais_metas_id"] > 0 )
                    $select->where("profissionais_metas_id = ?", $parametros["profissionais_metas_id"]);
                //fk_profissionais_id
                if(isset($parametros["fk_profissionais_id"]) && $parametros["fk_profissionais_id"] > 0 )
                    $select->where("fk_profissionais_id= ?", $parametros["fk_profissionais_id"]);
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order(array($parametros["orderBy"]));
                }else{
                    $select->order(array("profissionais_metas_id"));
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
    
    public function getRegistros($colunas = array(), $parametros = array())
   {
        if(sizeof($colunas) == 0){
            array(
                'profissionais_metas_id',
                'fk_profissionais_id',
                'fk_metas_id',
                'profissionais_metas_cota',
                'profissionais_metas_total_quinzena',
                'profissionais_metas_total_mes'
            );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema);
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["profissionais_metas_id"]) && $parametros["profissionais_metas_id"] > 0 )
                    $select->where("profissionais_metas_id = ?", $parametros["profissionais_metas_id"]);
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->group($parametros["orderBy"]);
                }else{
                    $select->order("profissionais_metas_id DESC");
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
