CREATE SCHEMA encuestas;

CREATE TABLE encuestas.evaluacion_docente (
	id	serial	primary key,
	id_profesor	int4	not null	references usuarios,
	id_evaluador	int4	not null	references usuarios,
	p1	int2	not null,
	p2	int2	not null,
	p3	int2	not null,
	p4	int2	not null,
	p5	int2	not null,
	p6	int2	not null,
	p7	int2	not null,
	p8	int2	not null,
	p9	int2	not null,
	p10	int2	not null,
	p11	int2	not null,
	p12	int2	not null,
	p13	int2	not null,
	p14	int2	not null,
	p15	int2	not null,
	p16	int2	not null,
	p17	int2	not null,
	p18	int2	not null,
	p19	int2	not null,
	p20	text,
	fecha	timestamp	default now(),
	UNIQUE(id_profesor,id_evaluador)
);
