<?php

class AutonomosHistoricoController extends Zend_Controller_Action
{
    protected $_name = 'autonomos_historico';
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
        $idForm = $this->getRequest()->getParam("autonomos_historico_id");
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais / Histórico", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais / Histórico", "Alterar");
        
        
        if($this->view->permissao["status"] == true){
            $dados = array();
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário
                $MAutonomosHistorico = new Application_Model_DbTable_AutonomosHistorico();
                $autonomos_historico = $MAutonomosHistorico->getRegistros(array('*'),array("autonomos_historico_id"=>$idForm));            
                $dados = $autonomos_historico[0];

            }
        
            $this->view->dados = $dados;
        }
    }
    
    public function filtrosAction(){ }
    
    public function pesquisarAction(){ 
        $this->retorno = array();
        
        try{
            $parametros = $this->getRequest()->getParams();

            $MAutonomosHistorico = new Application_Model_DbTable_AutonomosHistorico();
            $autonomos_historico = $MAutonomosHistorico->getRegistrosFiltradosGrid(array(), array('fk_autonomos_id'=>$parametros['fk_autonomos_id']));
            
            if(isset($autonomos_historico) && !empty($autonomos_historico) && sizeof($autonomos_historico) > 0){
                foreach($autonomos_historico as $item){
                    $arr = explode("-",$item["autonomos_historico_data"]);
                    $item["autonomos_historico_data"] = $arr[2]."/".$arr[1]."/".$arr[0];
                    $item["autonomos_historico_descricao"] = substr($item["autonomos_historico_descricao"],0,50);
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
        
        //Valida o nome da autonomos_historico
        if($dados["autonomos_historico_descricao"] == ""){
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
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'salvar') {
            try {
                $MAutonomosHistorico = new Application_Model_DbTable_AutonomosHistorico();
                $dados = $this->getRequest()->getPost();

                //Remover alguns campos que não são necessários no modelo
                $dados["autonomos_historico_status"] = "Ativo";
                $dados["fk_autonomos_cad_id"] = $this->session->_autonomos_id;
                
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados)){
                    $arrData = explode("/",$dados["autonomos_historico_data"]);
                    $dados["autonomos_historico_data"]= $arrData[2].'-'.$arrData[1].'-'.$arrData[0];
                    
                    //Alterar o registro
                    if($dados["autonomos_historico_id"] > 0){
                        if($MAutonomosHistorico->atualizar($dados)){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["autonomos_historico_id"]);
                        if($MAutonomosHistorico->inserir($dados)){
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
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais / Histórico", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {
                    $dados = $this->getRequest()->getPost();
                    
                    $dados["autonomos_historico_status"] = "Inativo";                
                    $MAutonomosHistorico = new Application_Model_DbTable_AutonomosHistorico();
                    $autonomos_historico = $MAutonomosHistorico->excluir($dados);
                    
                    if($autonomos_historico)
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
        try {
            $dados = $this->getRequest()->getPost();
               
            $MAutonomosHistorico = new Application_Model_DbTable_AutonomosHistorico();
            $autonomos_historico = $MAutonomosHistorico->getHistoricoPeloId($dados["autonomos_historico_id"]);
            if(empty($autonomos_historico) || !isset($autonomos_historico))
                $this->retorno['alerta'][] = 'Nenhum registro encontrado!';
            else
                $this->retorno['dados'] = $autonomos_historico;
        }catch (Exception $exc) {
            ChromePhp::log($exc);
            $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

}

