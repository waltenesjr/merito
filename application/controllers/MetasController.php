<?php

class MetasController extends Zend_Controller_Action
{
    protected $_name = 'metas';
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
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');
        // Verifica sessão do usuário
        if (!isset($this->session->_profissionais_id)) {
            Zend_Session::destroy();
            $this->_redirect("/login");
        }
    }

    public function indexAction()
    {
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Métas", "Visualizar");
        // action body
        $this->view->modulo = "Metas";
        
    }
    
    public function formAction(){
        $idForm = $this->getRequest()->getParam("metas_id");
        
        $dados = array();
        if($idForm > 0){
            //Pega os dados do registro para montar no formulário
            $MMetas = new Application_Model_DbTable_Metas();
            $metas = $MMetas->getRegistros(array('*'),array("metas_id"=>$idForm));
            $dados = $metas[0];
            
        }
        
        $this->view->dados = $dados;
    }
    
    public function filtrosAction(){ }
    
    public function pesquisarAction(){ 
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["metas_status_input"]);
            
            $MMetas = new Application_Model_DbTable_Metas();
            $metas = $MMetas->getRegistrosFiltradosGrid();
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($metas) && !empty($metas) && sizeof($metas) > 0){
                foreach($metas as $item){
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Grid de dados atualizada com sucesso!";
            }else{
                $this->retorno["alerta"][] = "Nenhum registro encontrado.";
            }
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    
    /**
     * @name validarDadosFormulario
     * @param Object $dados Dados que foram enviado do formulário
     * @return boolean Retorna se a validação foi efetivada com sucesso e o processo pode ser continuado
     */
    private function validarDadosFormulario($dados){
        $validacao = true;
        
        //Valida o nome da metas
        if($dados["metas_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome da Meta";
        }  
        //Verifica se a meta já foi cadastrado
        if($dados["metas_nome"] != ""){
            $MMetas = new Application_Model_DbTable_Metas();
            $metas = $MMetas->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"metas_id", "operador"=>"!=", "valor"=>$dados["metas_id"]),
                            array("condicao"=>"AND", "coluna"=>"metas_nome", "operador"=>"=", "valor"=>$dados["metas_nome"]),
                        ));
            
            if(sizeof($metas) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe uma meta cadastrada com o nome informado.";
            }
            
        }
            
        
        return $validacao;
        
    }
    
    /**
     * @name salvar
     * Observação importante para este método, é que ele pode tanto inserir um novo
     * registro quanto alterar um registro existe, e isso vai depender somente
     * da primary key passada pelo formulário, onde se tiver 0 (zero) será feito
     * uma nova inserção no banco de dados, caso seja maior que 0 (zero), a alteração
     * será aplicada a registro cujo id (primary key) foi passada.
     */
    public function salvarAction(){
        $this->retorno = array();
        
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'salvar') {
            try {
                $MMetas = new Application_Model_DbTable_Metas();
                $dados = $this->getRequest()->getPost();
                
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    //Alterar o registro
                    if($dados["dadosForm"]["metas_id"] > 0){
                        if($MMetas->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["metas_id"]);
                        if($MMetas->inserir($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível inserir o registro.";
                        }
                    }
                    
                }
                
            }catch (Exception $exc) {
                ChromePhp::log($exc);
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        }
    }
    
    public function excluirAction(){
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
            try {
                
                $MMetas = new Application_Model_DbTable_Metas();
                $dados = $this->getRequest()->getPost();
                
                $dados["metas_status"] = "Inativo";                
                $MMetas = new Application_Model_DbTable_Metas();
                $metas = $MMetas->excluir($dados);
                if($metas)
                    $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
                else
                    $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
            }catch (Exception $exc) {
                ChromePhp::log($exc);
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        }
    }

}

