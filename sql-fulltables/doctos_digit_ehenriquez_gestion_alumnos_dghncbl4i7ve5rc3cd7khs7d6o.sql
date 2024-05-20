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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]dr[ií][aá]n[aá]' OR  a.rut ~* '[aá]dr[ií][aá]n[aá]' OR  lower(a.email) ~* '[aá]dr[ií][aá]n[aá]' OR  text(a.id) ~* '[aá]dr[ií][aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[oó]f[ií][aá]' OR  a.rut ~* 's[oó]f[ií][aá]' OR  lower(a.email) ~* 's[oó]f[ií][aá]' OR  text(a.id) ~* 's[oó]f[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]r[eé]n[oó]' OR  a.rut ~* 'm[oó]r[eé]n[oó]' OR  lower(a.email) ~* 'm[oó]r[eé]n[oó]' OR  text(a.id) ~* 'm[oó]r[eé]n[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[uú]rg[oó]s' OR  a.rut ~* 'b[uú]rg[oó]s' OR  lower(a.email) ~* 'b[uú]rg[oó]s' OR  text(a.id) ~* 'b[uú]rg[oó]s' )  AND a.carrera_actual IN (94,97,104,110,98,105,102,91,15,103,126,4)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER