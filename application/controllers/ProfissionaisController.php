<?php

class ProfissionaisController extends Zend_Controller_Action
{
    protected $_name = 'profissionais';
    protected $_schema = 'dados';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
    }
    
    protected function initAppModel() {

        $accessController = str_replace('-', "", $this->getRequest()->getControllerName());
        $className = "Application_Model_DbTable_" . ucfirst($accessController);

        if (!class_exists($className)):
            $this->view->msg = 'ERROR: App not found (' . $className . ').';
        endif;

        return new $className();
        
    }
    
    public function preDispatch() {
        //Carrega a sessão
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');
        //Verifica sessão do usuário
        //if (!isset($this->session->_usuario_logado) && !isset($this->session->_usuario_logado)) {
		if (!isset($this->session->_profissionais_id)) {
            Zend_Session::destroy();
            $this->_redirect("/login");
        }
    }

    public function indexAction()
    {
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais", "Visualizar");
        
        // action body
        $this->view->modulo = "Profissionais";
        $params = $this->getRequest()->getParams();
        
        if (isset($params["profissionais_tipo"]))
			$this->view->profissionais_tipo = $params["profissionais_tipo"];
        
        $this->retorno = array();
        try{
            //Carrega as unidades cadastradas no sistema
            $MUnidades = new Application_Model_DbTable_Unidades();
            $this->view->unidades = $MUnidades->getRegistros(array('unidades_id', 'unidades_nome'), $show_und);
            
            //Carrega as funções cadastradas no sistema
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $this->view->funcoes = $MFuncoes->getRegistros(array('funcoes_id', 'funcoes_descricao'),array('funcoes_status'=>'Ativo', 'funcao_autonomo'=>'N'));

            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();

            unset($parametros["profissionais_status_input"]);
            unset($parametros["fk_unidades_id_input"]);
            unset($parametros["fk_funcoes_id_input"]);
            
            $chivi = "0";

			if (isset($this->session->_permissoes)) {
				foreach ($this->session->_permissoes as $item => $value){
					
					if ($value['permissoes_nome'] == "Listar todas unidades") {
						$chivi = "1";
						ChromePhp::log($value);
					}
				}
            }
            if ($this->session->_profissionais_cargo == "administrador")
				$chivi = "1";
			
			if ($chivi == "0" && isset($this->session->_permissoes))
				$parametros['show_und'] = $value['fk_unidades_id'];
            
            $MProfissionais = new Application_Model_DbTable_Profissionais();
            $profissionais = $MProfissionais->getRegistrosFiltradosGrid($parametros);
            
            $MProfissionaissupervisor = new Application_Model_DbTable_Profissionaissupervisor();
			$Profissionaissupervisor = $MProfissionaissupervisor->getProfissionais($this->session->_profissionais_id);

            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($profissionais) && !empty($profissionais) && sizeof($profissionais) > 0){
                foreach($profissionais as $item){
					$esta = 0 ;
					foreach($Profissionaissupervisor as $itemS => $valueS) {
						if ($item['profissionais_id'] == $valueS['fk_usuarios_id'])
							$esta = 1 ;
					}
						if ($esta == 1 || $this->session->_profissionais_cargo == "" || $this->session->_profissionais_cargo == "gerente" || $this->session->_profissionais_cargo == "administrador" ) {
                    //Verifica se a imagem existe para montagem na grid
							$img = 'imagens/sem_foto.png';
							if(file_exists($_SERVER['DOCUMENT_ROOT'].'/imagens/profissionais/'.explode('_', $_SESSION["cliente_schema"])[1].'_profissional_'.$item["profissionais_id"].'.jpg'))
								$img = 'imagens/profissionais/'.explode('_', $_SESSION["cliente_schema"])[1].'_profissional_'.$item["profissionais_id"].'.jpg?rid='.rand(0,9999999);
							$item['profissionais_img'] = $img;
							$this->retorno["dados"][] = $item;
						}
					
                }
                $this->retorno["sucesso"][] = "Dados carregados com sucesso!";
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
		//$this->_helper->layout->setLayout('layout_inc');
		
        $idForm = $this->getRequest()->getParam("profissionais_id");
        $this->view->profissionais_tipo = $this->getRequest()->getParam("profissionais_tipo");
        
        //Tratamento de permissões
        if($idForm == 0)
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais", "Inserir");
        else
            $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais", "Alterar");
        
        if($this->view->permissao["status"] == true){
            $MMunicipios = new Application_Model_DbTable_Municipios();

            $dados = array();
            $codigoIbge = 0;
            $uf = 'GO';
            $dados['profissionais_id'] = 0;
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário
                $MProfissionais = new Application_Model_DbTable_Profissionais();
                $profissionais = $MProfissionais->getRegistros(array("profissionais_id"=>$idForm));
                $dados = $profissionais[0];
                $codigoIbge = (!isset($dados["fk_municipios_codigo_ibge"]))?5208707:$dados["fk_municipios_codigo_ibge"];
                $uf = $MMunicipios->getUfPeloCodigoIbge($codigoIbge);

                if(isset($dados["profissionais_data_nascimento"])){
                    $arrDtNascimento = explode("-",$dados["profissionais_data_nascimento"]);
                    $dados["profissionais_data_nascimento"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }
                if(isset($dados["profissionais_data_admissao"])){
                    $arrDtNascimento = explode("-",$dados["profissionais_data_admissao"]);
                    $dados["profissionais_data_admissao"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }
                
                $this->view->profissionais_unidade = $MProfissionais->getRegistrosFiltradosGrid(array("fk_unidades_id"=>$dados['fk_unidades_id']));
                
                $MProfissionaissupervisor = new Application_Model_DbTable_Profissionaissupervisor();
				$this->view->Profissionaissupervisor = $MProfissionaissupervisor->getProfissionais($dados['profissionais_id']);
                
            }
            
            //$this->view->dados = $dados;

            //Carrega as unidades cadastradas no sistema
            $MUnidades = new Application_Model_DbTable_Unidades();
            $this->view->unidades = $MUnidades->getRegistros(array('unidades_id', 'unidades_nome'));

            //Carrega as funções cadastradas no sistema
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $this->view->funcoes = $MFuncoes->getRegistros(array('funcoes_id', 'funcoes_descricao'),array('funcoes_status'=>'Ativo', 'funcao_autonomo'=>'N'));

            //Carrega todas as permissões disponíveis
            $MPermissoes = new Application_Model_DbTable_Permissoes();
            $this->view->permissoes = $MPermissoes->getAll();

            //Carrega as permissões do usuário
            $this->view->profissionais_permissoes = $MPermissoes->getPermissoesPorProfissionaisId($idForm);


            //------------------------ Municípios ----------------------------------
            //Carrega os dados para montagem do campo uf
            $this->view->municipios_uf = $MMunicipios->getUf();

            //Carrega as cidades para montagem do campo cidades
            $this->view->municipios_codigo_ibge = $MMunicipios->getMunicipiosPelaUf($uf);
            $this->view->uf = $uf;
            $this->view->codigoIbge = ($codigoIbge == 0)?5208707:$codigoIbge; //Define o padrão de cidade como sendo Goiânia, pois o padrão do estado será Goiás
            $this->view->dados = $dados;
            //----------------------------------------------------------------------
            
			if($idForm > 0){
				$MProfissionaisHistorico = new Application_Model_DbTable_Profissionaishistorico();
				$profissionais_historico = $MProfissionaisHistorico->getRegistrosFiltradosGrid(array(), array('fk_profissionais_id'=>$idForm));
                
				
				//Montando o registro pra facilitar o acesso no javascript com json
				if(isset($profissionais_historico) && !empty($profissionais_historico) && sizeof($profissionais_historico) > 0){
					foreach($profissionais_historico as $item){
						$arr = explode("-",$item["profissionais_historico_data"]);
						$item["profissionais_historico_data"] = $arr[2]."/".$arr[1]."/".$arr[0];
						$item["profissionais_historico_descricao"] = substr($item["profissionais_historico_descricao"],0,50);
						$hist[] = $item;
					}
					//$this->retorno["sucesso"][] = "Grid de dados atualizada com sucesso!";
				}else{
					//$this->retorno["alerta"][] = "Nenhum registro encontrado.";
				}
				if (isset($hist))
					$this->view->hist = $hist;
           }
            
        }
    }
    
    public function filtrosAction($dados){ 
        
			$this->_helper->layout->disableLayout();
        
            $this->view->dados = $dados;

			$chivi = "0";
			$show_und = null ;

			if (isset($this->session->_permissoes)) {
				foreach ($this->session->_permissoes as $item => $value){
					
					if ($value['permissoes_nome'] == "Listar todas unidades") {
						$chivi = "1";
					}
				}
            }
			
			if ($chivi == "0" && isset($this->session->_permissoes))
				$show_und = $value['fk_unidades_id'];

            //Carrega as unidades cadastradas no sistema
            $MUnidades = new Application_Model_DbTable_Unidades();
            $this->view->unidades = $MUnidades->getRegistros(array('unidades_id', 'unidades_nome'), $show_und);
            
            //Carrega as funções cadastradas no sistema
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $this->view->funcoes = $MFuncoes->getRegistros(array('funcoes_id', 'funcoes_descricao'),array('funcoes_status'=>'Ativo', 'funcao_autonomo'=>'N'));
        
    }
    
    public function pesquisarAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();

            unset($parametros["profissionais_status_input"]);
            unset($parametros["fk_unidades_id_input"]);
            unset($parametros["fk_funcoes_id_input"]);
            
            $chivi = "0";

			if (isset($this->session->_permissoes)) {
				foreach ($this->session->_permissoes as $item => $value){
					
					if ($value['permissoes_nome'] == "Listar todas unidades") {
						$chivi = "1";
					}
				}
            }
			if ($this->session->_profissionais_cargo == "administrador")
				$chivi = "1";
				
			if ($chivi == "0" && isset($this->session->_permissoes))
				$parametros['show_und'] = $value['fk_unidades_id'];
            
            $MProfissionais = new Application_Model_DbTable_Profissionais();
            $profissionais = $MProfissionais->getRegistrosFiltradosGrid($parametros);

			/* EMIKI */

            $MProfissionaissupervisor = new Application_Model_DbTable_Profissionaissupervisor();
			$Profissionaissupervisor = $MProfissionaissupervisor->getProfissionais($this->session->_profissionais_id);
			
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($profissionais) && !empty($profissionais) && sizeof($profissionais) > 0){
                foreach($profissionais as $item){
					$esta = 0 ;
					foreach($Profissionaissupervisor as $itemS => $valueS) {
						if ($item['profissionais_id'] == $valueS['fk_usuarios_id'])
							$esta = 1 ;
					}
						if ($esta == 1 || $this->session->_profissionais_cargo == "" || $this->session->_profissionais_cargo == "gerente" || $this->session->_profissionais_cargo == "administrador" ) {
                    //Verifica se a imagem existe para montagem na grid
							$img = 'imagens/sem_foto.png';
							if(file_exists($_SERVER['DOCUMENT_ROOT'].'/imagens/profissionais/'.explode('_', $_SESSION["cliente_schema"])[1].'_profissional_'.$item["profissionais_id"].'.jpg'))
								$img = 'imagens/profissionais/'.explode('_', $_SESSION["cliente_schema"])[1].'_profissional_'.$item["profissionais_id"].'.jpg?rid='.rand(0,9999999);
							$item['profissionais_img'] = $img;
							$this->retorno["dados"][] = $item;
						}
					
                }
                if ( isset($this->retorno["dados"]) && count($this->retorno["dados"]) > 0)
					$this->retorno["sucesso"][] = "Dados carregados com sucesso!";
				else
					$this->retorno["alerta"][] = "Nenhum registro encontrado.";
            }else{
                $this->retorno["alerta"][] = "Nenhum registro encontrado.";
            }
        }catch(Exception $e){
            $this->retorno["erro"][] = 'Não foi possível executar a operação solicitada';
        }
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    
    public function pesquisarCargoAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();

            $chivi = "0";

			if (isset($this->session->_permissoes)) {
				foreach ($this->session->_permissoes as $item => $value){
					
					if ($value['permissoes_nome'] == "Listar todas unidades") {
						$chivi = "1";
						ChromePhp::log($value);
					}
				}
            }
			
			if ($chivi == "0" && isset($this->session->_permissoes))
				$parametros['show_und'] = $value['fk_unidades_id'];
            
            $MProfissionais = new Application_Model_DbTable_Profissionais();
            $profissionais = $MProfissionais->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($profissionais) && !empty($profissionais) && sizeof($profissionais) > 0){
                foreach($profissionais as $item){
                    //Verifica se a imagem existe para montagem na grid
                    $img = 'imagens/sem_foto.png';
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/imagens/profissionais/'.explode('_', $_SESSION["cliente_schema"])[1].'_profissional_'.$item["profissionais_id"].'.jpg'))
                        $img = 'imagens/profissionais/'.explode('_', $_SESSION["cliente_schema"])[1].'_profissional_'.$item["profissionais_id"].'.jpg?rid='.rand(0,9999999);
                    $item['profissionais_img'] = $img;
                    $this->retorno["dados"][] = $item;
                }
                $this->retorno["sucesso"][] = "Dados carregados com sucesso!";
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
        
        //Valida o nome da profissionais
        if($dados["profissionais_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome do Profissional";
        }  
        
        if($dados["fk_funcoes_id"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a funçao do Profissional";
        }  
        
       
        //Valida a data de nascimento
        if($dados["profissionais_data_nascimento"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe uma data de nascimento válida.";
        }else{
        
            $arrDt = explode("/",$dados["profissionais_data_nascimento"]);
            if(!checkdate($arrDt[1],$arrDt[0],$arrDt[2])){
                $validacao = false;
                $this->retorno["erro"][] = "Informe uma data de nascimento válida.";
            }
        }
        
        //Valida a data de admissao
        if($dados["profissionais_data_admissao"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe uma data de admissão.";
        }else{
        
            $arrDt = explode("/",$dados["profissionais_data_admissao"]);
            if(!checkdate($arrDt[1],$arrDt[0],$arrDt[2])){
                $validacao = false;
                $this->retorno["erro"][] = "Informe uma data de admissão válida.";
            }
        }
        
        //Verifica se a profissionais já foi cadastrado com o nome informado
        if($dados["profissionais_nome"] != ""){
            $MProfissionais = new Application_Model_DbTable_Profissionais();
            $profissionais = $MProfissionais->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"profissionais_id", "operador"=>"!=", "valor"=>$dados["profissionais_id"]),
                            array("condicao"=>"AND", "coluna"=>"profissionais_nome", "operador"=>"=", "valor"=>$dados["profissionais_nome"]),
                        ));
            
            if(sizeof($profissionais) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um profissional cadastrado com o nome informado.";
            }
            
        }
        
        //Verifica se a profissionais já foi cadastrado com o cpf informado
        if($dados["profissionais_cpf"] != ""){
            $MProfissionais = new Application_Model_DbTable_Profissionais();
            $profissionais = $MProfissionais->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"profissionais_id", "operador"=>"!=", "valor"=>$dados["profissionais_id"]),
                            array("condicao"=>"AND", "coluna"=>"profissionais_cpf", "operador"=>"=", "valor"=>$dados["profissionais_cpf"]),
                        ));
            
            if(sizeof($profissionais) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um profissional cadastrado com o cpf informado.";
            }
            
        }
            
        
        return $validacao;
        
    }
    
    /**
     * @name salvar
     * Observação importante para este método, é que ele pode tanto inserir um novo
     * registro quanto alterar um registro existente, e isso vai depender somente
     * da primary key passada pelo formulário, onde se tiver 0 (zero) será feito
     * uma nova inserção no banco de dados, caso seja maior que 0 (zero), a alteração
     * será aplicada a registro cujo id (primary key) foi passada.
     */
    public function salvarAction(){
		
		$this->_helper->layout->disableLayout();
        
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'salvar') {
            try {
                $MProfissionais = new Application_Model_DbTable_Profissionais();
                $dados = $this->getRequest()->getPost();
                
                ChromePhp::log($dados["dadosForm"]);
                
                //Remover alguns campos que não são necessários no modelo
                unset($dados["dadosForm"]["profissionais_status_input"]);
                unset($dados["dadosForm"]["fk_unidades_id_input"]);
                unset($dados["dadosForm"]["fk_funcoes_id_input"]);
                unset($dados["dadosForm"]["profissionais_file_upload"]);
                //unset($dados["dadosForm"]["municipios_codigo_ibge"]);
                unset($dados["dadosForm"]["municipios_codigo_ibge_input"]);
                unset($dados["dadosForm"]["fk_municipios_codigo_ibge_input"]);
                unset($dados["dadosForm"]["municipios_uf"]);
                unset($dados["dadosForm"]["municipios_uf_input"]);
                unset($dados["dadosForm"]["profissionais_sexo_input"]);
                unset($dados["dadosForm"]["profissionais_cargo_input"]);
                unset($dados["dadosForm"]["profissionais_historico_id"]);
                unset($dados["dadosForm"]["profissionais_historico_data"]);
                unset($dados["dadosForm"]["profissionais_historico_descricao"]);
                unset($dados["dadosForm"]["profissionais_unidade"]);
                
                unset($dados["dadosForm"]["dataTables-historico-profissionais_length"]);
                
                
                if($dados["dadosForm"]["profissionais_senha"] != "")
                    $dados["dadosForm"]["profissionais_senha"] = sha1($dados["dadosForm"]["profissionais_senha"]);
                else
                    unset($dados["dadosForm"]["profissionais_senha"]);
                
                //Tratamento de valores em alguns campos
                $dados["dadosForm"]["profissionais_salario"]        = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["profissionais_salario"])));
                $dados["dadosForm"]["profissionais_alimentacao"]    = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["profissionais_alimentacao"])));
                $dados["dadosForm"]["profissionais_transporte"]     = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["profissionais_transporte"])));
                $dados["dadosForm"]["profissionais_impostos"]       = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["profissionais_impostos"])));
                
                if (!isset($dados["dadosForm"]["profissionais_tipo"]) || $dados["dadosForm"]["profissionais_tipo"] == "")
					$dados["dadosForm"]["profissionais_tipo"] = "colaborador";
                
                //ChromePhp::log($dados["dadosForm"]);
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    
                    if ($dados["dadosForm"]["profissionais_salario"] == "")
						$dados["dadosForm"]["profissionais_salario"] = "0.00" ;
                    if ($dados["dadosForm"]["profissionais_alimentacao"] == "")
						$dados["dadosForm"]["profissionais_alimentacao"] = "0.00" ;
                    if ($dados["dadosForm"]["profissionais_transporte"] == "")
						$dados["dadosForm"]["profissionais_transporte"] = "0.00" ;
                    if ($dados["dadosForm"]["profissionais_impostos"] == "")
						$dados["dadosForm"]["profissionais_impostos"] = "0.00" ;
                    
                    $arrDtNascimento = explode("/",$dados["dadosForm"]["profissionais_data_nascimento"]);
                    $dados["dadosForm"]["profissionais_data_nascimento"]= $arrDtNascimento[2].'-'.$arrDtNascimento[1].'-'.$arrDtNascimento[0];
                    
                    $arrDtAdmissao = explode("/",$dados["dadosForm"]["profissionais_data_admissao"]);
                    $dados["dadosForm"]["profissionais_data_admissao"]= $arrDtAdmissao[2].'-'.$arrDtAdmissao[1].'-'.$arrDtAdmissao[0];
                    
                    //Alterar o registro
                    if($dados["dadosForm"]["profissionais_id"] > 0){
                        $idProfissional = $dados["dadosForm"]["profissionais_id"];
                        if($MProfissionais->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $idProfissional = 0;
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["profissionais_id"]);
                        $idProfissional = $MProfissionais->inserir($dados["dadosForm"]);
                        if($idProfissional){
                            $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                        }else{
                            $idProfissional = 0;
                            $this->retorno['erro'][] = "Não foi possível inserir o registro.";
                        }
                    }
                    
                    
                    if($idProfissional > 0 && isset($dados["permissoes"])){
                        //Inserir/Remover as permissões
                        $MPermissoes = new Application_Model_DbTable_Permissoes();
                        $MPermissoes->adicionarRemoverPermissoes($dados["permissoes"],$idProfissional);
                    }
                }
                
            }catch (Exception $exc) {
                $this->retorno['erro'][] = 'Desculpe. Ocorreu uma falha ao recuperar informações do sistema.';
            }
            
            $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
        }
    }
    
    public function excluirAction(){
		
		$this->_helper->layout->disableLayout();
		
        $this->retorno = array();
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {

                    $MProfissionais = new Application_Model_DbTable_Profissionais();
                    $dados = $this->getRequest()->getPost();

                    $dados["profissionais_status"] = "Inativo";                
                    $MProfissionais = new Application_Model_DbTable_Profissionais();
                    $profissionais = $MProfissionais->excluir($dados);
                    if($profissionais)
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
    
    public function carregarImagensAction(){
		
		$this->_helper->layout->disableLayout();
		
		ChromePhp::log("Entra");
        try{
            $idForm = $this->getRequest()->getParam('profissionais_id');
            
            ChromePhp::log("idForm: " . $idForm);
            
            $handle = new Upload($_FILES['uploadfile']);
            // Verifica se o arquivo foi carregado corretamente
            if ($handle->uploaded) 
            {
                // Definimos as configurações desejadas da imagem maior
                $handle->image_resize            = true;
                $handle->image_ratio_y           = true;
                $handle->image_ratio_x           = true;
                $handle->image_x                 = 250;
                $handle->image_y                 = 250;
                $handle->file_overwrite          = true;
                $handle->file_auto_rename        = false;
                $handle->file_new_name_ext       = 'jpg';
                $handle->file_new_name_body      = explode('_', $_SESSION["cliente_schema"])[1].'_profissional_'.$idForm;

                // Definimos a pasta para onde a imagem maior será armazenada
                $handle->Process($_SERVER['DOCUMENT_ROOT'].'/imagens/profissionais/');

                // Em caso de sucesso no upload podemos fazer outras ações como insert em um banco de cados
                if($handle->processed) 
                {
                    $this->retorno['sucesso'][] = "Imagem carregada com sucesso!";
                }else{
                    $this->retorno['erro'][] = $handle->error;
                }
                
                $this->retorno['arquivo'] = $handle->file_dst_name;
                // Excluir arquivos temporarios
                $handle->Clean();
                ChromePhp::log($this->retorno);
                
            }else{
                $this->retorno['erro'][] = $handle->error;
            } 
        }catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno['erro'][] = 'Não foi possível realizar o upload do arquivo. Erro interno.';
        }
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function mostrarSalarioAction () {
		
		$this->_helper->layout->disableLayout();
		
	$this->retorno = array();
	try{

	    $dados = $this->getRequest()->getParam('fk_profisionais_id');

            $MProfissionais = new Application_Model_DbTable_Profissionais();
            $profissionais = $MProfissionais->getSalario($dados);
	    
	    $this->view->salario = $profissionais['0']['profissionais_salario'] ;
            $this->view->alimentacao = $profissionais['0']['profissionais_alimentacao'] ;
            $this->view->transporte = $profissionais['0']['profissionais_transporte'] ;
            $this->view->imposto = $profissionais['0']['profissionais_impostos'] ;
            $this->view->obs = $profissionais['0']['profissionais_obs'] ;

            if($profissionais)
	            $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
            else
                $this->retorno['erro'][] = 'Não foi possível excluir o registro!';

		}catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno['erro'][] = 'Não foi possível realizar o upload do arquivo. Erro interno.';
        }
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function getProfAction () {
		
		$this->_helper->layout->disableLayout();
		
		$this->retorno = array();
		try{

			$dados = $this->getRequest()->getParam('fk_profissionais_id');

            $MProfissionais = new Application_Model_DbTable_Profissionais();
            $profissionais = $MProfissionais->getProf($dados);
	    
			
		}catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno['erro'][] = 'Não foi possível realizar o upload do arquivo. Erro interno.';
        }
        $this->view->dados = Zend_Json_Encoder::encode($profissionais[0]);
    }
    
    public function getProfissionaisSupervisorAction () {
		$this->_helper->layout->disableLayout();
		$this->retorno = array();
		
		$dados['fk_profissionais_id'] = $this->getRequest()->getParam('fk_profissionais_id');
		
		$MProfissionaissupervisor = new Application_Model_DbTable_Profissionaissupervisor();
		
		$this->retorno = $MProfissionaissupervisor->getProfissionais($dados['fk_profissionais_id']);
		
		$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
		
	}
    
    public function salvarProfissionalSupervisorAction () {
		$this->_helper->layout->disableLayout();
		$this->retorno = array();
		
		$dados['fk_usuarios_id'] = $this->getRequest()->getParam('profissional');
		$dados['profissionais_supervisor_nome'] = $this->getRequest()->getParam('nome');
		$dados['fk_supervisor_id'] = $this->getRequest()->getParam('supervisor');
		try {
			$MProfissionaissupervisor = new Application_Model_DbTable_Profissionaissupervisor();
			
			if($MProfissionaissupervisor->inserir($dados))
	            $this->retorno['sucesso'][] = 'Registro inserido com sucesso!';
            else
                $this->retorno['erro'][] = 'Não foi possível inserir o registro!';
			
		}catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno['erro'][] = 'Não foi possível inserir o registro!';
        }		
		$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
		
	}

    public function excluirProfissionalSupervisorAction () {
		$this->_helper->layout->disableLayout();
		$this->retorno = array();
		
		$dados['id'] = $this->getRequest()->getParam('id');

		try {
			$MProfissionaissupervisor = new Application_Model_DbTable_Profissionaissupervisor();
			
			if($MProfissionaissupervisor->excluir($dados))
	            $this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
            else
                $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
			
		}catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno['erro'][] = 'Não foi possível excluir o registro!';
        }		
		$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
		
	}

}

