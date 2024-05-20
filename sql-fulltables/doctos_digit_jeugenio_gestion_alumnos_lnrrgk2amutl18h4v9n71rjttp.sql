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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'b[eé][aá]tr[ií]z' OR  a.rut ~* 'b[eé][aá]tr[ií]z' OR  lower(a.email) ~* 'b[eé][aá]tr[ií]z' OR  text(a.id) ~* 'b[eé][aá]tr[ií]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]' OR  a.rut ~* 'm[aá]r[ií][aá]' OR  lower(a.email) ~* 'm[aá]r[ií][aá]' OR  text(a.id) ~* 'm[aá]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'y[uú]l[ií][aá]n[aá]' OR  a.rut ~* 'y[uú]l[ií][aá]n[aá]' OR  lower(a.email) ~* 'y[uú]l[ií][aá]n[aá]' OR  text(a.id) ~* 'y[uú]l[ií][aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]l[ií]n[aá]' OR  a.rut ~* 'm[oó]l[ií]n[aá]' OR  lower(a.email) ~* 'm[oó]l[ií]n[aá]' OR  text(a.id) ~* 'm[oó]l[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'fl[oó]r[eé]s' OR  a.rut ~* 'fl[oó]r[eé]s' OR  lower(a.email) ~* 'fl[oó]r[eé]s' OR  text(a.id) ~* 'fl[oó]r[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER