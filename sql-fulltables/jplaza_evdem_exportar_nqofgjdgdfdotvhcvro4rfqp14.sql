COPY (
select 
	id, 
	nombre, 
	glosa_tipo_ponderaciones, 
	unidad, 
	nombre_usuario,
	sum(resulteval) resulteval, 
	sum(asistencia) asistencia, 
	sum(capacitacion) capacitacion, 
	sum(autoevaluacion) autoevaluacion, 
	sum(poa) poa, 
	sum(final_resultado) final_resultado,
	p1_responsab,
	p2_responsab,
	p3_responsab,
	p1_actitud,
	p2_actitud,
	p3_actitud,
	p4_actitud,
	p5_actitud,
	p1_cargo,
	p2_cargo,
	p3_cargo,
	p4_cargo,
	p5_cargo,
	p1_direccion,
	p2_direccion,
	p3_direccion,
	p4_direccion,
	esfuerzo_de_mejora,
	necesidad_capacitacion,
	comentario_desempeno		
	from (
select 
u.id as id, 
u.nombre || ' ' || u.apellido as nombre, 
(select glosa_tipo_ponderaciones from tipo_ponderaciones where id = u.id_tipo_ponderaciones) glosa_tipo_ponderaciones, 
gu.nombre as unidad,
u.nombre_usuario nombre_usuario,
coalesce(uj.resultado_eval,0) as resulteval,
coalesce(uj.porc_asistencia,0) as asistencia, 
coalesce(uj.porc_capacitacion,0) as capacitacion,
coalesce(
	case when (
				(
					select uj2.final_auto_funcionario_directivo_vicerrector 
						from usuarios_jerarquia uj2 
							where uj2.id_evaluador = u.id 
							and uj2.id_evaluado = u.id 
							and uj2.id_periodo_eval = uj.id_periodo_eval
				) is not null	
			) then 
				(
					select uj2.final_auto_funcionario_directivo_vicerrector 
						from usuarios_jerarquia uj2 
							where uj2.id_evaluador = u.id 
							and uj2.id_evaluado = u.id 
							and uj2.id_periodo_eval = uj.id_periodo_eval
				)

	else uj.final_auto_funcionario_directivo_vicerrector
	end
	,0) as autoevaluacion, 			
coalesce(uj.cumplimiento_poa,0) as poa,
coalesce(uj.final_resultado,0) as final_resultado,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P1'
	)
) p1_responsab,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P2'
	)
) p2_responsab,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P3'
	)
) p3_responsab,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P4'
	)
) p1_actitud,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P5'
	)
) p2_actitud,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P6'
	)
) p3_actitud,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P7'
	)
) p4_actitud,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P8'
	)
) p5_actitud,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P9'
	)
) p1_cargo,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P10'
	)
) p2_cargo,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P11'
	)
) p3_cargo,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P12'
	)
) p4_cargo,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P13'
	)
) p5_cargo,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P14'
	)
) p1_direccion,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P15'
	)
) p2_direccion,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P16'
	)
) p3_direccion,
(
	select evaluacion  from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id
	and id_eval_items_preguntas = (
		select id_eval_items from eval_items_preguntas
		where cod_interno = '1_P17'
	)
) p4_direccion,

(	select evaluacion respuesta_01 from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id	
	and evaluacion is not null 
	and id_eval_items_preguntas = 18

) esfuerzo_de_mejora,
(
	select evaluacion respuesta_02 from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id 
	and id_eval_items_preguntas = 19
) necesidad_capacitacion,
(
	select evaluacion respuesta_03 from eval_items_evaluaciones 
	where id_usuario_jerarquia = uj.id 
	and id_eval_items_preguntas = 20
) comentario_desempeno
from 
usuarios u 
left join gestion.unidades as gu on gu.id = u.id_unidad
left join usuarios_jerarquia as uj on uj.id_evaluado = u.id 
								and uj.id_evaluador <> u.id 
								and uj.id_periodo_eval = 3
where u.tipo <> 3 and u.activo
and u.id_unidad is not null
) as tabla
group by 
id, 
nombre, 
glosa_tipo_ponderaciones,
unidad,
nombre_usuario, 
p1_responsab,
p2_responsab,
p3_responsab,
p1_actitud,
p2_actitud,
p3_actitud,
p4_actitud,
p5_actitud,
p1_cargo,
p2_cargo,
p3_cargo,
p4_cargo,
p5_cargo,
p1_direccion,
p2_direccion,
p3_direccion,
p4_direccion,
esfuerzo_de_mejora,
necesidad_capacitacion,
comentario_desempeno
order by unidad, nombre_usuario
) to stdout WITH CSV HEADER