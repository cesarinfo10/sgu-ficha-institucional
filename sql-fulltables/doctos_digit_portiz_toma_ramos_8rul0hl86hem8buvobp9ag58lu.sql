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
                  WHERE (SELECT count(id_alumno) FROM inscripciones_cursos WHERE id_alumno=a.id)=0   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]sw[aá]ld[oó]' OR  a.rut ~* '[oó]sw[aá]ld[oó]' OR  lower(a.email) ~* '[oó]sw[aá]ld[oó]' OR  text(a.id) ~* '[oó]sw[aá]ld[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[eé]l[aá]sq[uú][eé]z' OR  a.rut ~* 'v[eé]l[aá]sq[uú][eé]z' OR  lower(a.email) ~* 'v[eé]l[aá]sq[uú][eé]z' OR  text(a.id) ~* 'v[eé]l[aá]sq[uú][eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER