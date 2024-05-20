COPY (
select 
--ano db_ano,
a.id 
--id_asiscapac_origen db_id_asiscapac_origen,
,'ACTIVIDADES' origen
--,a.id_asiscapac_tipo
--,(select glosa from asiscapac_tipo where id = a.id_asiscapac_tipo) tipo
,a.descripcion
,to_char(a.fecha_inicio,'dd/mm/yyyy') fecha_inicio
,to_char(a.fecha_inicio,'hh:mi:ss') hora_inicio
,to_char(a.fecha_termino,'dd/mm/yyyy') fecha_termino
,to_char(a.fecha_termino,'hh:mi:ss') hora_termino
,a.duracion duracion_minutos
--,a.id_asiscapac_recordar recordar_dia
,a.link_zoom
--,a.id_asiscapac_estado
,(select glosa from asiscapac_estado where id = a.id_asiscapac_estado) 
,f.id_usuario
,(select nombre_usuario from usuarios where id = f.id_usuario) usuario
,(select concat(nombre,' ',apellido) from usuarios where id = f.id_usuario) nombre_usuario
--,f.id_asiscapac_actividades_funcionarios_check
,(select glosa from asiscapac_actividades_funcionarios_check where id = f.id_asiscapac_actividades_funcionarios_check) as check_funcionario
,f.observacion
,(case when f.convocado = 't' then 'SI' else 'NO' end) convocado
from asiscapac_actividades a, asiscapac_actividades_obligatorias_funcionarios f
where 
a.ano = 2022
and f.id_asiscapac_actividades = a.id
) to stdout WITH CSV HEADER