COPY (SELECT vu.id,vu.nombre,vu.nombre_usuario,vu.tipo,vu.escuela,gu.alias AS unidad,vu.email,
                        CASE WHEN jefe_unidad THEN '[Jefe]' ELSE '' END AS jefe_unidad
                 FROM vista_usuarios AS vu
                 LEFT JOIN usuarios AS u USING (id)
                 LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
                 WHERE u.tipo<>3  AND (lower(u.nombre||' '||u.apellido) ~* 'm[aá]rt[ií]' OR  u.rut ~* 'm[aá]rt[ií]' OR  lower(u.email) ~* 'm[aá]rt[ií]' OR  lower(u.nombre_usuario) ~* 'm[aá]rt[ií]' OR  text(u.id) ~* 'm[aá]rt[ií]' ) 
                 ORDER BY vu.nombre_usuario) to stdout WITH CSV HEADER