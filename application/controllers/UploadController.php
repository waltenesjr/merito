<?php

class UploadController extends Zend_Controller_Action
{
    
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
        /*$this->session = new Zend_Session_Namespace('subeauty');
        // Verifica sessão do usuário
        if (!isset($this->session->_id)) {
            Zend_Session::destroy();
            $this->_redirect("/");
        }*/
    }

    public function indexAction()
    {
        // action body
        
    }
    
    public function carregarImagensAction(){
        try{
            $handle = new Upload($_FILES['uploadfile']);
            // Verifica se o arquivo foi carregado corretamente
            if ($handle->uploaded) 
            {       
                // Definimos as configurações desejadas da imagem maior
                $handle->image_resize            = true;
                $handle->image_ratio_y           = true;
                $handle->image_ratio_x           = true;
                $handle->image_x                 = 440;
                $handle->image_y                 = 380;
                $handle->file_overwrite          = true;
                $handle->file_auto_rename        = false;
                $handle->file_new_name_body      = 'nome_imagem';

                // Definimos a pasta para onde a imagem maior será armazenada
                $handle->Process($_SERVER['DOCUMENT_ROOT'].'/imagens/profissionais/');

                // Em caso de sucesso no upload podemos fazer outras ações como insert em um banco de cados
                if($handle->processed) 
                {
                    $this->retorno['sucesso'][] = "Imagem carregada com sucesso!";
                }else{
                    // Em caso de erro listamos o erro abaixo
                    echo '<fieldset>';
                    echo '  <legend>Erro encontrado!</legend>';
                    echo '  Erro: ' . $handle->error . '';
                    echo '</fieldset>';
                }

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
    
}

