<?php

class AtribuicoesController extends Zend_Controller_Action
{
    protected $_name = 'atribuicoes';
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
		// $this->_helper->layout->disableLayout();
		
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Atribuições", "Visualizar");
        // action body
        $this->view->modulo = "Atribuições";
        
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            
            $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
            $atribuicoes = $MAtribuicoes->getRegistrosFiltradosGrid();
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($atribuicoes) && !empty($atribuicoes) && sizeof($atribuicoes) > 0){
                foreach($atribuicoes as $item){
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

        $MMenuDoc = new Application_Model_DbTable_Menudoc();
		$menuDoc = $MMenuDoc->getMenus();            
		$this->view->menu = $menuDoc;
		
		$subMenuDoc = $MMenuDoc->getSubMenus();            
		$this->view->menuSub = $subMenuDoc;
        
        $this->view->dados = $this->retorno;
        
    }
    
    public function formAction(){
		
		// $this->_helper->layout->disableLayout();
		
        $idForm = $this->getRequest()->getParam("atribuicoes_id");
 
         if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Atribuições", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Atribuições", "Alterar");
        
        $dados = array();
        if($idForm > 0){
            //Pega os dados do registro para montar no formulário
            $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
            $atribuicoes = $MAtribuicoes->getRegistros(array('*'),array("atribuicoes_id"=>$idForm));            
            $dados = $atribuicoes[0];
        }
        
        $this->view->dados = $dados;
    }
    
    public function mostrarAction(){
		$this->_helper->layout->disableLayout();
		
        $idForm = $this->getRequest()->getParam("atribuicoes_id");
        
        $dados = array();
        if($idForm > 0){
            //Pega os dados do registro para montar no formulário
            $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
            $atribuicoes = $MAtribuicoes->getRegistros(array('*'),array("atribuicoes_id"=>$idForm));            
            $dados = $atribuicoes[0];
        }
        
        $this->view->dados = $dados;
    }
    
    public function filtrosAction(){ }
    
    public function pesquisarAction(){ 
		
		$this->_helper->layout->disableLayout();
        
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            
            $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
            $atribuicoes = $MAtribuicoes->getRegistrosFiltradosGrid();
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($atribuicoes) && !empty($atribuicoes) && sizeof($atribuicoes) > 0){
                foreach($atribuicoes as $item){
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
        
        //Valida o nome da atribuicoes
        if($dados["atribuicoes_nome"] == ""){
            $validacao = false;
            $this->retorno["alerta"][] = "Informe o nome da Atribuição";
        }  
        //Verifica se a atribuicoes já foi cadastrado
        if($dados["atribuicoes_nome"] != ""){
            $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
            $atribuicoes = $MAtribuicoes->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"atribuicoes_id", "operador"=>"!=", "valor"=>$dados["atribuicoes_id"]),
                            array("condicao"=>"AND", "coluna"=>"atribuicoes_nome", "operador"=>"=", "valor"=>$dados["atribuicoes_nome"]),
                        ));
            
            if(sizeof($atribuicoes) > 0){
                $validacao = false;
                $this->retorno["alerta"][] = "Já existe uma atribuição cadastrada com o mesmo nome informado.";
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
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'salvar') {
            try {
                $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
                $dados = $this->getRequest()->getPost();
                
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    //Alterar o registro
                    if($dados["dadosForm"]["atribuicoes_id"] > 0){
                        if($MAtribuicoes->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["atribuicoes_id"]);
                        if($MAtribuicoes->inserir($dados["dadosForm"])){
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
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
            try {
                
                $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
                $dados = $this->getRequest()->getPost();
                
                //Verifica se a atribuição pode ser excluida
                $d = $MAtribuicoes->verificaDependencias($dados);
                
                if(isset($d) && !empty($d) && sizeof($d) > 0)
                {
                    $this->retorno['alerta'][] = 'O registro selecionado esta sendo utilizado! Operação não permitida.';
                }else{
                
                    $dados["atribuicoes_status"] = "Inativo";                

                    $atribuicoes = $MAtribuicoes->excluir($dados);
                    if($atribuicoes)
                        $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
                    else
                        $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
                }
            }catch (Exception $exc) {
                ChromePhp::log($exc);
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        }
    }

}

