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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]rm[aá]nd[oó]' OR  a.rut ~* '[aá]rm[aá]nd[oó]' OR  lower(a.email) ~* '[aá]rm[aá]nd[oó]' OR  text(a.id) ~* '[aá]rm[aá]nd[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]v[eé][oó]' OR  a.rut ~* 'c[aá]v[eé][oó]' OR  lower(a.email) ~* 'c[aá]v[eé][oó]' OR  text(a.id) ~* 'c[aá]v[eé][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]ng[uú][ií]n[eé]t[ií]' OR  a.rut ~* 's[aá]ng[uú][ií]n[eé]t[ií]' OR  lower(a.email) ~* 's[aá]ng[uú][ií]n[eé]t[ií]' OR  text(a.id) ~* 's[aá]ng[uú][ií]n[eé]t[ií]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER