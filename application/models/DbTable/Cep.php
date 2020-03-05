<?php

class Application_Model_DbTable_Cep extends Zend_Db_Table
{

    protected $_name = 'cep_geral';
    protected $_schema = 'dados';

    public function getDadosCep($cep)
    {
        
        try {
            
            $retorno = false;
            

            $columns = array(
                'logradouro' => 'a.cep_geral_logradouro',
                'tipo' => 'a.cep_geral_tpLogradouro',
                'bairro' => 'a.cep_geral_bairro',
                'cidade' => 'a.cep_geral_cidade',
                'uf' => 'UPPER(a.cep_geral_uf)'
            );
            
            
            $select = $this->select()
                    ->from(array('a' => $this->_name), $columns, $this->_schema)
                    ->where('a.cep_geral_cep = ?', $cep);
            
            
            //executa a sql
            $result = $select
                    ->query()
                    ->fetchAll();
            
            
            $retorno = $result;
            

        } catch (Exception $exc) {
            $retorno = false;

        }
        
        return $retorno;
    }
    
    
     public function DadosRua($ceprua)
    {
        
        try {
            
            $retorno = false;
            

            $columns = array(
                'cep' => 'a.cep_geral_cep',
                'logradouro' => 'a.cep_geral_logradouro',
                'bairro' => 'a.cep_geral_bairro',
                'cidade' => 'a.cep_geral_cidade',
                'estado' => 'UPPER(a.cep_geral_uf)'
            );
            
            //lower - busca no banco quanto maiuscula e minuscula
            
            $select = $this->select()
                    ->from(array('a' => $this->_name), $columns, $this->_schema)
                    ->where ("(LOWER(cep_geral_logradouro) LIKE LOWER( '%" . $ceprua ."%'))");
                   
                                        
            
            //executa a sql
            $result = $select
                    ->query()
                    ->fetchAll();
            
            
            $retorno = $result;
            

        } catch (Exception $exc) {
            $retorno = false;
        }
        
        return $retorno;
    }
    
}

?>
