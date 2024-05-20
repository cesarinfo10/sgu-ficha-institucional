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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]r[oó]l' OR  a.rut ~* 'c[aá]r[oó]l' OR  lower(a.email) ~* 'c[aá]r[oó]l' OR  text(a.id) ~* 'c[aá]r[oó]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[aá]v[aá]rr[eé]t[eé]' OR  a.rut ~* 'n[aá]v[aá]rr[eé]t[eé]' OR  lower(a.email) ~* 'n[aá]v[aá]rr[eé]t[eé]' OR  text(a.id) ~* 'n[aá]v[aá]rr[eé]t[eé]' )  AND a.carrera_actual IN (108,96,25,109,37,70,135,112,17)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER