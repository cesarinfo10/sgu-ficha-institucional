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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'b[eé]nj[aá]m[ií]n' OR  a.rut ~* 'b[eé]nj[aá]m[ií]n' OR  lower(a.email) ~* 'b[eé]nj[aá]m[ií]n' OR  text(a.id) ~* 'b[eé]nj[aá]m[ií]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'k' OR  a.rut ~* 'k' OR  lower(a.email) ~* 'k' OR  text(a.id) ~* 'k' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER