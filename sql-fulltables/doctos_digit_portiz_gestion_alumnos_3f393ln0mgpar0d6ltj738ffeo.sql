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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'y[uú]l[ií][aá]n[aá]' OR  a.rut ~* 'y[uú]l[ií][aá]n[aá]' OR  lower(a.email) ~* 'y[uú]l[ií][aá]n[aá]' OR  text(a.id) ~* 'y[uú]l[ií][aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR  lower(a.email) ~* '[aá]ndr[eé][aá]' OR  text(a.id) ~* '[aá]ndr[eé][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[eé][ií]v[aá]' OR  a.rut ~* 'l[eé][ií]v[aá]' OR  lower(a.email) ~* 'l[eé][ií]v[aá]' OR  text(a.id) ~* 'l[eé][ií]v[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[uú]ñ[eé]z' OR  a.rut ~* 'n[uú]ñ[eé]z' OR  lower(a.email) ~* 'n[uú]ñ[eé]z' OR  text(a.id) ~* 'n[uú]ñ[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER