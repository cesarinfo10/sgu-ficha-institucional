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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'q[uú][ií]r[oó]z' OR  a.rut ~* 'q[uú][ií]r[oó]z' OR  lower(a.email) ~* 'q[uú][ií]r[oó]z' OR  text(a.id) ~* 'q[uú][ií]r[oó]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rr[aá]sc[oó]' OR  a.rut ~* 'c[aá]rr[aá]sc[oó]' OR  lower(a.email) ~* 'c[aá]rr[aá]sc[oó]' OR  text(a.id) ~* 'c[aá]rr[aá]sc[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  a.rut ~* 'fr[aá]nc[ií]sc[oó]' OR  lower(a.email) ~* 'fr[aá]nc[ií]sc[oó]' OR  text(a.id) ~* 'fr[aá]nc[ií]sc[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]v[ií][eé]r' OR  a.rut ~* 'j[aá]v[ií][eé]r' OR  lower(a.email) ~* 'j[aá]v[ií][eé]r' OR  text(a.id) ~* 'j[aá]v[ií][eé]r' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER