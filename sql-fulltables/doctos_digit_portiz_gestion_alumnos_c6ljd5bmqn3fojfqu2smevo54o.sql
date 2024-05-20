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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'f[aá]b[ií][aá]n[aá]' OR  a.rut ~* 'f[aá]b[ií][aá]n[aá]' OR  lower(a.email) ~* 'f[aá]b[ií][aá]n[aá]' OR  text(a.id) ~* 'f[aá]b[ií][aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]st[eé]l[oó]' OR  a.rut ~* 'c[aá]st[eé]l[oó]' OR  lower(a.email) ~* 'c[aá]st[eé]l[oó]' OR  text(a.id) ~* 'c[aá]st[eé]l[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'br[aá]nc[oó]' OR  a.rut ~* 'br[aá]nc[oó]' OR  lower(a.email) ~* 'br[aá]nc[oó]' OR  text(a.id) ~* 'br[aá]nc[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[ií]r[ií]n[oó]' OR  a.rut ~* 'c[ií]r[ií]n[oó]' OR  lower(a.email) ~* 'c[ií]r[ií]n[oó]' OR  text(a.id) ~* 'c[ií]r[ií]n[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER