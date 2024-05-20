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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'n[ií]c[oó]l[eé]' OR  a.rut ~* 'n[ií]c[oó]l[eé]' OR  lower(a.email) ~* 'n[ií]c[oó]l[eé]' OR  text(a.id) ~* 'n[ií]c[oó]l[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]v[eé]lyn' OR  a.rut ~* '[eé]v[eé]lyn' OR  lower(a.email) ~* '[eé]v[eé]lyn' OR  text(a.id) ~* '[eé]v[eé]lyn' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[uú]b[ií][aá]br[eé]' OR  a.rut ~* 's[uú]b[ií][aá]br[eé]' OR  lower(a.email) ~* 's[uú]b[ií][aá]br[eé]' OR  text(a.id) ~* 's[uú]b[ií][aá]br[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ll[aá]gr[aá]' OR  a.rut ~* 'v[ií]ll[aá]gr[aá]' OR  lower(a.email) ~* 'v[ií]ll[aá]gr[aá]' OR  text(a.id) ~* 'v[ií]ll[aá]gr[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER