COPY (SELECT et.id,to_char(fecha_reg,'dd-tmMon-YYYY HH24:MI') AS fecha_reg,
                         estado,to_char(estado_fecha,'dd-tmMon-YYYY') AS estado_fecha,
                         et.tipo,tema,to_char(fecha_examen,'tmDay dd-tmMon-YYYY HH24:MI') AS fecha_examen,s.nombre AS sala,
						 u.nombre AS ministro_de_fe,(SELECT char_comma_sum(vp.nombre) FROM examenes_terminales_docentes LEFT JOIN vista_profesores AS vp ON vp.id=id_profesor WHERE id_examen=et.id) AS comision,e.nombre AS escuela,
						 (SELECT char_comma_sum(upper(a.apellidos)||' '||initcap(a.nombres)||' '||CASE WHEN (SELECT count(id) FROM finanzas.cobros WHERE id_alumno=a.id AND id_glosa IN (10,47,5,9) AND pagado AND fecha_venc>=now()::date-'6 months'::interval) >= 1 THEN '✅' ELSE '⛔' END) 
                    FROM examenes_terminales_estudiantes AS ete 
					LEFT JOIN alumnos AS a ON a.id=ete.id_alumno
					LEFT JOIN carreras AS c ON c.id=a.carrera_actual
					WHERE ete.id_exam_term=et.id ) AS estudiantes
                  FROM examenes_terminales AS et
				  LEFT JOIN vista_usuarios AS u ON u.id=et.id_ministro_de_fe
				  LEFT JOIN escuelas AS e ON e.id=et.id_escuela
				  LEFT JOIN salas AS s ON s.codigo=et.sala
				  WHERE true  AND (date_part('year',et.fecha_examen) = 2023)  AND et.fecha_examen::date BETWEEN '2023-02-27'::date AND '2023-02-27'::date  			  
				  ORDER BY et.fecha_examen ASC ) to stdout WITH CSV HEADER