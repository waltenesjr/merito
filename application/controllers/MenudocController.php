<?php

class MenudocController extends Zend_Controller_Action
{
	protected $retorno = array();

    public function init()
    {
    		$this->_helper->layout->setLayout('layout_aval');
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
        $this->session = new Zend_Session_Namespace('session_sad_subeauty');

    }

    public function indexAction()
    {

		$MMenuDoc = new Application_Model_DbTable_Menudoc();
		$menuDoc = $MMenuDoc->getMenus();            
		$this->view->menu = $menuDoc;
		
		$subMenuDoc = $MMenuDoc->getSubMenus();            
		$this->view->menuSub = $subMenuDoc;

		if ( $this->session->_profissionais_cargo == "administrador" || $this->session->_profissionais_tipo == "master" ){
			$this->view->ver = true ;
		} else {
			$this->view->ver = false ;
		}

		$MMenuDoc = new Application_Model_DbTable_Menudoc();
		$menuDoc = $MMenuDoc->getMenus();            
		
		foreach($menuDoc as $item){
			$this->retorno["dados"][] = $item;
		}
		
		$this->view->dados = $this->retorno;

    }

	public function menuAction(){
		$MMenuDoc = new Application_Model_DbTable_Menudoc();
		$menuDoc = $MMenuDoc->getMenus();            
		$this->view->menu = $menuDoc;
		
		$subMenuDoc = $MMenuDoc->getSubMenus();            
		$this->view->menuSub = $subMenuDoc;
	}

	public function getMenusAction () {
		$MMenuDoc = new Application_Model_DbTable_Menudoc();
		$menuDoc = $MMenuDoc->getMenus();            
		
		foreach($menuDoc as $item){
			$this->retorno["dados"][] = $item;
		}
		
		$this->view->dados = Zend_Json_Encoder::encode($this->retorno);
	}

    public function formAction(){
		
		$this->_helper->layout->disableLayout();
		
		//if ( $this->session->_profissionais_cargo == "administrador" || $this->session->_profissionais_tipo == "master" ){
			$idForm = $this->getRequest()->getParam("menu_id");
			
			$dados = array();
			$dados['menu_id'] = 0;
			if($idForm > 0){
				//Pega os dados do registro para montar no formulário
				$MMenu = new Application_Model_DbTable_Menudoc();
				$menu = $MMenu->getRegistros(array("menu_id"=>$idForm));
				if (isset($menu[0]))
					$dados = $menu[0];
				
				$MSMenu = new Application_Model_DbTable_Menusubdoc();
				$this->view->subMenus = $MSMenu->getRegistros(array("menu_id"=>$idForm));

			}

			
			$this->view->dados = $dados;
			$this->view->ver = true ;
		//} else {
		//	$this->view->ver = false ;
		//}
    }

    public function salvarAction(){
		
		$this->_helper->layout->disableLayout();

		try {
			$MMenu = new Application_Model_DbTable_Menudoc();
			$dados = $this->getRequest()->getPost();

			ChromePhp::log($dados);
			if($this->validarDadosFormularioMenu($dados["dadosForm"])){
				
				unset($dados["dadosForm"]["menusub_text"]);
				
				$dados["dadosForm"]["menu_imageurl"] 	= "";
				$dados["dadosForm"]["menu_cssclass"] 	= "subItemMenuPrincipal";
				$dados["dadosForm"]["menu_value"] 		= "";
				$dados["dadosForm"]["menu_url"] 		= "";
				if (!isset($dados["dadosForm"]["menu_tem_sub"]) || $dados["dadosForm"]["menu_tem_sub"] == "")
					$dados["dadosForm"]["menu_tem_sub"] = "0" ;

				if($dados["dadosForm"]["menu_id"] > 0){
					if($MMenu->atualizar($dados["dadosForm"])){
						$this->retorno['sucesso'][] = "Registro alterado com sucesso.";
					}else{
						$this->retorno['erro'][] = "Não foi possível alterar o registro.";
					}

				}else{
					unset($dados["dadosForm"]["menu_id"]);
					if($MMenu->inserir($dados["dadosForm"])){
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

    public function salvarSubAction(){
		$this->_helper->layout->disableLayout();

		try {
			$MMenu = new Application_Model_DbTable_Menusubdoc();
			$dados = $this->getRequest()->getPost();

			ChromePhp::log($dados);

			if($this->validarDadosFormularioSubMenu($dados["dadosForm"])){
				unset($dados["dadosForm"]["menu_text"]);
				

				$dados["dadosForm"]["fk_menu_id"] 			= $dados["dadosForm"]["menu_id"];
				$dados["dadosForm"]["menusub_cssclass"] 	= "subItemMenuPrincipal";
				$dados["dadosForm"]["menusub_value"] 		= "";
				$dados["dadosForm"]["menusub_url"] 			= "";
				
				unset($dados["dadosForm"]["menu_id"]);
				
				//if ($dados["dadosForm"]["menu_tem_sub"] == "")
					unset($dados["dadosForm"]["menu_tem_sub"]);

				if($dados["dadosForm"]["menusub_id"] > 0){
					if($MMenu->atualizar($dados["dadosForm"])){
						$this->retorno['sucesso'][] = "Registro alterado com sucesso.";
					}else{
						$this->retorno['erro'][] = "Não foi possível alterar o registro.";
					}

				}else{
					unset($dados["dadosForm"]["menusub_id"]);
									
					if($MMenu->inserir($dados["dadosForm"])){
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

    public function salvarMenuFileAction(){
		$this->_helper->layout->disableLayout();

		try {
			$MMenu = new Application_Model_DbTable_Menudoc();
			$dados = $this->getRequest()->getPost();

			$dados['menu_url'] = "/documentos" /* . $dados['menu_id'] */  . "/" .$dados['menu_url'] . ".pdf" ;

			if($MMenu->atualizar($dados)){
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

    public function salvarMenusubFileAction(){
		$this->_helper->layout->disableLayout();

		try {
			$MMenu = new Application_Model_DbTable_Menusubdoc();
			$dados = $this->getRequest()->getPost();

			$dados['menusub_url'] = "/documentos" /* . $dados['menusub_id'] */  . "/" .$dados['menusub_url'] . ".pdf" ;

			if($MMenu->atualizar($dados)){
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

    private function validarDadosFormularioMenu($dados){
        $validacao = true;
        if($dados["menu_text"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a Descrição do Menu";
        }  
        return $validacao;
    }
    private function validarDadosFormularioSubMenu($dados){
        $validacao = true;
        if($dados["menusub_text"] == ""){
            $validacao = false;
            $this->retorno["erro"][] = "Informe a Descrição do Sub Menu";
        }  
        return $validacao;
    }

    public function carregarFileMenuAction(){
		$this->_helper->layout->disableLayout();
		
	//	$idForm = $this->getRequest()->getParam('menu_id');
		$text = $this->getRequest()->getParam('menu_text');
		
        try{

			if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/documentos/'.$idForm.'/')) {
				mkdir($_SERVER['DOCUMENT_ROOT'].'/documentos/'.$idForm , 0777);
			}
            
            $handle = new Upload($_FILES['uploadfile']);
            // Verifica se o arquivo foi carregado corretamente
            if ($handle->uploaded) 
            {
                $handle->file_new_name_ext       = 'pdf';
                $handle->file_new_name_body      = $text;

                // Definimos a pasta para onde a imagem maior será armazenada
                $handle->Process($_SERVER['DOCUMENT_ROOT'].'/documentos/'.$idForm);

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
        
        /* this goes out */
        $this->retorno['sucesso'][] = "Imagem carregada com sucesso!";
        
        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

    public function carregarFileAction(){
		$this->_helper->layout->disableLayout();
		
	//	$idForm = $this->getRequest()->getParam('menusub_id');
		$text = $this->getRequest()->getParam('menusub_text');
		
        try{
			
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/documentos/'.$idForm.'/')) {
				mkdir($_SERVER['DOCUMENT_ROOT'].'/documentos/'.$idForm , 0777);
			}
            
            $handle = new Upload($_FILES['uploadfile']);
            // Verifica se o arquivo foi carregado corretamente
            if ($handle->uploaded) 
            {
                $handle->file_new_name_ext       = 'pdf';
                $handle->file_new_name_body      = $text;

                // Definimos a pasta para onde a imagem maior será armazenada
                $handle->Process($_SERVER['DOCUMENT_ROOT'].'/documentos/'.$idForm);

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

    public function excluirAction(){
		$this->_helper->layout->disableLayout();
        $this->retorno = array();

		try {

			$MMenu = new Application_Model_DbTable_Menudoc();
			$dados = $this->getRequest()->getPost();

			/* Deleting menu, without sub menus */
			if (!isset($dados['menu_tem_sub']) || $dados['menu_tem_sub'] == "0"){

				$MMenu = new Application_Model_DbTable_Menudoc();
				$menu = $MMenu->getRegistros(array("menu_id"=>$dados['id']));
				
				if ( isset($menu[0]['menu_url']) && $menu[0]['menu_url'] != "") {
					$path = $_SERVER['DOCUMENT_ROOT'].$menu[0]['menu_url'];
					unlink($path);
					$end = strrpos($path, "/") ;
					
					$folder = substr($path, 0 , $end);
					rmdir ($folder);
				}
			} else {

				$MSMenu = new Application_Model_DbTable_Menusubdoc();
				$subMenus = $MSMenu->getRegistros($dados['id']);
				
				foreach ($subMenus as $items => $value){
					if ($value['menusub_url'] != "") {
						$path = $_SERVER['DOCUMENT_ROOT'].$value['menusub_url'];
						unlink($path);
						
						$end = strrpos($path, "/") ;
						
						$folder = substr($path, 0 , $end);
						rmdir ($folder);
					}
				}
				
			}
               
			$menu = $MMenu->excluir($dados);
			if($menu)
				$this->retorno['sucesso'][] = 'Registro excluido com sucesso!';
			else
				$this->retorno['erro'][] = 'Não foi possível excluir o registro!';
			

				
			
		}catch (Exception $exc) {
			ChromePhp::log($exc);
			$this->retorno['erro'][] = 'Desculpe ocorreu uma falha ao recuperar informações do sistema!';
		}

        $this->view->dados = Zend_Json_Encoder::encode($this->retorno);
    }

}

