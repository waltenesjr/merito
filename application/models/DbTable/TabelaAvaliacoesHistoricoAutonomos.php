<?php

class Application_Model_DbTable_TabelaAvaliacoesHistoricoAutonomos extends Zend_Db_Table
{

    protected $_name = 'tah_autonomos';
    protected $_schema = '';

    public function __construct(){
        $this->_schema = $_SESSION["cliente_schema"];
        parent::__construct();
    }

    
    public function inserir($dados)
    {
        $retorno = false;
        
        ChromePhp::log($dados);
        
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
            
            $where = $this->getAdapter()->quoteInto('tah_id = ?', $dados['tah_id']);
            
            unset($dados['tah_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('tah_id = ?', $dados['id']);
            
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
     * Ex.: getRegistrosPorTipo([["condicao"=>"AND", "coluna"=>"tah_id", "operador"=>"=", "valor"=>"100"]])
     */
    public function getRegistrosFiltrados($parametros = null){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->where("tah_status != 'Inativo'");
        
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
                'a.tah_nota',
                'b.autonomos_nome',
                'c.atribuicoes_nome',
                'a.tah_id'
            );
            
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $columns, $this->_schema)
                        ->joinInner(array("b"=>$this->_schema.".autonomos"),"b.autonomos_id = a.fk_autonomos_id",array())
                        ->joinInner(array("c"=>$this->_schema.".atribuicoes"),"c.atribuicoes_id = a.fk_atribuicoes_id",array())
                        ->where("a.tah_status != 'Inativo'")
                        ->where('a.fk_autonomos_id = ?',$parametros["fk_autonomos_id"])
                        ->order('a.tah_autonomos_id DESC');
                
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
                    'tah_autonomos_id',
                    'tah_descricao',
                    'tah_status'
                );
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), $colunas, $this->_schema)
                        ->where("tah_status != 'Inativo'");
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["tah_autonomos_id"]) && $parametros["tah_autonomos_id"] > 0 )
                    $select->where("tah_autonomos_id = ?", $parametros["tah_autonomos_id"]);
                //descricao
                if(isset($parametros["tah_descricao"]) && $parametros["tah_descricao"] != "" )
                    $select->where("tah_descricao ILIKE '".$parametros["tah_descricao"]."%'");
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("tah_autonomos_id DESC");
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
    
    public function getParametroPeloId($tah_id, $parametro = 'tah_descricao'){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $parametro, $this->_schema)
                ->where("tah_status != 'Inativo'")
                ->where("tah_autonomos_id = ?",$tah_id);
        
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
                ->from($this->_name, 'tah_autonomos_id', $this->_schema)
                ->where("fk_autonomos_id = ?",$parametros["fk_autonomos_id"])
                ->where("fk_atribuicoes_id = ?",$parametros["fk_atribuicoes_id"])
                ->where("TO_CHAR(tah_timestamp, 'YYYY-MM-DD') = ?",$parametros['tah_timestamp']);
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno[0]['tah_autonomos_id'];
    }
    public function getUltimosInseridos($parametros){
        try{
            $columns = array(
                '*',
                'b.atribuicoes_nome',
                'c.autonomos_nome',
                'd.unidades_nome',
                'e.funcoes_descricao'
            );
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $columns, $this->_schema)
                ->joinInner(array('b'=>$this->_schema.'.atribuicoes'),'b.atribuicoes_id = fk_atribuicoes_id',array())
                ->joinInner(array('c'=>$this->_schema.'.autonomos'),'c.autonomos_id = fk_autonomos_id',array())
                ->joinInner(array('d'=>$this->_schema.'.unidades'),'d.unidades_id = c.fk_unidades_id',array())
                ->joinInner(array('e'=>$this->_schema.'.funcoes'),'e.funcoes_id = c.fk_funcoes_id',array())
                ->where("fk_autonomos_id = ?",$parametros["fk_autonomos_id"])
                ->where("TO_CHAR(tah_timestamp, 'YYYY-MM-DD') = ?",$parametros['tah_timestamp'])
                ->order("tah_autonomos_id DESC");

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
                'c.autonomos_nome',
                'd.unidades_nome',
                'e.funcoes_descricao'
            );
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, $columns, $this->_schema)
                ->joinInner(array('b'=>$this->_schema.'.atribuicoes'),'b.atribuicoes_id = fk_atribuicoes_id',array())
                ->joinInner(array('c'=>$this->_schema.'.autonomos'),'c.autonomos_id = fk_usuarios_id_cad',array())
                ->joinInner(array('d'=>$this->_schema.'.unidades'),'d.unidades_id = c.fk_unidades_id',array())
                ->joinInner(array('e'=>$this->_schema.'.funcoes'),'e.funcoes_id = c.fk_funcoes_id',array())
                ->where("fk_autonomos_id = ?",$parametros["fk_autonomos_id"])
                ->where("TO_CHAR(tah_timestamp, 'YYYY-MM-DD') = ?",$parametros['tah_timestamp'])
                ->order("tah_autonomos_id DESC");
            
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
