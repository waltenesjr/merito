<?php

class Admin_UsuariosController extends Zend_Controller_Action
{
    protected $_name = 'profissionais';
    protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('layout_admin');
    }
    
    public function preDispatch() {
        //Carrega a sessão
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');
        //Verifica sessão do usuário
        //if (!isset($this->session->_usuario_logado) && !isset($this->session->_usuario_logado)) {
		if (!isset($this->session->_admin_id)) {
            Zend_Session::destroy();
            $this->_redirect("/admin_login");
        }
    }

    public function indexAction()
    {
        
        // action body
        $this->view->modulo = "Usuários";
        $params = $this->getRequest()->getParams();
        
        $this->retorno = array();
        try{
            $MUsuarios = new Application_Model_DbTable_Admin_Usuarios();

            $this->retorno["dados"] = $MUsuarios->getRegistros();
        }catch(Exception $e){
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }

        $this->view->dados = $this->retorno;      
       
    }
    
    public function formAction(){
		
		// $this->_helper->layout->disableLayout();
		//$this->_helper->layout->setLayout('layout_inc');
		
        $idForm = $this->getRequest()->getParam("admin_id");

            $dados = array();
            $dados['admin_id'] = 0;
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário
                $MUsuarios = new Application_Model_DbTable_Admin_Usuarios();
                $admin = $MUsuarios->getRegistros(array("admin_id"=>$idForm));
                $dados = $admin[0];
                
            }
            

            $this->view->dados = $dados;
            //----------------------------------------------------------------------
        
        
    }
    
    
    /**
     * @name validarDadosFormulario
     * @param Object $dados Dados que foram enviado do formulário
     * @return boolean Retorna se a validação foi efetivada com sucesso e o processo pode ser continuado
     */
    private function validarDadosFormulario($dados){
        $validacao = true;
        
        //Valida o nome da profissionais
        if($dados["admin_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome";
        }  
        
        if($dados["admin_usuario"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome de Usuário";
        } 

        if($dados["dadosForm"]["admin_id"] == 0 && $dados["admin_senha"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a senha de Usuário";
        } 
            
        
        return $validacao;
        
    }
    
    /**
     * @name salvar
     * Observação importante para este método, é que ele pode tanto inserir um novo
     * registro quanto alterar um registro existente, e isso vai depender somente
     * da primary key passada pelo formulário, onde se tiver 0 (zero) será feito
     * uma nova inserção no banco de dados, caso seja maior que 0 (zero), a alteração
     * será aplicada a registro cujo id (primary key) foi passada.
     */
    public function salvarAction(){
		
		$this->_helper->layout->disableLayout();
        
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'salvar') {
            try {
                $MUsuarios = new Application_Model_DbTable_Admin_Usuarios();
                $dados = $this->getRequest()->getPost();
                
                ChromePhp::log($dados["dadosForm"]);
                
                if($dados["dadosForm"]["admin_senha"] != "")
                    $dados["dadosForm"]["admin_senha"] = sha1($dados["dadosForm"]["admin_senha"]);
                else
                    unset($dados["dadosForm"]["admin_senha"]);
                

                //ChromePhp::log($dados["dadosForm"]);
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    
                    //Alterar o registro
                    if($dados["dadosForm"]["admin_id"] > 0){
                        $idProfissional = $dados["dadosForm"]["admin_id"];
                        if($MUsuarios->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $idProfissional = 0;
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["admin_id"]);
                        $idProfissional = $MUsuarios->inserir($dados["dadosForm"]);
                        if($idProfissional){
                            $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                        }else{
                            $idProfissional = 0;
                            $this->retorno['erro'][] = "Não foi possível inserir o registro.";
                        }
                    }
                    
                }
                
            }catch (Exception $exc) {
                $this->retorno['erro'][] = 'Desculpe. Ocorreu uma falha ao recuperar informações do sistema.';
            }
            
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        }
    }
    
    public function excluirAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
            try {

                $MUsuarios = new Application_Model_DbTable_Admin_Usuarios();
                $dados = $this->getRequest()->getPost();

                $dados["admin_status"] = "Inativo";                
                $MUsuarios = new Application_Model_DbTable_Admin_Usuarios();
                $profissionais = $MUsuarios->excluir($dados);
                if($profissionais)
                    $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
                else
                    $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
            }catch (Exception $exc) {
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
        }

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

}

