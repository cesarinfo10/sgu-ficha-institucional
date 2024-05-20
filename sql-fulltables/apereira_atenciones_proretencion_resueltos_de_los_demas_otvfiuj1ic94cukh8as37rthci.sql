COPY (
select 
atpr.id id, 
(select concat(a.nombres,' ', a.apellidos) nombre_alumno from alumnos a where a.id = atpr.id_alumno) as nombre_alumno,
(select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) unidad_origen,
(select concat(u.nombre,' ', u.apellido) from usuarios u where u.id = atpr.id_usuario_origen) as nombre_usuario,
(select gu.nombre from gestion.unidades gu where gu.id = atpr.id_unidad_derivada)  derivado_nombre,                       
atpr.id_alumno id_alumno,
atpr.id_motivo id_motivo,
--(select concat(moti.clasificacion,': ',moti.nombre) from tipo_motivo_aux moti where moti.id = atpr.id_motivo) nombre_motivo,
(
  SELECT concat(
    (
      select b.clasificacion from tipo_motivo_clasif_proretencion b
      where b.id = a.id_clasificacion
    ),': <br>',a.nombre) AS nombre 
    FROM tipo_motivo_proretencion a 
    where a.id =  atpr.id_motivo                       
) nombre_motivo,
to_char(atpr.fecha,'dd/mm/yyyy') fecha,
to_char(atpr.fecha_derivacion,'dd/mm/yyyy') fecha_derivacion,   
--atpr.fecha fecha,
atpr.comentarios comentarios,
atpr.comentarios_derivado comentarios_derivado,
atpr.tipo_contacto tipo_contacto,
atpr.respuesta_contacto respuesta_contacto,                      
atpr.resuelto resuelto,
atpr.id_unidad_derivada id_unidad_derivada,
(select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado ,
atpr.comentarios comentarios
from atenciones_proretencion atpr
where 
resuelto = 'f' 
and (resuelto_derivado = 'f' or resuelto_derivado is null)
and (
(
(select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) <> 2) 
or (atpr.id_unidad_derivada <> 2)
)

order by atpr.fecha desc, atpr.id desc
) to stdout WITH CSV HEADER