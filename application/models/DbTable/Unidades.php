<?php

class Application_Model_DbTable_Unidades extends Zend_Db_Table
{

    protected $_name = 'unidades';
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
            
            $where = $this->getAdapter()->quoteInto('unidades_id = ?', $dados['unidades_id']);
            
            unset($dados['unidades_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('unidades_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"unidades_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = NULL){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("unidades_status != 'Inativo'");
        
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
                'a.unidades_id',
                'a.unidades_nome',
                'a.unidades_cel_1',
                'a.unidades_cel_2',
                'a.unidades_fixo_1',
                'a.unidades_fixo_2',
                'a.unidades_cep',
                'a.fk_municipios_codigo_ibge',
                'a.unidades_bairro',
                'a.unidades_logradouro',
                'a.unidades_complemento',
                'a.unidades_status'
            );
                    
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("a.unidades_status != 'Inativo'")
                        ->order("a.unidades_nome");
            
            
            //Tratamento para os filtros passados como parametros
            if(sizeof($parametros) > 0){
                if(isset($parametros["unidades_id"]) && $parametros["unidades_id"] > 0 )
                    $select->where("a.unidades_id = ?", $parametros["unidades_id"]);
                if(isset($parametros["unidades_nome"]) && $parametros["unidades_nome"] != "" )
                    $select->where("a.unidades_nome ILIKE '".$parametros["unidades_nome"]."%'");
                if(isset($parametros["unidades_fixo_1"]) && $parametros["unidades_fixo_1"] != "" )
                    $select->where("a.unidades_fixo_1 ILIKE '".$parametros["unidades_fixo_1"]."%'");
                if(isset($parametros["unidades_logradouro"]) && $parametros["unidades_logradouro"] != "" )
                    $select->where("a.unidades_logradouro ILIKE '".$parametros["unidades_logradouro"]."%'");
                if(isset($parametros["unidades_status"]) && $parametros["unidades_status"] != "" )
                    $select->where("a.unidades_status = '".$parametros["unidades_status"]."'");
                
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
    
    public function getRegistros($parametros = array(), $show_und = null){
        
        $retorno = true;
        try{
            
            $colunas = array(
                'a.unidades_id',
                'a.unidades_nome',
                'a.unidades_cel_1',
                'a.unidades_cel_2',
                'a.unidades_fixo_1',
                'a.unidades_fixo_2',
                'a.unidades_cep',
                'a.fk_municipios_codigo_ibge',
                'a.unidades_bairro',
                'a.unidades_logradouro',
                'a.unidades_complemento',
                'a.unidades_status'
            );
                    
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("a.unidades_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                if(isset($parametros["unidades_id"]) && $parametros["unidades_id"] > 0 )
                    $select->where("a.unidades_id = ?", $parametros["unidades_id"]);
                if(isset($parametros["unidades_nome"]) && $parametros["unidades_nome"] != "" )
                    $select->where("a.unidades_nome ILIKE '".$parametros["unidades_nome"]."%'");
                if(isset($parametros["unidades_fixo_1"]) && $parametros["unidades_fixo_1"] != "" )
                    $select->where("a.unidades_fixo_1 ILIKE '".$parametros["unidades_fixo_1"]."%'");
                if(isset($parametros["unidades_logradouro"]) && $parametros["unidades_logradouro"] != "" )
                    $select->where("a.unidades_logradouro ILIKE '".$parametros["unidades_logradouro"]."%'");
                if(isset($parametros["unidades_status"]) && $parametros["unidades_status"] != "" )
                    $select->where("a.unidades_status = '".$parametros["unidades_status"]."'");
                
                
            }
            
            if (isset($show_und) && $show_und != null){
				$select->where("a.unidades_id = ?", $show_und);
			}
            
            //Order by
            if(isset($parametros["orderBy"])){
                $select->order($parametros["orderBy"]);
            }else{
                $select->order("a.unidades_nome");
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
