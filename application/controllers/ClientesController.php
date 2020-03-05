<?php

class ClientesController extends Zend_Controller_Action
{
    protected $_name = 'clientes';
    protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout('layout_login');
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
    }

    public function _indexAction()
    {
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Clientes", "Visualizar");
        // action body
        $this->view->modulo = "Clientes";

		$parametros = $this->getRequest()->getParams();

        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            //$parametros = null;

            
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($clientes) && !empty($clientes) && sizeof($clientes) > 0){
                foreach($clientes as $item){
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
    
    public function indexAction(){
		
		// $this->_helper->layout->disableLayout();

        //Vou utilizar em todo o scopo, por isso declarei no topo
        $MMunicipios = new Application_Model_DbTable_Municipios();

        $dados = array();
        $codigoIbge = 0;
        $uf = 'GO';

        //Carrega os dados para montagem do campo uf
        $this->view->municipios_uf = $MMunicipios->getUf();

        //Carrega as cidades para montagem do campo cidades
        $this->view->municipios_codigo_ibge = $MMunicipios->getMunicipiosPelaUf($uf);

        $this->view->uf = $uf;
        $this->view->codigoIbge = ($codigoIbge == 0)?5208707:$codigoIbge; //Define o padrão como sendo Goiânia, pois o padrão será Goiás
        $this->view->dados = $dados;
        
    }
    
    public function filtrosAction(){
			$this->_helper->layout->disableLayout();
	}
    
    public function pesquisarAction(){ 
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            unset($parametros["clientes_status_input"]);
            unset($parametros["controller"]);
            unset($parametros["action"]);
            unset($parametros["module"]);
            
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($clientes) && !empty($clientes) && sizeof($clientes) > 0){
                foreach($clientes as $item){
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
        if($dados["clientes_cnpj"] == ""){
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
        //Verifica se a unidade já foi cadastrado
        if($dados["clientes_email"] != ""){
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"clientes_email", "operador"=>"=", "valor"=>$dados["clientes_email"]),
                        ));
            
            if(sizeof($clientes) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um cliente cadastrado com o e-mail informado.";
            }
            
        }

        if($dados["clientes_identificador"] != ""){
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"clientes_identificador", "operador"=>"=", "valor"=>$dados["clientes_identificador"]),
                        ));
            
            if(sizeof($clientes) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um cliente cadastrado com o identificador informado.";
            }
            
        }

        if($dados["clientes_cnpj"] != ""){
            $MClientes = new Application_Model_DbTable_Clientes();
            $clientes = $MClientes->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"clientes_cnpj", "operador"=>"=", "valor"=>$dados["clientes_cnpj"]),
                        ));

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
                $MClientes = new Application_Model_DbTable_Admin_Clientes();
                $res = $this->getRequest()->getPost();
                $dados["dadosForm"] = $res["dados"] ;
                $dados["dadosForm"]["clientes_status"] = 'Bloqueado';
                $dados["dadosForm"]["clientes_uf"] = $dados["dadosForm"]["municipios_uf"];
                $dados["dadosForm"]["clientes_cnpj"] = str_replace(array('.','/','-'), '', $dados["dadosForm"]["clientes_cnpj"]);

                //Remover alguns campos que não são necessários no modelo
                unset($dados["dadosForm"]["municipios_codigo_ibge"]);
                unset($dados["dadosForm"]["municipios_uf"]);
               
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    //Alterar o registro
                    if($dados["dadosForm"]["clientes_id"] > 0){
                        if($MClientes->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
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
                        
                        // if($MClientes->inserir($dados["dadosForm"])){
                        //     $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                        // }else{
                        //     $this->retorno['erro'][] = "Não foi possível inserir o registro.";
                        // }
                    }
                    
                }
                
            }catch (Exception $exc) {
                ChromePhp::log($exc);
                $this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
            }
            ChromePhp::log($this->retorno);
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        } else {
			//ChromePhp::log("Salgo");
		}
    }
    
    public function excluirAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Clientes", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {

                    $MClientes = new Application_Model_DbTable_Clientes();
                    $dados = $this->getRequest()->getPost();

                    $dados["clientes_status"] = "Inativo";                
                    $MClientes = new Application_Model_DbTable_Clientes();
                    $clientes = $MClientes->excluir($dados);
                    if($clientes)
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
    
