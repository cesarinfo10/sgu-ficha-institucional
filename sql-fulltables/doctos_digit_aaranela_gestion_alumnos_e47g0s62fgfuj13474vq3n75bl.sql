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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  a.rut ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  lower(a.email) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  text(a.id) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]r[eé]n[aá]' OR  a.rut ~* 'l[oó]r[eé]n[aá]' OR  lower(a.email) ~* 'l[oó]r[eé]n[aá]' OR  text(a.id) ~* 'l[oó]r[eé]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]r[ií][aá]s' OR  a.rut ~* '[aá]r[ií][aá]s' OR  lower(a.email) ~* '[aá]r[ií][aá]s' OR  text(a.id) ~* '[aá]r[ií][aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]r[eé]n[aá]s' OR  a.rut ~* '[aá]r[eé]n[aá]s' OR  lower(a.email) ~* '[aá]r[eé]n[aá]s' OR  text(a.id) ~* '[aá]r[eé]n[aá]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER