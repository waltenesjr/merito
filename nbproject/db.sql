
-- Table: dados.autonomos

-- DROP TABLE dados.autonomos;

CREATE TABLE dados.autonomos
(
  autonomos_id serial NOT NULL,
  autonomos_nome character varying(100),
  fk_unidades_id integer,
  fk_funcoes_id integer,
  autonomos_email character varying(100),
  autonomos_status status_registro DEFAULT 'Ativo'::status_registro,
  autonomos_cnpj character varying(14),
  autonomos_rg character varying(30),
  autonomos_data_nascimento date,
  autonomos_fixo_1 character varying(15),
  autonomos_fixo_2 character varying(15),
  autonomos_cel_1 character varying(15),
  autonomos_cel_2 character varying(15),
  autonomos_logradouro character varying(100),
  autonomos_bairro character varying(50),
  autonomos_complemento character varying(100),
  fk_municipios_codigo_ibge integer,
  autonomos_cep character varying(10),
  autonomos_salario numeric(12,2),
  autonomos_alimentacao numeric(12,2),
  autonomos_transporte numeric(12,2),
  autonomos_impostos numeric(12,2),
  autonomos_senha character varying(50),
  autonomos_obs text,
  autonomos_data_admissao date,
  autonomos_sexo character(1) DEFAULT 'F'::bpchar,
  autonomos_cargo character varying(13) NOT NULL DEFAULT 'autonomo'::character varying,
  autonomos_tipo character varying(11) NOT NULL DEFAULT 'autonomo'::character varying, -- Determina o tipo do profissional como Autônomo ou Colaborador
  CONSTRAINT pk_autonomos_id PRIMARY KEY (autonomos_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.autonomos
  OWNER TO postgres;
COMMENT ON COLUMN dados.autonomos.autonomos_tipo IS 'Determina o tipo do profissional como Autônomo ou Colaborador';

-- Table: dados.autonomos_historico

-- DROP TABLE dados.autonomos_historico;

CREATE TABLE dados.autonomos_historico
(
  autonomos_historico_id serial NOT NULL,
  autonomos_historico_data date,
  autonomos_historico_descricao text,
  fk_autonomos_cad_id integer,
  autonomos_historico_status status_registro NOT NULL DEFAULT 'Ativo'::status_registro,
  fk_autonomos_id integer,
  CONSTRAINT pk_autonomos_historico_id PRIMARY KEY (autonomos_historico_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.autonomos_historico
  OWNER TO postgres;
  
-- Table: dados.autonomos_metas

-- DROP TABLE dados.autonomos_metas;

CREATE TABLE dados.autonomos_metas
(
  autonomos_metas_id serial NOT NULL,
  fk_autonomos_id integer,
  fk_metas_id integer,
  autonomos_metas_cota integer,
  autonomos_metas_total_quinzena integer,
  autonomos_metas_total_mes integer,
  CONSTRAINT pk_autonomos_metas_id PRIMARY KEY (autonomos_metas_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.autonomos_metas
  OWNER TO postgres;
  
-- Table: dados.permissoes_autonomos

-- DROP TABLE dados.permissoes_autonomos;

CREATE TABLE dados.permissoes_autonomos
(
  permissoes_autonomos_id serial NOT NULL,
  fk_permissoes_id integer,
  fk_autonomos_id integer,
  CONSTRAINT pk_permissoes_autonomos_id PRIMARY KEY (permissoes_autonomos_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.permissoes_autonomos
  OWNER TO postgres;
  
-- Table: dados.justificativas

-- DROP TABLE dados.justificativas;

CREATE TABLE dados.justificativas_autonomos
(
  justificativas_id serial NOT NULL,
  porque text,
  descricao text,
  fk_tabela_avaliacoes_id_autonomos integer,
  fk_usuarios_id_cad integer,
  data_cad timestamp with time zone,
  CONSTRAINT pk_justificativas_autonomos_id PRIMARY KEY (justificativas_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.justificativas_autonomos
  OWNER TO postgres;
  
-- Table: dados.tabela_avaliacoes_autonomos

-- DROP TABLE dados.tabela_avaliacoes_autonomos;

CREATE TABLE dados.tabela_avaliacoes_autonomos
(
  tabela_avaliacoes_autonomos_id serial NOT NULL,
  fk_autonomos_id integer,
  fk_atribuicoes_id integer,
  tabela_avaliacoes_nota integer,
  fk_usuarios_id_cad integer,
  tabela_avaliacoes_status status_registro NOT NULL DEFAULT 'Ativo'::status_registro,
  tabela_avaliacoes_timestamp timestamp with time zone,
  fk_usuarios_data_operacao timestamp with time zone,
  CONSTRAINT pk_tabela_avaliacao_autonomos_id PRIMARY KEY (tabela_avaliacoes_autonomos_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.tabela_avaliacoes_autonomos
  OWNER TO postgres;

CREATE TABLE dados.tah_autonomos
(
  tah_autonomos_id serial NOT NULL,
  fk_autonomos_id integer,
  fk_atribuicoes_id integer,
  tah_nota integer,
  fk_usuarios_id_cad integer,
  tah_status status_registro DEFAULT 'Ativo'::status_registro,
  tah_timestamp timestamp with time zone,
  fk_usuarios_data_operacao timestamp with time zone,
  CONSTRAINT fk_tah_autonomos_id PRIMARY KEY (tah_autonomos_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.tah_autonomos
  OWNER TO postgres;  

-- Table: dados.tabela_avaliacoes_historico_autonomos

-- DROP TABLE dados.tabela_avaliacoes_historico_autonomos;



--CREATE TABLE dados.tabela_avaliacoes_historico_autonomos
--(
--  tabela_avaliacoes_historico_autonomos_id serial NOT NULL,
--  fk_autonomos_id integer,
--  fk_atribuicoes_id integer,
--  tabela_avaliacoes_historico_nota integer,
--  fk_usuarios_id_cad integer,
--  tabela_avaliacoes_historico_status status_registro DEFAULT 'Ativo'::status_registro,
--  tabela_avaliacoes_historico_timestamp timestamp with time zone,
--  fk_usuarios_data_operacao timestamp with time zone,
--  CONSTRAINT fk_tabela_avaliacoes_historico_autonomos_id PRIMARY KEY (tabela_avaliacoes_historico_autonomos_id )
--)
--WITH (
--  OIDS=FALSE
--);
--ALTER TABLE dados.tabela_avaliacoes_historico_autonomos
--  OWNER TO postgres;



--##################################################3

CREATE TABLE dados.menu_doc
(
	menu_id 		serial	NOT NULL,
	menu_text 		character varying(100),
	menu_imageUrl 	character varying(100),
	menu_cssClass 	character varying(100),
	menu_value  	character varying(100),
	menu_url  		character varying(100),
	menu_tem_sub	integer,
	CONSTRAINT menu_id PRIMARY KEY (menu_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.menu_doc
  OWNER TO postgres;

CREATE TABLE dados.menuSub_doc
(
	menuSub_id 			serial NOT NULL,
	menuSub_text 		character varying(100),
	menuSub_cssClass 	character varying(100),
	menuSub_value  		character varying(100),
	menuSub_url  		character varying(100),
	fk_menu_id 			integer,
	CONSTRAINT menuSub_id PRIMARY KEY (menuSub_id)	
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.menuSub_doc
  OWNER TO postgres;


--1
INSERT INTO dados.menu_doc(menu_text, menu_imageurl, menu_cssclass, menu_value, menu_url, menu_tem_sub) VALUES 
('Colaboradoes', '', 'subItemMenuPrincipal', '', '', 1);
--2
INSERT INTO dados.menu_doc(menu_text, menu_imageurl, menu_cssclass, menu_value, menu_url, menu_tem_sub) VALUES 
('Grupo Su Beauty', '', 'subItemMenuPrincipal', '', '', 1);
--3
INSERT INTO dados.menu_doc(menu_text, menu_imageurl, menu_cssclass, menu_value, menu_url, menu_tem_sub) VALUES 
('Código de Ética', '', 'subItemMenuPrincipal', '', '/documentos/codigo_etica.pdf', 0);
------------------------------------------------------------------------------------------------
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Gerência', 					'subItemMenuPrincipal', '/documentos/gerencia.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Supervisão de atendimento', 'subItemMenuPrincipal', '/documentos/supervisao.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Operador de Caixa', 		'subItemMenuPrincipal', '/documentos/operadorcaixa.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Recepção', 					'subItemMenuPrincipal', '/documentos/recepcao.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Telefonia', 				'subItemMenuPrincipal', '/documentos/telefonia.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Atendente de Color bar', 	'subItemMenuPrincipal', '/documentos/colorbar.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Serviços de Copa', 			'subItemMenuPrincipal', '/documentos/copa.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Manobrista', 				'subItemMenuPrincipal', '/documentos/manobrista.pdf', 1);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Serviços gerais', 			'subItemMenuPrincipal', '/documentos/gerais.pdf', 1);

INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Corpore', 					'subItemMenuPrincipal', '/documentos/grupo/corpore.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Dep. Autônomos', 			'subItemMenuPrincipal', '/documentos/grupo/autonomos.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Dep. Pessoal', 				'subItemMenuPrincipal', '/documentos/grupo/dppessoal.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Gestão Administrativa', 	'subItemMenuPrincipal', '/documentos/grupo/adm.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Gestão de Marketing', 		'subItemMenuPrincipal', '/documentos/grupo/marketing.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Gestão Comercial', 			'subItemMenuPrincipal', '/documentos/grupo/comercial.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Gestão Financeira', 		'subItemMenuPrincipal', '/documentos/grupo/financeiro.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('CETECAP/Recursos Humanos', 	'subItemMenuPrincipal', '/documentos/grupo/rh.pdf', 2);
INSERT INTO dados.menusub_doc(menusub_text, menusub_cssclass, menusub_url, fk_menu_id) VALUES ('Academia Hair School', 		'subItemMenuPrincipal', '/documentos/grupo/academia.pdf', 2);


CREATE TABLE dados.metasProfissionais (
	metasProfissional_id serial NOT NULL,
	metasProfissional_nome character varying(255),
	metasProfissional_text text,
	metasProfissional_file character varying(255),
	fk_profissionais_id integer,
	fk_usuario_id integer,
	data_creacao timestamp with time zone default current_timestamp,
	data_para timestamp with time zone,
	metasprofissionais_status status_registro NOT NULL DEFAULT 'Ativo'::status_registro,
	CONSTRAINT pk_metasProfissional_id PRIMARY KEY (metasProfissional_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.metasProfissionais
  OWNER TO postgres;


CREATE TABLE dados.metasAutonomos (
	metasAutonomos_id serial NOT NULL,
	metasAutonomos_nome character varying(255),
	metasAutonomos_text text,
	metasAutonomos_file character varying(255),
	fk_autonomos_id integer,
	fk_usuario_id integer,
	data_creacao timestamp with time zone default current_timestamp,
	data_para timestamp with time zone,
	metasAutonomos_status status_registro NOT NULL DEFAULT 'Ativo'::status_registro,
	CONSTRAINT pk_metasAutonomos_id PRIMARY KEY (metasAutonomos_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.metasAutonomos
  OWNER TO postgres;

INSERT INTO dados.metasprofissionais(metasprofissional_nome, metasprofissional_text, metasprofissional_file, fk_profissionais_id, fk_usuario_id, data_creacao, data_para)
VALUES ( 'Nome', 'Text', '', 42, 42, NOW(), NOW());


ALTER TABLE dados.funcoes ADD COLUMN funcao_autonomo character varying(25) DEFAULT 'N';

INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Cabeleireiro Master 1', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Cabeleireiro Master 2', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Cabeleireiro Master 3', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Depilador', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Esteticista', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Maquiador', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Terapeuta Capilar', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Manicure', 'Ativo', 'S');
INSERT INTO dados.funcoes(funcoes_descricao, funcoes_status, funcao_autonomo) VALUES ('Podóloga', 'Ativo', 'S');

ALTER TABLE dados.autonomos ADD COLUMN autonomos_eleitor character varying(50);
ALTER TABLE dados.autonomos ADD COLUMN autonomos_recibo character varying(50);
ALTER TABLE dados.autonomos ADD COLUMN autonomos_filiacao character varying(50);
ALTER TABLE dados.autonomos ADD COLUMN autonomos_dentrada date;
ALTER TABLE dados.autonomos ADD COLUMN autonomos_dsaida date;
ALTER TABLE dados.autonomos ADD COLUMN autonomos_porcentagem numeric(12,2);
ALTER TABLE dados.autonomos ADD COLUMN autonomos_comissao numeric(12,2);


