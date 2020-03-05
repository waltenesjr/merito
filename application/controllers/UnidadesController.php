<?php

class UnidadesController extends Zend_Controller_Action
{
    protected $_name = 'unidades';
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
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Unidades", "Visualizar");
        // action body
        $this->view->modulo = "Unidades";

		$parametros = $this->getRequest()->getParams();

        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            //$parametros = null;

            
            $MUnidades = new Application_Model_DbTable_Unidades();
            $unidades = $MUnidades->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($unidades) && !empty($unidades) && sizeof($unidades) > 0){
                foreach($unidades as $item){
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
		
		
        $idForm = $this->getRequest()->getParam("unidades_id");
        
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Unidades", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Unidades", "Alterar");
        
        
        if($this->view->permissao["status"] == true){
            //Vou utilizar em todo o scopo, por isso declarei no topo
            $MMunicipios = new Application_Model_DbTable_Municipios();

            $dados = array();
            $codigoIbge = 0;
            $uf = 'GO';
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário se for uma alteração
                $MUnidades = new Application_Model_DbTable_Unidades();
                $unidade = $MUnidades->getRegistros(array("unidades_id"=>$idForm));            
                $dados = $unidade[0];
                $codigoIbge = (!isset($dados["fk_municipios_codigo_ibge"]))?5208707:$dados["fk_municipios_codigo_ibge"];
                $uf = $MMunicipios->getUfPeloCodigoIbge($codigoIbge);
            }

            //Carrega os dados para montagem do campo uf
            $this->view->municipios_uf = $MMunicipios->getUf();

            //Carrega as cidades para montagem do campo cidades
            $this->view->municipios_codigo_ibge = $MMunicipios->getMunicipiosPelaUf($uf);

            $this->view->uf = $uf;
            $this->view->codigoIbge = ($codigoIbge == 0)?5208707:$codigoIbge; //Define o padrão como sendo Goiânia, pois o padrão será Goiás
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
            unset($parametros["unidades_status_input"]);
            unset($parametros["controller"]);
            unset($parametros["action"]);
            unset($parametros["module"]);
            
            $MUnidades = new Application_Model_DbTable_Unidades();
            $unidades = $MUnidades->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($unidades) && !empty($unidades) && sizeof($unidades) > 0){
                foreach($unidades as $item){
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
        if($dados["unidades_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome da Unidade";
        }  
        //Verifica se a unidade já foi cadastrado
        if($dados["unidades_nome"] != ""){
            $MUnidades = new Application_Model_DbTable_Unidades();
            $unidades = $MUnidades->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"unidades_id", "operador"=>"!=", "valor"=>$dados["unidades_id"]),
                            array("condicao"=>"AND", "coluna"=>"unidades_nome", "operador"=>"=", "valor"=>$dados["unidades_nome"]),
                        ));
            
            if(sizeof($unidades) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe uma unidade cadastrada com o nome informado.";
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
                $MUnidades = new Application_Model_DbTable_Unidades();
                $res = $this->getRequest()->getPost();
                $dados["dadosForm"] = $res["dados"] ;

                //Remover alguns campos que não são necessários no modelo
                unset($dados["dadosForm"]["unidades_status_input"]);
                unset($dados["dadosForm"]["municipios_codigo_ibge"]);
                unset($dados["dadosForm"]["municipios_codigo_ibge_input"]);
                unset($dados["dadosForm"]["municipios_uf_input"]);
                unset($dados["dadosForm"]["municipios_uf"]);
               
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    //Alterar o registro
                    if($dados["dadosForm"]["unidades_id"] > 0){
                        if($MUnidades->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["unidades_id"]);
                        
                        if($MUnidades->inserir($dados["dadosForm"])){
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
    
    public function excluirAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Unidades", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {

                    $MUnidades = new Application_Model_DbTable_Unidades();
                    $dados = $this->getRequest()->getPost();

                    $dados["unidades_status"] = "Inativo";                
                    $MUnidades = new Application_Model_DbTable_Unidades();
                    $unidades = $MUnidades->excluir($dados);
                    if($unidades)
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
    
