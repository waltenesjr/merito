<?php

class Application_Model_DbTable_Relatorios extends Zend_Db_Table
{

    protected $_name = 'tabela_avaliacoes';
    protected $_schema = '';

    public function __construct(){
        $this->_schema = $_SESSION["cliente_schema"];
        parent::__construct();
    }

	public function getMelhorProf($parametros) {
		try{
 
			$colunas = array(
                new Zend_Db_Expr("to_char(avg(a.tabela_avaliacoes_nota)::real,'999D99') as media"),
                "p.profissionais_nome",
                "u.unidades_nome",
                "f.funcoes_descricao"
                );

			$select = $this
				->select()
				->setIntegrityCheck(false)
				->from(array('a'=>'tabela_avaliacoes'), $colunas, $this->_schema)
				->join(array("p"=>$this->_schema.".profissionais"),"a.fk_profissionais_id = p.profissionais_id", "")
				->join(array("u"=>$this->_schema.".unidades"),"u.unidades_id = p.fk_unidades_id", "")
				->join(array("f"=>$this->_schema.".funcoes"), "p.fk_funcoes_id = f.funcoes_id", "")
				->where("a.tabela_avaliacoes_timestamp >= ?" ,$parametros["data_ini"])
				->where("a.tabela_avaliacoes_timestamp <= ?" ,$parametros["data_fim"])
				->group("p.profissionais_nome")
				->group("u.unidades_nome")
				->group("f.funcoes_descricao")
				->order("media DESC")
				->limit("50");

			
			if (isset($parametros['fk_unidades_id']) && $parametros['fk_unidades_id'] != ""){
				$select->where("p.fk_unidades_id = ?", $parametros['fk_unidades_id']);
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
    
    public function getMelhorProfFunc($parametros) {
		try{

			$colunas = array(
                new Zend_Db_Expr("to_char(avg(a.tabela_avaliacoes_nota)::real,'999D99') as media"),
                "p.profissionais_nome",
                "u.unidades_nome",
                "f.funcoes_descricao"
                );

			$select = $this
				->select()
				->setIntegrityCheck(false)
				->from(array('a'=>'tabela_avaliacoes'), $colunas, $this->_schema)
				->join(array("p"=>$this->_schema.".profissionais"),"a.fk_profissionais_id = p.profissionais_id", "")
				->join(array("u"=>$this->_schema.".unidades"),"u.unidades_id = p.fk_unidades_id", "")
				->join(array("f"=>$this->_schema.".funcoes"), "p.fk_funcoes_id = f.funcoes_id", "")
				->where("a.tabela_avaliacoes_timestamp >= ?" ,$parametros["data_ini"])
				->where("a.tabela_avaliacoes_timestamp <= ?" ,$parametros["data_fim"])
				->group("p.profissionais_nome")
				->group("u.unidades_nome")
				->group("f.funcoes_descricao")
				->order("media DESC")
				->limit("50");

			
			if (isset($parametros['fk_unidades_id']) && $parametros['fk_unidades_id'] != ""){
				$select->where("p.fk_unidades_id = ?", $parametros['fk_unidades_id']);
			}

			if (isset($parametros['fk_funcoes_id']) && $parametros['fk_funcoes_id'] != ""){
				$select->where("p.fk_funcoes_id = ?", $parametros['fk_funcoes_id']);
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
    
    public function getPiorProf($parametros) {
		try{

			$colunas = array(
                new Zend_Db_Expr("to_char(avg(a.tabela_avaliacoes_nota)::real,'999D99') as media"),
                "p.profissionais_nome",
                "u.unidades_nome",
                "f.funcoes_descricao"
                );

			$select = $this
				->select()
				->setIntegrityCheck(false)
				->from(array('a'=>'tabela_avaliacoes'), $colunas, $this->_schema)
				->join(array("p"=>$this->_schema.".profissionais"),"a.fk_profissionais_id = p.profissionais_id", "")
				->join(array("u"=>$this->_schema.".unidades"),"u.unidades_id = p.fk_unidades_id", "")
				->join(array("f"=>$this->_schema.".funcoes"), "p.fk_funcoes_id = f.funcoes_id", "")
				->where("a.tabela_avaliacoes_timestamp >= ?" ,$parametros["data_ini"])
				->where("a.tabela_avaliacoes_timestamp <= ?" ,$parametros["data_fim"])
				->group("p.profissionais_nome")
				->group("u.unidades_nome")
				->group("f.funcoes_descricao")
				->order("media ASC")
				->limit("50");

			
			if (isset($parametros['fk_unidades_id']) && $parametros['fk_unidades_id'] != ""){
				$select->where("p.fk_unidades_id = ?", $parametros['fk_unidades_id']);
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
    
    public function getPiorProfFunc($parametros) {
		try{

			$colunas = array(
                new Zend_Db_Expr("to_char(avg(a.tabela_avaliacoes_nota)::real,'999D99') as media"),
                "p.profissionais_nome",
                "u.unidades_nome",
                "f.funcoes_descricao"
                );

			$select = $this
				->select()
				->setIntegrityCheck(false)
				->from(array('a'=>'tabela_avaliacoes'), $colunas, $this->_schema)
				->join(array("p"=>$this->_schema.".profissionais"),"a.fk_profissionais_id = p.profissionais_id", "")
				->join(array("u"=>$this->_schema.".unidades"),"u.unidades_id = p.fk_unidades_id", "")
				->join(array("f"=>$this->_schema.".funcoes"), "p.fk_funcoes_id = f.funcoes_id", "")
				->where("a.tabela_avaliacoes_timestamp >= ?" ,$parametros["data_ini"])
				->where("a.tabela_avaliacoes_timestamp <= ?" ,$parametros["data_fim"])
				->group("p.profissionais_nome")
				->group("u.unidades_nome")
				->group("f.funcoes_descricao")
				->order("media ASC")
				->limit("50");
			
			if (isset($parametros['fk_unidades_id']) && $parametros['fk_unidades_id'] != ""){
				$select->where("p.fk_unidades_id = ?", $parametros['fk_unidades_id']);
			}

			if (isset($parametros['fk_funcoes_id']) && $parametros['fk_funcoes_id'] != ""){
				$select->where("p.fk_funcoes_id = ?", $parametros['fk_funcoes_id']);
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
    
    public function getProfAval($parametros) {
		try{

			$colunas = array(
                "p.profissionais_nome",
                "u.unidades_nome",
                "f.funcoes_descricao",
                "a.tabela_avaliacoes_nota",
                "at.atribuicoes_nome",
                "j.porque",
                "j.descricao"
                );

			$select = $this
				->select()
				->setIntegrityCheck(false)
				->from(array('a'=>'tabela_avaliacoes'), $colunas, $this->_schema)
				->join(array("p"=>$this->_schema.".profissionais"),"a.fk_profissionais_id = p.profissionais_id", "")
				->join(array("u"=>$this->_schema.".unidades"),"u.unidades_id = p.fk_unidades_id", "")
				->join(array("at"=>$this->_schema.".atribuicoes"),"at.atribuicoes_id = a.fk_atribuicoes_id", "")
				->join(array("f"=>$this->_schema.".funcoes"), "p.fk_funcoes_id = f.funcoes_id", "")
				->joinLeft(array("j"=>$this->_schema.".justificativas"), "j.fk_tabela_avaliacoes_id = a.tabela_avaliacoes_id", "")
				->where("a.tabela_avaliacoes_timestamp >= ?" ,$parametros["data_ini"])
				->where("a.tabela_avaliacoes_timestamp <= ?" ,$parametros["data_fim"])
				->where("a.tabela_avaliacoes_nota = ?", $parametros['nota'])
				->limit("50");
				

            $retorno = $select
                        ->query()
                        ->fetchAll();
    
		} catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno = false;
        }

        return $retorno;
        
    }
    
    public function getMediaUnidades($parametros) {
		try{

			$colunas = array(
                new Zend_Db_Expr("to_char(avg(a.tabela_avaliacoes_nota)::real,'999D99') as media"),
                "u.unidades_nome"
                );

			$select = $this
				->select()
				->setIntegrityCheck(false)
				->from(array('a'=>'tabela_avaliacoes'), $colunas, $this->_schema)
				->join(array("p"=>$this->_schema.".profissionais"),"a.fk_profissionais_id = p.profissionais_id", "")
				->join(array("u"=>$this->_schema.".unidades"),"p.fk_unidades_id = u.unidades_id", "")
                                ->where("a.tabela_avaliacoes_timestamp >= ?" ,$parametros["data_ini"])
				->where("a.tabela_avaliacoes_timestamp <= ?" ,$parametros["data_fim"])
				->group("u.unidades_id")
                                ->order("media DESC")
				;
            $retorno = $select
                        ->query()
                        ->fetchAll();
			
		} catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno = false;
        }

        return $retorno;
        
    }
    
    public function getManual($parametros) {
		try{

			$colunas = array(
				"a.atribuicoes_nome",
				"a.atribuicoes_descricao"
			);

			$select = $this
				->select()
				->setIntegrityCheck(false)
				->from(array('fa'=>'funcoes_atribuicoes'), $colunas, $this->_schema)
				->join(array("a"=>$this->_schema.".atribuicoes"),"fa.fk_atribuicoes_id = a.atribuicoes_id", "")
				->where("fa.fk_funcoes_id = ?", $parametros["fk_funcoes_id"]);

            $retorno = $select
                        ->query()
                        ->fetchAll();
                        
		} catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno = false;
        }

        return $retorno;
        
    }    
    
}
