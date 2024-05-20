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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  a.rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  lower(a.email) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  text(a.id) ~* 'm[aá][uú]r[ií]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]z[aá]n[oó]' OR  a.rut ~* 'l[oó]z[aá]n[oó]' OR  lower(a.email) ~* 'l[oó]z[aá]n[oó]' OR  text(a.id) ~* 'l[oó]z[aá]n[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]st[aá]nz[oó]' OR  a.rut ~* 'c[oó]st[aá]nz[oó]' OR  lower(a.email) ~* 'c[oó]st[aá]nz[oó]' OR  text(a.id) ~* 'c[oó]st[aá]nz[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER