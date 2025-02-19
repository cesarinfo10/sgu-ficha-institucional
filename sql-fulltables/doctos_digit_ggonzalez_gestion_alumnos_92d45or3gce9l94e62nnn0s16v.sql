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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]br[ií][eé]l' OR  a.rut ~* 'g[aá]br[ií][eé]l' OR  lower(a.email) ~* 'g[aá]br[ií][eé]l' OR  text(a.id) ~* 'g[aá]br[ií][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[ií]ll[aá]m[aá]n' OR  a.rut ~* 'b[ií]ll[aá]m[aá]n' OR  lower(a.email) ~* 'b[ií]ll[aá]m[aá]n' OR  text(a.id) ~* 'b[ií]ll[aá]m[aá]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER