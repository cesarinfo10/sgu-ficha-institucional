COPY (SELECT c.id,to_char(c.fecha,'DD-tmMon-YYYY') AS fecha,c.estado,
                            date_part('year',c.fecha) AS periodo,trim(a.rut) AS rut,c.id_alumno,
                            upper(a.apellidos) AS al_apellidos,initcap(a.nombres) AS al_nombres,c.monto_liqci,
                            c.monto_liqci::float/uf.valor::float AS monto_liqci_uf,
                            c.descuento_inicial,c.monto_adicional,c.monto_adicional::float/uf.valor::float AS monto_adicional_uf,
                            c.descuento_inicial::float/uf.valor::float AS descuento_inicial_uf,
                            trim(car.alias) AS carrera,a.jornada,
                            c.liqci_efectivo,c.liqci_cheque,coalesce(c.liqci_cant_cheques,0) AS liqci_cant_cheques,
                            c.liqci_pagare,coalesce(c.liqci_cuotas_pagare,0) AS liqci_cuotas_pagare,
                            c.liqci_tarj_credito,coalesce(c.liqci_cant_tarj_credito,0) AS liqci_cant_tarj_credito,
                            CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,
                            to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                            vc.total_pagado AS monto_pagado,vc.saldo_total AS monto_saldot,vc.monto_moroso,vc.cant_cuotas_morosas AS cuotas_morosas,
                            (SELECT sum(coalesce(monto_abonado,monto)) FROM finanzas.cobros WHERE id_convenio_ci=c.id AND date_part('year',fecha_venc) > date_part('year',now()) AND (NOT pagado OR abonado)) AS monto_saldot_lp,
                            (SELECT 0) AS pagos_rango_fechas,
                            u.nombre_usuario AS emisor,c.comentarios,
                            a.direccion,va.comuna,va.region,a.telefono,a.tel_movil,a.email,a.genero,a.fec_nac,
                            a.cohorte,a.mes_cohorte,uf.valor AS valor_uf,c.nulo
                     FROM finanzas.convenios_ci AS c
                     LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
                     LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                     LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
                     LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                     LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
                     LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=c.fecha
                     WHERE (lower(va.nombre) ~* '5528385' OR  a.rut ~* '5528385' OR  text(c.id) ~* '5528385' ) 
                     ORDER BY c.fecha DESC,al_apellidos,al_nombres ) to stdout WITH CSV HEADER