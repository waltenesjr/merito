<?php

class Application_Model_DbTable_Profissionaissupervisor extends Zend_Db_Table
{

    protected $_name = 'profissionais_supervisor';
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
    
    public function excluir($dados)
    {
        $retorno = false;
        try {
            $where = $this->getAdapter()->quoteInto('profissionais_supervisor_id = ?', $dados['id']);
            unset($dados['id']);
            $retorno = $this->delete($where);
        } catch (Exception $exc) {
            ChromePhp::log($exc);
            $retorno = false;
        }
        
        return $retorno;
    }
    
    public function getProfissionais($fk_supervisor_id){
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from($this->_name, "*", $this->_schema)
                ->joinInner(array("p"=>$this->_schema.".profissionais"),"p.profissionais_id = fk_usuarios_id",array("profissionais_nome"))
                ->where("fk_supervisor_id = ?", $fk_supervisor_id)
                ->where("p.profissionais_status != 'Inativo'")
                ->where("p.profissionais_status != 'Bloqueado'");
        
            $retorno = $select
                        ->query()
                        ->fetchAll();
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }

}
