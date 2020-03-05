<?php

class ProfissionaisHistoricoController extends Zend_Controller_Action
{
    protected $_name = 'profissionais_historico';
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
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais / Histórico", "Visualizar");
        // action body
        $this->view->modulo = "Profissionais / Histórico";
        
    }
    
    public function formAction(){
        $idForm = $this->getRequest()->getParam("profissionais_historico_id");
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais / Histórico", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais / Histórico", "Alterar");
        
        
        if($this->view->permissao["status"] == true){
            $dados = array();
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário
                $MProfissionaisHistorico = new Application_Model_DbTable_Profissionaishistorico();
                $profissionais_historico = $MProfissionaisHistorico->getRegistros(array('*'),array("profissionais_historico_id"=>$idForm));            
                $dados = $profissionais_historico[0];

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
            //unset($parametros["profissionais_historico_status_input"]);

            $MProfissionaisHistorico = new Application_Model_DbTable_Profissionaishistorico();
            $profissionais_historico = $MProfissionaisHistorico->getRegistrosFiltradosGrid(array(), array('fk_profissionais_id'=>$parametros['fk_profissionais_id']));
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($profissionais_historico) && !empty($profissionais_historico) && sizeof($profissionais_historico) > 0){
                foreach($profissionais_historico as $item){
                    $arr = explode("-",$item["profissionais_historico_data"]);
                    $item["profissionais_historico_data"] = $arr[2]."/".$arr[1]."/".$arr[0];
                    $item["profissionais_historico_descricao"] = substr($item["profissionais_historico_descricao"],0,50);
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
        
        //Valida o nome da profissionais_historico
        if($dados["profissionais_historico_descricao"] == ""){
            $validacao = false;
            $this->retorno["alerta"][] = "Informe a Descição do Histórico";
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
        //if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'salvar') {
            try {
                $MProfissionaisHistorico = new Application_Model_DbTable_Profissionaishistorico();
                $dados = $this->getRequest()->getPost();

                //Remover alguns campos que não são necessários no modelo
                $dados["profissionais_historico_status"] = "Ativo";
                $dados["fk_profissionais_cad_id"] = $this->session->_profissionais_id;
                
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados)){
                    $arrData = explode("/",$dados["profissionais_historico_data"]);
                    $dados["profissionais_historico_data"]= $arrData[2].'-'.$arrData[1].'-'.$arrData[0];
                    
                    //Alterar o registro
                    if($dados["profissionais_historico_id"] > 0){
                        if($MProfissionaisHistorico->atualizar($dados)){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["profissionais_historico_id"]);
                        
                        ChromePhp::log($dados);
                        
                        if($MProfissionaisHistorico->inserir($dados)){
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
        //}
    }
    
    public function excluirAction(){
		$this->_helper->layout->disableLayout();
        $this->retorno = array();
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais / Histórico", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {
                    $dados = $this->getRequest()->getPost();
                    
                    $dados["profissionais_historico_status"] = "Inativo";                
                    $MProfissionaisHistorico = new Application_Model_DbTable_Profissionaishistorico();
                    $profissionais_historico = $MProfissionaisHistorico->excluir($dados);
                    
                    if($profissionais_historico)
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
    
    public function getHistoricoPeloIdAction(){
		$this->_helper->layout->disableLayout();
        try {
            $dados = $this->getRequest()->getPost();
               
            $MProfissionaisHistorico = new Application_Model_DbTable_Profissionaishistorico();
            $profissionais_historico = $MProfissionaisHistorico->getHistoricoPeloId($dados["profissionais_historico_id"]);
            if(empty($profissionais_historico) || !isset($profissionais_historico))
                $this->retorno['alerta'][] = 'Nenhum registro encontrado!';
            else
                $this->retorno['dados'] = $profissionais_historico;
        }catch (Exception $exc) {
            ChromePhp::log($exc);
            $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

}

