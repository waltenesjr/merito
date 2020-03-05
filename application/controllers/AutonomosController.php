<?php

class AutonomosController extends Zend_Controller_Action
{
    protected $_name = 'autonomos';
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
        if (!isset($this->session->_usuario_logado) && !isset($this->session->_usuario_logado) > true) {
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
        $this->view->autonomos_tipo = $params["autonomos_tipo"];
        
    }
    
    public function formAction(){
        $idForm = $this->getRequest()->getParam("autonomos_id");
        $this->view->autonomos_tipo = $this->getRequest()->getParam("autonomos_tipo");
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
            $dados['autonomos_id'] = 0;
            if($idForm > 0){
                //Pega os dados do registro para montar no formulário
                $MAutonomos = new Application_Model_DbTable_Autonomos();
                $autonomos = $MAutonomos->getRegistros(array("autonomos_id"=>$idForm));
                $dados = $autonomos[0];
                $codigoIbge = (!isset($dados["fk_municipios_codigo_ibge"]))?5208707:$dados["fk_municipios_codigo_ibge"];
                $uf = $MMunicipios->getUfPeloCodigoIbge($codigoIbge);

                if(isset($dados["autonomos_data_nascimento"])){
                    $arrDtNascimento = explode("-",$dados["autonomos_data_nascimento"]);
                    $dados["autonomos_data_nascimento"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }
                if(isset($dados["autonomos_dentrada"])){
                    $arrDtNascimento = explode("-",$dados["autonomos_dentrada"]);
                    $dados["autonomos_dentrada"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }
//                if(isset($dados["autonomos_dentrada"])){
//                    $arrDtNascimento = explode("-",$dados["autonomos_dentrada"]);
//                    $dados["autonomos_dentrada"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
//                }
                if(isset($dados["autonomos_dsaida"])){
                    $arrDtNascimento = explode("-",$dados["autonomos_dsaida"]);
                    $dados["autonomos_dsaida"] = $arrDtNascimento[2].'/'.$arrDtNascimento[1].'/'.$arrDtNascimento[0];
                }
            }
            
            $this->view->dados = $dados;

            //Carrega as unidades cadastradas no sistema
            $MUnidades = new Application_Model_DbTable_Unidades();
            $this->view->unidades = $MUnidades->getRegistros(array('unidades_id', 'unidades_nome'));

            //Carrega as funções cadastradas no sistema
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $this->view->funcoes = $MFuncoes->getRegistros(array('funcoes_id', 'funcoes_descricao'),array('funcoes_status'=>'Ativo'));

            //Carrega todas as permissões disponíveis
            //$MPermissoes = new Application_Model_DbTable_PermissoesAutonomos();
           // $this->view->permissoes = $MPermissoes->getAll();

            //Carrega as permissões do usuário
            //$this->view->autonomos_permissoes = $MPermissoes->getPermissoesPorAutonomosId($idForm);


            //------------------------ Municípios ----------------------------------
            //Carrega os dados para montagem do campo uf
            $this->view->municipios_uf = $MMunicipios->getUf();

            //Carrega as cidades para montagem do campo cidades
            $this->view->municipios_codigo_ibge = $MMunicipios->getMunicipiosPelaUf($uf);
            $this->view->uf = $uf;
            $this->view->codigoIbge = ($codigoIbge == 0)?5208707:$codigoIbge; //Define o padrão de cidade como sendo Goiânia, pois o padrão do estado será Goiás
            $this->view->dados = $dados;
            //----------------------------------------------------------------------
        }
    }
    
    public function filtrosAction($dados){ 
        
            $this->view->dados = $dados;

            //Carrega as unidades cadastradas no sistema
            $MUnidades = new Application_Model_DbTable_Unidades();
            $this->view->unidades = $MUnidades->getRegistros(array('unidades_id', 'unidades_nome'));
            
            //Carrega as funções cadastradas no sistema
            $MFuncoes = new Application_Model_DbTable_Funcoes();
            $this->view->funcoes = $MFuncoes->getRegistros(array('funcoes_id', 'funcoes_descricao'),array('funcoes_status'=>'Ativo'));
        
    }
    
    public function pesquisarAction(){ 
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();
            
            unset($parametros["autonomos_status_input"]);
            unset($parametros["fk_unidades_id_input"]);
            unset($parametros["fk_funcoes_id_input"]);
            
            $MAutonomos = new Application_Model_DbTable_Autonomos();
            $autonomos = $MAutonomos->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($autonomos) && !empty($autonomos) && sizeof($autonomos) > 0){
                foreach($autonomos as $item){
                    //Verifica se a imagem existe para montagem na grid
                    $img = 'imagens/sem_foto.png';
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/imagens/autonomos/profissional_'.$item["autonomos_id"].'.jpg'))
                        $img = 'imagens/autonomos/profissional_'.$item["autonomos_id"].'.jpg?rid='.rand(0,9999999);
                    $item['autonomos_img'] = $img;
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
    
    public function pesquisarCargoAction(){ 
        $this->retorno = array();
        try{
            // Pega os dados enviados pelo formulário
            $parametros = $this->getRequest()->getParams();

            
            $MAutonomos = new Application_Model_DbTable_Autonomos();
            $autonomos = $MAutonomos->getRegistrosFiltradosGrid($parametros);
            
            //Montando o registro pra facilitar o acesso no javascript com json
            if(isset($autonomos) && !empty($autonomos) && sizeof($autonomos) > 0){
                foreach($autonomos as $item){
                    //Verifica se a imagem existe para montagem na grid
                    $img = 'imagens/sem_foto.png';
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/imagens/autonomos/profissional_'.$item["autonomos_id"].'.jpg'))
                        $img = 'imagens/autonomos/profissional_'.$item["autonomos_id"].'.jpg?rid='.rand(0,9999999);
                    $item['autonomos_img'] = $img;
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
        //Valida o nome da autonomos
        if($dados["autonomos_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome do Profissional";
        }  
        
        //Valida a data de nascimento
        if($dados["autonomos_data_nascimento"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe uma data de nascimento válida.";
        }else{
        
            $arrDt = explode("/",$dados["autonomos_data_nascimento"]);
            if(!checkdate($arrDt[1],$arrDt[0],$arrDt[2])){
                $validacao = false;
                $this->retorno["erro"][] = "Informe uma data de nascimento válida.";
            }
        }
        
        //Valida a data de admissao
        if($dados["autonomos_dentrada"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe uma data de Entrada.";
        }else{
        
            $arrDt = explode("/",$dados["autonomos_dentrada"]);
            if(!checkdate($arrDt[1],$arrDt[0],$arrDt[2])){
                $validacao = false;
                $this->retorno["erro"][] = "Informe uma data de admissão válida.";
            }
        }
        
        //Verifica se a autonomos já foi cadastrado com o nome informado
        if($dados["autonomos_nome"] != ""){
            $MAutonomos = new Application_Model_DbTable_Autonomos();
            $autonomos = $MAutonomos->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"autonomos_id", "operador"=>"!=", "valor"=>$dados["autonomos_id"]),
                            array("condicao"=>"AND", "coluna"=>"autonomos_nome", "operador"=>"=", "valor"=>$dados["autonomos_nome"]),
                        ));
            
            if(sizeof($autonomos) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um profissional cadastrado com o nome informado.";
            }
            
        }
        
        //Verifica se a autonomos já foi cadastrado com o cnpj informado
        if($dados["autonomos_cnpj"] != ""){
            $MAutonomos = new Application_Model_DbTable_Autonomos();
            $autonomos = $MAutonomos->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"autonomos_id", "operador"=>"!=", "valor"=>$dados["autonomos_id"]),
                            array("condicao"=>"AND", "coluna"=>"autonomos_cnpj", "operador"=>"=", "valor"=>$dados["autonomos_cnpj"]),
                        ));
            
            if(sizeof($autonomos) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe um profissional cadastrado com o cpf informado.";
            }
            
        }
        
        
        
        if ($dados['fk_funcoes_id'] == "Selecione uma função") {
			$validacao = false;
            $this->retorno["erro"][] = "Informe a funçao Profissional";
		}
        if ($dados['autonomos_sexo'] == "Selecione uma opção") {
			$validacao = false;
            $this->retorno["erro"][] = "Informe o sexo do Profissional";
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
		ChromePhp::log("Entra");
        $this->retorno = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'salvar') {
            try {
                $MAutonomos = new Application_Model_DbTable_Autonomos();
                $dados = $this->getRequest()->getPost();
                
                //Remover alguns campos que não são necessários no modelo
                unset($dados["dadosForm"]["autonomos_status_input"]);
                unset($dados["dadosForm"]["fk_unidades_id_input"]);
                unset($dados["dadosForm"]["fk_funcoes_id_input"]);
                unset($dados["dadosForm"]["autonomos_file_upload"]);
                unset($dados["dadosForm"]["municipios_codigo_ibge"]);
                unset($dados["dadosForm"]["municipios_codigo_ibge_input"]);
                unset($dados["dadosForm"]["fk_municipios_codigo_ibge_input"]);
                unset($dados["dadosForm"]["municipios_uf"]);
                unset($dados["dadosForm"]["municipios_uf_input"]);
                unset($dados["dadosForm"]["autonomos_sexo_input"]);
                unset($dados["dadosForm"]["autonomos_cargo_input"]);
                unset($dados["dadosForm"]["autonomos_historico_id"]);
                unset($dados["dadosForm"]["autonomos_historico_data"]);
                unset($dados["dadosForm"]["autonomos_historico_descricao"]);
                
                if($dados["dadosForm"]["autonomos_senha"] != "")
                    $dados["dadosForm"]["autonomos_senha"] = sha1($dados["dadosForm"]["autonomos_senha"]);
                else
                    unset($dados["dadosForm"]["autonomos_senha"]);
                
                //Tratamento de valores em alguns campos
                $dados["dadosForm"]["autonomos_salario"]        = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["autonomos_salario"])));
                $dados["dadosForm"]["autonomos_alimentacao"]    = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["autonomos_alimentacao"])));
                $dados["dadosForm"]["autonomos_transporte"]     = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["autonomos_transporte"])));
                $dados["dadosForm"]["autonomos_impostos"]       = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["autonomos_impostos"])));
                $dados["dadosForm"]["autonomos_porcentagem"]    = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["autonomos_porcentagem"])));
                $dados["dadosForm"]["autonomos_comissao"]       = str_replace(",", ".", str_replace(".", "", str_replace("R$ ", "", $dados["dadosForm"]["autonomos_comissao"])));
                
                //Valida os dados do formulário
                if($this->validarDadosFormulario($dados["dadosForm"])){
                    
                    $arrDtNascimento = explode("/",$dados["dadosForm"]["autonomos_data_nascimento"]);
                    $dados["dadosForm"]["autonomos_data_nascimento"]= $arrDtNascimento[2].'-'.$arrDtNascimento[1].'-'.$arrDtNascimento[0];
                    
//                    $arrDtAdmissao = explode("/",$dados["dadosForm"]["autonomos_dentrada"]);
//                    $dados["dadosForm"]["autonomos_dentrada"]= $arrDtAdmissao[2].'-'.$arrDtAdmissao[1].'-'.$arrDtAdmissao[0];

					if ($dados["dadosForm"]["autonomos_dentrada"] != "") {
						$arrDtEntrada = explode("/",$dados["dadosForm"]["autonomos_dentrada"]);
						$dados["dadosForm"]["autonomos_dentrada"]= $arrDtEntrada[2].'-'.$arrDtEntrada[1].'-'.$arrDtEntrada[0];
					} else {
						unset ($dados["dadosForm"]["autonomos_dentrada"]);
					}
					
					if ($dados["dadosForm"]["autonomos_dsaida"] != "") {
						$arrDtSaida = explode("/",$dados["dadosForm"]["autonomos_dsaida"]);
						$dados["dadosForm"]["autonomos_dsaida"]= $arrDtSaida[2].'-'.$arrDtSaida[1].'-'.$arrDtSaida[0];
					} else {
						unset ($dados["dadosForm"]["autonomos_dsaida"]);
					}
					ChromePhp::log($dados);
                    
                    //Alterar o registro
                    if($dados["dadosForm"]["autonomos_id"] > 0){
                        $idProfissional = $dados["dadosForm"]["autonomos_id"];
                        if($MAutonomos->atualizar($dados["dadosForm"])){
                            $this->retorno['sucesso'][] = "Registro alterado com sucesso.";
                        }else{
                            $idProfissional = 0;
                            $this->retorno['erro'][] = "Não foi possível alterar o registro.";
                        }

                    //Inserir o registro
                    }else{
                        unset($dados["dadosForm"]["autonomos_id"]);
                        $idProfissional = $MAutonomos->inserir($dados["dadosForm"]);
                        if($idProfissional){
                            $this->retorno['sucesso'][] = "Registro inserido com sucesso.";
                        }else{
                            $idProfissional = 0;
                            $this->retorno['erro'][] = "Não foi possível inserir o registro.";
                        }
                    }
                    
                    
                    if($idProfissional > 0 && isset($dados["permissoes"])){
                        //Inserir/Remover as permissões
                        $MPermissoes = new Application_Model_DbTable_PermissoesAutonomos();
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
        $this->retorno = array();
        $permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais", "Excluir");
        if($permissao['status'] == true){
            if ($this->getRequest()->isPost() && $this->getRequest()->getParam('action') == 'excluir') {
                try {

                    $MAutonomos = new Application_Model_DbTable_Autonomos();
                    $dados = $this->getRequest()->getPost();

                    $dados["autonomos_status"] = "Inativo";                
                    $MAutonomos = new Application_Model_DbTable_Autonomos();
                    $autonomos = $MAutonomos->excluir($dados);
                    if($autonomos)
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
        try{
            $idForm = $this->getRequest()->getParam('autonomos_id');
            $handle = new Upload($_FILES['uploadfile']);
            // Verifica se o arquivo foi carregado corretamente
            if ($handle->uploaded) 
            {
                // Definimos as configurações desejadas da imagem maior
                $handle->image_resize            = true;
                $handle->image_ratio_y           = true;
                $handle->image_ratio_x           = true;
                $handle->image_x                 = 400;
                $handle->image_y                 = 350;
                $handle->file_overwrite          = true;
                $handle->file_auto_rename        = false;
                $handle->file_new_name_ext       = 'jpg';
                $handle->file_new_name_body      = 'profissional_'.$idForm;

                // Definimos a pasta para onde a imagem maior será armazenada
                $handle->Process($_SERVER['DOCUMENT_ROOT'].'/imagens/autonomos/');

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
	$this->retorno = array();
	try{

	    $dados = $this->getRequest()->getParam('fk_autonomos_id');

            $MAutonomos = new Application_Model_DbTable_Autonomos();
            $autonomos = $MAutonomos->getSalario($dados);
	    
	    $this->view->salario = $autonomos['0']['autonomos_salario'] ;
            $this->view->alimentacao = $autonomos['0']['autonomos_alimentacao'] ;
            $this->view->transporte = $autonomos['0']['autonomos_transporte'] ;
            $this->view->imposto = $autonomos['0']['autonomos_impostos'] ;
            $this->view->obs = $autonomos['0']['autonomos_obs'] ;

            if($autonomos)
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
		$this->retorno = array();
		try{

			$dados = $this->getRequest()->getParam('fk_autonomos_id');

			

            $MAutonomos = new Application_Model_DbTable_Autonomos();
            $autonomos = $MAutonomos->getProf($dados);
            
            //ChromePhp::log($autonomos);
	    
			
		}catch(Exception $e){
            ChromePhp::log($e);
            $this->retorno['erro'][] = 'Não foi possível realizar o upload do arquivo. Erro interno.';
        }
        $this->view->dados = Zend_Json_Encoder::encode($autonomos[0]);
    }

}

