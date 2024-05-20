COPY (SELECT s.id,a.rut,a.nombres,a.apellidos,va.carrera||'-'||a.jornada AS carrera,
                     a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,va.estado,a.moroso_financiero,
                     ts.nombre AS tipo_solic,s.estado AS estado_solic,resp_obs,ts.alias AS alias_solic,
                     to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,
					 to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,
					 (SELECT char_comma_sum(alias||' ('||nombre_usuario||'): '||vobo||' '||coalesce(fecha_respuesta,fecha_reasignacion,'')) AS resp FROM (SELECT sr.id_usuario,u.nombre_usuario,gu.alias,
                          CASE WHEN visto_bueno = 't' THEN 'Aceptada' 
						       WHEN visto_bueno = 'f' THEN 'Rechazada' 
						       WHEN visto_bueno IS NULL AND fecha_reasignacion IS NOT NULL THEN 'Reasignada'
							   ELSE 'Sin responder' 
						  END AS vobo,
						  to_char(fecha_respuesta,'DD-tmMon-YYYY HH24:MI') AS fecha_respuesta,
						  to_char(fecha_reasignacion,'DD-tmMon-YYYY HH24:MI') AS fecha_reasignacion
				   FROM gestion.solic_respuestas AS sr
				   LEFT JOIN usuarios         AS u  ON u.id=sr.id_usuario
				   LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
				   WHERE sr.id_solicitud = s.id) AS solic_resp) AS responsables,s.email,s.tel_movil,s.telefono
			  FROM gestion.solicitudes AS s
			  LEFT JOIN gestion.solic_tipos AS ts ON ts.id = s.id_tipo
			  LEFT JOIN vista_alumnos       AS va ON va.id = s.id_alumno
			  LEFT JOIN alumnos             AS a  ON a.id = s.id_alumno
			  LEFT JOIN carreras			AS c  ON c.id = a.carrera_actual 
			  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '20130976-k' OR  a.rut ~* '20130976-k' OR  text(a.id) ~* '20130976-k' ) 
			  ORDER BY s.estado_fecha DESC ) to stdout WITH CSV HEADER