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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]r[eé]d[eé]s' OR  a.rut ~* 'p[aá]r[eé]d[eé]s' OR  lower(a.email) ~* 'p[aá]r[eé]d[eé]s' OR  text(a.id) ~* 'p[aá]r[eé]d[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[aá]r[ií][aá]s' OR  a.rut ~* 'f[aá]r[ií][aá]s' OR  lower(a.email) ~* 'f[aá]r[ií][aá]s' OR  text(a.id) ~* 'f[aá]r[ií][aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[uú]z' OR  a.rut ~* 'l[uú]z' OR  lower(a.email) ~* 'l[uú]z' OR  text(a.id) ~* 'l[uú]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  a.rut ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  lower(a.email) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  text(a.id) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER