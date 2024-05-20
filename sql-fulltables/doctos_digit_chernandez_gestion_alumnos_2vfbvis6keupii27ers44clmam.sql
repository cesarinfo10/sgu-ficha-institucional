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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'k[aá]r[ií]n[aá]' OR  a.rut ~* 'k[aá]r[ií]n[aá]' OR  lower(a.email) ~* 'k[aá]r[ií]n[aá]' OR  text(a.id) ~* 'k[aá]r[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'br[aá]v[oó]' OR  a.rut ~* 'br[aá]v[oó]' OR  lower(a.email) ~* 'br[aá]v[oó]' OR  text(a.id) ~* 'br[aá]v[oó]' )  AND a.carrera_actual IN (94,97,104,110,98,105,102,91,15,103,126,4)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER