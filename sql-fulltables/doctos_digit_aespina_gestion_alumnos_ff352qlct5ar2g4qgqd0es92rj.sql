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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]s[eé]f[aá]' OR  a.rut ~* 'j[oó]s[eé]f[aá]' OR  lower(a.email) ~* 'j[oó]s[eé]f[aá]' OR  text(a.id) ~* 'j[oó]s[eé]f[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]br[ií][eé]l[aá]' OR  a.rut ~* 'g[aá]br[ií][eé]l[aá]' OR  lower(a.email) ~* 'g[aá]br[ií][eé]l[aá]' OR  text(a.id) ~* 'g[aá]br[ií][eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'fryd[eé]r[uú]p' OR  a.rut ~* 'fryd[eé]r[uú]p' OR  lower(a.email) ~* 'fryd[eé]r[uú]p' OR  text(a.id) ~* 'fryd[eé]r[uú]p' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[eé]r[oó]n' OR  a.rut ~* 'c[eé]r[oó]n' OR  lower(a.email) ~* 'c[eé]r[oó]n' OR  text(a.id) ~* 'c[eé]r[oó]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER