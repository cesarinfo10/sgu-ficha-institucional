COPY (SELECT poas.id,tipo_act,coalesce(p.nombre,'') AS proyecto,actividad,prioridad,
                      to_char(fecha_prog_termino,'DD-tmMon-YYYY') AS fecha_prog_termino,fecha_prog_termino_hist,
                      to_char(fecha_fin_real,'DD-tmMon-YYYY') AS fecha_fin_real,
                      estado,poas.comentarios,CASE WHEN evidencia IS NOT NULL THEN 1 ELSE 0 END AS evidencia,
                      date_part('month',fecha_prog_termino) AS mes_termino,gu.alias AS unidad
               FROM gestion.poas AS poas
               LEFT JOIN gestion.unidades  AS gu ON gu.id=poas.id_unidad
               LEFT JOIN gestion.proyectos AS p  ON p.id=poas.id_proyecto
               WHERE date_part('year',fecha_prog_termino)=2022  AND date_part('month',fecha_prog_termino)=11  AND poas.id_unidad='16' 
               ORDER BY poas.fecha_prog_termino) to stdout WITH CSV HEADER