


ALTER TABLE dados.profissionais
   ADD COLUMN profissionais_uf character varying(72);
   
ALTER TABLE dados.autonomos
   ADD COLUMN autonomos_uf character varying(72);
   
ALTER TABLE dados.autonomos ALTER COLUMN fk_municipios_codigo_ibge TYPE character varying(90);

ALTER TABLE dados.profissionais ALTER COLUMN fk_municipios_codigo_ibge TYPE character varying(90);

update dados.profissionais set profissionais_cep = replace(profissionais_cep, '.', '');
update dados.autonomos set autonomos_cep = replace(autonomos_cep, '.', '');

update dados.autonomos 
set autonomos_logradouro = (Select cep_geral_logradouro from cep.cep_geral where cep_geral_cep = dados.autonomos.autonomos_cep),
autonomos_bairro = (Select cep_geral_bairro from cep.cep_geral where cep_geral_cep = dados.autonomos.autonomos_cep),
fk_municipios_codigo_ibge = (Select cep_geral_cidade from cep.cep_geral where cep_geral_cep = dados.autonomos.autonomos_cep),
autonomos_uf = (Select cep_geral_uf from cep.cep_geral where cep_geral_cep = dados.autonomos.autonomos_cep)
where autonomos_logradouro = ''


update dados.profissionais 
set profissionais_logradouro = (Select cep_geral_logradouro from cep.cep_geral where cep_geral_cep = dados.profissionais.profissionais_cep),
profissionais_bairro = (Select cep_geral_bairro from cep.cep_geral where cep_geral_cep = dados.profissionais.profissionais_cep),
fk_municipios_codigo_ibge = (Select cep_geral_cidade from cep.cep_geral where cep_geral_cep = dados.profissionais.profissionais_cep),
profissionais_uf = (Select cep_geral_uf from cep.cep_geral where cep_geral_cep = dados.profissionais.profissionais_cep)
where profissionais_logradouro = ''

INSERT INTO dados.permissoes(permissoes_nome, permissoes_status, permissoes_modulo)
VALUES ( 'Listar todas unidades', 'Ativo', 'Profissionais');
