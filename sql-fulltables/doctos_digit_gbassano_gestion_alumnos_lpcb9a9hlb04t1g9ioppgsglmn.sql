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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'cr[ií]st[ií]n[aá]' OR  a.rut ~* 'cr[ií]st[ií]n[aá]' OR  lower(a.email) ~* 'cr[ií]st[ií]n[aá]' OR  text(a.id) ~* 'cr[ií]st[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[eé]r[ií][aá]' OR  a.rut ~* 'm[aá][uú]r[eé]r[ií][aá]' OR  lower(a.email) ~* 'm[aá][uú]r[eé]r[ií][aá]' OR  text(a.id) ~* 'm[aá][uú]r[eé]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[uú]ln[eé]s' OR  a.rut ~* 'b[uú]ln[eé]s' OR  lower(a.email) ~* 'b[uú]ln[eé]s' OR  text(a.id) ~* 'b[uú]ln[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER