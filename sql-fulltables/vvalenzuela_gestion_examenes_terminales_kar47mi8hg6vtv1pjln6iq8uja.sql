COPY (SELECT et.id,to_char(fecha_reg,'dd-tmMon-YYYY HH24:MI') AS fecha_reg,
                         estado,to_char(estado_fecha,'dd-tmMon-YYYY') AS estado_fecha,
                         et.tipo,tema,to_char(fecha_examen,'tmDay dd-tmMon-YYYY HH24:MI') AS fecha_examen,s.nombre AS sala,
						 u.nombre AS ministro_de_fe,(SELECT char_comma_sum(vp.nombre) FROM examenes_terminales_docentes LEFT JOIN vista_profesores AS vp ON vp.id=id_profesor WHERE id_examen=et.id) AS comision,e.nombre AS escuela,
						 (SELECT char_comma_sum(va2.nombre) 
                    FROM examenes_terminales_estudiantes AS ete 
					LEFT JOIN vista_alumnos AS va2 ON va2.id=ete.id_alumno 
					LEFT JOIN alumnos AS a2 ON a2.id=va2.id
					LEFT JOIN carreras AS c2 ON c2.id=a2.carrera_actual
					WHERE ete.id_exam_term=et.id AND (c2.regimen = 'PRE') ) AS estudiantes
                  FROM examenes_terminales AS et
				  LEFT JOIN vista_usuarios AS u ON u.id=et.id_ministro_de_fe
				  LEFT JOIN escuelas AS e ON e.id=et.id_escuela
				  LEFT JOIN salas AS s ON s.codigo=et.sala
				  WHERE true  AND (date_part('year',et.fecha_examen) = 2023)  AND carrera_actual IN (108,96,25,109,37,70,112,17)  			  
				  ORDER BY et.fecha_reg DESC ) to stdout WITH CSV HEADER