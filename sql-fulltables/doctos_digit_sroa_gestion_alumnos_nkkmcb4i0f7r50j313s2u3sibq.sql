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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'g[eé]n[aá]r[oó]' OR  a.rut ~* 'g[eé]n[aá]r[oó]' OR  lower(a.email) ~* 'g[eé]n[aá]r[oó]' OR  text(a.id) ~* 'g[eé]n[aá]r[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[ií]d' OR  a.rut ~* 'c[ií]d' OR  lower(a.email) ~* 'c[ií]d' OR  text(a.id) ~* 'c[ií]d' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'br[ií][oó]n[eé]s' OR  a.rut ~* 'br[ií][oó]n[eé]s' OR  lower(a.email) ~* 'br[ií][oó]n[eé]s' OR  text(a.id) ~* 'br[ií][oó]n[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER