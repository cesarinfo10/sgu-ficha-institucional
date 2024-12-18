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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 's[ií]r[ií][aá]' OR  a.rut ~* 's[ií]r[ií][aá]' OR  lower(a.email) ~* 's[ií]r[ií][aá]' OR  text(a.id) ~* 's[ií]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 't[eé]j[oó]' OR  a.rut ~* 't[eé]j[oó]' OR  lower(a.email) ~* 't[eé]j[oó]' OR  text(a.id) ~* 't[eé]j[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]nt[ií]b[aá]ñ[eé]z' OR  a.rut ~* 's[aá]nt[ií]b[aá]ñ[eé]z' OR  lower(a.email) ~* 's[aá]nt[ií]b[aá]ñ[eé]z' OR  text(a.id) ~* 's[aá]nt[ií]b[aá]ñ[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER