<?php

/**
 * @autor Kaio Gomes.
 * Data/Hora alteração: 15/08/2012 - 10:13 am
 * 
 * Descrição da classe CepController: 
 *      - CepController fará a busca no banco(CEP) para detalhar nos campos do endereço.
 * 
 */

class CepController extends Zend_Controller_Action {

    public function init() {
     
    }

    public function preDispatch() {
        // INICIA SESSÃO
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');
        // VERIFICA SE USUARIO ESTÁ LOGADO!
        // if (!isset($this->session->_profissionais_id)) {
        //     Zend_Session::destroy();
        //     $this->_redirect("/");
        // }
    }

    public function indexAction() {
        try {
            // CAPTURA DADOS
            $getParams = $this->getRequest()->getParams();
            $this->view->params = $getParams;
            
        } catch (Exception $exc) {
            Application_Model_Notificacao::erro('Desculpe ocorreu uma falha ao recuperar informações do sistema.');
        }
    }
    
    
    public function buscaAction() {
		$this->_helper->layout->disableLayout();
        try {
            
            $dados = $this->getRequest()->getPost();
            
            $dados_endereco = new Application_Model_DbTable_Cep();
            $dados_endereco = $dados_endereco->getDadosCep($dados['cep']);
            
            if ($dados_endereco) {
                $retorno['ok'] = 'Ok';
                $retorno['dados'] = $dados_endereco[0];
            } else {
                $retorno['alerta'] = 'Dados não encontrados!';
            }
            
        } catch (Exception $exc) {
            $retorno['erro'] = false;
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($retorno);
    }
    
    
    public function buscaCepRuaAction(){
		$this->_helper->layout->disableLayout();
        
         try {
            
            $dados = $this->getRequest()->getParams();
           
            $dados_endereco = new Application_Model_DbTable_Cep();
            $dados_endereco = $dados_endereco->DadosRua($dados['valor']);
             
            if ($dados_endereco) {
                $retorno['ok'] = 'Ok';
                $retorno['dados'] = $dados_endereco;
            } else {
                $retorno['alerta'] = 'Dados não encontrados!';
            }
            
        } catch (Exception $exc) {
            $retorno['erro'] = false;
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($retorno);
    }
    
    
    public function buscaCodIbgeAction() {
		$this->_helper->layout->disableLayout();
        try {
            
            $dados = $this->getRequest()->getPost();
            
            //Instância os UF
            $codigoUf = Application_Model_Vars::codigoUF();
            
            
            $valores['uf']     = $codigoUf[$dados['uf']];
            $valores['cidade'] = strtoupper(Application_Model_Funcoes::trataAcentuacao($dados['cidade']));
            
            
            //Busca o código IBGE da cidade
            $codCityIbge = new Application_Model_DbTable_Codigocityibge();
            $codMunicipioIBGE = $codCityIbge->getCodCityIbge($valores);
            
            
            if ($codMunicipioIBGE) {
                $retorno['ok'] = 'Ok';
                $retorno['codigo'] = $codMunicipioIBGE;
            } else {
                $retorno['alerta'] = 'Dados não encontrados!';
            }
            
        } catch (Exception $exc) {
            $retorno['erro'] = false;
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($retorno);
    }
    
}

