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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[aá]rr[aá][ií]n' OR  a.rut ~* 'l[aá]rr[aá][ií]n' OR  lower(a.email) ~* 'l[aá]rr[aá][ií]n' OR  text(a.id) ~* 'l[aá]rr[aá][ií]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'br[aá]v[oó]' OR  a.rut ~* 'br[aá]v[oó]' OR  lower(a.email) ~* 'br[aá]v[oó]' OR  text(a.id) ~* 'br[aá]v[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá][ií]m[eé]' OR  a.rut ~* 'j[aá][ií]m[eé]' OR  lower(a.email) ~* 'j[aá][ií]m[eé]' OR  text(a.id) ~* 'j[aá][ií]m[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lb[eé]rt[oó]' OR  a.rut ~* '[aá]lb[eé]rt[oó]' OR  lower(a.email) ~* '[aá]lb[eé]rt[oó]' OR  text(a.id) ~* '[aá]lb[eé]rt[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER