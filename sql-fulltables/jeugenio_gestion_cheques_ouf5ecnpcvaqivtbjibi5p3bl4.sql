COPY (SELECT p.id AS id_pago,CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' WHEN p.nro_factura IS NOT NULL THEN 'F' END AS tipo_doc,
                       coalesce(p.nro_boleta,p.nro_factura) AS nro_docto,to_char(p.fecha,'DD-MM-YYYY') AS fecha,vpr.rut AS rut_alumno,
                       u.nombre_usuario AS cajero,to_char(ch.fecha_venc,'DD-MM-YYYY') AS fecha_venc,ch.nombre_emisor,ch.rut_emisor,
                       if.nombre AS banco,ch.monto,ch.numero,ch.id AS id_cheque,
                       CASE WHEN ch.depositado THEN 'Si' ELSE 'No' END AS depositado,
                       CASE ch.protestado WHEN true THEN 'Si' WHEN false THEN 'No' ELSE 'N/D' END AS protestado,
                       CASE ch.aclarado   WHEN true THEN 'Si' WHEN false THEN 'No' ELSE 'N/D' END AS aclarado
                FROM finanzas.cheques AS ch
                LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=ch.cod_inst_finan
                LEFT JOIN finanzas.pagos AS p             ON p.id=ch.id_pago
                LEFT JOIN vista_pagos_rut AS vpr          ON p.id=vpr.id
                LEFT JOIN vista_usuarios AS u             ON u.id=p.id_cajero
                WHERE (p.nro_boleta IS NOT NULL OR p.nro_factura IS NOT NULL) AND ch.fecha_venc BETWEEN now()-'7 days'::interval AND now()  
                ORDER BY ch.fecha_venc DESC ) to stdout WITH CSV HEADER