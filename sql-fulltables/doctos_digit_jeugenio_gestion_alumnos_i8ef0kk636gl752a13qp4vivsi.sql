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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'gl[oó]r[ií][aá]' OR  a.rut ~* 'gl[oó]r[ií][aá]' OR  lower(a.email) ~* 'gl[oó]r[ií][aá]' OR  text(a.id) ~* 'gl[oó]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 't[aá]p[ií][aá]' OR  a.rut ~* 't[aá]p[ií][aá]' OR  lower(a.email) ~* 't[aá]p[ií][aá]' OR  text(a.id) ~* 't[aá]p[ií][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER