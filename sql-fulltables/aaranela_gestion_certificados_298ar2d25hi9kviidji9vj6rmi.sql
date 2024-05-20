COPY (SELECT ac.folio,vac.cod,trim(a.rut) AS rut,va.nombre AS alumno,trim(c.alias) AS carrera,a.jornada,r.nombre AS regimen,va.estado AS estado_alumno,cert.nombre AS docto,
                         to_char(ac.fec_impresion,'DD-tmMon-YYYY') AS fec_impresion,u.nombre_usuario AS emisor,to_char(ac.fecha,'DD-tmMon-YYYY') AS fecha,
                         to_char(ac.fec_entrega,'DD-tmMon-YYYY') AS fec_entrega,u2.nombre_usuario AS entregador,ac.ano_academico,
                         ac.estado,to_char(ac.estado_fecha,'DD-tmMon-YYYY  HH24:MI') AS estado_fecha,
                         CASE WHEN length(ac.archivo)>0 THEN 'Si' ELSE 'No' END AS docto_firmado,
                         CASE WHEN (SELECT 1 FROM matriculas WHERE id_alumno=a.id AND ano=2022 AND semestre=2 LIMIT 1)=1 THEN 'Si' ELSE 'No' END AS matric,
                         CASE WHEN a.moroso_financiero THEN '(M)' ELSE '' END AS moroso_financiero,
                         to_char(ac.archivo_fecha,'DD-tmMon-YYYY') AS archivo_fecha,u3.nombre_usuario as archivo_usuario,
                         ac.texto_adicional,u4.nombre_usuario as estado_usuario
                  FROM alumnos_certificados AS ac
                  LEFT JOIN vista_alumnos_certificados_codbarras AS vac USING (folio)
                  LEFT JOIN certificados    AS cert ON cert.id=ac.id_certificado
                  LEFT JOIN alumnos         AS a    ON a.id=ac.id_alumno
                  LEFT JOIN vista_alumnos   AS va   ON va.id=ac.id_alumno
                  LEFT JOIN carreras        AS c    ON c.id=a.carrera_actual
                  LEFT JOIN regimenes       AS r    ON r.id=c.regimen
                  LEFT JOIN usuarios        AS u    ON u.id=ac.id_emisor
                  LEFT JOIN usuarios        AS u2   ON u2.id=ac.id_entregador
                  LEFT JOIN usuarios        AS u3   ON u3.id=ac.archivo_id_usuario
                  LEFT JOIN usuarios        AS u4   ON u4.id=ac.estado_id_usuario
                  WHERE true AND ac.ano_academico=2022 AND ac.id_certificado=101 AND (c.regimen = 'PRE') 
                  ORDER BY ac.fecha DESC,va.nombre ) to stdout WITH CSV HEADER