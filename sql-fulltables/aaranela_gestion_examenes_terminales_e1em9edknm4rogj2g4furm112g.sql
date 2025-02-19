COPY (SELECT et.id,to_char(et.fecha_reg,'dd-tmMon-YYYY HH24:MI') AS fecha_reg,
                         et.estado,to_char(et.estado_fecha,'dd-tmMon-YYYY') AS estado_fecha,
                         et.tipo,et.tema,to_char(et.fecha_examen,'tmDay dd-tmMon-YYYY HH24:MI') AS fecha_examen,s.nombre AS sala,
						 u.nombre AS ministro_de_fe,(SELECT char_comma_sum(vp.nombre) FROM examenes_terminales_docentes LEFT JOIN vista_profesores AS vp ON vp.id=id_profesor WHERE id_examen=et.id) AS comision,e.nombre AS escuela,
						 (SELECT char_comma_sum(upper(a.apellidos)||' '||initcap(a.nombres)||' '||CASE WHEN (SELECT count(id) FROM finanzas.cobros WHERE id_alumno=a.id AND id_glosa IN (10,47,5,9) AND pagado AND fecha_venc>=now()::date-'6 months'::interval) >= 1 THEN '✅' ELSE '⛔' END) 
                    FROM examenes_terminales_estudiantes AS ete 
					LEFT JOIN alumnos AS a ON a.id=ete.id_alumno
					LEFT JOIN carreras AS c ON c.id=a.carrera_actual
					WHERE ete.id_exam_term=et.id ) AS estudiantes
                  FROM examenes_terminales AS et
				  LEFT JOIN vista_examenes_terminales AS vet USING (id)
				  LEFT JOIN vista_usuarios AS u ON u.id=et.id_ministro_de_fe
				  LEFT JOIN escuelas AS e ON e.id=et.id_escuela
				  LEFT JOIN salas AS s ON s.codigo=et.sala
				  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR  text(a.id) ~* 'j[uú][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]m[ií]l[ií][oó]' OR  a.rut ~* '[eé]m[ií]l[ií][oó]' OR  text(a.id) ~* '[eé]m[ií]l[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]l' OR  a.rut ~* 'd[eé]l' OR  text(a.id) ~* 'd[eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]ll[eé]' OR  a.rut ~* 'v[aá]ll[eé]' OR  text(a.id) ~* 'v[aá]ll[eé]' )  			  
				  ORDER BY et.fecha_examen ASC ) to stdout WITH CSV HEADER