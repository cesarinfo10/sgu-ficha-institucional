CREATE SCHEMA encuestas;

CREATE TABLE encuestas.autoevaluacion_docente (
	id	serial	primary key,
	id_profesor	int4	not null	references usuarios	unique,
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
	p20	int2	not null,
	p21	int2	not null,
	p22	int2	not null,
	p23	int2	not null,
	p24	int2	not null,
	p25	int2	not null,
	p26	int2	not null,
	p27	int2	not null,
	p28	int2	not null,
	p29	int2	not null,
	p30	int2	not null,
	fecha	timestamp	default now()
);
