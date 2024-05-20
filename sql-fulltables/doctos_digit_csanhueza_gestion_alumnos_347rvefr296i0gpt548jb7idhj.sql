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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[eé]c[ií]l[ií][aá]' OR  a.rut ~* 'c[eé]c[ií]l[ií][aá]' OR  lower(a.email) ~* 'c[eé]c[ií]l[ií][aá]' OR  text(a.id) ~* 'c[eé]c[ií]l[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR  lower(a.email) ~* '[aá]ndr[eé][aá]' OR  text(a.id) ~* '[aá]ndr[eé][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'll[aá]ñ[aá]' OR  a.rut ~* 'll[aá]ñ[aá]' OR  lower(a.email) ~* 'll[aá]ñ[aá]' OR  text(a.id) ~* 'll[aá]ñ[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[uú]b[ií]l[aá]r' OR  a.rut ~* 'r[uú]b[ií]l[aá]r' OR  lower(a.email) ~* 'r[uú]b[ií]l[aá]r' OR  text(a.id) ~* 'r[uú]b[ií]l[aá]r' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER