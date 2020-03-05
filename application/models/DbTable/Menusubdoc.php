<?php

class Application_Model_DbTable_Menusubdoc extends Zend_Db_Table
{

    protected $_name = 'menusub_doc';
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
            
            $where = $this->getAdapter()->quoteInto('menusub_id = ?', $dados['menusub_id']);
            
            unset($dados['menusub_id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = $exc;
        }
        return $retorno;
    }
    
    public function excluirXid($dados)
    {
        
        $retorno = false;
        
        try {
			ChromePhp::log("Deleting");
			$where = $this->getAdapter()->quoteInto('menusub_id = ?', $dados['id']);
 
			$retorno = $this->delete($where);
			ChromePhp::log("Deleted");
			
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
			$where = $this->getAdapter()->quoteInto('fk_menu_id = ?', $dados['id']);
 
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
    
    public function getRegistros($parametros = array()){
        
        $retorno = true;
        try{            
            $select = $this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from($this->_name, '*', $this->_schema);
            
            if(sizeof($parametros) > 0){
                if(isset($parametros["menu_id"]) && $parametros["menu_id"] > 0 )
                    $select->where("fk_menu_id = ?", $parametros["menu_id"]);
                if(isset($parametros["id"]) && $parametros["id"] > 0 ) {
					ChromePhp::log("Entro");
					ChromePhp::log($parametros["id"]);
                    $select->where("menusub_id = ?", $parametros["id"]);
                }
                if(isset($parametros["orderBy"])){
                    $select->order($parametros["orderBy"]);
                }else{
                    $select->order("menusub_id DESC");
                }
            }
            
            ChromePhp::log($select->__toString());
            
            $retorno = $select
                        ->query()
                        ->fetchAll();
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }

}
