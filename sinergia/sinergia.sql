CREATE SCHEMA sinergia;

CREATE TABLE sinergia.pruebas (
	id serial primary key,
	nombre text not null unique,
	alias text not null unique,
	activo bool default false not null
);

INSERT INTO sinergia.pruebas (nombre,alias,activo) VALUES ('Autoconcepto Forma 5','AF5',true);
INSERT INTO sinergia.pruebas (nombre,alias,activo) VALUES ('Escala de Estrategias de Aprendizaje','ACRA',true);
INSERT INTO sinergia.pruebas (nombre,alias,activo) VALUES ('Inteligencia y Orientación Vocacional','OTIS_sencillo',true);

CREATE TABLE sinergia.respuestas (
	id         serial primary key,
	id_prueba  int4 not null references sinergia.pruebas,
	semestre   int2 not null,
	ano        int2 not null,
	fecha      timestamp default now(),
	rut_alumno text not null,
	resp       int2[],
	UNIQUE(id_prueba,semestre,ano,rut_alumno)
);

CREATE TABLE sinergia.aplicadores (
	rut text not null unique,
	activo bool default true
);

CREATE TABLE sinergia.interpretaciones (
	id serial,
	id_prueba integer references sinergia.pruebas not null,
	tipo text check (tipo IN ('grupal','individual','analítico')) not null,
	categoria_nombre text not null,
	categoria text not null,
	nivel text not null,
	descripcion text not null,
	sugerencias text,
	PRIMARY KEY(id_prueba,tipo,categoria_nombre,categoria,nivel)
);


INSERT INTO sinergia.aplicadores (rut) VALUES ('9386444-1');

INSERT INTO aplicaciones VALUES (20,'Resultados Pruebas Sinergia (Informe Grupal)','Permite obtener los resultados a nivel de grupo de las pruebas psicométricas','sinergia_resultados_grupales',true,true);
INSERT INTO aplicaciones VALUES (21,'Resultados Pruebas Sinergia (individual)','Permite obtener resultados por alumno','sinergia_resultados_individual',true,true);
INSERT INTO permisos_apps VALUES (3,20),(3,21);
