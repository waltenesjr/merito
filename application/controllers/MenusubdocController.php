<?php

class MenusubdocController extends Zend_Controller_Action
{
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
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');

    }


    public function excluirAction(){
        $this->retorno = array();

		try {

			$MMenu = new Application_Model_DbTable_Menusubdoc();
			$dados = $this->getRequest()->getPost();

               
			$menu = $MMenu->excluir($dados);
			if($menu)
				$this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
			else
				$this->retorno['erro'][] = 'Não foi possível excluir o registro!';
		}catch (Exception $exc) {
			ChromePhp::log($exc);
			$this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
		}

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function excluirIdAction(){
        $this->retorno = array();

		try {

			$MMenu = new Application_Model_DbTable_Menusubdoc();
			$dados = $this->getRequest()->getPost();

			$MSMenu = new Application_Model_DbTable_Menusubdoc();
			$subMenus = $MSMenu->getRegistros($dados);
			
			ChromePhp::log("excluirIdAction");
			ChromePhp::log($subMenus);
			
			$path = $_SERVER['DOCUMENT_ROOT'].$subMenus[0]['menusub_url'];
			
			if (file_exists($path)) {
				unlink($path);
			}

			$end = strrpos($path, "/") ;
			
			$folder = substr($path, 0, $end);
			
			ChromePhp::log("path: " . $path . " - Folder: " . $folder);
			
			rmdir ($folder);

			$menu = $MMenu->excluirXid($dados);
			if($menu) {
				$this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
				

			}else
				$this->retorno['erro'][] = 'Não foi possível excluir o registro!';
		}catch (Exception $exc) {
			ChromePhp::log($exc);
			$this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
		}

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

}

