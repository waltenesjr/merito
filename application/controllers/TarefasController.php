<?php

class TarefasController extends Zend_Controller_Action
{
    protected $_name = 'tarefas';
    protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
    }
    
    protected function initAppModel() {

//        $accessController = str_replace('-', "", $this->getRequest()->getControllerName());
//        $className = "Application_Model_DbTable_" . ucfirst($accessController);
//
//        if (!class_exists($className)):
//            $this->view->msg = 'ERROR: App not found (' . $className . ').';
//        endif;
//
//        return new $className();
        
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
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tarefas", "Visualizar");
        // action body
        $this->view->modulo = "Tarefas";

		$parametros = $this->getRequest()->getParams();

        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            //$parametros = null;

            if($_SESSION['profissionais_cargo'] != 'administrador')
                $parametros['funcionario_id'] = $_SESSION['profissionais_id'];
            if($_SESSION['profissionais_cargo'] != 'funcionario')
                $parametros['gestor_id'] = $_SESSION['profissionais_id'];

            
            $MTarefas = new Application_Model_DbTable_Tarefas();
            $tarefas = $MTarefas->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tarefas) && !empty($tarefas) && sizeof($tarefas) > 0){
                foreach($tarefas as $item){
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Grid de dados atualizada com sucesso!";
            }else{
                $this->retorno["alerta"][] = "Nenhum registro encontrado.";
            }
        }catch(Exception $e){
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }        
        
        $MMenuDoc = new Application_Model_DbTable_Menudoc();
		$menuDoc = $MMenuDoc->getMenus();            
		$this->view->menu = $menuDoc;
		
		$subMenuDoc = $MMenuDoc->getSubMenus();            
		$this->view->menuSub = $subMenuDoc;
		
        $this->view->dados = $this->retorno;

		if ( $this->session->_profissionais_cargo == "administrador" || $this->session->_profissionais_tipo == "master" ){	
			$this->view->ver = true ;
		} else {
			$this->view->ver = false ;
		}    
        
    }
    
    public function formAction(){
		
		// $this->_helper->layout->disableLayout();
		
		
        $idForm = $this->getRequest()->getParam("tarefas_id");
        
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tarefas", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tarefas", "Alterar");
        
        
        if($this->view->permissao["status"] == true){
            //Vou utilizar em todo o scopo, por isso declarei no topo
            $MProfissionais = new Application_Model_DbTable_Profissionais();

            $dados = array();
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário se for uma alteração
                $MTarefas = new Application_Model_DbTable_Tarefas();
                $tarefa = $MTarefas->getRegistros(array("tarefas_id"=>$idForm));            
                $dados = $tarefa[0];
                if(isset($dados["tarefas_data_inicio"])){
                    $arrDtNascimento = explode("-",$dados["tarefas_data_inicio"]);
                    $dados["tarefas_data_inicio"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }
                if(isset($dados["tarefas_data_termino"])){
                    $arrDtNascimento = explode("-",$dados["tarefas_data_termino"]);
                    $dados["tarefas_data_termino"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }

                // var_dump($idForm);exit;
            }

            //Carrega os dados para montagem do campo uf
            $this->view->gestores = $MProfissionais->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"profissionais_cargo", "operador"=>"!=", "valor"=>'funcionario')
                        ));

             $this->view->funcionarios = $MProfissionais->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"profissionais_cargo", "operador"=>"!=", "valor"=>'administrador')
                        ));

            $this->view->dados = $dados;
        }
    }

    public function formfuncionarioAction(){
        
        // $this->_helper->layout->disableLayout();
        
        
        $idForm = $this->getRequest()->getParam("tarefas_id");
        
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tarefas", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tarefas", "Alterar");
        
        
        if($this->view->permissao["status"] == true){
            //Vou utilizar em todo o scopo, por isso declarei no topo
            $MProfissionais = new Application_Model_DbTable_Profissionais();

            $dados = array();
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário se for uma alteração
                $MTarefas = new Application_Model_DbTable_Tarefas();
                $tarefa = $MTarefas->getRegistros(array("tarefas_id"=>$idForm));            
                $dados = $tarefa[0];
                if(isset($dados["tarefas_data_inicio"])){
                    $arrDtNascimento = explode("-",$dados["tarefas_data_inicio"]);
                    $dados["tarefas_data_inicio"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }
                if(isset($dados["tarefas_data_termino"])){
                    $arrDtNascimento = explode("-",$dados["tarefas_data_termino"]);
                    $dados["tarefas_data_termino"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }

                // var_dump($idForm);exit;
            }

            //Carrega os dados para montagem do campo uf
            $this->view->gestores = $MProfissionais->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"profissionais_cargo", "operador"=>"!=", "valor"=>'funcionario')
                        ));

             $this->view->funcionarios = $MProfissionais->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"profissionais_cargo", "operador"=>"=", "valor"=>'funcionario')
                        ));

            $this->view->dados = $dados;
        }
    }
    
    public function filtrosAction(){
			$this->_helper->layout->disableLayout();
	}
    
    public function pesquisarAction(){ 
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            unset($parametros["tarefas_status_input"]);
            unset($parametros["controller"]);
            unset($parametros["action"]);
            unset($parametros["module"]);
            
            $MTarefas = new Application_Model_DbTable_Tarefas();
            $tarefas = $MTarefas->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($tarefas) && !empty($tarefas) && sizeof($tarefas) > 0){
                foreach($tarefas as $item){
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Grid de dados atualizada com sucesso!";
            }else{
                $this->retorno["alerta"][] = "Nenhum registro encontrado.";
            }
        }catch(Exception $e){
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
        
        //Valida o nome da tarefa
        if($dados["tarefas_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome da tarefa";
        }  

        if($dados["tarefas_data_inicio"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a data de início";
        }

        if($dados["tarefas_data_termino"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a data de término";
        }

        if($dados["tarefas_pontos"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a quantidade de pontos da tarefa";
        }

        if($dados["tarefas_gestor_id"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o gestor da tarefa";
        }

        if(!$dados["tarefas_id"] && empty($dados["tarefas_funcionarios"])){
            $validacao = false;
            $this->retorno["erro"][] = "Informe ao menos um funcionário para a tarefa";
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
                $MTarefas = new Application_Model_DbTable_Tarefas();
                $res = $this->getRequest()->getPost();
                $dados["dadosForm"] = $res["dados"] ;

                unset($dados["dadosForm"]["tarefas_funcionarios["]);

                $dados["dadosForm"]["tarefas_funcionarios"] = $_POST['funcionarios'];
               
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    $arrDtInic = explode("/",$dados["dadosForm"]["tarefas_data_inicio"]);
                    $dados["dadosForm"]["tarefas_data_inicio"]= $arrDtInic[2].'-'.$arrDtInic[1].'-'.$arrDtInic[0];

                    $arrDtTer = explode("/",$dados["dadosForm"]["tarefas_data_termino"]);
                    $dados["dadosForm"]["tarefas_data_termino"]= $arrDtTer[2].'-'.$arrDtTer[1].'-'.$arrDtTer[0];

                    //Alterar o registro
                    if($dados["dadosForm"]["tarefas_id"] > 0){
                        unset($dados["dadosForm"]["tarefas_funcionarios"]);

                        if($MTarefas->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["tarefas_id"]);
                        
                        $funcionarios = $dados["dadosForm"]["tarefas_funcionarios"];
                        unset($dados["dadosForm"]["tarefas_funcionarios"]);

                        $retorno = $MTarefas->inserir($dados["dadosForm"]);
                        if($retorno){
                            $MTarefasProfissionais = new Application_Model_DbTable_TarefasProfissionais();

                            foreach ($funcionarios as $funcionario) {
                                $MTarefasProfissionais->inserir(array('tarefas_profissionais_profissional_id' => $funcionario, 'tarefas_profissionais_tarefa_id' => $retorno, 'tarefas_profissionais_finalizada' => 0));
                            }


                            $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível inserir o registro.";
                        }
                    }
                    
                }
                
            }catch (Exception $exc) {
                var_dump($exc);exit;
                ChromePhp::log($exc);
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
            ChromePhp::log($this->retorno);
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        } else {
			//ChromePhp::log("Salgo");
		}
    }

    public function salvarstatusAction(){
        
        $this->_helper->layout->disableLayout();
        
        $this->retorno = array();

        if ($this->getRequest()->isPost()) {
            try {

                $dados = $this->getRequest()->getPost();
                $dados = $dados["dados"];
            
                $MTarefas = new Application_Model_DbTable_Tarefas();
                $tarefas = $MTarefas->atualizar($dados);
                if($tarefas)
                    $this->retorno['sucesso'][] = 'Registro alterado com sucesso!';
                else
                    $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
            }catch (Exception $exc) {
                echo 'aqui';exit;
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
        }

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function salvarfuncionarioAction(){
        
        $this->_helper->layout->disableLayout();
        
        $this->retorno = array();

        if ($this->getRequest()->isPost()) {
            try {

                $dados = $this->getRequest()->getPost();

                $dados["tarefas_profissionais_id"] = $dados["dados"]["tarefas_profissionais_id"];  
                unset($dados["dados"]);
                $dados["tarefas_profissionais_finalizada"] = 1;  
                $dados["tarefas_profissionais_finalizada_em"] = 'now()';                
                $MTarefas = new Application_Model_DbTable_TarefasProfissionais();
                $tarefas = $MTarefas->atualizar($dados);
                if($tarefas)
                    $this->retorno['sucesso'][] = 'Registro alterado com sucesso!';
                else
                    $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
            }catch (Exception $exc) {
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
        }

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function salvarobservacaoAction(){
        
        $this->_helper->layout->disableLayout();
        
        $this->retorno = array();

        if ($this->getRequest()->isPost()) {
            try {

                $dados = $this->getRequest()->getPost();

                $dados["tarefas_observacoes_profissional_id"] = $_SESSION['profissionais_id'];  
                $dados["tarefas_observacoes_tarefa_id"] = $dados["dados"]["tarefas_id"]; 
                $dados["tarefas_observacoes_mensagem"] = $dados["dados"]["tarefas_observacoes_mensagem"]; 
                unset($dados["dados"]);             
                $MTarefas = new Application_Model_DbTable_TarefasObservacoes();
                $tarefas = $MTarefas->inserir($dados);
                if($tarefas)
                    $this->retorno['sucesso'][] = 'Registro inserido com sucesso!';
                else
                    $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
            }catch (Exception $exc) {
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
        }

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    
    public function excluirAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Tarefas", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {

                    $MTarefas = new Application_Model_DbTable_Tarefas();
                    $dados = $this->getRequest()->getPost();

                    $dados["tarefas_status"] = "Inativo";                
                    $MTarefas = new Application_Model_DbTable_Tarefas();
                    $tarefas = $MTarefas->excluir($dados);
                    if($tarefas)
                        $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
                    else
                        $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
                }catch (Exception $exc) {
                    $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
                }
            }
        }else{
            $this->retorno['alerta'][] = $permissao['mensagem'];
        }
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

}


//    public function autocomplete($getParams = array()) {
//        try {
//            $columns = array(
//                'clientes_id',
//                'clientes_nome',
//                'clientes_cpf',
//                'clientes_cnpj'
//            );
//            
//            $select = $this
//                    ->select()
//                    ->setIntegrityCheck(false)
//                    ->from($this->_name, $columns, $this->_schema)
//                    ->where("(LOWER(clientes_nome) LIKE LOWER('%" . $getParams['valor'] . "%'))
//                            OR (clientes_id = '" . (int) substr($getParams['valor'], 0, 5) . "')
//                            OR (REPLACE(REPLACE(clientes_cpf, '-', ''), '.', '') = '" . $getParams['valor'] . "')
//                            OR (REPLACE(REPLACE(REPLACE(clientes_cnpj, '-', ''), '.', ''), '/', '') = '" . $getParams['valor'] . "')
//                          ");
//
//            $select->where('clientes_excluido = FALSE')
//                    ->where('clientes_ativo = TRUE')
//                    ->order('LOWER(clientes_nome) ASC');
//
//            $dados = $select
//                    ->query()
//                    ->fetchAll();
//
//            $retorno = $dados;
//        } catch (Exception $exc) {
//            $retorno = FALSE;
//        }
//
//        return $retorno;
//    }
    
