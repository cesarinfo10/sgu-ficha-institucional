COPY (SELECT act.*,(SELECT char_comma_sum(tipo_publico||': '||coalesce((coalesce(cant_personas,0)+coalesce(cant_personas_virtuales,0))::text,'*')) 
              FROM vcm.participacion_act
			  WHERE id_actividad=act.id) AS asistencia,(SELECT char_comma_sum(doc_tipo.nombre||':'||doctos.id::text) 
               FROM vcm.documentos_act AS doctos
			   LEFT JOIN vcm.documentos_act_tipo AS doc_tipo ON doc_tipo.id=doctos.id_tipo
               WHERE doctos.id_actividad=act.id) AS doctos ,(SELECT char_comma_sum(it.nombre||': '||coalesce(valor::text||CASE WHEN porcentaje THEN '%' ELSE '' END,'*'))
            FROM vcm.indicadores_act AS ind 
            LEFT JOIN vcm.indicadores_act_tipo AS it ON it.id=ind.id_tipo 
            WHERE id_actividad=act.id) AS indicadores,(SELECT sum(coalesce(cant_personas,0)+coalesce(cant_personas_virtuales,0)) 
                  FROM vcm.participacion_act
			      WHERE id_actividad=act.id) AS asist_tot 
            FROM vista_vcm_actividades AS act
			LEFT JOIN gestion.unidades AS u1 ON u1.id=act.id_unidad1
			LEFT JOIN gestion.unidades AS u2 ON u2.id=act.id_unidad2
			LEFT JOIN gestion.unidades AS u3 ON u3.id=act.id_unidad3
			WHERE true  AND (ano = 2023) AND (22 IN (id_unidad1,id_unidad2,id_unidad3)) 
			ORDER BY fecha_termino ) to stdout WITH CSV HEADER