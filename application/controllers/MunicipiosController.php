<?php

class MunicipiosController extends Zend_Controller_Action
{
    protected $_name = 'municipios';
    protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
    }
    
    protected function initAppModel() {

        /*$accessController = str_replace('-', "", $this->getRequest()->getControllerName());
        $className = "Application_Model_DbTable_" . ucfirst($accessController);

        if (!class_exists($className)):
            $this->view->msg = 'ERROR: App not found (' . $className . ').';
        endif;

        return new $className();*/
    }
    
    public function preDispatch() {
        // Carrega a sessão
        /*$this->session = new Zend_Session_Namespace('subeauty');
        // Verifica sessão do usuário
        if (!isset($this->session->_id)) {
            Zend_Session::destroy();
            $this->_redirect("/");
        }*/
    }
    
    public function getmunicipiosAction(){
        try{
            $this->_helper->layout->disableLayout();
            
            $params = $this->getRequest()->getParams();
            $MMunicipios = new Application_Model_DbTable_Municipios();
            $this->retorno["dados"] = $MMunicipios->getMunicipiosPelaUf($params["uf"]);
            $this->retorno["sucesso"][] = "Cidades carregas com sucesso!";
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = "Desculpe ocorreu uma falha ao recuperar informações do sistema!";
        }
        return $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
}

