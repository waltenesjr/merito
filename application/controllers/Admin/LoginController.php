<?php

/**
 * @autor Kaio Gomes.
 * Data/Hora alteração: 15/08/2012 - 10:13 am
 * 
 * Descrição da classe CepController: 
 *      - CepController fará a busca no banco(CEP) para detalhar nos campos do endereço.
 * 
 */

class Admin_LoginController extends Zend_Controller_Action {

    public function init() {
     
    }

    public function preDispatch() {
        // INICIA SESSÃO
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');
        // VERIFICA SE USUARIO ESTÁ LOGADO!
    }

    public function indexAction() {
        if (isset($this->session->_admin_id)) {
            $this->_redirect("/admin_usuarios");
        }

        try {
          $this->_helper->layout->setLayout('layout_login_admin');

            // CAPTURA DADOS
            $getParams = $this->getRequest()->getParams();
            $this->view->params = $getParams;
            
        } catch (Exception $exc) {
            Application_Model_Notificacao::erro('Desculpe ocorreu uma falha ao recuperar informações do sistema.');
        }
    }

    public function authAction(){
        
        $this->_helper->layout->disableLayout();
        
        try{
            
            $dados = $this->getRequest()->getPost();
            if(isset($dados)){
                
                if(!isset($dados['dados']['usuario']) || $dados['dados']['usuario'] == "")
                    $this->retorno["erro"][] = "Informe o usuário para continuar.";
                elseif(!isset($dados['dados']['senha']) || $dados['dados']['senha'] == "")
                    $this->retorno["erro"][] = "Informe a senha para continuar.";
                else{
                    
                    //Primeiro verifica os admin, depis o usuário master
                    $MProfissional = new Application_Model_DbTable_Admin_Usuarios();
                    $dProfissional = $MProfissional->validarLogin($dados['dados']['usuario'], $dados['dados']['senha']);
                    
                    if(isset($dProfissional) && !empty($dProfissional[0]["admin_id"]) && $dProfissional[0]["admin_id"] > 0){
                        $_SESSION["admin_nome"] = $dProfissional[0]["admin_nome"];

                        //Aqui será criado a sessão válida para acesso ao sistema e redirecionar para a index
                        $this->session->_admin_id           = $dProfissional[0]["admin_id"];
                        $this->session->_admin_nome         = $dProfissional[0]["admin_nome"];
                        $this->session->_admin_email         = $dProfissional[0]["admin_email"];

                        $this->retorno["status_login"]              = "liberado";
                    }else{
                        $this->retorno["erro"][]                    = "Acesso Negado";
                    }
                    
                    

                }
            }else{
                $this->retorno['alerta'][] = 'Informe os dados para continuar.';
            }
        }catch(Exception $e){
            ChromePhp::log($e);
        }

        echo json_encode($this->retorno);
        exit;
    }
    
    public function logoutAction(){        
        $this->retorno["dados"] = "desconectado";
        /* Limpando todos os dados da sessao referente ao namespace Webservice */
        if(Zend_Session::namespaceIsset("session_sad_subeauty")) {
            Zend_Session::namespaceUnset("session_sad_subeauty");
            Zend_Session::destroy();
        }
        $this->_redirect("/admin_login");
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    
    
  //   public function buscaAction() {
		// $this->_helper->layout->disableLayout();
  //       try {
            
  //           $dados = $this->getRequest()->getPost();
            
  //           $dados_endereco = new Application_Model_DbTable_Cep();
  //           $dados_endereco = $dados_endereco->getDadosCep($dados['cep']);
            
  //           if ($dados_endereco) {
  //               $retorno['ok'] = 'Ok';
  //               $retorno['dados'] = $dados_endereco[0];
  //           } else {
  //               $retorno['alerta'] = 'Dados não encontrados!';
  //           }
            
  //       } catch (Exception $exc) {
  //           $retorno['erro'] = false;
  //       }
        
  //       $this->view->dados = Zend_Json_Encoder::encode($retorno);
  //   }
    
    
  //   public function buscaCepRuaAction(){
		// $this->_helper->layout->disableLayout();
        
  //        try {
            
  //           $dados = $this->getRequest()->getParams();
           
  //           $dados_endereco = new Application_Model_DbTable_Cep();
  //           $dados_endereco = $dados_endereco->DadosRua($dados['valor']);
             
  //           if ($dados_endereco) {
  //               $retorno['ok'] = 'Ok';
  //               $retorno['dados'] = $dados_endereco;
  //           } else {
  //               $retorno['alerta'] = 'Dados não encontrados!';
  //           }
            
  //       } catch (Exception $exc) {
  //           $retorno['erro'] = false;
  //       }
        
  //       $this->view->dados = Zend_Json_Encoder::encode($retorno);
  //   }
    
    
  //   public function buscaCodIbgeAction() {
		// $this->_helper->layout->disableLayout();
  //       try {
            
  //           $dados = $this->getRequest()->getPost();
            
  //           //Instância os UF
  //           $codigoUf = Application_Model_Vars::codigoUF();
            
            
  //           $valores['uf']     = $codigoUf[$dados['uf']];
  //           $valores['cidade'] = strtoupper(Application_Model_Funcoes::trataAcentuacao($dados['cidade']));
            
            
  //           //Busca o código IBGE da cidade
  //           $codCityIbge = new Application_Model_DbTable_Codigocityibge();
  //           $codMunicipioIBGE = $codCityIbge->getCodCityIbge($valores);
            
            
  //           if ($codMunicipioIBGE) {
  //               $retorno['ok'] = 'Ok';
  //               $retorno['codigo'] = $codMunicipioIBGE;
  //           } else {
  //               $retorno['alerta'] = 'Dados não encontrados!';
  //           }
            
  //       } catch (Exception $exc) {
  //           $retorno['erro'] = false;
  //       }
        
  //       $this->view->dados = Zend_Json_Encoder::encode($retorno);
  //   }
    
}

