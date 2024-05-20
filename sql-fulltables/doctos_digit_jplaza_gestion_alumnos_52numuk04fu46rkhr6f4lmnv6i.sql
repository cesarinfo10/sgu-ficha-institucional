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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'f[oó]rt[uú]n[aá]t[oó]' OR  a.rut ~* 'f[oó]rt[uú]n[aá]t[oó]' OR  lower(a.email) ~* 'f[oó]rt[uú]n[aá]t[oó]' OR  text(a.id) ~* 'f[oó]rt[uú]n[aá]t[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[ií]r[aá][ií]n[oó]' OR  a.rut ~* 'p[ií]r[aá][ií]n[oó]' OR  lower(a.email) ~* 'p[ií]r[aá][ií]n[oó]' OR  text(a.id) ~* 'p[ií]r[aá][ií]n[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER