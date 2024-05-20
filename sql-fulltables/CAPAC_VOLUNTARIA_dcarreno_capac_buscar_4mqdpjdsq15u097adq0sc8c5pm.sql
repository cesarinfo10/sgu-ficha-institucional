COPY (
select 
--id
--,ano
--id_asiscapac_tipo
(select glosa from asiscapac_tipo where id = f.id_asiscapac_tipo) tipo
,f.descripcion
,f.fecha_inicio
,f.fecha_termino
,f.duracion duracion_horas
,f.link_capacitaciones
,(select glosa from asiscapac_estado where id = f.id_asiscapac_estado) estado
,f.sala
,f.id_usuario
,(select nombre_usuario from usuarios where id = f.id_usuario) usuario
,(select concat(nombre,' ',apellido) from usuarios where id = f.id_usuario) nombre_usuario
,f.confirmado
,f.observacion_revocar
,f.observacion
,to_char(f.fecha_aceptacion,'dd/mm/yyyy') fecha_aceptacion
,to_char(f.fecha_aceptacion,'hh:mi:ss') hora_aceptacion
,to_char(f.fecha_revocar,'dd/mm/yyyy') fecha_revocar
,to_char(f.fecha_revocar,'hh:mi:ss') hora_revocar
--,f.id_regimen
from asiscapac_usuario_capacitaciones f
where ano = 2023
) to stdout WITH CSV HEADER