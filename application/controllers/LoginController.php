<?php

class LoginController extends Zend_Controller_Action
{
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('layout_login');

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
    }

    public function indexAction()
    {
        // action body
        
    }
    
    public function authAction(){

		$this->_helper->layout->disableLayout();
		
        try{
            
            $dados = $this->getRequest()->getPost();
            if(isset($dados)){
                
                if(!isset($dados['dados']['identificador']) || $dados['dados']['identificador'] == "")
                    $this->retorno["erro"][] = "Informe identificador de acesso para continuar.";
                elseif(!isset($dados['dados']['usuario']) || $dados['dados']['usuario'] == "")
                    $this->retorno["erro"][] = "Informe o usuário para continuar.";
                elseif(!isset($dados['dados']['senha']) || $dados['dados']['senha'] == "")
                    $this->retorno["erro"][] = "Informe a senha para continuar.";
                else{

                    $MClientes = new Application_Model_DbTable_Admin_Clientes();
                    $cliente = $MClientes->getRegistros(array('clientes_identificador' => $dados['dados']['identificador']));

                    if(!empty($cliente) && isset($cliente[0])){

                        if($cliente[0]['clientes_status'] == 'Ativo'){

                            $_SESSION["cliente_schema"] = 'data_'.$cliente[0]['clientes_cnpj'];
                        
                            //Primeiro verifica os profissionais, depis o usuário master
                            $MProfissional = new Application_Model_DbTable_Profissionais();
                            $dProfissional = $MProfissional->validarLogin($dados['dados']['usuario'], $dados['dados']['senha']);
                            
                            if(isset($dProfissional) && !empty($dProfissional[0]["profissionais_id"]) && $dProfissional[0]["profissionais_id"] > 0){
                                //Aqui será criado a sessão válida para acesso ao sistema e redirecionar para a index
                                $_SESSION['profissionais_id'] = $dProfissional[0]["profissionais_id"];
                                $_SESSION['profissionais_nome'] = $dProfissional[0]["profissionais_nome"];
                                $_SESSION['profissionais_cargo'] = $dProfissional[0]["profissionais_cargo"];

                                $this->session->_profissionais_id           = $dProfissional[0]["profissionais_id"];
                                $this->session->_profissionais_nome         = $dProfissional[0]["profissionais_nome"];
                                $this->session->_profissionais_cargo        = $dProfissional[0]["profissionais_cargo"];
                                $this->retorno["status_login"]              = "liberado";
                                $this->session->_profissionais_tipo         = "profissional";
                                
                                //Carrega as permissões
                                $MPermissoes = new Application_Model_DbTable_Permissoes();
                                $this->session->_permissoes = $MPermissoes->getPermissoesPorProfissionaisId($dProfissional[0]["profissionais_id"]);
                            }else{
                                //Agora vou verificar se é um usuário master
                                $dProfissional = $MProfissional->validarLoginMaster($dados['dados']['usuario'], $dados['dados']['senha']);
                                
                                if(isset($dProfissional) && !empty($dProfissional[0]["usuarios_master_id"]) && $dProfissional[0]["usuarios_master_id"] > 0){
                                    $_SESSION['profissionais_nome'] = $dProfissional[0]["usuarios_master_nome"];

                                    //Aqui será criado a sessão válida para acesso ao sistema e redirecionar para a index
                                    $this->session->_profissionais_id           = $dProfissional[0]["usuarios_master_id"];
                                    $this->session->_profissionais_nome         = $dProfissional[0]["usuarios_master_nome"];
                                    $this->retorno["status_login"]              = "liberado";
                                    $this->session->_profissionais_tipo         = "master";
                                }else{
                                    $this->session->_profissional_logado        = false;
                                    $this->retorno["erro"][]                    = "Acesso Negado";
                                }
                            }

                        }else{
                            $this->session->_profissional_logado        = false;
                            $this->retorno["erro"][]                    = "Identificador com acesso bloqueado";
                        }
                    
                    }else{
                        $this->session->_profissional_logado        = false;
                        $this->retorno["erro"][]                    = "Identificador não existe";
                    }

                }
            }else{
                $this->retorno['alerta'][] = 'Informe os dados para continuar.';
            }
        }catch(Exception $e){
            ChromePhp::log($e);
        }
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    
    public function logoutAction(){        
        $this->retorno["dados"] = "desconectado";
        /* Limpando todos os dados da sessao referente ao namespace Webservice */
        if(Zend_Session::namespaceIsset("session_sad_subeauty")) {
            Zend_Session::namespaceUnset("session_sad_subeauty");
            Zend_Session::destroy();
        }
        unset($_SESSION["cliente_schema"]);
        $this->_redirect("/login");
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
}

