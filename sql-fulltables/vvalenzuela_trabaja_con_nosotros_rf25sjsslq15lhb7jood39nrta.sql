COPY (SELECT pp.id,pp.rut,pp.apellidos,initcap(pp.nombres) AS nombres,(SELECT char_comma_sum(alias) FROM carreras WHERE id::text = ANY(string_to_array(pp.carreras,','))) AS carreras,pp.genero,
                          horarios,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,ga.nombre AS grado_acad,pp.email,p.nombre AS pais
                   FROM portalweb.profes_post AS pp
                   LEFT JOIN grado_acad       AS ga ON ga.id=pp.est_grado_acad
                   LEFT JOIN pais             AS p ON p.localizacion=pp.nacionalidad
                   WHERE true  AND (lower(pp.nombres) ~* 'j[oó]s[eé]') OR (lower(pp.apellidos) ~* 'j[oó]s[eé]') OR (lower(pp.rut) ~* 'j[oó]s[eé]') AND (lower(pp.nombres) ~* 'h[eé]rn[aá]nd[eé]z') OR (lower(pp.apellidos) ~* 'h[eé]rn[aá]nd[eé]z') OR (lower(pp.rut) ~* 'h[eé]rn[aá]nd[eé]z') AND 17::text = ANY(string_to_array(pp.carreras,',')) 
                   ORDER BY pp.fecha DESC,pp.apellidos,pp.nombres ) to stdout WITH CSV HEADER