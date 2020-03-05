<?php

class PermissoesAutonomosController extends Zend_Controller_Action
{
    protected $_name = 'permissoes_autonomos';
    protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
    }
    
    protected function initAppModel() {

        $accessController = str_replace('-', "", $this->getRequest()->getControllerName());
        $className = "Application_Model_DbTable_" . ucfirst($accessController);

        if (!class_exists($className)):
            $this->view->msg = 'ERROR: App not found (' . $className . ').';
        endif;

        return new $className();
    }
    
    public function preDispatch() {
        //Carrega a sessão
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');
        //Verifica sessão do usuário
        if (!isset($this->session->_usuario_logado) && !isset($this->session->_usuario_logado) > true) {
            Zend_Session::destroy();
            $this->_redirect("/login");
        }
    }
    
    public function getPermissoesPorAutonomos($autonomos_id){
        try{
            $MPermissoes = new Application_Model_DbTable_PermissoesAutonomos();
            $this->session->permissoes = $MPermissoes->getPermissoesPorAutonomosId($autonomos_id);
            ChromePhp::log($this->session->permissoes);
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->session->permissoes = [];
        }
    }
}

