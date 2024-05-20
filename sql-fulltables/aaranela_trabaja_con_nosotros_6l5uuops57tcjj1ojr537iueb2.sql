COPY (SELECT pp.id,pp.rut,pp.apellidos,initcap(pp.nombres) AS nombres,(SELECT char_comma_sum(alias) FROM carreras WHERE id::text = ANY(string_to_array(pp.carreras,','))) AS carreras,pp.genero,
                          horarios,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,ga.nombre AS grado_acad,pp.email,p.nombre AS pais
                   FROM portalweb.profes_post AS pp
                   LEFT JOIN grado_acad       AS ga ON ga.id=pp.est_grado_acad
                   LEFT JOIN pais             AS p ON p.localizacion=pp.nacionalidad
                   WHERE true  AND (lower(pp.nombres) ~* 'v[eé]g[aá]') OR (lower(pp.apellidos) ~* 'v[eé]g[aá]') OR (lower(pp.rut) ~* 'v[eé]g[aá]') AND (lower(pp.nombres) ~* '[aá]rc[oó]s') OR (lower(pp.apellidos) ~* '[aá]rc[oó]s') OR (lower(pp.rut) ~* '[aá]rc[oó]s')
                   ORDER BY pp.fecha DESC,pp.apellidos,pp.nombres ) to stdout WITH CSV HEADER