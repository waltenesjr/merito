<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _initControllerHelpers(){Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/Helper', 'Zend_Controller_Action_Helper');	}
    
    /**
     * Função faz a conexão com o banco de dados e registra a variável $db para
     * que ela esteja disponível em toda a aplicação.
     */

    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }

    protected function _initConnection()
    {        
        /**
         * Obtém os resources(recursos).
         */
        $options    = $this->getOption('resources');
        $db_adapter = $options['db']['adapter'];
        $params     = $options['db']['params'];

        try{

            /**
             * Este método carrega dinamicamente a classe adptadora
             * usando Zend_Loader::loadClass().
             */
            $db = Zend_Db::factory($db_adapter, $params);

            /**
             * Este método retorna um objeto para a conexão representada por uma
             * respectiva extensão de banco de dados.
             */
            $db->getConnection();

            // Registra a $db para que se torne acessível em toda app
            $registry = Zend_Registry::getInstance();
            $registry->set('db', $db);

        }catch( Zend_Exception $e){
            ChromePhp::log($e);
            echo "Estamos sem conexão ao banco de dados neste momento. Tente mais tarde por favor.";

            exit;
        }

    }

}
