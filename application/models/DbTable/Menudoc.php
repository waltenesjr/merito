<?php

class Application_Model_DbTable_Menudoc extends Zend_Db_Table
{

    protected $_name = 'menu_doc';
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
            
            $where = $this->getAdapter()->quoteInto('menu_id = ?', $dados['menu_id']);
            
            unset($dados['menu_id']);
            
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

			$where = $this->getAdapter()->quoteInto('menu_id = ?', $dados['id']);
 
			$retorno = $this->delete($where);

        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    public function getMenus(){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema);

            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getSubMenus(){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
			->from("menusub_doc", "*", $this->_schema);
            
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
                        ->from($this->_name, '*', $this->_schema);
            
            
            //Tratamento para os filtros passados como parâmetros
            if(sizeof($parametros) > 0){
                //id
                if(isset($parametros["menu_id"]) && $parametros["menu_id"] > 0 )
                    $select->where("menu_id = ?", $parametros["menu_id"]);
                if(isset($parametros["id"]) && $parametros["id"] > 0 )
                    $select->where("menu_id = ?", $parametros["id"]);
                
                //Order by
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("menu_id DESC");
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
