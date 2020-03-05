<?php

class FuncoesController extends Zend_Controller_Action
{
    protected $_name = 'funcoes';
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
		//$this->_helper->layout->setLayout('layout_inc');
		
        $this->view->tipo = $this->getRequest()->getParam("tipo");
        
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções", "Visualizar");
        // action body
        $this->view->modulo = "Funções";
        
		$tipo = $this->getRequest()->getParam("tipo");
		
		$dummy = array();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);
            
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $funcoes = $MFuncoes->getRegistrosFiltradosGrid($dummy,array('funcao_autonomo'=>$tipo));
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($funcoes) && !empty($funcoes) && sizeof($funcoes) > 0){
                foreach($funcoes as $item){
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Dados carregados com sucesso!";
            }else{
                $this->retorno["alerta"][] = "Nenhum registro encontrado.";
            }
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = $this->retorno;        
        
    }
    
    public function formAction(){
		
		// $this->_helper->layout->disableLayout();
		//$this->_helper->layout->setLayout('layout_inc');
		
		
        $idForm = $this->getRequest()->getParam("funcoes_id");
        $tipo = $this->getRequest()->getParam("tipo");
        
        if ($tipo != "")
			$this->view->tipo = $tipo ;
        
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções", "Alterar");
        
        
        if($this->view->permissao["status"] == true){
            $dados = array();
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário
                $MFuncoes = new Application_Model_DbTable_Funcoes();
                $funcoes = $MFuncoes->getRegistros(array('*'),array("funcoes_id"=>$idForm));            
                $dados = $funcoes[0];

            }
        
            $this->view->dados = $dados;
        }
    }
    
    public function filtrosAction(){ }
    
    public function pesquisarAction(){ 
		
		$this->_helper->layout->disableLayout();
		
		$tipo = $this->getRequest()->getParam("tipo");
		
		$dummy = array();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);
            
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $funcoes = $MFuncoes->getRegistrosFiltradosGrid($dummy,array('funcao_autonomo'=>$tipo));
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($funcoes) && !empty($funcoes) && sizeof($funcoes) > 0){
                foreach($funcoes as $item){
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Dados carregados com sucesso!";
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
        
        //Valida o nome da funcoes
        if($dados["funcoes_descricao"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a Descrição da Função";
        }  
        //Verifica se a funcoes já foi cadastrado
        if($dados["funcoes_descricao"] != ""){
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $funcoes = $MFuncoes->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"funcoes_id", "operador"=>"!=", "valor"=>$dados["funcoes_id"]),
                            array("condicao"=>"AND", "coluna"=>"funcoes_descricao", "operador"=>"=", "valor"=>$dados["funcoes_descricao"]),
                        ));
            
            if(sizeof($funcoes) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe uma função cadastrada com a descrição informada.";
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
                $MFuncoes = new Application_Model_DbTable_Funcoes();
                //$dados = $this->getRequest()->getPost();
                $res = $this->getRequest()->getParams() ;
                
                $dados["dadosForm"] = $res["dadosForm"] ;
                
                //Remover alguns campos que não são necessários no modelo
                unset($dados["dadosForm"]["funcoes_status_input"]);
                               
                //if (!isset($dados["dadosForm"]['tipo']) ){
				//	$dados["dadosForm"]['funcao_autonomo'] = 'S' ;
					
				//}
				unset($dados["dadosForm"]["tipo"]);

                if($this->validarDadosFormulario($dados["dadosForm"])){
                    if($dados["dadosForm"]["funcoes_id"] > 0){
                        if($MFuncoes->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }
                    }else{
                        unset($dados["dadosForm"]["funcoes_id"]);
                        if($MFuncoes->inserir($dados["dadosForm"])){
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
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {

                    $MFuncoes = new Application_Model_DbTable_Funcoes();
                    $dados = $this->getRequest()->getPost();

                    $dados["funcoes_status"] = "Inativo";                
                    $MFuncoes = new Application_Model_DbTable_Funcoes();
                    $funcoes = $MFuncoes->excluir($dados);
                    if($funcoes)
                        $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
                    else
                        $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
                }catch (Exception $exc) {
                    ChromePhp::log($exc);
                    $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
                }
            }
        }else{
            $this->retorno['alerta'][] = $permissao['mensagem'];
        }
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

}

