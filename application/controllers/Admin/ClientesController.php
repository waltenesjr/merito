<?php

class Admin_ClientesController extends Zend_Controller_Action
{
    protected $_name = 'clientes';
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
        $this->view->modulo = "Clientes";
        $params = $this->getRequest()->getParams();
        
        $this->retorno = array();
        try{
            $MClientes = new Application_Model_DbTable_Admin_Clientes();

            $this->retorno["dados"] = $MClientes->getRegistros();
        }catch(Exception $e){
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }

        $this->view->dados = $this->retorno;      
       
    }
    
    public function formAction(){
		
		// $this->_helper->layout->disableLayout();
		//$this->_helper->layout->setLayout('layout_inc');

        //Vou utilizar em todo o scopo, por isso declarei no topo
        $MMunicipios = new Application_Model_DbTable_Municipios();

        $dados = array();
		
        $idForm = $this->getRequest()->getParam("clientes_id");

        $dados = array();
        $dados['clientes_id'] = 0;
        if($idForm > 0){
            //Pega os dados do registro para montar no formulário
            $MClientes = new Application_Model_DbTable_Admin_Clientes();
            $admin = $MClientes->getRegistros(array("clientes_id"=>$idForm));
            $dados = $admin[0];
        }
        
        //Carrega os dados para montagem do campo uf
        $this->view->municipios_uf = $MMunicipios->getUf();

        //Carrega as cidades para montagem do campo cidades
        $this->view->municipios_codigo_ibge = $MMunicipios->getMunicipiosPelaUf($dados['clientes_uf']);

        $this->view->uf = $dados['clientes_uf'];
        $this->view->codigoIbge = $dados['fk_municipios_codigo_ibge']; 

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
        
        //Valida o nome da unidade
        if($dados["clientes_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome";
        }
        if($dados["clientes_email"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o e-mail";
        } 
        if($dados["clientes_telefone"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o telefone";
        } 
        if($dados["clientes_empresa"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a empresa";
        }   
        if($dados["clientes_id"] == 0 && $dados["clientes_cnpj"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o CNPJ da empresa";
        }   
        if($dados["clientes_identificador"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o identificador";
        }  
        if($dados["clientes_cep"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o cep";
        } 

        $cond = $dados["clientes_id"] > 0 ? array(array("condicao"=>"AND", "coluna"=>"clientes_id", "operador"=>"<>", "valor"=>$dados["clientes_id"])) : array();
        
        //Verifica se a unidade já foi cadastrado
        if($dados["clientes_email"] != ""){
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltrados(array_merge(array(
                            array("condicao"=>"AND", "coluna"=>"clientes_email", "operador"=>"=", "valor"=>$dados["clientes_email"]),
                        ), $cond));
            
            if(sizeof($clientes) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um cliente cadastrado com o e-mail informado.";
            }
            
        }

        if($dados["clientes_identificador"] != ""){
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltrados(array_merge(array(
                            array("condicao"=>"AND", "coluna"=>"clientes_identificador", "operador"=>"=", "valor"=>$dados["clientes_identificador"]),
                        ), $cond));
            
            if(sizeof($clientes) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um cliente cadastrado com o identificador informado.";
            }
            
        }

        if($dados["clientes_id"] == 0 && $dados["clientes_cnpj"] != ""){
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltrados(array_merge(array(
                            array("condicao"=>"AND", "coluna"=>"clientes_cnpj", "operador"=>"=", "valor"=>$dados["clientes_cnpj"]),
                        ), $cond));
            
            if(sizeof($clientes) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um cliente cadastrado com o CNPJ informado.";
            }
            
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
                $MClientes = new Application_Model_DbTable_Admin_Clientes();
                $dados = $this->getRequest()->getPost();

                ChromePhp::log($dados["dadosForm"]);

                unset($dados["dadosForm"]["municipios_codigo_ibge"]);

                //ChromePhp::log($dados["dadosForm"]);
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    
                    //Alterar o registro
                    if($dados["dadosForm"]["clientes_id"] > 0){
                        $idProfissional = $dados["dadosForm"]["clientes_id"];
                        if($MClientes->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $idProfissional = 0;
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        $dados["dadosForm"]["clientes_cnpj"] = str_replace(array('.','/','-'), '', $dados["dadosForm"]["clientes_cnpj"]);

                        unset($dados["dadosForm"]["clientes_id"]);
                        $idProfissional = $MClientes->inserir($dados["dadosForm"]);
                        if($idProfissional){
                            $MMain = new Application_Model_DbTable_Main();
                            $createSchema = $MMain->createSchemaForClient($dados["dadosForm"]["clientes_cnpj"]);
                            if($createSchema)
                                $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                            else
                                $this->retorno['sucesso'][] = "Registro inserido com sucesso, FALHA AO CRIAR SCHEMA.";
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

                $MClientes = new Application_Model_DbTable_Admin_Clientes();
                $dados = $this->getRequest()->getPost();
               
                $MClientes = new Application_Model_DbTable_Admin_Clientes();
                $profissionais = $MClientes->excluir($dados);
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

