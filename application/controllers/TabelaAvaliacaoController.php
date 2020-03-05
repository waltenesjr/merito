<?php

class TabelaAvaliacaoController extends Zend_Controller_Action
{
    protected $_name = 'tabela_avaliacao';
    protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        $this->_helper->layout->setLayout('layout_aval');
        // $this->_helper->layout->setLayout('layout');
    }
    
    protected function initAppModel() {

//        $accessController = str_replace('-', "", $this->getRequest()->getControllerName());
//        $className = "Application_Model_DbTable_" . ucfirst($accessController);
//
//        if (!class_exists($className)):
//            $this->view->msg = 'ERROR: App not found (' . $className . ').';
//        endif;
//
//        return new $className();
        
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
		
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tabela de Avaliação", "Visualizar");
        
        // action body
        $this->view->modulo = "Tabela de Avaliação";
        
        //Carregar todas as unidades
        $MUnidades = new Application_Model_DbTable_Unidades();
        $this->view->unidades = $MUnidades->getRegistros();

		if ( $this->view->permissao['profissionais_cargo'] == "gerente" || $this->view->permissao['profissionais_cargo'] == "supervisor" ) {
			$this->view->onlyUnidade = $this->view->permissao['fk_unidades_id'] ;
		}

		if ( $this->session->_profissionais_cargo == "administrador" || $this->session->_profissionais_tipo == "master" ){	
			$this->view->ver = true ;
		} else {
			$this->view->ver = false ;
		}
		
		$parametros = $this->getRequest()->getParams();
		if (isset($parametros['fk_profissionais_id']))
			$this->view->fk_profissionais_id = $parametros['fk_profissionais_id'];
		
    }
    
    public function pesquisarAction(){
		
		$this->_helper->layout->disableLayout();

        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);
            
            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoes();
            $tabela_avaliacoes = $MTabelaAvaliacoes->getRegistrosFiltradosGrid($parametros);
            
            //$this->retorno["timestamp"] = (string) new Zend_Date();
            $this->retorno["timestamp"] = date("d-m-Y");
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes) && !empty($tabela_avaliacoes) && sizeof($tabela_avaliacoes) > 0){
                foreach($tabela_avaliacoes as $item){
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Dados carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function mostrarDiaAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);

            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoes();
            $tabela_avaliacoes = $MTabelaAvaliacoes->getRegistrosFiltradosGrid($parametros);
            
            $this->retorno["timestamp"] = (string) new Zend_Date();
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes) && !empty($tabela_avaliacoes) && sizeof($tabela_avaliacoes) > 0){
                foreach($tabela_avaliacoes as $item){
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Dados carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
	
	$this->view->data = $parametros['tabela_avaliacoes_timestamp'];
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }    

    public function setAtribuicaoAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            $MTabelaAvaliacao = new Application_Model_DbTable_TabelaAvaliacoes();

            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_id = $MTabelaAvaliacao->getId($parametros);
            
            $inserirHistorico = true;
            if(isset($tabela_avaliacao_id) && $tabela_avaliacao_id > 0){
                // --- Atualizar
                $dados = array(
                    "tabela_avaliacoes_id"=>$tabela_avaliacao_id,
                    "fk_profissionais_id"=>$parametros['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_status"=>'Ativo',
                    "fk_usuarios_data_operacao"=>'now()'
                );

                if($MTabelaAvaliacao->atualizar($dados)){
                    $this->retorno['sucesso'][] = "Operação realizada com sucesso.";
                }else{
                    $this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
                    $inserirHistorico = false;
                }
            }else{
                // --- Inserir
                $dados = array(
                    "fk_profissionais_id"=>$parametros['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_status"=>'Ativo',
                    "tabela_avaliacoes_timestamp"=>'now()',
                    "fk_usuarios_data_operacao"=>'now()'
                );

                if($MTabelaAvaliacao->inserir($dados)){
                    $this->retorno['sucesso'][] = "Operação realizada com sucesso.";
                }else{
                    $this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
                    $inserirHistorico = false;
                }
            }
            
            if($inserirHistorico){
                $d = array(
                    "fk_profissionais_id"=>$dados['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$dados['fk_atribuicoes_id'],
                    "tabela_avaliacoes_historico_nota"=>$dados['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_historico_status"=>'Ativo',
                    "tabela_avaliacoes_historico_timestamp"=>'now()',
                    "fk_usuarios_data_operacao"=>'now()'
                );
                $MTabelaAvaliacaoHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistorico();
                $MTabelaAvaliacaoHistorico->inserir($d);
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function setAtribuicaoDiaAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            $MTabelaAvaliacao = new Application_Model_DbTable_TabelaAvaliacoes();

            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_id = $MTabelaAvaliacao->getId($parametros);
            
            $inserirHistorico = true;
            if(isset($tabela_avaliacao_id) && $tabela_avaliacao_id > 0){
                // --- Atualizar
                $dados = array(
                    "tabela_avaliacoes_id"=>$tabela_avaliacao_id,
                    "fk_profissionais_id"=>$parametros['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_status"=>'Ativo',
                    "fk_usuarios_data_operacao"=>$parametros['tabela_avaliacoes_timestamp']
                );

                if($MTabelaAvaliacao->atualizar($dados)){
                    $this->retorno['sucesso'][] = "Operação realizada com sucesso.";
                }else{
                    $this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
                    $inserirHistorico = false;
                }
            }else{
                // --- Inserir
                $dados = array(
                    "fk_profissionais_id"=>$parametros['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_status"=>'Ativo',
                    "tabela_avaliacoes_timestamp"=>$parametros['tabela_avaliacoes_timestamp'],
                    "fk_usuarios_data_operacao"=>$parametros['tabela_avaliacoes_timestamp']
                );

                if($MTabelaAvaliacao->inserir($dados)){
                    $this->retorno['sucesso'][] = "Operação realizada com sucesso.";
                }else{
                    $this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
                    $inserirHistorico = false;
                }
            }
            
            if($inserirHistorico){
                $d = array(
                    "fk_profissionais_id"=>$dados['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$dados['fk_atribuicoes_id'],
                    "tabela_avaliacoes_historico_nota"=>$dados['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_historico_status"=>'Ativo',
                    "tabela_avaliacoes_historico_timestamp"=>$parametros['tabela_avaliacoes_timestamp'],
                    "fk_usuarios_data_operacao"=>$parametros['tabela_avaliacoes_timestamp']
                );
                $MTabelaAvaliacaoHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistorico();
                $MTabelaAvaliacaoHistorico->inserir($d);
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    
    public function getUltimosInseridosAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);
            
            $MTabelaAvaliacoesHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistorico();
            $tabela_avaliacoes_historico = $MTabelaAvaliacoesHistorico->getUltimosInseridos($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_historico) && !empty($tabela_avaliacoes_historico) && sizeof($tabela_avaliacoes_historico) > 0){
                foreach($tabela_avaliacoes_historico as $item){
                    $this->retorno["dados"][] = $item;
                }
            }else{
                $this->retorno["alerta"][] = "Não foi possível carregar as últimas atribuições.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    
    public function listarHistoricoAtribuicoesAction(){
		$this->_helper->layout->disableLayout();    
        
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();

            $MTabelaAvaliacoesHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistorico();
            $tabela_avaliacoes_historico = $MTabelaAvaliacoesHistorico->getRegistrosFiltradosGrid($parametros);
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_historico) && !empty($tabela_avaliacoes_historico) && sizeof($tabela_avaliacoes_historico) > 0){
                foreach($tabela_avaliacoes_historico as $item){
                    $item['tabela_avaliacoes_historico_nota'] = $this->_helper->Validacoes->getClassRadioAtribuicoes($item['tabela_avaliacoes_historico_nota']) . "_" . $item['tabela_avaliacoes_historico_id'];
                    $this->retorno["dados"][] = $item;
                }
            }else{
                $this->retorno["alerta"][] = "Não foi possível carregar as últimas atribuições.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }

        $this->view->dados = $this->retorno;        
        
    }
    /*
    public function getRegistrosAtribuicaoAction(){
		
		$this->_helper->layout->disableLayout();


        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();

            $MTabelaAvaliacoesHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistorico();
            $tabela_avaliacoes_historico = $MTabelaAvaliacoesHistorico->getRegistrosFiltradosGrid($parametros);
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_historico) && !empty($tabela_avaliacoes_historico) && sizeof($tabela_avaliacoes_historico) > 0){
                foreach($tabela_avaliacoes_historico as $item){
                    $item['tabela_avaliacoes_historico_nota'] = $this->_helper->Validacoes->getClassRadioAtribuicoes($item['tabela_avaliacoes_historico_nota']) . "_" . $item['tabela_avaliacoes_historico_id'];
                    $this->retorno["dados"][] = $item;
                }
            }else{
                $this->retorno["alerta"][] = "Não foi possível carregar as últimas atribuições.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    */
    
    /**
     * **********************************************
     *              TABELA DE AVALIAÇÕES
     * **********************************************
     */
    public function justificativasAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tabela de Avaliação", "Justificativas");
        // Pega os dados enviados pelo formulário
        $this->view->fk_tabela_avaliacoes_id = $this->getRequest()->getParam('fk_tabela_avaliacoes_id');
		$this->view->nota_avaliacao = $this->getRequest()->getParam('nota');
		$this->view->fk_atribuicoes_id = $this->getRequest()->getParam('fk_atribuicoes_id');
		$this->view->fk_profissionais_id = $this->getRequest()->getParam('fk_profissionais_id');
		$this->view->tabela_avaliacoes_timestamp = $this->getRequest()->getParam('tabela_avaliacoes_timestamp');
        
        
        //Pega os dados para mostrar no formulário se existir
        $MTabelaAvaliacoesJustificativas = new Application_Model_DbTable_TabelaAvaliacoesJustificativas();
        $this->view->dados = $MTabelaAvaliacoesJustificativas->getDados($this->getRequest()->getParam('fk_tabela_avaliacoes_id') );
        
        ChromePhp::log($this->view->dados);
        
    }
    public function setJustificativasAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            $MTabelaAvaliacaoJustificativas = new Application_Model_DbTable_TabelaAvaliacoesJustificativas();
            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_justificativas_id = $MTabelaAvaliacaoJustificativas->getId($parametros['dadosForm']);
            
            $inserirJustificativas = true;
            if(isset($tabela_avaliacao_justificativas_id) && $tabela_avaliacao_justificativas_id > 0){
                // --- Atualizar
                $dados = array(
                    "id"=>$tabela_avaliacao_justificativas_id,
                    "porque"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas_porque'],
                    "descricao"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas'],
                    "fk_tabela_avaliacoes_id"=>$parametros['dadosForm']['fk_tabela_avaliacoes_id'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "data_cad"=>'now()'
                );
                
                if($MTabelaAvaliacaoJustificativas->atualizar($dados)){
                    $this->retorno['sucesso'][] = "Operação realizada com sucesso.";
                }else{
                    $this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
                    $inserirJustificativas = false;
                }
            }else{
                // --- Inserir
                $dados = array(
                    "porque"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas_porque'],
                    "descricao"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas'],
                    "fk_tabela_avaliacoes_id"=>$parametros['dadosForm']['fk_tabela_avaliacoes_id'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "data_cad"=>'now()'
                );
                
                if($MTabelaAvaliacaoJustificativas->inserir($dados)){
                    $this->retorno['sucesso'][] = "Operação realizada com sucesso.";
                }else{
                    $this->retorno['erro'][] = "Não foi possível realizar a solicitação, verifique se já clicou na cor da pontuação.";
                    $inserirJustificativas = false;
                }
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function setJustiavalAction() {
		
		$this->_helper->layout->disableLayout();

	$var = true ;
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            $MTabelaAvaliacao = new Application_Model_DbTable_TabelaAvaliacoes();

            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_id = $MTabelaAvaliacao->getId($parametros['dadosForm']);
            $paramForm = $parametros['dadosForm'] ;
            $inserirHistorico = true;
            if(isset($tabela_avaliacao_id) && $tabela_avaliacao_id > 0){
                // --- Atualizar
                $dados = array(
                    "tabela_avaliacoes_id"=>$tabela_avaliacao_id,
                    "fk_profissionais_id"=>$paramForm['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$paramForm['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$paramForm['nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_status"=>'Ativo',
                    "fk_usuarios_data_operacao"=>'now()'
                );

                if($MTabelaAvaliacao->atualizar($dados)){
                    //$this->retorno['sucesso'][] = "Operação realizada com sucesso 1.";
                }else{
                    //$this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
                    $inserirHistorico = false;
		    $var = false;
                }
            }else{
                // --- Inserir
                $dados = array(
                    "fk_profissionais_id"=>$paramForm['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$paramForm['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$paramForm['nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_status"=>'Ativo',
                    "tabela_avaliacoes_timestamp"=>$parametros['dadosForm']['tabela_avaliacoes_timestamp'],
                    "fk_usuarios_data_operacao"=>'now()'
                );

                if($MTabelaAvaliacao->inserir($dados)){
                    //$this->retorno['sucesso'][] = "Operação realizada com sucesso 2.";
                }else{
                   // $this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
                    $inserirHistorico = false;
		    $var = false;
                }
            }
            
            if($inserirHistorico){
                $d = array(
                    "fk_profissionais_id"=>$dados['fk_profissionais_id'],
                    "fk_atribuicoes_id"=>$dados['fk_atribuicoes_id'],
                    "tabela_avaliacoes_historico_nota"=>$dados['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
                    "tabela_avaliacoes_historico_status"=>'Ativo',
                    "tabela_avaliacoes_historico_timestamp"=>$parametros['dadosForm']['tabela_avaliacoes_timestamp'],
                    "fk_usuarios_data_operacao"=>'now()'
                );
                $MTabelaAvaliacaoHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistorico();
                $MTabelaAvaliacaoHistorico->inserir($d);
            }
	if($inserirHistorico){
		$paramForm['qtde'] = "1";
		$ultima_avaliacao = $MTabelaAvaliacao->getUltimaAvaliacao($paramForm);

	            $MTabelaAvaliacaoJustificativas = new Application_Model_DbTable_TabelaAvaliacoesJustificativas();
	            //Verifico se já existe registro atribuido na data informada
	            $tabela_avaliacao_justificativas_id = $MTabelaAvaliacaoJustificativas->getId($parametros['dadosForm']);
	            
	            $inserirJustificativas = true;
	            if(isset($tabela_avaliacao_justificativas_id) && $tabela_avaliacao_justificativas_id > 0){
	                // --- Atualizar
	                $dados = array(
	                    "id"=>$tabela_avaliacao_justificativas_id,
	                    "porque"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas_porque'],
	                    "descricao"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas'],
	                    "fk_tabela_avaliacoes_id"=>$ultima_avaliacao[0]['tabela_avaliacoes_id'],
	                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
	                    "data_cad"=>$parametros['dadosForm']['tabela_avaliacoes_timestamp']
	                );
                
	                if($MTabelaAvaliacaoJustificativas->atualizar($dados)){
	                    //$this->retorno['sucesso'][] = "Operação realizada com sucesso 3.";
	                }else{
	                    //$this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
	                    $var = false;
	                }
	          }else{
	                // --- Inserir
	                $dados = array(
	                    "porque"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas_porque'],
	                    "descricao"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas'],
	                    "fk_tabela_avaliacoes_id"=>$ultima_avaliacao[0]['tabela_avaliacoes_id'],
	                    "fk_usuarios_id_cad"=>$this->session->_profissionais_id,
	                    "data_cad"=>$parametros['dadosForm']['tabela_avaliacoes_timestamp']
	                );
                
	                if($MTabelaAvaliacaoJustificativas->inserir($dados)){
	                    //$this->retorno['sucesso'][] = "Operação realizada com sucesso 4.";
	                }else{
	                    //$this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
	                    $var = false;
	                }
	         }
	   }


	   if ($var == true)
              $this->retorno['sucesso'][] = "Operação realizada com sucesso.";
	   else
		$this->retorno['erro'][] = "Não foi possível realizar a solicitação.";
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }

 
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);

    }
    
    public function pesquisarMensalAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{

            $parametros = $this->getRequest()->getParams();
//            $mes = substr($parametros,0,7);
            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoes();

            $tabela_avaliacoes = $MTabelaAvaliacoes->getMensalAvaliacao($parametros);

//            //unset($parametros["funcoes_status_input"]);
//            
//            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoes();
//            $tabela_avaliacoes = $MTabelaAvaliacoes->getRegistrosFiltradosGrid($parametros);
//            
//            $this->retorno["timestamp"] = (string) new Zend_Date();
//            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes) && !empty($tabela_avaliacoes) && sizeof($tabela_avaliacoes) > 0){
				//$this->retorno["success"] = "1" ;

                foreach($tabela_avaliacoes as $item){
                    //$this->retorno["dados"][] = $item;
                    
                    if ($item['min_nota'] == "0")
						$class = "mensal_vermelho" ;
                    else if ($item['min_nota'] == "25")
						$class = "mensal_laranja" ;
                    else if ($item['min_nota'] == "50")
						$class = "mensal_amarelho" ;
                    else if ($item['min_nota'] == "75")
						$class = "mensal_verde" ;
                    else if ($item['min_nota'] == "100")
						$class = "mensal_azul" ;
                    
                    $out[] = array(
						'id' 	=> $item['dia'],
						'min' => $item['min_nota'],
						'max' 	=> $item['max_nota'],
						'start' => strtotime($item['fecha']) . '000',
						'class' => $class
					);

                    
//                    $this->retorno["result"][]['id'] = $item['dia'];
//                    $this->retorno["result"][]['min'] = $item['min_nota'];
//                    $this->retorno["result"][]['max'] = $item['max_nota'];
//                    $this->retorno["result"][]['fecha'] = strtotime($item['fecha']) . '000';
                    
//                    $this->retorno["result"][$item['dia']]['min'] = $item['min_nota'];
//                    $this->retorno["result"][$item['dia']]['max'] = $item['max_nota'];
//                    $this->retorno["result"][$item['dia']]['fecha'] = strtotime($item['fecha']) . '000';
                    
                }
                $this->retorno["sucesso"][] = "Dados mensais carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        
        //$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        $this->view->dados = json_encode(array('success' => 1, 'result' => $out));
    } 

    public function pesquisarTrimestralAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            $parametros = $this->getRequest()->getParams();
			$parametros['meses'] = "3" ;
            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoes();
            $tabela_avaliacoes = $MTabelaAvaliacoes->getTrimSemMensalAvaliacao($parametros);
            if(isset($tabela_avaliacoes) && !empty($tabela_avaliacoes) && sizeof($tabela_avaliacoes) > 0){
                foreach($tabela_avaliacoes as $item){
					if ($item['mes'] == $parametros['mes'] ){

						if ($item['min_nota'] == "0")
							$class = "mensal_vermelho" ;
						else if ($item['min_nota'] == "25")
							$class = "mensal_laranja" ;
						else if ($item['min_nota'] == "50")
							$class = "mensal_amarelho" ;
						else if ($item['min_nota'] == "75")
							$class = "mensal_verde" ;
						else if ($item['min_nota'] == "100")
							$class = "mensal_azul" ;
						
						$out[] = array(
							'id' 	=> $item['dia'],
							'min' => $item['min_nota'],
							'max' 	=> $item['max_nota'],
							'start' => strtotime($item['fecha']) . '000',
							'class' => $class
						);						
						
						$this->retorno["dados"][$item['dia']]['min'] = $item['min_nota'];
						$this->retorno["dados"][$item['dia']]['max'] = $item['max_nota'];
						//$this->retorno["dados"][$item['mes']][$item['dia']]['min'] = $item['min_nota'];
						//$this->retorno["dados"][$item['mes']][$item['dia']]['max'] = $item['max_nota'];
						
						
                    }
                }
                $this->retorno["sucesso"][] = "Dados trimensais carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        $this->view->dados = json_encode(array('success' => 1, 'result' => $out));
        //$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    } 

    public function pesquisarSemestralAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            $parametros = $this->getRequest()->getParams();
	    $parametros['meses'] = "6" ;
            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoes();
            $tabela_avaliacoes = $MTabelaAvaliacoes->getTrimSemMensalAvaliacao($parametros);
            if(isset($tabela_avaliacoes) && !empty($tabela_avaliacoes) && sizeof($tabela_avaliacoes) > 0){
                foreach($tabela_avaliacoes as $item){
					if ($item['mes'] == $parametros['mes'] ){

						if ($item['min_nota'] == "0")
							$class = "mensal_vermelho" ;
						else if ($item['min_nota'] == "25")
							$class = "mensal_laranja" ;
						else if ($item['min_nota'] == "50")
							$class = "mensal_amarelho" ;
						else if ($item['min_nota'] == "75")
							$class = "mensal_verde" ;
						else if ($item['min_nota'] == "100")
							$class = "mensal_azul" ;
						
						$out[] = array(
							'id' 	=> $item['dia'],
							'min' => $item['min_nota'],
							'max' 	=> $item['max_nota'],
							'start' => strtotime($item['fecha']) . '000',
							'class' => $class
						);						
						
						$this->retorno["dados"][$item['dia']]['min'] = $item['min_nota'];
						$this->retorno["dados"][$item['dia']]['max'] = $item['max_nota'];
						//$this->retorno["dados"][$item['mes']][$item['dia']]['min'] = $item['min_nota'];
						//$this->retorno["dados"][$item['mes']][$item['dia']]['max'] = $item['max_nota'];
						
						
                    }
                }
                $this->retorno["sucesso"][] = "Dados trimensais carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        $this->view->dados = json_encode(array('success' => 1, 'result' => $out));
        //$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    } 
}
