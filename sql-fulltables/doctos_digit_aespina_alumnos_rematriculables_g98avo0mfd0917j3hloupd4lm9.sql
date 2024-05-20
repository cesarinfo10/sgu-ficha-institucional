COPY (SELECT a.rut,apellidos,nombres,ae.nombre as estado,c.alias||'-'||a.jornada as carrera,
                         ad.nombre AS admision,semestre_cohorte||'-'||cohorte as cohorte,a.nacionalidad,
                         ddt.nombre AS tipo_docto,dd.fecha
                  FROM doctos_digitalizados dd 
                  LEFT JOIN doctos_digital_tipos ddt on ddt.id=dd.id_tipo 
                  LEFT JOIN alumnos a                using(rut) 
                  LEFT JOIN admision_tipo ad         on ad.id=a.admision
                  LEFT JOIN al_estados ae            on ae.id=a.estado
                  LEFT JOIN mallas     AS m ON m.id=a.malla_actual
                  LEFT JOIN carreras c               on c.id=carrera_actual 
                  LEFT JOIN usuarios u               on u.id=dd.id_usuario
                  WHERE true  AND (a.cohorte = '2021' ) AND (a.estado = '1') AND (a.admision IN ('1','3')) AND (c.regimen = 'PRE') AND (a.id NOT IN (SELECT id_alumno FROM matriculas WHERE ano=2023 AND semestre=1))  AND al_sies.rut IS NOT NULL  AND ((SELECT estado 
                          FROM gestion.solicitudes AS sol 
                          LEFT JOIN gestion.solic_tipos AS gst ON gst.id=sol.id_tipo 
                          WHERE id_alumno=a.id AND gst.alias='solic_excep_finan' AND sol.fecha::date>='2021-10-01'
                          ORDER BY sol.fecha DESC
                          LIMIT 1) IS NULL)  AND ((SELECT CASE WHEN fecha_compromiso IS NOT NULL   THEN 'Comprometido(a)'
                                   WHEN id_motivo_no_remat IS NOT NULL THEN 'Desertor(a)'
                                   WHEN obtiene_respuesta='f'          THEN 'Sin respuesta'
                              END AS atencion_remat
                       FROM gestion.atenciones_remat AS gar
                       WHERE gar.id_alumno=a.id AND ((fecha_compromiso IS NOT NULL AND fecha_compromiso>=now()::date) OR id_motivo_no_remat IS NOT NULL OR NOT obtiene_respuesta)
                       ORDER BY gar.fecha DESC
                       LIMIT 1) IS NULL)  AND ((SELECT estado
                     FROM dae.fuas
                     WHERE id_alumno=a.id and ano=2023
                     ORDER BY fecha_creacion DESC
                     LIMIT 1) IS NULL)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER