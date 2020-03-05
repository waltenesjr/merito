<?php

class Application_Model_DbTable_TabelaAvaliacoesHistorico extends Zend_Db_Table
{

    protected $_name = 'tabela_avaliacoes_historico';
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
            
            $where = $this->getAdapter()->quoteInto('tabela_avaliacoes_historico_id = ?', $dados['tabela_avaliacoes_historico_id']);
            
            unset($dados['tabela_avaliacoes_historico_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('tabela_avaliacoes_historico_id = ?', $dados['id']);
            
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
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"tabela_avaliacoes_historico_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("tabela_avaliacoes_historico_status != 'Inativo'");
        
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
    
    public function getRegistrosFiltradosGrid($parametros = array())
    {
        
        $retorno = true;
        try{
            $columns = array(
                'fk_usuarios_data_operacao' => 'TO_CHAR(a.fk_usuarios_data_operacao,\'DD/MM/YYYY HH24:MI:SS\')',
                'a.tabela_avaliacoes_historico_nota',
                'b.profissionais_nome',
                'c.atribuicoes_nome',
                'a.tabela_avaliacoes_historico_id'
            );
            
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $columns, $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".profissionais"),"b.profissionais_id = a.fk_profissionais_id",array())
                        ->joinInner(array("c"=>$this->_schema.".atribuicoes"),"c.atribuicoes_id = a.fk_atribuicoes_id",array())
                        ->where("a.tabela_avaliacoes_historico_status != 'Inativo'")
                        ->where('a.fk_profissionais_id = ?',$parametros["fk_profissionais_id"])
                        ->order('a.tabela_avaliacoes_historico_id DESC');
                
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
                    'tabela_avaliacoes_historico_id',
                    'tabela_avaliacoes_historico_descricao',
                    'tabela_avaliacoes_historico_status'
                );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("tabela_avaliacoes_historico_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["tabela_avaliacoes_historico_id"]) && $parametros["tabela_avaliacoes_historico_id"] > 0 )
                    $select->where("tabela_avaliacoes_historico_id = ?", $parametros["tabela_avaliacoes_historico_id"]);
                //descricao
                if(isset($parametros["tabela_avaliacoes_historico_descricao"]) && $parametros["tabela_avaliacoes_historico_descricao"] != "" )
                    $select->where("tabela_avaliacoes_historico_descricao ILIKE '".$parametros["tabela_avaliacoes_historico_descricao"]."%'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("tabela_avaliacoes_historico_id DESC");
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
    
    public function getParametroPeloId($tabela_avaliacoes_historico_id, $parametro = 'tabela_avaliacoes_historico_descricao'){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $parametro, $this->_schema)
                ->where("tabela_avaliacoes_historico_status != 'Inativo'")
                ->where("tabela_avaliacoes_historico_id = ?",$tabela_avaliacoes_historico_id);
        
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
                ->from($this->_name, 'tabela_avaliacoes_historico_id', $this->_schema)
                ->where("fk_profissionais_id = ?",$parametros["fk_profissionais_id"])
                ->where("fk_atribuicoes_id = ?",$parametros["fk_atribuicoes_id"])
                ->where("TO_CHAR(tabela_avaliacoes_historico_timestamp, 'YYYY-MM-DD') = ?",$parametros['tabela_avaliacoes_historico_timestamp']);
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno[0]['tabela_avaliacoes_historico_id'];
    }
    public function getUltimosInseridos($parametros){
        try{
            $columns = array(
                '*',
                'b.atribuicoes_nome',
                'c.profissionais_nome',
                'd.unidades_nome',
                'e.funcoes_descricao'
            );
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $columns, $this->_schema)
                ->joinInner(array('b'=>$this->_schema.'.atribuicoes'),'b.atribuicoes_id = fk_atribuicoes_id',array())
                ->joinInner(array('c'=>$this->_schema.'.profissionais'),'c.profissionais_id = fk_profissionais_id',array())
                ->joinInner(array('d'=>$this->_schema.'.unidades'),'d.unidades_id = c.fk_unidades_id',array())
                ->joinInner(array('e'=>$this->_schema.'.funcoes'),'e.funcoes_id = c.fk_funcoes_id',array())
                ->where("fk_profissionais_id = ?",$parametros["fk_profissionais_id"])
                ->where("TO_CHAR(tabela_avaliacoes_historico_timestamp, 'YYYY-MM-DD') = ?",$parametros['tabela_avaliacoes_historico_timestamp'])
                ->order("tabela_avaliacoes_historico_id DESC");

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
    public function getRegistrosAtribuicao($parametros){
        try{
            $columns = array(
                '*',
                'b.atribuicoes_nome',
                'c.profissionais_nome',
                'd.unidades_nome',
                'e.funcoes_descricao'
            );
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $columns, $this->_schema)
                ->joinInner(array('b'=>$this->_schema.'.atribuicoes'),'b.atribuicoes_id = fk_atribuicoes_id',array())
                ->joinInner(array('c'=>$this->_schema.'.profissionais'),'c.profissionais_id = fk_usuarios_id_cad',array())
                ->joinInner(array('d'=>$this->_schema.'.unidades'),'d.unidades_id = c.fk_unidades_id',array())
                ->joinInner(array('e'=>$this->_schema.'.funcoes'),'e.funcoes_id = c.fk_funcoes_id',array())
                ->where("fk_profissionais_id = ?",$parametros["fk_profissionais_id"])
                ->where("TO_CHAR(tabela_avaliacoes_historico_timestamp, 'YYYY-MM-DD') = ?",$parametros['tabela_avaliacoes_historico_timestamp'])
                ->order("tabela_avaliacoes_historico_id DESC");
            
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
}
