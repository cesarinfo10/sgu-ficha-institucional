COPY (SELECT coalesce(nro_boleta,nro_boleta_e,nro_factura) AS nro_docto,
	               CASE WHEN nro_boleta IS NOT NULL THEN 'Bol'
				        WHEN nro_boleta_e IS NOT NULL THEN 'Bol-E'
						WHEN nro_factura IS NOT NULL THEN 'Fac'
				   END AS tipo_docto,p.fecha::date,
				   g.nombre AS glosa,c.ano AS ano_contrato,pd.monto_pagado,
				   trim(coalesce(a.rut,a2.rut,a3.rut,pap.rut)) AS rut,
				   coalesce(car.alias,car2.alias,car3.alias) AS carrera,
				   coalesce(car.regimen,car2.regimen,car3.regimen) AS regimen,
				   g.cod_producto_erp,ccc.codigo_erp AS cod_centrodecosto_erp,
				   coalesce(g.cod_cta_contable_erp,cpc1.codigo::text,cpc2.codigo::text) AS cod_cta_contable_erp
			FROM finanzas.pagos_detalle     AS pd
			LEFT JOIN finanzas.pagos        AS p    ON p.id=pd.id_pago
			LEFT JOIN vista_usuarios        AS u    ON u.id=p.id_cajero
			LEFT JOIN finanzas.cobros       AS cob  ON cob.id=pd.id_cobro
			LEFT JOIN finanzas.glosas       AS g    ON g.id=cob.id_glosa
			LEFT JOIN finanzas.contratos    AS c    ON c.id=cob.id_contrato
			LEFT JOIN finanzas.convenios_ci AS cci  ON cci.id=cob.id_convenio_ci
			LEFT JOIN alumnos               AS a    ON a.id=c.id_alumno
			LEFT JOIN alumnos               AS a2   ON a2.id=cci.id_alumno
			LEFT JOIN alumnos               AS a3   ON a3.id=cob.id_alumno
			LEFT JOIN carreras              AS car  ON car.id=c.id_carrera
			LEFT JOIN carreras              AS car2 ON car2.id=a2.carrera_actual
			LEFT JOIN carreras              AS car3 ON car3.id=a3.carrera_actual
			LEFT JOIN pap                           ON pap.id=c.id_pap
			LEFT JOIN finanzas.conta_plandecuentas AS cpc1 ON (cpc1.ano=coalesce(c.ano,date_part('year',cci.fecha)) 
			                                               AND cpc1.regimen=coalesce(car.regimen,car2.regimen,car3.regimen) 
											               AND cpc1.docto_xcobrar=g.docto_xcobrar)
            LEFT JOIN finanzas.conta_plandecuentas AS cpc2 ON (cpc2.docto_xcobrar=g.docto_xcobrar 
			                                               AND cpc2.ano IS NULL 
											               AND cpc2.regimen IS NULL)
	        LEFT JOIN finanzas.conta_centrosdecosto AS ccc ON ccc.id_carrera=coalesce(a.carrera_actual,a2.carrera_actual,c.id_carrera)
			WHERE (p.nro_boleta IS NOT NULL OR p.nro_boleta_e IS NOT NULL OR p.nro_factura IS NOT NULL) AND p.fecha BETWEEN '2023-05-18'::date AND '2023-05-18'::date 
			ORDER BY p.fecha) to stdout WITH CSV HEADER