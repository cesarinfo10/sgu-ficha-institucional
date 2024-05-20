COPY (SELECT pp.id,pp.rut,pp.apellidos,initcap(pp.nombres) AS nombres,(SELECT char_comma_sum(alias) FROM carreras WHERE id::text = ANY(string_to_array(pp.carreras,','))) AS carreras,pp.genero,
                          horarios,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,ga.nombre AS grado_acad,pp.email,p.nombre AS pais
                   FROM portalweb.profes_post AS pp
                   LEFT JOIN grado_acad       AS ga ON ga.id=pp.est_grado_acad
                   LEFT JOIN pais             AS p ON p.localizacion=pp.nacionalidad
                   WHERE true  AND (lower(pp.nombres) ~* '[aá]rm[aá]nd[oó]') OR (lower(pp.apellidos) ~* '[aá]rm[aá]nd[oó]') OR (lower(pp.rut) ~* '[aá]rm[aá]nd[oó]') AND (lower(pp.nombres) ~* '[aá]l[eé]j[aá]ndr[oó]') OR (lower(pp.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]') OR (lower(pp.rut) ~* '[aá]l[eé]j[aá]ndr[oó]') AND (lower(pp.nombres) ~* '[oó]rm[eé]ñ[oó]') OR (lower(pp.apellidos) ~* '[oó]rm[eé]ñ[oó]') OR (lower(pp.rut) ~* '[oó]rm[eé]ñ[oó]') AND (lower(pp.nombres) ~* '[oó]rt[ií]z') OR (lower(pp.apellidos) ~* '[oó]rt[ií]z') OR (lower(pp.rut) ~* '[oó]rt[ií]z')
                   ORDER BY pp.fecha DESC,pp.apellidos,pp.nombres ) to stdout WITH CSV HEADER