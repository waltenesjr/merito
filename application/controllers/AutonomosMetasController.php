<?php

class AutonomosMetasController extends Zend_Controller_Action
{
    protected $_name = 'autonomos_metas';
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
        if (!isset($this->session->_usuario_logado) && !isset($this->session->_usuario_logado) > true) {
            Zend_Session::destroy();
            $this->_redirect("/login");
        }
    }

    public function indexAction()
    {
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais Metas", "Visualizar");
        
        // action body
        $this->view->fk_autonomos_id = $this->getRequest()->getParam("fk_autonomos_id");
        $MAutonomos = new Application_Model_DbTable_Autonomos();
        $dadosProfissional = $MAutonomos->getCampoPeloId($this->view->fk_autonomos_id);
        $this->view->autonomos_nome = $dadosProfissional[0]["autonomos_nome"];
        $this->view->modulo = "Autonomos e Metas";
        
    }
    
    public function formAction(){
        $idForm = $this->getRequest()->getParam("autonomos_metas_id");
        $dados = array();
        if($idForm > 0){
            //Pega os dados do registro para montar no formulário
            $MAutonomosMetas = new Application_Model_DbTable_AutonomosMetas();
            $autonomos_metas = $MAutonomosMetas->getRegistros(array('*'),array("autonomos_metas_id"=>$idForm));            
            $dados = $autonomos_metas[0];
            
        }
        $this->view->fk_autonomos_id = $this->getRequest()->getParam("fk_autonomos_id");
        $MAutonomos = new Application_Model_DbTable_Autonomos();
        $autonomos = $MAutonomos->getCampoPeloId($this->getRequest()->getParam("fk_autonomos_id"));
        $this->view->autonomos_nome = $autonomos[0]['autonomos_nome'];
        $this->view->dados = $dados;
        
        //Busca Metas Cadastradas no sistema
        $MMetas = new Application_Model_DbTable_Metas();
        $metas = $MMetas->getRegistros();
        $this->view->metas = $metas;
        
        
    }
    
    public function filtrosAction(){ }
    
    public function pesquisarAction(){
        
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            //$parametros = $this->getRequest()->getParams();
            //unset($parametros["autonomos_metas_status_input"]);
            
            $MAutonomosMetas = new Application_Model_DbTable_AutonomosMetas();
            $autonomos_metas = $MAutonomosMetas->getRegistrosFiltradosGrid( array(), array("fk_autonomos_id"=>$this->getRequest()->getParam("fk_autonomos_id")) );
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($autonomos_metas) && !empty($autonomos_metas) && sizeof($autonomos_metas) > 0){
                foreach($autonomos_metas as $item){
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
        
        //Valida o nome da autonomos_metas
        if($dados["fk_metas_id"] == ""){
            $validacao = false;
            $this->retorno["alerta"][] = "Selecione uma Meta";
        }  
        //Verifica se o autonomos_metas já foi cadastrado
        if($dados["fk_metas_id"] != ""){
            $MAutonomosMetas = new Application_Model_DbTable_AutonomosMetas();
            
            //Se a validação for de um registro que esta sendo alterado, não deve ser feito a comparação com ele próprio
            //Por isso tenho que passar estes filtros para tratar da maneira que preciso
            $condicoes = array();
            if($dados["autonomos_metas_id"] > 0)
                $condicoes = array(
                                array("condicao"=>"AND", "coluna"=>"autonomos_metas_id", "operador"=>"!=", "valor"=>$dados["autonomos_metas_id"]),
                                array("condicao"=>"AND", "coluna"=>"fk_autonomos_id", "operador"=>"=", "valor"=>$dados["fk_autonomos_id"]),
                                array("condicao"=>"AND", "coluna"=>"fk_metas_id", "operador"=>"=", "valor"=>$dados["fk_metas_id"])
                             );
            else
                $condicoes = array(
                                array("condicao"=>"AND", "coluna"=>"fk_autonomos_id", "operador"=>"=", "valor"=>$dados["fk_autonomos_id"]),
                                array("condicao"=>"AND", "coluna"=>"fk_metas_id", "operador"=>"=", "valor"=>$dados["fk_metas_id"])
                             );
            
            $autonomos_metas = $MAutonomosMetas->getRegistrosFiltrados($condicoes);
            if($autonomos_metas && sizeof($autonomos_metas[0]) > 0){
                $validacao = false;
                $this->retorno["alerta"][] = "A Meta selecionada já foi cadastrada para este profissional.";
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
                $MAutonomosMetas = new Application_Model_DbTable_AutonomosMetas();
                $dados = $this->getRequest()->getPost();
                //Limpa alguns campos desnecessários para inclusão ou alteração do registro
                unset($dados['dadosForm']['fk_metas_id_input']);
                
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    //Alterar o registro
                    if($dados["dadosForm"]["autonomos_metas_id"] > 0){
                        if($MAutonomosMetas->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["autonomos_metas_id"]);
                        if($MAutonomosMetas->inserir($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível inserir o registro.";
                        }
                    }
                    
                }
                
            }catch (Exception $exc) {
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        }
    }
    
    public function excluirAction(){
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
            try {
                
                $MAutonomosMetas = new Application_Model_DbTable_AutonomosMetas();
                $dados = $this->getRequest()->getPost();
                          
                $MAutonomosMetas = new Application_Model_DbTable_AutonomosMetas();
                $autonomos_metas = $MAutonomosMetas->excluir($dados);
                if($autonomos_metas)
                    $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
                else
                    $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
            }catch (Exception $exc) {
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        }
    }
    
    public function validarLogin($usuario,$senha){
        try {
                       
            $retorno = false;
            
            $columns = array(
                'autonomos_id',
                'autonomos_nome'
            );

            $select = $this
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from(array('a' => $this->_name), $columns, $this->_schema)
                    ->where('a.autonomos_cpf = ?', $usuario)
                    ->where('a.autonomos_senha = ?', sha1($senha))
                    ->where('a.autonomos_status = "Ativo"');
            ChromePhp::log($select->assemble());
            $retorno = $this->fetchAll($select);

//            foreach ($data as $valor) {
//                $retorno['id']                      = $valor['usuarios_id'];
//                $retorno['nome']                    = $valor['autonomos_nome'];
//            }
  
        } catch (Exception $exc) {
            $retorno = false;
        }
        return $retorno;
    }

}

