<?php

    class Zend_Controller_Action_Helper_Printdata extends Zend_Controller_Action_Helper_Abstract{

	public function testPrint($passData){
            ChromePhp::log('aaaaaaaa');
		if($passData != ""){	
			echo "Testing Printdata from action helper. Welcome ". $passData;
		}else{
			echo "Testing action helper";
		}
	} 	

    }