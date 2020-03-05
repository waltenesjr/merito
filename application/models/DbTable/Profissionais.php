<?php

class Application_Model_DbTable_Profissionais extends Zend_Db_Table
{

    protected $_name = 'profissionais';
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
            $where = $this->getAdapter()->quoteInto('profissionais_id = ?', $dados['profissionais_id']);
            unset($dados['profissionais_id']);
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
            $where = $this->getAdapter()->quoteInto('profissionais_id = ?', $dados['id']);
            unset($dados['id']);
            $retorno = $this->update($dados, $where);
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosFiltrados([["condicao"=>"AND", "coluna"=>"profissionais_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = NULL){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("profissionais_status != 'Inativo'");
        
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
                'profissionais_id',
                'profissionais_nome',
                'profissionais_fixo_1',
                'profissionais_email',
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
                ->order("profissionais_nome");
            
            if(isset($parametros["profissionais_tipo"]))
                $select->where("profissionais_tipo = ?",$parametros["profissionais_tipo"]);
            if(isset($parametros["profissionais_id"]) && $parametros["profissionais_id"] > 0 )
                $select->where("profissionais_id = ?", $parametros["profissionais_id"]);
            if(isset($parametros["profissionais_nome"]) && $parametros["profissionais_nome"] != "")
                $select->where("profissionais_nome ILIKE '%".$parametros["profissionais_nome"]."%'");
            if(isset($parametros["fk_funcoes_id"]) && $parametros["fk_funcoes_id"] > 0 )
                $select->where("fk_funcoes_id = ?", $parametros["fk_funcoes_id"]);
			if (isset($parametros['show_und'])) {
				$select->where("fk_unidades_id = ?", $parametros["show_und"]);
			} else {
				if(isset($parametros["fk_unidades_id"]) && $parametros["fk_unidades_id"] > 0 )
					$select->where("fk_unidades_id = ?", $parametros["fk_unidades_id"]);
            }
            if(isset($parametros["profissionais_cargo"]) && $parametros["profissionais_cargo"] != "" )
                $select->where("profissionais_cargo = ?", $parametros["profissionais_cargo"]);
            
            if(isset($parametros["profissionais_status"]) && $parametros["profissionais_status"] != "" )
                $select->where("profissionais_status = ?", $parametros["profissionais_status"]);
            else {
				$select->where("profissionais_status != 'Inativo'");
				$select->where("profissionais_status != 'Bloqueado'");
			}
            // $sql = $select->__toString();
            // var_dump($sql);exit;
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
                        ->where("profissionais_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["profissionais_id"]) && $parametros["profissionais_id"] > 0 )
                    $select->where("profissionais_id = ?", $parametros["profissionais_id"]);
                //nome
                if(isset($parametros["profissionais_nome"]) && $parametros["profissionais_nome"] != "" )
                    $select->where("profissionais_nome ILIKE '".$parametros["profissionais_nome"]."%'");
                //telefone
                if(isset($parametros["profissionais_telefone"]) && $parametros["profissionais_telefone"] != "" )
                    $select->where("profissionais_telefone ILIKE '".$parametros["profissionais_telefone"]."%'");
                //email
                if(isset($parametros["profissionais_email"]) && $parametros["profissionais_email"] != "" )
                    $select->where("profissionais_email ILIKE '".$parametros["profissionais_email"]."%'");
                //funções
                if(isset($parametros["fk_funcoes_id"]) && $parametros["fk_funcoes_id"] != "" )
                    $select->where("fk_funcoes_id = '".$parametros["fk_funcoes_id"]."%'");
                //unidades
                if(isset($parametros["fk_unidades_id"]) && $parametros["fk_unidades_id"] != "" )
                    $select->where("fk_unidades_id = '".$parametros["fk_unidades_id"]."%'");
                //status
                if(isset($parametros["profissionais_status"]) && $parametros["profissionais_status"] != "" )
                    $select->where("profissionais_status = '".$parametros["profissionais_status"]."'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("profissionais_id DESC");
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
    
    public function getCampoPeloId($profissionais_id = 0, $campo = 'profissionais_nome'){
        if($profissionais_id > 0){
            return $this
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from($this->_name, $campo, $this->_schema)
                    ->where("profissionais_id = ?",$profissionais_id)
                    ->query()
                    ->fetchAll();
        }else{
            return "Não foi possível encontrar o registro solicitado.";
        }
    }
    
    public function validarLogin($usuario, $senha){
        
        //ChromePhp::log(sha1($senha));
        
        try{                    
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            $query  = $db
                        ->select()
                        ->from($this->_name, array("profissionais_id","profissionais_nome","profissionais_cargo") , $this->_schema)
                        ->where("profissionais_cpf = ?", $usuario)
                        ->where("profissionais_senha = ?", sha1($senha))
                        ->where("profissionais_status = 'Ativo'");
            
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
                        ->from($this->_name, array("profissionais_salario", "profissionais_alimentacao", "profissionais_transporte", "profissionais_impostos", "profissionais_obs"), $this->_schema)
                        ->where("profissionais_id = ?",$prof);

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
                        ->from($this->_name, array("profissionais_id", "profissionais_nome", "fk_unidades_id", "fk_funcoes_id", "b.unidades_nome", "c.funcoes_descricao"), $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".unidades"),"b.unidades_id = fk_unidades_id",array())
						->joinInner(array("c"=>$this->_schema.".funcoes"),"c.funcoes_id = fk_funcoes_id",array())
                        ->where("profissionais_id = ?",$prof);

	    $dados = $query
                        ->query()
                        ->fetchAll();

            return $dados;

        }catch(Exception $e){
            ChromePhp::log($e);
            return false;
        }
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
