<?php

class MetasautonomosController extends Zend_Controller_Action
{
    protected $_name = 'metasautonomos';
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
        $this->view->permissao = $this->_helper->Validacoes->validarPermissoesUsuarioLogado($this->session->_permissoes, "Profissionais Metas", "Visualizar");
        $this->view->modulo = "Metas";
        
        ChromePhp::log($this->session->_permissoes);
        
        $this->view->idProf = $this->getRequest()->getParam("fk_autonomos_id");
        
    }
    
    public function formAction(){
		
		//ChromePhp::log("Id: " . $this->session->_autonomos_id);
		
        $idForm = $this->getRequest()->getParam("metasautonomos_id");
        $this->view->fk_autonomos_id = $this->getRequest()->getParam("fk_autonomos_id");
        $this->view->metasautonomos_id = $this->getRequest()->getParam("metasautonomos_id");
        $this->view->fk_usuario_id = $this->session->_profissionais_id ;
        
        $dados = array();
        if($idForm > 0){
            $MMetasautonomos = new Application_Model_DbTable_Metasautonomos();
            $metasautonomos = $MMetasautonomos->getRegistros(array('*'),array("metasautonomos_id"=>$idForm));
            $dados = $metasautonomos[0];
            
			//ChromePhp::log($dados);
			
			$dados['day'] = date('d/m/Y' ,strtotime($dados['data_para']));
			$dados['hour'] = date('H:i' ,strtotime($dados['data_para']));
            
        }
        
        $this->view->dados = $dados;
    }
    
    public function filtrosAction(){ }
    
    public function pesquisarAction(){ 
			
        $this->retorno = array();
        try{
            $parametros = $this->getRequest()->getParams();

            
            $MMetasautonomos = new Application_Model_DbTable_Metasautonomos();
            $metasautonomos = $MMetasautonomos->getMetasAutonomos($parametros);
            
            if(isset($metasautonomos) && !empty($metasautonomos) && sizeof($metasautonomos) > 0){
                foreach($metasautonomos as $item){
							
					ChromePhp::log($item);
					if ($item['fk_usuario_id'] == "0" || $item['fk_usuario_id'] == "1")
						$item['fk_usuario_id'] = "Master" ;
						
					else {
						$MAutonomos = new Application_Model_DbTable_Autonomos();
						$autonomos = $MAutonomos->getRegistrosFiltradosGrid(array("autonomos_id"=>$item['fk_usuario_id']));
						
						$item['fk_usuario_id'] = $autonomos[0]['autonomos_nome'] ;
					}
					
					if (is_null($item['metasautonomos_file'])){
						$item['metasautonomos_file'] = "" ;
					}
					
					$item['data_creacao'] = date('d/m/Y H:i', strtotime($item['data_creacao']));
					$item['data_para'] = date('d/m/Y H:i', strtotime($item['data_para']));
					ChromePhp::log($item);
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
    
    private function validarDadosFormulario($dados){
        $validacao = true;

        if($dados["metasautonomos_nome"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe o nome da Meta";
        } else if ($dados["metasautonomos_text"] == "") {
            $validacao = false;
            $this->retorno["erro"][] = "Informe a descriçao da Meta";
        } else if ($dados["data_para_day"] == "") {
            $validacao = false;
            $this->retorno["erro"][] = "Informe a data";
        } else if ($dados["data_para_hour"] == "") {
            $validacao = false;
            $this->retorno["erro"][] = "Informe a hora";
		}
/*
        if($dados["metasautonomos_nome"] != ""){
            $MMetasautonomos = new Application_Model_DbTable_Metasautonomos();
            $metasautonomos = $MMetasautonomos->getRegistrosFiltrados(array(
                            array("condicao"=>"AND", "coluna"=>"metasautonomos_id", "operador"=>"!=", "valor"=>$dados["metasautonomos_id"]),
                            array("condicao"=>"AND", "coluna"=>"metasautonomos_nome", "operador"=>"=", "valor"=>$dados["metasautonomos_nome"]),
                        ));
            
            if(sizeof($metasautonomos) > 0){
                $validacao = false;
                $this->retorno["erro"][] = "Já existe uma meta cadastrada com o nome informado.";
            }
            
        }
*/            
        
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

		try {
			$MMetasautonomos = new Application_Model_DbTable_Metasautonomos();
			$dados = $this->getRequest()->getPost();

			ChromePhp::log($dados);
			
			$dados["dadosForm"]['data_para'] = $dados["dadosForm"]['data_para_day'] . " " . $dados["dadosForm"]['data_para_hour'];
			
			if($this->validarDadosFormulario($dados["dadosForm"])){
				
				unset ($dados["dadosForm"]['data_para_day']);
				unset ($dados["dadosForm"]['data_para_hour']);
			
				if($dados["dadosForm"]["metasautonomos_id"] > 0){
					if($MMetasautonomos->atualizar($dados["dadosForm"])){
						$this->retorno['sucesso'][] = "Registro alterado com sucesso.";
					}else{
						$this->retorno['erro'][] = "Não foi possível alterar o registro.";
					}

				}else{
					unset($dados["dadosForm"]["metasautonomos_id"]);
					if($MMetasautonomos->inserir($dados["dadosForm"])){
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
    
    public function excluirAction(){
        $this->retorno = array();

		try {

			$dados = $this->getRequest()->getPost();

			$dados["metasautonomos_status"] = "Inativo";                
			$MMetasautonomos = new Application_Model_DbTable_Metasautonomos();
			$metasautonomos = $MMetasautonomos->excluir($dados);
			if($metasautonomos)
				$this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
			else
				$this->retorno['erro'][] = 'Não foi possível excluir o registro!';
		}catch (Exception $exc) {
			ChromePhp::log($exc);
			$this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
		}
		$this->view->dados = Zend_Json_Encoder::encode($this->retorno);

    }

    public function carregarImagensAction(){
        try{
			
			$idForm = $this->getRequest()->getParam('meta_id');
			$name = $this->getRequest()->getParam('meta_nome');
			
			
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/metas/autonomos/'.$idForm.'/')) {
				mkdir($_SERVER['DOCUMENT_ROOT'].'/metas/autonomos/'.$idForm , 0777);
			}
			
            
            $handle = new Upload($_FILES['uploadfile'.$idForm]);
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
                $handle->file_new_name_body      = 'meta_'.$name;

                // Definimos a pasta para onde a imagem maior será armazenada
                $handle->Process($_SERVER['DOCUMENT_ROOT'].'/metas/autonomos/'.$idForm.'/');

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

    public function salvarFileAction(){

		try {
			$MMeta = new Application_Model_DbTable_Metasautonomos();
			$dados = $this->getRequest()->getPost();

			$dados['metasautonomos_file'] = "/metas/autonomos/" . $dados['metasautonomos_id'] . "/meta_" .$dados['meta_nome'] . ".jpg" ;

			unset ($dados['meta_nome']);

			if($MMeta->atualizar($dados)){
				$this->retorno['sucesso'][] = "Registro alterado com sucesso.";
			}else{
				$this->retorno['erro'][] = "Não foi possível alterar o registro.";
			}

			
		}catch (Exception $exc) {
			ChromePhp::log($exc);
			$this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
		}
		$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }
    
    public function imprimirMetaAction(){
		
        $idForm = $this->getRequest()->getParam("meta_id");
        
        $dados = array();
        if($idForm > 0){
            $MMetasautonomos = new Application_Model_DbTable_Metasautonomos();
            $metasautonomos = $MMetasautonomos->getRegistros(array('*'),array("metasautonomos_id"=>$idForm));
            $dados = $metasautonomos[0];
            
			ChromePhp::log($dados);
			
			if ($dados['fk_usuario_id'] == "0" || $dados['fk_usuario_id'] == "1")
				$dados['criador'] = "Master" ;
				
			else {
				$MProfissionais = new Application_Model_DbTable_Autonomos();
				$profissionais = $MProfissionais->getRegistrosFiltradosGrid(array("autonomos_id"=>$dados['fk_usuario_id']));
				
				$dados['criador'] = $profissionais[0]['autonomos_nome'] ;
			}
			$MProfissionais = new Application_Model_DbTable_Autonomos();
			$profissionais = $MProfissionais->getRegistrosFiltradosGrid(array("autonomos_id"=>$dados['fk_autonomos_id']));
			$dados['prof'] = $profissionais[0]['autonomos_nome'] ;
			
			$dados['day'] = date('d/m/Y' ,strtotime($dados['data_para']));
			$dados['hour'] = date('H:i' ,strtotime($dados['data_para']));
            
        }
        
        $this->view->dados = $dados;
	
	}

}

