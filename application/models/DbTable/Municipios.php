<?php

class Application_Model_DbTable_Municipios extends Zend_Db_Table
{

    protected $_name = 'municipios';
    protected $_schema = '';

    public function __construct(){
        $this->_schema = $_SESSION["cliente_schema"] ? $_SESSION["cliente_schema"] : 'dados';
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
            
            $where = $this->getAdapter()->quoteInto('municipios_id = ?', $dados['municipios_id']);
            
            unset($dados['municipios_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('municipios_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    public function getRegistros($colunas = array(), $parametros = array())
    {
        if(sizeof($colunas) == 0){
            $colunas = array('municipios_uf','municipios_descricao','municipios_codigo_ibge');
        }
        $retorno = true;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from($this->_name, $colunas, $this->_schema);
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["municipios_uf"]) && $parametros["municipios_uf"] != '' )
                    $select->where("municipios_id = '?'", $parametros["municipios_id"]);
                //descricao
                if(isset($parametros["municipios_descricao"]) && $parametros["municipios_descricao"] != "" )
                    $select->where("municipios_descricao ILIKE '".$parametros["municipios_descricao"]."%'");
            }
            
            
            //Group by
            $select->group("municipios_codigo_ibge");

            //Order by
            $select->order( "municipios_descricao" );
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
                
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getUf()
    {
        $retorno = false;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from($this->_name, array("municipios_uf"), $this->_schema);
            
            
            //Group by
            $select->group("municipios_uf");

            //Order by
            $select->order("municipios_uf");
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
                
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getMunicipiosPelaUf($uf = 'GO')
    {
        $retorno = false;
        try{
            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array("a"=>$this->_name), array("municipios_codigo_ibge","municipios_descricao"), $this->_schema);

            
            $select->where("a.municipios_uf = ?",$uf);
            
            
            //Order by
            $select->order(array("municipios_descricao"));
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
                
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getUfPeloCodigoIbge($codigoIbge = 0){
        $uf = 'GO';
        try{
            if($codigoIbge > 0){
                $select = $this
                            ->select()
                            ->setIntegrityCheck(false)
                            ->from(array("a"=>$this->_name), array("municipios_uf"), $this->_schema)
                            ->where("municipios_codigo_ibge = ?", $codigoIbge)
                            ->group("municipios_codigo_ibge");
                
                
                
                $dados = $select
                            ->query()
                            ->fetchAll();
                
                $uf = $dados[0]['municipios_uf'];
            }
        } catch (Exception $exc) {
            ChromePhp::log($exc);
        }
        return $uf;
    }
}
