<?php

class Application_Model_DbTable_Permissoes extends Zend_Db_Table
{

    protected $_name = 'permissoes';
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
            
            $where = $this->getAdapter()->quoteInto('permissoes_id = ?', $dados['permissoes_id']);
            
            unset($dados['permissoes_id']);
            
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
            
            $where = $this->getAdapter()->quoteInto('permissoes_id = ?', $dados['id']);
            
            unset($dados['id']);
            
            $retorno = $this->update($dados, $where);
            
        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
    public function getPermissoesPorProfissionaisId($profissionais_id){
        $retorno = false;
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from(array("a"=>$this->_name), array("a.permissoes_nome","a.permissoes_modulo", "p.profissionais_cargo", "p.fk_unidades_id", "p.profissionais_id"), $this->_schema)
                ->joinInner(array("b"=>$this->_schema.".permissoes_profissionais"),"b.fk_permissoes_id = a.permissoes_id",array())
                ->joinInner(array("p"=>$this->_schema.".profissionais"),"b.fk_profissionais_id = p.profissionais_id", "")
                ->where("b.fk_profissionais_id = ?",$profissionais_id)
                ->where("a.permissoes_status = 'Ativo'");
        
            $retorno = $select
                        ->query()
                        ->fetchAll();
        }catch(Exception $e){
            ChromePhp::log($e);
            $retorno = false;
        }
        return $retorno;
    }
    
    public function getAll(){
        $retorno = false;
        try{
            $select = $this
                ->select()
                ->setIntegrityCheck(false)
                ->from(array("a"=>$this->_name), "*", $this->_schema)
                ->where("a.permissoes_status = 'Ativo'")
                ->order(array("a.permissoes_modulo","a.permissoes_nome"));
        
            $retorno = $select
                        ->query()
                        ->fetchAll();
        }catch(Exception $e){
            ChromePhp::log($e);
            $retorno = false;
        }
        return $retorno;
    }
    
    public function adicionarRemoverPermissoes($permissoes,$profissionais_id){
        try{
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            
            //Deleta todas as permissões
            $db->delete($this->_schema.'.permissoes_profissionais', array( 'fk_profissionais_id = ?' => $profissionais_id ));
            
            if(sizeof($permissoes)>0){
                //Adiciona as permissões se tiver alguma
                $sql = sprintf( 'INSERT INTO '.$this->_schema.'.permissoes_profissionais (fk_profissionais_id, fk_permissoes_id) VALUES ('.$profissionais_id.',%s)', implode( '), ('.$profissionais_id.',' , $permissoes ) );
                $db->query( $sql );
            }
        }catch(Exception $e){
            ChromePhp::log($e);
        }
    }
    
}
