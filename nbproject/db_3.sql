


CREATE TABLE dados.profissionais_supervisor
(
  profissionais_supervisor_id serial NOT NULL,
  fk_supervisor_id integer,
  fk_usuarios_id integer,
  profissionais_supervisor_nome character varying(100),
  CONSTRAINT pk_profissionais_supervisor_id PRIMARY KEY ( profissionais_supervisor_id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dados.profissionais_supervisor
  OWNER TO postgres;
