<?php

class RelatoriosController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function preDispatch() {
        // Carrega a sessão
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');
        // Verifica sessão do usuário
        if (!isset($this->session->_profissionais_id)) {
            Zend_Session::destroy();
            $this->_redirect("/login");
        }
    }


    public function indexAction()
    {
        // action body
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Relatorios", "Visualizar");
        
        $this->view->dados = "Relatórios";
        
        $MMenuDoc = new Application_Model_DbTable_Menudoc();
		$menuDoc = $MMenuDoc->getMenus();            
		$this->view->menu = $menuDoc;
		
		$subMenuDoc = $MMenuDoc->getSubMenus();            
		$this->view->menuSub = $subMenuDoc;

		if ( $this->session->_profissionais_cargo == "administrador" || $this->session->_profissionais_tipo == "master" ){	
			$this->view->ver = true ;
		} else {
			$this->view->ver = false ;
		}    
    }

	public function melhorProfAction () {
		// $this->_helper->layout->disableLayout();
		$this->view->params = $this->getRequest()->getParams();

		$chivi = "0";
		$show_und = null ;
/*
		if (isset($this->session->_permissoes)) {
			foreach ($this->session->_permissoes as $item => $value){
				
				if ($value['permissoes_nome'] == "Listar todas unidades") {
					$chivi = "1";
				}
			}
		}

		if ($chivi == "0" && isset($this->session->_permissoes))
			$show_und = $value['fk_unidades_id'];
*/		
        $MUnidades = new Application_Model_DbTable_Unidades();
        $this->view->unidades = $MUnidades->getRegistros(array('*'),$show_und);
	}

	public function melhorProfFuncAction () {
		// $this->_helper->layout->disableLayout();
		$this->view->params = $this->getRequest()->getParams();
		
		$chivi = "0";
		$show_und = null ;
/*
		if (isset($this->session->_permissoes)) {
			foreach ($this->session->_permissoes as $item => $value){
				
				if ($value['permissoes_nome'] == "Listar todas unidades") {
					$chivi = "1";
				}
			}
		}
		
		if ($chivi == "0" && isset($this->session->_permissoes))
			$show_und = $value['fk_unidades_id'];
*/		
        $MUnidades = new Application_Model_DbTable_Unidades();
        $this->view->unidades = $MUnidades->getRegistros(array('*'),$show_und);

		$MFuncoes = new Application_Model_DbTable_Funcoes();
		$this->view->funcoes = $MFuncoes->getRegistros(array('*'),'');            
	}

	public function piorProfAction () {
		// $this->_helper->layout->disableLayout();
		$this->view->params = $this->getRequest()->getParams();
		
		$chivi = "0";
		$show_und = null ;
/*
		if (isset($this->session->_permissoes)) {
			foreach ($this->session->_permissoes as $item => $value){
				
				if ($value['permissoes_nome'] == "Listar todas unidades") {
					$chivi = "1";
				}
			}
		}
		
		if ($chivi == "0" && isset($this->session->_permissoes))
			$show_und = $value['fk_unidades_id'];
*/		
        $MUnidades = new Application_Model_DbTable_Unidades();
        $this->view->unidades = $MUnidades->getRegistros(array('*'),$show_und);
	}

	public function piorProfFuncAction () {
		// $this->_helper->layout->disableLayout();
		$this->view->params = $this->getRequest()->getParams();
		
		$chivi = "0";
		$show_und = null ;
/*
		if (isset($this->session->_permissoes)) {
			foreach ($this->session->_permissoes as $item => $value){
				
				if ($value['permissoes_nome'] == "Listar todas unidades") {
					$chivi = "1";
				}
			}
		}
		
		if ($chivi == "0" && isset($this->session->_permissoes))
			$show_und = $value['fk_unidades_id'];
*/		
        $MUnidades = new Application_Model_DbTable_Unidades();
        $this->view->unidades = $MUnidades->getRegistros(array('*'),$show_und);

		$MFuncoes = new Application_Model_DbTable_Funcoes();
		$this->view->funcoes = $MFuncoes->getRegistros(array('*'),'');            
	}
	
	public function profAvalAction () {
		// $this->_helper->layout->disableLayout();
		
		$avaliacoes['100'] 	= 'Azul - Ótimo, Prabéns, Excelente!' ;
		$avaliacoes['75'] 	= 'Verde - Dever cumprido!' ;
		$avaliacoes['50'] 	= 'Amarelo - Atenção (Corrigir de forma educativa)' ;
		$avaliacoes['25'] 	= 'Laranja - Alerta (Erro cometido mais vezes)' ;
		$avaliacoes['0'] 	= 'Vermelho - Falha Grave!' ;
		
		$this->view->aval = $avaliacoes ;
		
	}

	public function mediaUnidadesAction () {
		// $this->_helper->layout->disableLayout();
			$this->view->params = $this->getRequest()->getParams();
		
	
	}

	public function manualAction () {
		// $this->_helper->layout->disableLayout();
		$this->view->params = $this->getRequest()->getParams();
		
		$MFuncoes = new Application_Model_DbTable_Funcoes();
		$this->view->funcoes = $MFuncoes->getRegistros(array('*'),'');	
	}

    public function imprimirMelhorProfAction() {
		$this->_helper->layout->disableLayout();
        try {
            $this->session = new Zend_Session_Namespace('session_sad_subeauty');
            $dados = $this->getRequest()->getParams();
            $this->view->params = $dados;
            $DbRelatorios = new Application_Model_DbTable_Relatorios();
            $data         = $DbRelatorios->getMelhorProf($dados);
            if ($data) {
                $retorno['dados'] = $data;
            } else {
                $retorno['alerta'] = 'Não existe informações com os Parâmetros informados!';
            }
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno['erro'] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema.';
        }
        $this->view->dados = $retorno;
    }

    public function imprimirMelhorProfFuncAction() {
		$this->_helper->layout->disableLayout();
        try {
            $this->session = new Zend_Session_Namespace('session_sad_subeauty');
            $dados = $this->getRequest()->getParams();
            $this->view->params = $dados;
            $DbRelatorios = new Application_Model_DbTable_Relatorios();
            $data         = $DbRelatorios->getMelhorProfFunc($dados);
            if ($data) {
                $retorno['dados'] = $data;
            } else {
                $retorno['alerta'] = 'Não existe informações com os Parâmetros informados!';
            }
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno['erro'] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema.';
        }
        $this->view->dados = $retorno;
    }

    public function imprimirPiorProfAction() {
		$this->_helper->layout->disableLayout();
        try {
            $this->session = new Zend_Session_Namespace('session_sad_subeauty');
            $dados = $this->getRequest()->getParams();
            $this->view->params = $dados;
            $DbRelatorios = new Application_Model_DbTable_Relatorios();
            $data         = $DbRelatorios->getPiorProf($dados);
            if ($data) {
                $retorno['dados'] = $data;
            } else {
                $retorno['alerta'] = 'Não existe informações com os Parâmetros informados!';
            }
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno['erro'] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema.';
        }
        $this->view->dados = $retorno;
    }

    public function imprimirPiorProfFuncAction() {
		$this->_helper->layout->disableLayout();
        try {
            $this->session = new Zend_Session_Namespace('session_sad_subeauty');
            $dados = $this->getRequest()->getParams();
            $this->view->params = $dados;
            $DbRelatorios = new Application_Model_DbTable_Relatorios();
            $data         = $DbRelatorios->getPiorProfFunc($dados);
            if ($data) {
                $retorno['dados'] = $data;
            } else {
                $retorno['alerta'] = 'Não existe informações com os Parâmetros informados!';
            }
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno['erro'] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema.';
        }
        $this->view->dados = $retorno;
    }
    
    public function imprimirProfAvalAction() {
		$this->_helper->layout->disableLayout();
        try {
            $this->session = new Zend_Session_Namespace('session_sad_subeauty');
            $dados = $this->getRequest()->getParams();
            $this->view->params = $dados;
            $DbRelatorios = new Application_Model_DbTable_Relatorios();
            $data         = $DbRelatorios->getProfAval($dados);
            if ($data) {
                $retorno['dados'] = $data;
            } else {
                $retorno['alerta'] = 'Não existe informações com os Parâmetros informados!';
            }
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno['erro'] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema.';
        }
        $this->view->dados = $retorno;
    }
    
    public function imprimirMediaUnidadesAction() {
		$this->_helper->layout->disableLayout();
        try {
            $this->session = new Zend_Session_Namespace('session_sad_subeauty');
            $dados = $this->getRequest()->getParams();
            $this->view->params = $dados;
            $DbRelatorios = new Application_Model_DbTable_Relatorios();
            $data         = $DbRelatorios->getMediaUnidades($dados);
            if ($data) {
                $retorno['dados'] = $data;
            } else {
                $retorno['alerta'] = 'Não existe informações com os Parâmetros informados!';
            }
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno['erro'] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema.';
        }
        $this->view->dados = $retorno;
    }
    
    public function imprimirManualAction() {
		$this->_helper->layout->disableLayout();
        try {
            $this->session = new Zend_Session_Namespace('session_sad_subeauty');
            $dados = $this->getRequest()->getParams();
            $this->view->params = $dados;
            $DbRelatorios = new Application_Model_DbTable_Relatorios();
            $data         = $DbRelatorios->getManual($dados);
            if ($data) {
                $retorno['dados'] = $data;
            } else {
                $retorno['alerta'] = 'Não existe informações com os Parâmetros informados!';
            }
        } catch (Exception $exc) {
			ChromePhp::log($exc);
            $retorno['erro'] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema.';
        }
        $this->view->dados = $retorno;
    }
    
}

