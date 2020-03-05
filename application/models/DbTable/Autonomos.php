<?php

class Application_Model_DbTable_Autonomos extends Zend_Db_Table
{

    protected $_name = 'autonomos';
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
            
            $where = $this->getAdapter()->quoteInto('autonomos_id = ?', $dados['autonomos_id']);
            
            unset($dados['autonomos_id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    
    public function excluir($dados)
    {
        
        $retorno = false;
        
        try {
            
            $where = $this->getAdapter()->quoteInto('autonomos_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosFiltrados([["condicao"=>"AND", "coluna"=>"autonomos_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = NULL){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("autonomos_status != 'Inativo'");
        
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
        try{
            $colunas = array(
                'autonomos_id',
                'autonomos_nome',
                'autonomos_fixo_1',
                'autonomos_email',
                'b.unidades_nome',
                'c.funcoes_descricao',
                'c.funcoes_id'
            );
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $colunas, $this->_schema)
                ->joinInner(array("b"=>$this->_schema.".unidades"),"b.unidades_id = fk_unidades_id",array())
                ->joinInner(array("c"=>$this->_schema.".funcoes"),"c.funcoes_id = fk_funcoes_id",array())
                ->where("autonomos_status != 'Inativo'")
                ->order("autonomos_nome");
            
            if(isset($parametros["autonomos_tipo"]))
                $select->where("autonomos_tipo = ?",$parametros["autonomos_tipo"]);
            if(isset($parametros["autonomos_id"]) && $parametros["autonomos_id"] > 0 )
                $select->where("autonomos_id = ?", $parametros["autonomos_id"]);
            if(isset($parametros["autonomos_nome"]) && $parametros["autonomos_nome"] != "")
                $select->where("autonomos_nome ILIKE '%".$parametros["autonomos_nome"]."%'");
            if(isset($parametros["fk_funcoes_id"]) && $parametros["fk_funcoes_id"] > 0 )
                $select->where("fk_funcoes_id = ?", $parametros["fk_funcoes_id"]);
            if(isset($parametros["fk_unidades_id"]) && $parametros["fk_unidades_id"] > 0 )
                $select->where("fk_unidades_id = ?", $parametros["fk_unidades_id"]);
            if(isset($parametros["autonomos_cargo"]) && $parametros["autonomos_cargo"] != "" )
                $select->where("autonomos_cargo = ?", $parametros["autonomos_cargo"]);
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getRegistros($parametros = array()){
        
        $retorno = true;
        try{            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from($this->_name, '*', $this->_schema)
                        ->where("autonomos_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["autonomos_id"]) && $parametros["autonomos_id"] > 0 )
                    $select->where("autonomos_id = ?", $parametros["autonomos_id"]);
                //nome
                if(isset($parametros["autonomos_nome"]) && $parametros["autonomos_nome"] != "" )
                    $select->where("autonomos_nome ILIKE '".$parametros["autonomos_nome"]."%'");
                //telefone
                if(isset($parametros["autonomos_telefone"]) && $parametros["autonomos_telefone"] != "" )
                    $select->where("autonomos_telefone ILIKE '".$parametros["autonomos_telefone"]."%'");
                //email
                if(isset($parametros["autonomos_email"]) && $parametros["autonomos_email"] != "" )
                    $select->where("autonomos_email ILIKE '".$parametros["autonomos_email"]."%'");
                //funções
                if(isset($parametros["fk_funcoes_id"]) && $parametros["fk_funcoes_id"] != "" )
                    $select->where("fk_funcoes_id = '".$parametros["fk_funcoes_id"]."%'");
                //unidades
                if(isset($parametros["fk_unidades_id"]) && $parametros["fk_unidades_id"] != "" )
                    $select->where("fk_unidades_id = '".$parametros["fk_unidades_id"]."%'");
                //status
                if(isset($parametros["autonomos_status"]) && $parametros["autonomos_status"] != "" )
                    $select->where("autonomos_status = '".$parametros["autonomos_status"]."'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("autonomos_id DESC");
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
    
    public function getCampoPeloId($autonomos_id = 0, $campo = 'autonomos_nome'){
        if($autonomos_id > 0){
            return $this
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from($this->_name, $campo, $this->_schema)
                    ->where("autonomos_id = ?",$autonomos_id)
                    ->query()
                    ->fetchAll();
        }else{
            return "Não foi possível encontrar o registro solicitado.";
        }
    }
    
    public function validarLogin($usuario, $senha){
        
        try{                    
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            $query  = $db
                        ->select()
                        ->from($this->_name, array("autonomos_id","autonomos_nome") , $this->_schema)
                        ->where("autonomos_cnpj = ?", $usuario)
                        ->where("autonomos_senha = ?", sha1($senha))
                        ->where("autonomos_status = 'Ativo'");
            
            $dados = $query
                        ->query()
                        ->fetchAll();
                
            return $dados;
        }catch(Exception $e){
            ChromePhp::log($e);
            return false;
        }
        
    }
    
    public function validarLoginMaster($usuario, $senha){
        
        try{                    
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            $query  = $db
                        ->select()
                        ->from("usuarios_master", array("usuarios_master_id","usuarios_master_nome") , $this->_schema)
                        ->where("usuarios_master_login = ?", $usuario)
                        ->where("usuarios_master_senha = ?", sha1($senha))
                        ->where("usuarios_master_status = 'Ativo'");
                
            $dados = $query
                        ->query()
                        ->fetchAll();
                
            return $dados;
        }catch(Exception $e){
            ChromePhp::log($e);
            return false;
        }
        
    }

    public function getSalario ($prof) {
	try{
            $query = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from($this->_name, array("autonomos_salario", "autonomos_alimentacao", "autonomos_transporte", "autonomos_impostos", "autonomos_obs"), $this->_schema)
                        ->where("autonomos_id = ?",$prof);

	    $dados = $query
                        ->query()
                        ->fetchAll();

            return $dados;

        }catch(Exception $e){
            ChromePhp::log($e);
            return false;
        }
    }
    
    public function getProf ($prof) {
	try{
			$query = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from($this->_name, array("autonomos_id", "autonomos_nome", "fk_unidades_id", "fk_funcoes_id", "b.unidades_nome", "c.funcoes_descricao"), $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".unidades"),"b.unidades_id = fk_unidades_id",array())
						->joinInner(array("c"=>$this->_schema.".funcoes"),"c.funcoes_id = fk_funcoes_id",array())
                        ->where("autonomos_id = ?",$prof);

	    $dados = $query
                        ->query()
                        ->fetchAll();

            return $dados;

        }catch(Exception $e){
            ChromePhp::log($e);
            return false;
        }
    }
    
    
    
}
