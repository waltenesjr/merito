<?php

class Application_Model_DbTable_TabelaAvaliacoesAutonomos extends Zend_Db_Table
{

    protected $_name = 'tabela_avaliacoes_autonomos';
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
            
            $where = $this->getAdapter()->quoteInto('tabela_avaliacoes_autonomos_id = ?', $dados['tabela_avaliacoes_autonomos_id']);
            
            unset($dados['tabela_avaliacoes_autonomos_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('tabela_avaliacoes_autonomos_id = ?', $dados['id']);
            
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
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"tabela_avaliacoes_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("tabela_avaliacoes_status != 'Inativo'");
        
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
    
    public function getRegistrosMes($mes){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("tabela_avaliacoes_status != 'Inativo'");
            
        ChromePhp::log($select->assemble());
            
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getRegistrosFiltradosGrid($parametros = array())
    {
		//ChromePhp::log("Entra");
		
        $retorno = true;
        try{
            $colunas = array(
                'a.atribuicoes_id',
                'a.atribuicoes_nome',
                'nota' => new Zend_Db_Expr('(SELECT a1.tabela_avaliacoes_nota 
                                        FROM '.$this->_schema.'.tabela_avaliacoes_autonomos AS a1 
                                        WHERE a1.fk_autonomos_id = '.$parametros["fk_autonomos_id"].' 
                                            AND a1.fk_atribuicoes_id = b.fk_atribuicoes_id  
                                            AND TO_CHAR(a1.tabela_avaliacoes_timestamp, \'YYYY-MM-DD\') = \''.$parametros["tabela_avaliacoes_timestamp"].'\'
                                            order by a1.tabela_avaliacoes_autonomos_id desc limit 1)'),

                'id' => new Zend_Db_Expr('(SELECT a1.tabela_avaliacoes_autonomos_id 
                                        FROM '.$this->_schema.'.tabela_avaliacoes_autonomos AS a1 
                                        WHERE a1.fk_autonomos_id = '.$parametros["fk_autonomos_id"].' 
                                            AND a1.fk_atribuicoes_id = b.fk_atribuicoes_id
                                            AND TO_CHAR(a1.tabela_avaliacoes_timestamp, \'YYYY-MM-DD\') = \''.$parametros["tabela_avaliacoes_timestamp"].'\'
                                            order by a1.tabela_avaliacoes_autonomos_id desc limit 1)')
            );
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_schema.".atribuicoes"), $colunas, $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".funcoes_atribuicoes"),"b.fk_atribuicoes_id = a.atribuicoes_id")
                        ->where("a.atribuicoes_status != 'Inativo'")
                        ->where("b.funcoes_atribuicoes_status != 'Inativo'")
                        ->where('b.fk_funcoes_id = ?',$parametros["fk_funcoes_id"]);

			//ChromePhp::log($select->__toString());

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
                    'tabela_avaliacoes_autonomos_id',
                    'tabela_avaliacoes_descricao',
                    'tabela_avaliacoes_status'
                );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("tabela_avaliacoes_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["tabela_avaliacoes_autonomos_id"]) && $parametros["tabela_avaliacoes_autonomos_id"] > 0 )
                    $select->where("tabela_avaliacoes_autonomos_id = ?", $parametros["tabela_avaliacoes_autonomos_id"]);
                //descricao
                if(isset($parametros["tabela_avaliacoes_descricao"]) && $parametros["tabela_avaliacoes_descricao"] != "" )
                    $select->where("tabela_avaliacoes_descricao ILIKE '".$parametros["tabela_avaliacoes_descricao"]."%'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("tabela_avaliacoes_autonomos_id DESC");
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
    
    public function getParametroPeloId($tabela_avaliacoes_autonomos_id, $parametro = 'tabela_avaliacoes_descricao'){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $parametro, $this->_schema)
                ->where("tabela_avaliacoes_status != 'Inativo'")
                ->where("tabela_avaliacoes_autonomos_id = ?",$tabela_avaliacoes_autonomos_id);
        
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno[0][$parametro];
    }
    
    public function getId($parametros){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from(array('a'=>$this->_name), array('a.tabela_avaliacoes_autonomos_id'), $this->_schema)
                ->where("a.fk_autonomos_id = ?",$parametros["fk_autonomos_id"])
                ->where("a.fk_atribuicoes_id = ?",$parametros["fk_atribuicoes_id"])
                ->where("TO_CHAR(a.tabela_avaliacoes_timestamp, 'YYYY-MM-DD') = ?",$parametros['tabela_avaliacoes_timestamp']);

            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        
        if(isset($retorno) && sizeof($retorno) > 0)
            return $retorno[0]['tabela_avaliacoes_autonomos_id'];
        else
            return 0;
    }
    
    public function getUltimosInseridos($parametros){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, '*', $this->_schema)
                ->where("fk_autonomos_id = ?",$parametros["fk_autonomos_id"])
                ->where("TO_CHAR(tabela_avaliacoes_timestamp, 'YYYY-MM-DD') = ?",$parametros['tabela_avaliacoes_timestamp'])
                ->order("tabela_avaliacoes_autonomos_id DESC");
            
            if($parametros['qtde'] > 0 )
                $select->limit($parametros['qtde']);
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }

    public function getUltimaAvaliacao($parametros){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, '*', $this->_schema)
                ->where("fk_autonomos_id = ?",$parametros["fk_autonomos_id"])
		->where("fk_atribuicoes_id = ?",$parametros["fk_atribuicoes_id"])
                ->where("TO_CHAR(tabela_avaliacoes_timestamp, 'YYYY-MM-DD') = ?",$parametros['tabela_avaliacoes_timestamp'])
                ->order("fk_usuarios_data_operacao DESC");

            if($parametros['qtde'] > 0 )
                $select->limit($parametros['qtde']);

            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }

    public function getMensalAvaliacao ($parametros){
	try{
	    $select = $this
		->select()
		->setIntegrityCheck(false)
		->from($this->_name, array(new Zend_Db_Expr("min(tabela_avaliacoes_nota) as min_nota"),
								   new Zend_Db_Expr("max(tabela_avaliacoes_nota) as max_nota"),
							       new Zend_Db_Expr("TO_CHAR(tabela_avaliacoes_timestamp, 'DD') as dia")
								   ), $this->_schema)
		->where("fk_autonomos_id = ?",$parametros['fk_autonomos_id'])
		->where("TO_CHAR(tabela_avaliacoes_timestamp, 'YYYY-MM') = TO_CHAR(NOW(), 'YYYY-MM')")
		->group("TO_CHAR(tabela_avaliacoes_timestamp, 'DD')")
		->order("TO_CHAR(tabela_avaliacoes_timestamp, 'DD')");


            $retorno = $select
                        ->query()
                        ->fetchAll();
	} catch (Exception $exc) {
            $retorno = false;
	    ChromePhp::log($exc);
        }
        return $retorno;
  
   }

    public function getTrimSemMensalAvaliacao ($parametros){
	try{
	    $select = $this
		->select()
		->setIntegrityCheck(false)
		->from($this->_name, array( new Zend_Db_Expr("min(tabela_avaliacoes_nota) as min_nota"),
									new Zend_Db_Expr("max(tabela_avaliacoes_nota) as max_nota"),
									new Zend_Db_Expr("TO_CHAR(tabela_avaliacoes_timestamp, 'DD') as dia"), 
									new Zend_Db_Expr("TO_CHAR(tabela_avaliacoes_timestamp, 'MM') as mes")), $this->_schema)
		->where("fk_autonomos_id = ?",$parametros['fk_autonomos_id'])
		->where("TO_CHAR(tabela_avaliacoes_timestamp, 'YYYY-MM') <= TO_CHAR(NOW(), 'YYYY-MM')")
		->where("TO_CHAR(tabela_avaliacoes_timestamp, 'YYYY-MM') >= TO_CHAR(NOW() - interval '".$parametros['meses']." month', 'YYYY-MM')")
		->group("TO_CHAR(tabela_avaliacoes_timestamp, 'DD')")
		->group("TO_CHAR(tabela_avaliacoes_timestamp, 'MM')")
		->order("TO_CHAR(tabela_avaliacoes_timestamp, 'DD')")
		->order("TO_CHAR(tabela_avaliacoes_timestamp, 'MM')");
		
            $retorno = $select
                        ->query()
                        ->fetchAll();
	} catch (Exception $exc) {
            $retorno = false;
	    ChromePhp::log($exc);
        }

        return $retorno;
   }

}
