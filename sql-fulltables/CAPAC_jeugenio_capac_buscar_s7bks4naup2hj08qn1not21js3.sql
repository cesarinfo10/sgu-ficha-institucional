COPY (
select 
c.id
,c.ano
--,c.id_asiscapac_origen
,'CAPACITACION' origen
--,c.id_asiscapac_tipo
,(select glosa from asiscapac_tipo where id = c.id_asiscapac_tipo) glosa_tipo
,c.descripcion
,to_char(c.fecha_inicio,'dd/mm/yyyy') fecha_inicio
,to_char(c.fecha_termino,'dd/mm/yyyy') fecha_termino
,c.duracion duracion_horas
,c.link_capacitaciones
--,c.id_asiscapac_estado
,(select glosa from asiscapac_estado where id = c.id_asiscapac_estado) glosa_estado
,c.sala 
--id,
--,ano
--id_asiscapac_capacitaciones
,f.id_usuario
,(select nombre_usuario from usuarios where id = f.id_usuario) usuario
,(select concat(nombre,' ',apellido) from usuarios where id = f.id_usuario) nombre_usuario
--,id_asiscapac_actividades_funcionarios_check
,f.observacion
--,convocado
,(case when f.convocado = 't' then 'SI' else 'NO' end) convocado
,f.confirmado
,f.observacion_revocar
,to_char(f.fecha_aceptacion,'dd/mm/yyyy') fecha_aceptacion
,to_char(f.fecha_aceptacion,'hh:mi:ss') hora_aceptacion
,to_char(f.fecha_revocar,'dd/mm/yyyy') fecha_revocar
,to_char(f.fecha_revocar,'hh:mi:ss') hora_revocar
,(
	select count(*) from capac_doctos_digitalizados
	where id_asiscapac_capacitaciones = f.id_asiscapac_capacitaciones
	and id_usuario = f.id_usuario
 ) evidencias

from asiscapac_capacitaciones c, asiscapac_capacitaciones_funcionarios f
where c.ano = 2022
and f.id_asiscapac_capacitaciones = c.id
) to stdout WITH CSV HEADER