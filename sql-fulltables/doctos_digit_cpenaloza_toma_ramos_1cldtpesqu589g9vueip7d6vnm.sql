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
                  WHERE (SELECT count(id_alumno) FROM inscripciones_cursos WHERE id_alumno=a.id)=0   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]l[ií]ss[aá]' OR  a.rut ~* 'm[eé]l[ií]ss[aá]' OR  lower(a.email) ~* 'm[eé]l[ií]ss[aá]' OR  text(a.id) ~* 'm[eé]l[ií]ss[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[ií][aá]z' OR  a.rut ~* 'd[ií][aá]z' OR  lower(a.email) ~* 'd[ií][aá]z' OR  text(a.id) ~* 'd[ií][aá]z' )  AND a.carrera_actual IN (101,62,26,88,18,3,36,67)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER