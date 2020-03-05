<?php

class Application_Model_DbTable_Clientes extends Zend_Db_Table
{

    protected $_name = 'clientes';
    protected $_schema = 'main';

    
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
            
            $where = $this->getAdapter()->quoteInto('clientes_id = ?', $dados['clientes_id']);
            
            unset($dados['clientes_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('clientes_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    /**
     * Este método retorna todos os registros com base nos parametros passados
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"clientes_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = NULL){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("clientes_status != 'Inativo'");
        
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

            // echo $select->__toString();exit;
            
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
                'a.clientes_id',
                'a.clientes_nome',
                'a.clientes_cel_1',
                'a.clientes_cel_2',
                'a.clientes_fixo_1',
                'a.clientes_fixo_2',
                'a.clientes_cep',
                'a.fk_municipios_codigo_ibge',
                'a.clientes_bairro',
                'a.clientes_logradouro',
                'a.clientes_complemento',
                'a.clientes_status'
            );
                    
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("a.clientes_status != 'Inativo'")
                        ->order("a.clientes_nome");
            
            
            //Tratamento para os filtros passados como parametros
            if(sizeof($parametros) > 0){
                if(isset($parametros["clientes_id"]) && $parametros["clientes_id"] > 0 )
                    $select->where("a.clientes_id = ?", $parametros["clientes_id"]);
                if(isset($parametros["clientes_nome"]) && $parametros["clientes_nome"] != "" )
                    $select->where("a.clientes_nome ILIKE '".$parametros["clientes_nome"]."%'");
                if(isset($parametros["clientes_fixo_1"]) && $parametros["clientes_fixo_1"] != "" )
                    $select->where("a.clientes_fixo_1 ILIKE '".$parametros["clientes_fixo_1"]."%'");
                if(isset($parametros["clientes_logradouro"]) && $parametros["clientes_logradouro"] != "" )
                    $select->where("a.clientes_logradouro ILIKE '".$parametros["clientes_logradouro"]."%'");
                if(isset($parametros["clientes_status"]) && $parametros["clientes_status"] != "" )
                    $select->where("a.clientes_status = '".$parametros["clientes_status"]."'");
                
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
                'a.clientes_id',
                'a.clientes_nome',
                'a.clientes_cel_1',
                'a.clientes_cel_2',
                'a.clientes_fixo_1',
                'a.clientes_fixo_2',
                'a.clientes_cep',
                'a.fk_municipios_codigo_ibge',
                'a.clientes_bairro',
                'a.clientes_logradouro',
                'a.clientes_complemento',
                'a.clientes_status'
            );
                    
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("a.clientes_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                if(isset($parametros["clientes_id"]) && $parametros["clientes_id"] > 0 )
                    $select->where("a.clientes_id = ?", $parametros["clientes_id"]);
                if(isset($parametros["clientes_nome"]) && $parametros["clientes_nome"] != "" )
                    $select->where("a.clientes_nome ILIKE '".$parametros["clientes_nome"]."%'");
                if(isset($parametros["clientes_fixo_1"]) && $parametros["clientes_fixo_1"] != "" )
                    $select->where("a.clientes_fixo_1 ILIKE '".$parametros["clientes_fixo_1"]."%'");
                if(isset($parametros["clientes_logradouro"]) && $parametros["clientes_logradouro"] != "" )
                    $select->where("a.clientes_logradouro ILIKE '".$parametros["clientes_logradouro"]."%'");
                if(isset($parametros["clientes_status"]) && $parametros["clientes_status"] != "" )
                    $select->where("a.clientes_status = '".$parametros["clientes_status"]."'");
                
                
            }
            
            if (isset($show_und) && $show_und != null){
				$select->where("a.clientes_id = ?", $show_und);
			}
            
            //Order by
            if(isset($parametros["orderBy"])){
                $select->order($parametros["orderBy"]);
            }else{
                $select->order("a.clientes_nome");
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
