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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'sh[aá]nd[oó]r' OR  a.rut ~* 'sh[aá]nd[oó]r' OR  lower(a.email) ~* 'sh[aá]nd[oó]r' OR  text(a.id) ~* 'sh[aá]nd[oó]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]n[ií][eé]l' OR  a.rut ~* 'd[aá]n[ií][eé]l' OR  lower(a.email) ~* 'd[aá]n[ií][eé]l' OR  text(a.id) ~* 'd[aá]n[ií][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'dr[oó]g[uú][eé]tt' OR  a.rut ~* 'dr[oó]g[uú][eé]tt' OR  lower(a.email) ~* 'dr[oó]g[uú][eé]tt' OR  text(a.id) ~* 'dr[oó]g[uú][eé]tt' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]rr[eé][aá]' OR  a.rut ~* 'c[oó]rr[eé][aá]' OR  lower(a.email) ~* 'c[oó]rr[eé][aá]' OR  text(a.id) ~* 'c[oó]rr[eé][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER