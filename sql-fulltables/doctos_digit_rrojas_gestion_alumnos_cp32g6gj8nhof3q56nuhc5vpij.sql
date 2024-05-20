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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]br[ií][eé]l[aá]' OR  a.rut ~* 'g[aá]br[ií][eé]l[aá]' OR  lower(a.email) ~* 'g[aá]br[ií][eé]l[aá]' OR  text(a.id) ~* 'g[aá]br[ií][eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[aá]' OR  a.rut ~* 'f[eé]rn[aá]nd[aá]' OR  lower(a.email) ~* 'f[eé]rn[aá]nd[aá]' OR  text(a.id) ~* 'f[eé]rn[aá]nd[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]l[ií]v[aá]r[eé]s' OR  a.rut ~* '[oó]l[ií]v[aá]r[eé]s' OR  lower(a.email) ~* '[oó]l[ií]v[aá]r[eé]s' OR  text(a.id) ~* '[oó]l[ií]v[aá]r[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  a.rut ~* 'r[oó]dr[ií]g[uú][eé]z' OR  lower(a.email) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  text(a.id) ~* 'r[oó]dr[ií]g[uú][eé]z' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125,147,148)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER