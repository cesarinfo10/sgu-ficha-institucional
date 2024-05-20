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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]sv[aá]ld[oó]' OR  a.rut ~* '[oó]sv[aá]ld[oó]' OR  lower(a.email) ~* '[oó]sv[aá]ld[oó]' OR  text(a.id) ~* '[oó]sv[aá]ld[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[ií]c[oó]l[aá]s' OR  a.rut ~* 'n[ií]c[oó]l[aá]s' OR  lower(a.email) ~* 'n[ií]c[oó]l[aá]s' OR  text(a.id) ~* 'n[ií]c[oó]l[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[ií][aá]z' OR  a.rut ~* 'd[ií][aá]z' OR  lower(a.email) ~* 'd[ií][aá]z' OR  text(a.id) ~* 'd[ií][aá]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]t[aá]m[aá]l[aá]' OR  a.rut ~* 'm[aá]t[aá]m[aá]l[aá]' OR  lower(a.email) ~* 'm[aá]t[aá]m[aá]l[aá]' OR  text(a.id) ~* 'm[aá]t[aá]m[aá]l[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER