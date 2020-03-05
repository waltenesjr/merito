<?php

class TabelaAvaliacaoAutonomosController extends Zend_Controller_Action
{
    //protected $_name = 'tabela_avaliacao_autonomos';
    //protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
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
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tabela de Avaliação Autonomos", "Visualizar");
        // action body
        $this->view->modulo = "Tabela de Avaliação Autonomos";
        
        //Carregar todas as unidades
        $MUnidades = new Application_Model_DbTable_Unidades();
        $this->view->unidades = $MUnidades->getRegistros();
        
    }
    
    public function pesquisarAction(){

        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);
            
            $MTabelaAvaliacoes_Autonomos = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();
            $tabela_avaliacoes_autonomos = $MTabelaAvaliacoes_Autonomos->getRegistrosFiltradosGrid($parametros);
            
            $this->retorno["timestamp"] = (string) new Zend_Date();
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_autonomos) && !empty($tabela_avaliacoes_autonomos) && sizeof($tabela_avaliacoes_autonomos) > 0){
                foreach($tabela_avaliacoes_autonomos as $item){
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
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);
;
            $MTabelaAvaliacoes_Autonomos = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();
            $tabela_avaliacoes_autonomos = $MTabelaAvaliacoes_Autonomos->getRegistrosFiltradosGrid($parametros);
            
            $this->retorno["timestamp"] = (string) new Zend_Date();
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_autonomos) && !empty($tabela_avaliacoes_autonomos) && sizeof($tabela_avaliacoes_autonomos) > 0){
                foreach($tabela_avaliacoes_autonomos as $item){
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
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            ChromePhp::log($parametros);
            
            $MTabelaAvaliacao = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();

            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_autonomos_id = $MTabelaAvaliacao->getId($parametros);
            
            $inserirHistorico = true;
            if(isset($tabela_avaliacao_autonomos_id) && $tabela_avaliacao_autonomos_id > 0){
                // --- Atualizar
                $dados = array(
                    "tabela_avaliacoes_autonomos_id"=>$tabela_avaliacao_autonomos_id,
                    "fk_autonomos_id"=>$parametros['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
                    "fk_autonomos_id"=>$parametros['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
                    "fk_autonomos_id"=>$dados['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$dados['fk_atribuicoes_id'],
                    "tah_nota"=>$dados['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
                    "tah_status"=>'Ativo',
                    "tah_timestamp"=>'now()',
                    "fk_usuarios_data_operacao"=>'now()'
                );
                $MTabelaAvaliacaoHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistoricoAutonomos();
                $MTabelaAvaliacaoHistorico->inserir($d);
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function setAtribuicaoDiaAction(){
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            $MTabelaAvaliacao = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();

            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_autonomos_id = $MTabelaAvaliacao->getId($parametros);
            
            $inserirHistorico = true;
            if(isset($tabela_avaliacao_autonomos_id) && $tabela_avaliacao_autonomos_id > 0){
                // --- Atualizar
                $dados = array(
                    "tabela_avaliacoes_autonomos_id"=>$tabela_avaliacao_autonomos_id,
                    "fk_autonomos_id"=>$parametros['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
                    "fk_autonomos_id"=>$parametros['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$parametros['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$parametros['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
                    "fk_autonomos_id"=>$dados['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$dados['fk_atribuicoes_id'],
                    "tah_nota"=>$dados['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
                    "tah_status"=>'Ativo',
                    "tah_timestamp"=>$parametros['tabela_avaliacoes_timestamp'],
                    "fk_usuarios_data_operacao"=>$parametros['tabela_avaliacoes_timestamp']
                );
                $MTabelaAvaliacaoHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistoricoAutonomos();
                $MTabelaAvaliacaoHistorico->inserir($d);
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    
    public function getUltimosInseridosAction(){
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);
            
            $MTabelaAvaliacoesHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistoricoAutonomos();
            $tabela_avaliacoes_autonomos_historico = $MTabelaAvaliacoesHistorico->getUltimosInseridos($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_autonomos_historico) && !empty($tabela_avaliacoes_autonomos_historico) && sizeof($tabela_avaliacoes_autonomos_historico) > 0){
                foreach($tabela_avaliacoes_autonomos_historico as $item){
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
        //$this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tabela de Avaliação Histórico", "Visualizar");    
    }
    public function getRegistrosAtribuicaoAction(){


        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();

            $MTabelaAvaliacoesHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistoricoAutonomos();
            $tabela_avaliacoes_autonomos_historico = $MTabelaAvaliacoesHistorico->getRegistrosFiltradosGrid($parametros);
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_autonomos_historico) && !empty($tabela_avaliacoes_autonomos_historico) && sizeof($tabela_avaliacoes_autonomos_historico) > 0){
                foreach($tabela_avaliacoes_autonomos_historico as $item){
                    $item['tah_nota'] = $this->_helper->Validacoes->getClassRadioAtribuicoes($item['tah_nota']) . "_" . $item['tah_id'];
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
    
    
    /**
     * **********************************************
     *              TABELA DE AVALIAÇÕES
     * **********************************************
     */
    public function justificativasAction(){
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tabela de Avaliação", "Justificativas");
        // Pega os dados enviados pelo formulário
        
        //$parametros = $this->getRequest()->getParams();
        //ChromePhp::log($parametros);
        
        $this->view->fk_tabela_avaliacoes_id = $this->getRequest()->getParam('fk_tabela_avaliacoes_id');
		$this->view->nota_avaliacao = $this->getRequest()->getParam('nota');
		$this->view->fk_atribuicoes_id = $this->getRequest()->getParam('fk_atribuicoes_id');
		$this->view->fk_autonomos_id = $this->getRequest()->getParam('fk_autonomos_id');
		$this->view->tabela_avaliacoes_timestamp = $this->getRequest()->getParam('tabela_avaliacoes_timestamp');
        
        
        //Pega os dados para mostrar no formulário se existir
        $MTabelaAvaliacoesJustificativas = new Application_Model_DbTable_TabelaAvaliacoesJustificativasAutonomos();
        $this->view->dados = $MTabelaAvaliacoesJustificativas->getDados($this->getRequest()->getParam('fk_tabela_avaliacoes_id'));
    }
    public function setJustificativasAction(){
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            ChromePhp::log($parametros);
            
            
            $MTabelaAvaliacaoJustificativas = new Application_Model_DbTable_TabelaAvaliacoesJustificativasAutonomos();
            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_autonomos_justificativas_id = $MTabelaAvaliacaoJustificativas->getId($parametros['dadosForm']);
            
            $inserirJustificativas = true;
            if(isset($tabela_avaliacao_autonomos_justificativas_id) && $tabela_avaliacao_autonomos_justificativas_id > 0){
                // --- Atualizar
                $dados = array(
                    "id"=>$tabela_avaliacao_autonomos_justificativas_id,
                    "porque"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas_porque'],
                    "descricao"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas'],
                    "fk_tabela_avaliacoes_id_autonomos"=>$parametros['dadosForm']['fk_tabela_avaliacoes_id'],
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
                    "fk_tabela_avaliacoes_id_autonomos"=>$parametros['dadosForm']['fk_tabela_avaliacoes_id_autonomos'],
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

	$var = true ;
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            $MTabelaAvaliacao = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();

            //Verifico se já existe registro atribuido na data informada
            $tabela_avaliacao_autonomos_id = $MTabelaAvaliacao->getId($parametros['dadosForm']);
            $paramForm = $parametros['dadosForm'] ;
            $inserirHistorico = true;
            if(isset($tabela_avaliacao_autonomos_id) && $tabela_avaliacao_autonomos_id > 0){
                // --- Atualizar
                $dados = array(
                    "tabela_avaliacoes_autonomos_id"=>$tabela_avaliacao_autonomos_id,
                    "fk_autonomos_id"=>$paramForm['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$paramForm['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$paramForm['nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
                    "fk_autonomos_id"=>$paramForm['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$paramForm['fk_atribuicoes_id'],
                    "tabela_avaliacoes_nota"=>$paramForm['nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
                    "fk_autonomos_id"=>$dados['fk_autonomos_id'],
                    "fk_atribuicoes_id"=>$dados['fk_atribuicoes_id'],
                    "tah_nota"=>$dados['tabela_avaliacoes_nota'],
                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
                    "tah_status"=>'Ativo',
                    "tah_timestamp"=>$parametros['dadosForm']['tabela_avaliacoes_timestamp'],
                    "fk_usuarios_data_operacao"=>'now()'
                );
                $MTabelaAvaliacaoHistorico = new Application_Model_DbTable_TabelaAvaliacoesHistoricoAutonomos();
                $MTabelaAvaliacaoHistorico->inserir($d);
            }
	if($inserirHistorico){
		$paramForm['qtde'] = "1";
		$ultima_avaliacao = $MTabelaAvaliacao->getUltimaAvaliacao($paramForm);

	            $MTabelaAvaliacaoJustificativas = new Application_Model_DbTable_TabelaAvaliacoesJustificativasAutonomos();
	            //Verifico se já existe registro atribuido na data informada
	            $tabela_avaliacao_autonomos_justificativas_id = $MTabelaAvaliacaoJustificativas->getId($parametros['dadosForm']);
	            
	            $inserirJustificativas = true;
	            if(isset($tabela_avaliacao_autonomos_justificativas_id) && $tabela_avaliacao_autonomos_justificativas_id > 0){
	                // --- Atualizar
	                $dados = array(
	                    "id"=>$tabela_avaliacao_autonomos_justificativas_id,
	                    "porque"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas_porque'],
	                    "descricao"=>$parametros['dadosForm']['tabela_avaliacoes_justificativas'],
	                    "fk_tabela_avaliacoes_id_autonomos"=>$ultima_avaliacao[0]['tabela_avaliacoes_autonomos_id'],
	                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
	                    "fk_tabela_avaliacoes_id_autonomos"=>$ultima_avaliacao[0]['tabela_avaliacoes_autonomos_id'],
	                    "fk_usuarios_id_cad"=>$this->session->_autonomos_id,
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
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
//            $mes = substr($parametros,0,7);
            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();
//$dados['fk_autonomos_id']
            $tabela_avaliacoes_autonomos = $MTabelaAvaliacoes->getMensalAvaliacao($parametros);
//            //unset($parametros["funcoes_status_input"]);
//            
//            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();
//            $tabela_avaliacoes_autonomos = $MTabelaAvaliacoes->getRegistrosFiltradosGrid($parametros);
//            
//            $this->retorno["timestamp"] = (string) new Zend_Date();
//            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tabela_avaliacoes_autonomos) && !empty($tabela_avaliacoes_autonomos) && sizeof($tabela_avaliacoes_autonomos) > 0){
                foreach($tabela_avaliacoes_autonomos as $item){
                    //$this->retorno["dados"][] = $item;
                    $this->retorno["dados"][$item['dia']]['min'] = $item['min_nota'];
                    $this->retorno["dados"][$item['dia']]['max'] = $item['max_nota'];
                }
                $this->retorno["sucesso"][] = "Dados mensais carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
        
        ChromePhp::log($this->retorno["dados"]);
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    } 

    public function pesquisarTrimestralAction(){
        $this->retorno = array();
        try{
            $parametros = $this->getRequest()->getParams();
	    $parametros['meses'] = "3" ;
            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();
            $tabela_avaliacoes_autonomos = $MTabelaAvaliacoes->getTrimSemMensalAvaliacao($parametros);
            if(isset($tabela_avaliacoes_autonomos) && !empty($tabela_avaliacoes_autonomos) && sizeof($tabela_avaliacoes_autonomos) > 0){
                foreach($tabela_avaliacoes_autonomos as $item){
                    $this->retorno["dados"][$item['mes']][$item['dia']]['min'] = $item['min_nota'];
                    $this->retorno["dados"][$item['mes']][$item['dia']]['max'] = $item['max_nota'];
                }
                $this->retorno["sucesso"][] = "Dados trimensais carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    } 

    public function pesquisarSemestralAction(){
        $this->retorno = array();
        try{
            $parametros = $this->getRequest()->getParams();
	    $parametros['meses'] = "6" ;
            $MTabelaAvaliacoes = new Application_Model_DbTable_TabelaAvaliacoesAutonomos();
            $tabela_avaliacoes_autonomos = $MTabelaAvaliacoes->getTrimSemMensalAvaliacao($parametros);
            if(isset($tabela_avaliacoes_autonomos) && !empty($tabela_avaliacoes_autonomos) && sizeof($tabela_avaliacoes_autonomos) > 0){
                foreach($tabela_avaliacoes_autonomos as $item){
                   $this->retorno["dados"][$item['mes']][$item['dia']]['min'] = $item['min_nota'];
                   $this->retorno["dados"][$item['mes']][$item['dia']]['max'] = $item['max_nota'];
                }
                $this->retorno["sucesso"][] = "Dados trimensais carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "A função selecionada não possui atribuições vinculadas.";
            }
            
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    } 
}
