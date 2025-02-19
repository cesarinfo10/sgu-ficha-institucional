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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]v[eé]ly' OR  a.rut ~* '[eé]v[eé]ly' OR  lower(a.email) ~* '[eé]v[eé]ly' OR  text(a.id) ~* '[eé]v[eé]ly' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]br[eé]r[aá]' OR  a.rut ~* 'c[aá]br[eé]r[aá]' OR  lower(a.email) ~* 'c[aá]br[eé]r[aá]' OR  text(a.id) ~* 'c[aá]br[eé]r[aá]' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER