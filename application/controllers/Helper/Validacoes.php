<?php

    class Zend_Controller_Action_Helper_Validacoes extends Zend_Controller_Action_Helper_Abstract{

	public function validarPermissoesUsuarioLogado($permissoes, $modulo, $acao){
            $situacao["status"] = false;

            //Tratamento para o usuário master
            if(is_null($permissoes)){
                $sessao = new Zend_Session_Namespace('session_sad_subeauty');
                if($sessao->_profissionais_tipo == "master") {
                    $situacao["status"] = true;
                    $situacao["profissionais_cargo"] = "master";
                    $situacao["profissionais_id"] = "0";
                }
                    
            }
            
            //Tratamento para o profissional cadastrado.
            if($situacao['status'] == false){
				
				$situacao["fk_unidades_id"] = $permissoes[0]["fk_unidades_id"] ;
				$situacao["profissionais_cargo"] = $permissoes[0]["profissionais_cargo"] ;
				$situacao["profissionais_id"] = $permissoes[0]["profissionais_id"] ;
				
                foreach($permissoes as $item){
                    if($item["permissoes_modulo"] == $modulo && $item["permissoes_nome"] == $acao){
                        $situacao["status"] = true;
                    }
                }
            }
            
            if($situacao["status"] == false)
                $situacao["mensagem"] = "Você não possui permissões para acessar o módulo ou ação solicitada.";
            return $situacao;
	} 
        
        public function getClassRadioAtribuicoes($nota){
            if($nota <= 0)
                return 'radioVermelho';
            else if($nota == 25 )
                return 'radioLaranjado';
            else if($nota == 50)
                return 'radioAmarelo';
            else if($nota == 75)
                return 'radioVerde';
            else if($nota == 100)
                return 'radioAzul';
        }

    }
