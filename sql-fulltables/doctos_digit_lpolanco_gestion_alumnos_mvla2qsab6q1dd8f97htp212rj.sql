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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 's[ií]lv[ií][aá]' OR  a.rut ~* 's[ií]lv[ií][aá]' OR  lower(a.email) ~* 's[ií]lv[ií][aá]' OR  text(a.id) ~* 's[ií]lv[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[aá]r[aá]' OR  a.rut ~* 'l[aá]r[aá]' OR  lower(a.email) ~* 'l[aá]r[aá]' OR  text(a.id) ~* 'l[aá]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]lg[aá]d[oó]' OR  a.rut ~* 's[aá]lg[aá]d[oó]' OR  lower(a.email) ~* 's[aá]lg[aá]d[oó]' OR  text(a.id) ~* 's[aá]lg[aá]d[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER