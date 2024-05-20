COPY (SELECT vu.id,vu.nombre,vu.nombre_usuario,vu.tipo,vu.escuela,gu.alias AS unidad,vu.email,
                        CASE WHEN jefe_unidad THEN '[Jefe]' ELSE '' END AS jefe_unidad
                 FROM vista_usuarios AS vu
                 LEFT JOIN usuarios AS u USING (id)
                 LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
                 WHERE u.tipo<>3  AND (lower(u.nombre||' '||u.apellido) ~* 'gm[aá]rt[ií]n[eé]z' OR  u.rut ~* 'gm[aá]rt[ií]n[eé]z' OR  lower(u.email) ~* 'gm[aá]rt[ií]n[eé]z' OR  lower(u.nombre_usuario) ~* 'gm[aá]rt[ií]n[eé]z' OR  text(u.id) ~* 'gm[aá]rt[ií]n[eé]z' ) 
                 ORDER BY vu.nombre_usuario) to stdout WITH CSV HEADER