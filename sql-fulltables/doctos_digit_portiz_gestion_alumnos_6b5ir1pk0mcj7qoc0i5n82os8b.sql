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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'k[aá]r[eé]n' OR  a.rut ~* 'k[aá]r[eé]n' OR  lower(a.email) ~* 'k[aá]r[eé]n' OR  text(a.id) ~* 'k[aá]r[eé]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR  lower(a.email) ~* '[aá]ndr[eé][aá]' OR  text(a.id) ~* '[aá]ndr[eé][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[eé]z' OR  a.rut ~* 'f[eé]rn[aá]nd[eé]z' OR  lower(a.email) ~* 'f[eé]rn[aá]nd[eé]z' OR  text(a.id) ~* 'f[eé]rn[aá]nd[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'q[uú][ií]v[ií]r[aá]' OR  a.rut ~* 'q[uú][ií]v[ií]r[aá]' OR  lower(a.email) ~* 'q[uú][ií]v[ií]r[aá]' OR  text(a.id) ~* 'q[uú][ií]v[ií]r[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER