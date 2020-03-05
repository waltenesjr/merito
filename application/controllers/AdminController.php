<?php

class AdminController extends Zend_Controller_Action
{
    protected $_name = '';
    protected $_schema = '';
    protected $retorno = array();
    
    public function init()
    {
        /* Initialize action controller here */
    }
    
    
    public function preDispatch() {
    }

    public function indexAction()
    {
         $this->_redirect("/admin_login");
    }
}

?>