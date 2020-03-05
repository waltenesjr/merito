<?php

class FuncoesAtribuicoesController extends Zend_Controller_Action
{
    protected $_name = 'funcoes_atribuicoes';
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
		
		$this->_helper->layout->disableLayout();
		
        $params = $this->getRequest()->getParams();
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções / Atribuições", "Visualizar");
        // action body
        $this->view->modulo = "Funções / Atribuições";
        
        ChromePhp::log($params);
        
        $this->view->fk_funcoes_id = $params['fk_funcoes_id'];
        
        //Pega o nome da função pelo id selecionado
        $MFuncoes = new Application_Model_DbTable_Funcoes();
        $this->view->funcao = $params['fk_funcoes_id'].' - '.$MFuncoes->getParametroPeloId($params['fk_funcoes_id'],'funcoes_descricao');
        
        //Carrega todas as atribuicoes
        $MAtribuicoes = new Application_Model_DbTable_Atribuicoes();
        $dados_atribuicoes = $MAtribuicoes->getRegistros();
        
        
        $dados['atribuicoes'] = $dados_atribuicoes;
        
        $this->view->dados = $dados;
        
        //Carrega todas as atribuicoes vinculadas a função seleionada
        
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);

            $MFuncoesAtribuicoes = new Application_Model_DbTable_Funcoesatribuicoes();
            $funcoes_atribuicoes = $MFuncoesAtribuicoes->getRegistrosFiltradosGrid(array(),$parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($funcoes_atribuicoes) && !empty($funcoes_atribuicoes) && sizeof($funcoes_atribuicoes) > 0){
                foreach($funcoes_atribuicoes as $item){
                    $dados2["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Grid de dados atualizada com sucesso!";
            }else{
                $this->retorno["alerta"][] = "Nenhum registro encontrado.";
            }
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados2 = $dados2;
        
    }
    
    public function formAction(){
		
		$this->_helper->layout->disableLayout();
        
        $idForm = $this->getRequest()->getParam("funcoes_atribuicoes_id");
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções / Atribuições", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções / Atribuições", "Alterar");
        
        
        if($this->view->permissao["status"] == true){
            $dados = array();
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário
                $MFuncoesAtribuicoes = new Application_Model_DbTable_FuncoesAtribuicoes();
                $funcoes_atribuicoes = $MFuncoesAtribuicoes->getRegistros(array('*'),array("funcoes_atribuicoes_id"=>$idForm));            
                $dados = $funcoes_atribuicoes[0];

            }
        
            $this->view->dados = $dados;
        }
    }
    
    public function filtrosAction(){ }
    
    public function pesquisarAction(){
		
		$this->_helper->layout->disableLayout();
		 
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            //unset($parametros["funcoes_status_input"]);

            $MFuncoesAtribuicoes = new Application_Model_DbTable_Funcoesatribuicoes();
            $funcoes_atribuicoes = $MFuncoesAtribuicoes->getRegistrosFiltradosGrid(array(),$parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($funcoes_atribuicoes) && !empty($funcoes_atribuicoes) && sizeof($funcoes_atribuicoes) > 0){
                foreach($funcoes_atribuicoes as $item){
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
        
        if(!isset($dados['fk_atribuicoes_id']) || $dados['fk_atribuicoes_id'] <= 0){
            $validacao = false;
            $this->retorno["alerta"][] = "Informe uma Atribuição";
        }
        
        //Verifica se já foi inserido um registro com o mesmo fk de funcoes e atribuicoes, caso esteja criando um novo registro
        $MFuncoesAtribuicoes = new Application_Model_DbTable_Funcoesatribuicoes();
        if($dados['funcoes_atribuicoes_id'] == 0 && $MFuncoesAtribuicoes->existeRegistro($dados['fk_funcoes_id'],$dados['fk_atribuicoes_id'],$dados['funcoes_atribuicoes_id'])){
            $validacao = false;
            $this->retorno["alerta"][] = "Já foi inserido um registro com os mesmos dados informados.";
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
                $MFuncoesAtribuicoes = new Application_Model_DbTable_Funcoesatribuicoes();
                $dados = $this->getRequest()->getPost();
                unset($dados["dadosForm"]['fk_atribuicoes_id_input']);

                
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    //Alterar o registro
                    if($dados["dadosForm"]["funcoes_atribuicoes_id"] > 0){
                        if($MFuncoesAtribuicoes->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        $dados["dadosForm"]["funcoes_atribuicoes_status"] = "Ativo";
                        unset($dados["dadosForm"]["funcoes_atribuicoes_id"]);
                        if($MFuncoesAtribuicoes->inserir($dados["dadosForm"])){
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
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Funções / Atribuições", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {
                    $dados = $this->getRequest()->getPost();

                    $dados["funcoes_atribuicoes_status"] = "Inativo";                
                    $MFuncoesAtribuicoes = new Application_Model_DbTable_Funcoesatribuicoes();
                    $funcoes_atribuicoes = $MFuncoesAtribuicoes->excluir($dados);
                    if($funcoes_atribuicoes)
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

