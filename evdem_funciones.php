<?php

function evdem_existeEvaluacion($id_usuarios_jerarquia) {
	$ss = "
		select a.id 
		from eval_items_evaluaciones a , eval_items_preguntas b
		where 
		a.id_usuario_jerarquia = $id_usuarios_jerarquia
		and b.id = a.id_eval_items_preguntas		
		and es_autoevaluacion = 'N'	
	";
	$sqlCuenta     = consulta_sql($ss);
	$cuentaRegistro = count($sqlCuenta);
	if ($cuentaRegistro == 0) {
		return false;
	} else {
		return true;
	}	
}
function evdem_grabarEvaluaciones($llaves, $id_usuarios_jerarquia, $evdem_modo) {
	$huboError = 0;
	setlocale(LC_NUMERIC, "en_US.UTF-8");
	setlocale(LC_MONETARY, "en_US.UTF-8");

	if ($llaves!="") {
		//echo($llaves."</br>");
		//echo("SE PROCEDE A GRABAR</br>");
		//echo("evdem_modo = $evdem_modo</br>");
		//$llaves = "21,1;22,1;23,1;24,1;25,1;26,1;27,1;28,1;29,1;30,1;31,1;32,1;33,1;34,1;35,1;36,1;37,1;39,sdfsd;40,sdfs;";
		$llaves = substr($llaves,0,strlen($llaves)-1);
		//echo("llaves=".$llaves."</br>");
		$variables = explode(";", $llaves);
		
		//echo($variables[0]);
		
		$huboError = 0;
		for ($x = 0; $x < count($variables); $x++) {
			//echo("x=".$x."</br>");
			$miVar = $variables[$x];
			//echo("-->".$miVar."</br>");
			
			$id_y_valor = explode(",", $miVar);
			
			$id_eval_items_preguntas = $id_y_valor[0];
			$evaluacion = $id_y_valor[1];
			if ($evdem_modo == 'NEW') {
				//echo("INSERTAR---->id_eval_items_preguntas=".$id_eval_items_preguntas.", evaluacion=".$evaluacion."</br>");
				$SQL_insert = "insert into eval_items_evaluaciones(
									id_usuario_jerarquia, 
									id_eval_items_preguntas, 
									evaluacion) 
									values ($id_usuarios_jerarquia, $id_eval_items_preguntas,'$evaluacion');";
				//echo($SQL_insert."</br>");
				if (consulta_dml($SQL_insert) == 1) {
					//echo(msje_js("Se han guardado exitosamente los cambios."));

				} else {
					//echo(msje_js("ERROR: No se han guardado los cambios."));
					$huboError = 1;
				}

			}
			if ($evdem_modo == 'UPDATE') {
				//echo("UPDATE---->id_eval_items_preguntas=".$id_eval_items_preguntas.", evaluacion=".$evaluacion."</br>");
				$SQL_update = "update eval_items_evaluaciones
					set evaluacion = '$evaluacion'
					where 
					id_usuario_jerarquia = $id_usuarios_jerarquia
					and id_eval_items_preguntas = $id_eval_items_preguntas;
					";
				//echo($SQL_update."</br>");
				if (consulta_dml($SQL_update) == 1) {
				//echo("Se han guardado exitosamente los cambios."	);

				} else {
					//echo(msje_js("ERROR: No se han guardado los cambios."));
					$huboError = 1;
				}

			}

		}
	}
}
function evdem_eval_pregunta($id_usuarios_jerarquia, $id_tipo_ponderaciones, $ITEM, $IN_PREGUNTAS, $es_autoevaluacion) {
	$ss = "select
	sum(
		((cast(a.evaluacion as int)*100)/7)/100::float
		* 
		(
			select c.ponderacion from ponderaciones c
			where c.id_tipo_ponderaciones = $id_tipo_ponderaciones
			and eval_items_preguntas_cod_interno in (b.cod_interno)
		)
	)/100::float
	*
	(
		(
			select ponderacion from ponderaciones
			where id_tipo_ponderaciones = $id_tipo_ponderaciones
			and eval_items_cod_interno in ('$ITEM')
		)/100::float
	) f1
	from eval_items_evaluaciones a, eval_items_preguntas b
	where b.id = a.id_eval_items_preguntas
	and a.id_usuario_jerarquia = $id_usuarios_jerarquia
	and b.cod_interno in ($IN_PREGUNTAS)
	and b.es_autoevaluacion = '$es_autoevaluacion'";

	//echo("eliminar--->".$ss."</br>");
	$sqlss     = consulta_sql($ss);
	extract($sqlss[0]);	
	return $f1;
	};

function evdem_obtienePonderacion($id_tipo_ponderacion, $id_factor_instrumento) {
	$ss = "
	select ponderacion as ponderacion from tp_fi
	where 
	id_tipo_ponderaciones = $id_tipo_ponderacion
	and id_factor_instrumento = $id_factor_instrumento
	";
	
	//echo($ss."</br>");
	$sqlss     = consulta_sql($ss);
	extract($sqlss[0]);	
	return $ponderacion;

}
function evdem_realizaGraba($anoEnCurso, $id_usuarios_jerarquia, $porcAsistencia, $porcCapacitacion, $es_autoevaluacion) {
	setlocale(LC_NUMERIC, "en_US.UTF-8");
	setlocale(LC_MONETARY, "en_US.UTF-8");

	$SS_NEW =  "select mini_glosa::float as new_ano_en_curso from periodo_eval where id = (select id_periodo_eval from usuarios_jerarquia where id = $id_usuarios_jerarquia)";
	$sql_SS_NEW     = consulta_sql($SS_NEW);
	extract($sql_SS_NEW[0]);


		$ss_unidadEvaluado = "select id_unidad as id_unidadevaluado, id_tipo_ponderaciones as id_tipo_ponderaciones_evaluado from usuarios where id = (select id_evaluado from usuarios_jerarquia where id = $id_usuarios_jerarquia)";
		$sql_unidadEvaluado     = consulta_sql($ss_unidadEvaluado);
		extract($sql_unidadEvaluado[0]);
		//echo("1.-$ss_unidadEvaluado"."</br>");				
		if ($id_unidadevaluado!="") {
			$ss_numerador = "select count(id)::float as numerador
			from gestion.poas where 
			date_part('year',fecha_prog_termino) = '$new_ano_en_curso' 
			and id_unidad = $id_unidadevaluado 
			and estado in ('Terminada', 'OK') ";
		//	echo("2.-".$ss_numerador."</br>");				
			$sql_numerador     = consulta_sql($ss_numerador);
			extract($sql_numerador[0]);
			$myNumerador = $numerador;
			if ($numerador=="") {
			$myNumerador = "0";
			}
			$ss_denominador = "select count(id)::float as denominador 
				from gestion.poas 
				where 
				date_part('year',fecha_prog_termino) = '$new_ano_en_curso' 
				and id_unidad = $id_unidadevaluado
				and estado not in ('Eliminada', 'Aplazada') ";

		//	echo("3.-".$ss_denominador."</br>");				
			$sql_denominador     = consulta_sql($ss_denominador);
			if (count($sql_denominador) == 0) {
				$myDenominador = "0";
			} else {
				extract($sql_denominador[0]);
				$myDenominador = $denominador;
				if ($denominador=="") {
					$myDenominador = "0";
				}
	
			}


//			echo("4.-numerador = ".$myNumerador."</br>");
//			echo("5.-denominador = ".$myDenominador."</br>");
			if ($myDenominador == "0") {
				$resultadoPOA = "100";
			} else {
				$resultadoPOA = round(($myNumerador / $myDenominador)*100,2);
			}
			
//echo("6.-resultado POA = ".$resultadoPOA);
//echo("7.-id_tipo_ponderaciones_evaluado  = ".$id_tipo_ponderaciones_evaluado);
		} else {
			$resultadoPOA = "0";
		}
		//if ($es_autoevaluacion == "N") {
			$result_item1 = evdem_eval_pregunta($id_usuarios_jerarquia, $id_tipo_ponderaciones_evaluado, "1_ITEM1", "'1_P1','1_P2','1_P3'","N" );
			$result_item2 = evdem_eval_pregunta($id_usuarios_jerarquia, $id_tipo_ponderaciones_evaluado, "1_ITEM2", "'1_P4','1_P5','1_P6','1_P7','1_P8'","N" );
			$result_item3 = evdem_eval_pregunta($id_usuarios_jerarquia, $id_tipo_ponderaciones_evaluado, "1_ITEM3", "'1_P9','1_P10','1_P11','1_P12','1_P13'","N" );
			$result_item4 = evdem_eval_pregunta($id_usuarios_jerarquia, $id_tipo_ponderaciones_evaluado, "1_ITEM4", "'1_P14','1_P15','1_P16','1_P17'","N" );
		//}

		/*
		echo("7.-id_usuarios_jerarquia = $id_usuarios_jerarquia"."</br>");
		echo("7.-id_tipo_ponderaciones = $id_tipo_ponderaciones"."</br>");
		echo("7.-result_item1 = $result_item1"."</br>");
		echo("7.-result_item2 = $result_item2"."</br>");
		echo("7.-result_item3 = $result_item3"."</br>");
		echo("7.-result_item4 = $result_item4"."</br>");
		*/

		$resultEval = round(($result_item1 + $result_item2 + $result_item3 + $result_item4)*100,2);

		$SQL_update = "update usuarios_jerarquia set ";
		if ($es_autoevaluacion == "N") {	
			$SQL_update .= " id_estado_eval = 1, ";
		}	

if ($porcAsistencia == "") {
	$porcAsistencia = 0;
}	
if ($porcCapacitacion == ""){
	$porcCapacitacion = 0;
}	
if ($resultadoPOA == ""){
	$resultadoPOA = 0;							
}	
if ($resultEval == ""){
	$resultEval = 0;						
}	
		
		$SQL_update .= "
						fecha_evaluador 								= current_timestamp,
						porc_asistencia 								= cast(replace('$porcAsistencia',',','.') as float),
						porc_capacitacion 								= cast(replace('$porcCapacitacion',',','.') as float),
						porc_procon										= 0,
						cumplimiento_poa 								= cast(replace('$resultadoPOA',',','.') as float),
						resultado_eval 									= cast(replace('$resultEval',',','.') as float)
		";
		$SQL_update .=	"								where id = $id_usuarios_jerarquia;";
		//echo("7.-$SQL_update"."</br>");
		if (consulta_dml($SQL_update) == 1) {
		//echo("Grabación exitosa!");
		}

}
function evdem_realizaGrabaCalculos(
									$id_tipo_ponderaciones, 
									$id_usuarios_jerarquia, 
									$porcAsistencia, 
									$porcCapacitacion, 
									$resultadoPOA, 
									$resultEval, 
									$es_autoevaluacion, 
									$autoevaluacion) {

//SACA ERROR
/*
	$SQL = "
		SELECT 
		(select b.final_auto_funcionario_directivo_vicerrector from usuarios_jerarquia b
		where b.id_evaluado = a.id_evaluado
					and b.id_evaluador = a.id_evaluado
				and b.id_periodo_eval = a.id_periodo_eval
		) obtiene_autoevaluacion
		from usuarios_jerarquia a 
		where 
		a.id = $id_usuarios_jerarquia";
	$fPending = consulta_sql($SQL);
	extract($fPending[0]);
	$autoevaluacion = $obtiene_autoevaluacion;
 */
//FIN SACA ERROR

//CORRIGE ERROR EMEDINA - CADABRA.CL -- 20230824
  $SQL = "
		select round(sum(cast(a.evaluacion as int)/7::numeric * 100) / count(*),1) as obtiene_autoevaluacion
			from eval_items_evaluaciones a , eval_items_preguntas b
			where a.id_usuario_jerarquia = $id_usuarios_jerarquia
			and b.id = a.id_eval_items_preguntas
			and b.es_autoevaluacion = 'S'";
	$fPending = consulta_sql($SQL);
	extract($fPending[0]);
	$autoevaluacion = $obtiene_autoevaluacion;
//FIN CORRIGE ERROR

	//if ($evdem_modo == 'NEW') {
		setlocale(LC_NUMERIC, "en_US.UTF-8");
		setlocale(LC_MONETARY, "en_US.UTF-8");
		//EVALUACION CREADA POR EVALUADOR
		//obteniendo cumplimiento POA
		//* * * * * * * 
		//$ss_unidadEvaluado = "select id_unidad as id_unidadevaluado                                                          from usuarios where id = (select id_evaluado from usuarios_jerarquia where id = $id_usuarios_jerarquia);"; 
		$ss_unidadEvaluado = "select id_unidad as id_unidadevaluado, id_tipo_ponderaciones as id_tipo_ponderaciones_evaluado from usuarios where id = (select id_evaluado from usuarios_jerarquia where id = $id_usuarios_jerarquia)";
		//echo("a.-".$ss_unidadEvaluado."</br>");
		$sql_unidadEvaluado     = consulta_sql($ss_unidadEvaluado);
		extract($sql_unidadEvaluado[0]);


		//echo("f.-resultEval = $resultEval"."</br>");

		//LEER LOS FACTORES INSTRUMENTOS
		
		$id_fi_EVAL_JEFE = "1"; //evaluacion jefe
		$id_fi_AUTOEVAL_F_D_V = "2"; //autoevaluacipn funcionario/directivo/vicerrector
		$id_fi_ASISTENCIA = "3"; //asistencia
		$id_fi_PROCON = "4"; //PROCON
		$id_fi_CAPATICATION = "5"; //capacitación!
//echo("UNO"."</br>");		
		$ponderacion_EVAL_JEFE 		= evdem_obtienePonderacion($id_tipo_ponderaciones_evaluado, $id_fi_EVAL_JEFE);
		$ponderacion_AUTOEVAL_F_D_V = evdem_obtienePonderacion($id_tipo_ponderaciones_evaluado, $id_fi_AUTOEVAL_F_D_V);
		$ponderaciones_ASISTENCIA 	= evdem_obtienePonderacion($id_tipo_ponderaciones_evaluado, $id_fi_ASISTENCIA);
		$ponderaciones_PROCON 		= evdem_obtienePonderacion($id_tipo_ponderaciones_evaluado, $id_fi_PROCON);
		$ponderaciones_CAPACITACION = evdem_obtienePonderacion($id_tipo_ponderaciones_evaluado, $id_fi_CAPATICATION);
//echo("DOS"."</br>");		
		$ponderacion_EVAL_JEFE 		= $ponderacion_EVAL_JEFE / 100;
		$ponderacion_AUTOEVAL_F_D_V = $ponderacion_AUTOEVAL_F_D_V / 100;
		$ponderaciones_ASISTENCIA 	= $ponderaciones_ASISTENCIA / 100;
		$ponderaciones_PROCON 		= $ponderaciones_PROCON / 100;
		$ponderaciones_CAPACITACION = $ponderaciones_CAPACITACION / 100;
//echo("TRES"."</br>");		
		$final_ev_jefe 									= round($resultEval * $ponderacion_EVAL_JEFE,2);
		$final_auto_funcionario_directivo_vicerrector 	= round($autoevaluacion * $ponderacion_AUTOEVAL_F_D_V,2);
		$final_asistencia 								= round($porcAsistencia * $ponderaciones_ASISTENCIA,2);
		$final_procon     								= round($resultadoPOA * $ponderaciones_PROCON,2);
		$final_capacitacion 							= round($porcCapacitacion * $ponderaciones_CAPACITACION,2);
/*
echo("preguntas cuestionario 		= ".$SQL)
echo("<br>CUATRO"."</br>");		
echo("CUATRO resultEval 		= ".$resultEval."</br>");
echo("CUATRO autoevaluacion = ".$autoevaluacion."</br>");
echo("CUATRO porcAsistencia 	= ".$porcAsistencia."</br>");
echo("CUATRO resultadoPOA 		= ".$resultadoPOA."</br>");
echo("CUATRO porcCapacitacion = ".$porcCapacitacion."</br>");
	
echo("<br>CUATRO"."</br>");		
echo("CUATRO ponderacion_EVAL_JEFE 		= ".$ponderacion_EVAL_JEFE."</br>");
echo("CUATRO ponderacion_AUTOEVAL_F_D_V = ".$ponderacion_AUTOEVAL_F_D_V."</br>");
echo("CUATRO ponderaciones_ASISTENCIA 	= ".$ponderaciones_ASISTENCIA."</br>");
echo("CUATRO ponderaciones_PROCON 		= ".$ponderaciones_PROCON."</br>");
echo("CUATRO ponderaciones_CAPACITACION = ".$ponderaciones_CAPACITACION."</br>");

echo("CUATRO final_ev_jefe 									= ".$final_ev_jefe."</br>");
echo("CUATRO final_auto_funcionario_directivo_vicerrector 	= ".$final_auto_funcionario_directivo_vicerrector."</br>");
echo("CUATRO final_asistencia 								= ".$final_asistencia."</br>");
echo("CUATRO final_procon     								= ".$final_procon."</br>"); 
echo("CUATRO final_capacitacion 							= ".$final_capacitacion."</br>");
*/

		//$final_resultado = $final_ev_jefe + $final_auto_funcionario_directivo_vicerrector + $final_asistencia + $final_procon + $final_capacitacion;
		$final_resultado =  $final_ev_jefe + 
							$final_auto_funcionario_directivo_vicerrector + 
							$final_asistencia + 
							//$resultadoPOA + 
							$final_procon + 
							$final_capacitacion;

//echo("CINCO final_resultado = $final_resultado"."</br>");		


		$SQL_update = "update usuarios_jerarquia set ";
		//echo("********-es_autoevaluacion = $es_autoevaluacion"."</br>");
		if ($es_autoevaluacion == "N") {
			$SQL_update .= " id_estado_eval = 1, cerrado = null ,";
		}	

if ($final_ev_jefe 									== "") {
	$final_ev_jefe = 0;
}	
if ($autoevaluacion    == ""){
	$autoevaluacion = 0;
}	
if ($final_asistencia 								== ""){
	$final_asistencia = 0;							
}	
if ($final_procon     								== ""){
	$final_procon = 0;						
}	
if ($final_capacitacion 								== ""){
	$final_capacitacion = 0; 							
}	
if ($final_resultado   								== ""){
	$final_resultado  = 0;													
}	


		$SQL_update .= " 				fecha_evaluador 								= current_timestamp,
											final_ev_jefe 									= cast(replace('$final_ev_jefe',',','.') as float),
											final_auto_funcionario_directivo_vicerrector    = cast(replace('$autoevaluacion',',','.') as float),
											final_asistencia 								= cast(replace('$final_asistencia',',','.') as float),
											final_procon     								= cast(replace('$final_procon',',','.') as float),
											final_capacitacion 								= cast(replace('$final_capacitacion',',','.') as float),
											final_resultado   								= cast(replace('$final_resultado',',','.') as float)
																		
											";

		$SQL_update .=	"								where id = $id_usuarios_jerarquia;";
		//echo("emedina77.-$SQL_update"."</br>");
		if (consulta_dml($SQL_update) == 1) {
			//echo("Grabación exitosa!");
		}
	//}	
	//no hubo error!

}
function actualizaPorcentajeAsistencia($anoEnCurso) {

	$SQL_update = "	update usuarios_jerarquia
	set 	porc_asistencia = 
		(
		round(
		case when 
		  (
		  select count(*) from asiscapac_actividades_obligatorias_funcionarios a, asiscapac_actividades b
		  where a.ano = (select mini_glosa::decimal from periodo_eval where activo = 't')
		  and a.id_usuario = id_evaluado
		  and b.id = a.id_asiscapac_actividades
		  and b.id_asiscapac_estado in (2,3) -- ejecutada, cerrada
		  ) > 0 then (
				(
					select count(*) from asiscapac_actividades_obligatorias_funcionarios a, asiscapac_actividades b
					where a.ano = (select mini_glosa::decimal from periodo_eval where activo = 't')
					and a.id_usuario = id_evaluado
					and a.id_asiscapac_actividades_funcionarios_check not in (5,7) --todos menos los inasistentes y sin correo
					and b.id = a.id_asiscapac_actividades
					and b.id_asiscapac_estado in (2,3) -- ejecutada, cerrada
					)
					/
					(
					select count(*) from asiscapac_actividades_obligatorias_funcionarios a, asiscapac_actividades b
					where a.ano = (select mini_glosa::decimal from periodo_eval where activo = 't')
					and a.id_usuario = id_evaluado
					and b.id = a.id_asiscapac_actividades
					and b.id_asiscapac_estado in (2,3) -- ejecutada, cerrada
					) 
					::decimal*100
					)
		else 0
		  end
		)
		)
	where 
	id_periodo_eval = (select id from periodo_eval where activo = 't')
	and id_evaluador <> id_evaluado 
--	and id_estado_eval = 0
	"; 
//	--and porc_capacitacion is not null
//	--and id_evaluado = 744 --dcarreno
	//echo("<br>$SQL_update");
	if (consulta_dml($SQL_update) > 0) {
	//echo("Grabación exitosa de porcentaje de asistencia!");
	}


}

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

function glb_universo_obligatorias($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_obligatorias_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }



  function glb_universo_deteccion_necesidades($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_deteccion_necesidades_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function glb_universo_estudios_superiores($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_estudios_superiores_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
      id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function glb_universo_sin_deteccion_necesidades($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in ( 2, 4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
      ) = 0
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function glb_universo_sin_deteccion_necesidades_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in (2,4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 

    and (
      select count(*) as cuenta
      from asiscapac_usuario_capacitaciones a
      where 
       id_usuario = $id_usuario
      and a.ano = $ano
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
      ) = 0
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }




  function glb_universo_voluntarias($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIAS
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_voluntarias_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIAS
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  //--------------------------------------------------------------------------------------------------------------------------------------------------------
  function glb_universo_obligatorias_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS
    and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_obligatorias_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS
    and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }




  function glb_universo_deteccion_necesidades_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_deteccion_necesidades_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function glb_universo_estudios_superiores_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
    and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_estudios_superiores_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
    and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }


  function glb_universo_sin_deteccion_necesidades_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in ( 2, 4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
      ) = 0
      and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_sin_deteccion_necesidades_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in (2,4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 

    and (
      select count(*) as cuenta
      from asiscapac_usuario_capacitaciones a
      where 
       id_usuario = $id_usuario
      and a.ano = $ano
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
      ) = 0
      and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_voluntarias_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIAS
    and b.confirmado = 't'
    ";    
    //$sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function glb_universo_voluntarias_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIUAS
    and a.confirmado = 't'
    ";    
    //$sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }




function tieneCapacitaciones($ano, $id_usuario)  {
	$SQL_consulta = "
				select 
				a.id id_capacitacion, 
				(select glosa from asiscapac_tipo where id = a.id_asiscapac_tipo) glosa_tipo,
				a.id_asiscapac_tipo id_asiscapac_tipo, 
				a.descripcion descripcion,
				to_char(a.fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY') fecha_inicio, 
				to_char(a.fecha_termino,'DD \"de\" tmMonth \"de\" YYYY') fecha_termino, 
				to_char(a.fecha_inicio,'YYYY-MM-DD') fec_ini_asist,
				to_char(a.fecha_termino,'YYYY-MM-DD') fec_fin_asist,
				a.duracion duracion, 
				a.id_asiscapac_estado id_asiscapac_estado_db,
				(select glosa from asiscapac_estado where id = a.id_asiscapac_estado) glosa_estado,
				0 as universo_convocados,
				0 as universo_presentes,
				0 as universo_justificados,
				0 as universo_licencia_medica,
				0 as universo_inasistente,
				trim(a.sala) id_sala
				, (
				select s.nombre||' (cap. '||s.capacidad||')' FROM salas s where s.codigo = a.sala
				) sala
				,a.link_capacitaciones link_capacitaciones,
				(
				select 
						CASE when ((
						select count(*) 
						from asiscapac_usuario_capacitaciones
						where confirmado = 't' and fecha_aceptacion is not null
						and id = a.id
						)> 0) THEN 'SI' 
						ELSE (
						CASE when ((
							select count(*) 
							from asiscapac_usuario_capacitaciones
							where confirmado = 'f' and fecha_revocar is not null
							and id = a.id
						) > 0) THEN 'NO' 
						ELSE 
							'NADA'
						END
						)
						END
						) AS confirmado,
				(
					select 
						CASE when ((
							select count(*) 
							from asiscapac_usuario_capacitaciones
							where confirmado = 't' and fecha_aceptacion is not null
							and id = a.id
						)> 0) THEN observacion
						ELSE (
							CASE when ((
							select count(*) 
							from asiscapac_usuario_capacitaciones
							where confirmado = 'f' and fecha_revocar is not null
							and id = a.id
							) > 0) THEN observacion_revocar
							ELSE 
							''
							END
							)
						END
						) AS observacion
				from asiscapac_usuario_capacitaciones a
				where true
				";

				$SQL_consulta = $SQL_consulta." and id_usuario = $id_usuario";

				//if ($id_origen != "") {
				$SQL_consulta = $SQL_consulta." and a.ano = $ano";
				//}
				//$SQL_consulta = $SQL_consulta." order by a.fecha_inicio asc";
				//echo("<br>CONSULTA = $SQL_consulta");
				$capacitaciones = consulta_sql($SQL_consulta);


	return count($capacitaciones);			
}

function obtienePorcentajeCapacitacionPorUsuario($anoEnCurso, $id_usuario) {
	$ano = $anoEnCurso;
	$u_obligatorias = glb_universo_obligatorias($ano, $id_usuario) 
					+ glb_universo_obligatorias_usuario($ano, $id_usuario);


	$u_deteccion_necesidades = glb_universo_deteccion_necesidades($ano, $id_usuario) 
							+ glb_universo_deteccion_necesidades_usuario($ano, $id_usuario);

	$u_estudios_superiores = glb_universo_estudios_superiores($ano, $id_usuario)
						+ glb_universo_estudios_superiores_usuario($ano, $id_usuario);

	$u_sin_deteccion_necesidades = glb_universo_sin_deteccion_necesidades($ano, $id_usuario)
								+ glb_universo_sin_deteccion_necesidades_usuario($ano, $id_usuario);

	$u_voluntarias = glb_universo_voluntarias($ano, $id_usuario) 
					+ glb_universo_voluntarias_usuario($ano, $id_usuario);
				

	//--------------------------------------------------------------------------------------------------------------------------------------------


	$u_obligatorias_validadas = glb_universo_obligatorias_validadas($ano, $id_usuario)
				+ glb_universo_obligatorias_validadas_usuario($ano, $id_usuario);


	$u_deteccion_necesidades_validadas = glb_universo_deteccion_necesidades_validadas($ano, $id_usuario)
										+ glb_universo_deteccion_necesidades_validadas_usuario($ano, $id_usuario);

	$u_estudios_superiores_validadas = glb_universo_estudios_superiores_validadas($ano, $id_usuario)
									+ glb_universo_estudios_superiores_validadas_usuario($ano, $id_usuario);

	$u_sin_deteccion_necesidades_validadas = glb_universo_sin_deteccion_necesidades_validadas($ano, $id_usuario)
										+ glb_universo_sin_deteccion_necesidades_validadas_usuario($ano, $id_usuario);

	$u_voluntarias_validadas = glb_universo_voluntarias_validadas($ano, $id_usuario) 
							+ glb_universo_voluntarias_validadas_usuario($ano, $id_usuario);




	if ($u_deteccion_necesidades) {
	//echo("REGLA DETECCION NECESIDADES");
	if ($u_obligatorias > 0) {
		$total1 = ($u_obligatorias_validadas / $u_obligatorias) * 0.6;
	} else {
		$total1 = 0;
	}
	if ($u_deteccion_necesidades>0) {
		$total2 = ($u_deteccion_necesidades_validadas / $u_deteccion_necesidades) * 0.3;
	} else {
		$total2 = 0;
	}
	
	if ($u_voluntarias > 0) {
		$total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
	} else {
		$total3 = 0;
	}

	$total = $total1 + $total2 +  $total3;
	} else {
	if (($u_deteccion_necesidades==0) && ($u_estudios_superiores==0)) {
		//echo("</br>REGLA SIN DETECCION NECESIDADES");
		if ($u_sin_deteccion_necesidades>0) {
			$total1 = ($u_sin_deteccion_necesidades_validadas / $u_sin_deteccion_necesidades) * 0.9;
		} else {
			$total1 = 0;
		}
		
		$total2 = 0;
		
		if ($u_voluntarias > 0) {
			$total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
		} else {
			$total3 = 0;
		}
		
	
		$total = $total1 + $total2 +  $total3;
		} else {
		if (($u_deteccion_necesidades==0) && ($u_estudios_superiores>0)) {
		//echo("REGLA ESTUDIOS SUPERIORES");
		if ($u_obligatorias > 0) {
			$total1 = ($u_obligatorias_validadas / $u_obligatorias) * 0.6;
		} else {
			$total1 = 0;
		}

		//if ($u_estudios_superiores>0) {
		//  $total2 = ($u_estudios_superiores_validadas / $u_estudios_superiores) * 0.3;
		//} else {
		//  $total2 = 0;
		//}
		
		//if ($u_voluntarias > 0) {
		//  $total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
		//} else {
		//  $total3 = 0;
		//}
		$total2 = 1*0.3;
		$total3 = 1*0.1;
			
		$total = $total1 + $total2 +  $total3;
		}
	}
	}
	//agrega %capacitaciones (10%)
	/*MODIF_*/
	$cuentaCapacitaciones = tieneCapacitaciones($ano, $id_usuario);
	if ($cuentaCapacitaciones > 0 && ($u_estudios_superiores==0)) {//emedina 07112023 se incorpora _uestduios_superiores==0
	
		$t = ($total + 0.1); // * 100; // + 0.1);
		//$t = ($total/100); // + 0.1);
		//$t = ($total/100 + 0.1)*100;
		$total = $t;
		//echo($t);
	}
  


	$porc_capacitaciones_usuario = round(($total*100),0);

	return $porc_capacitaciones_usuario;

}

function actualizaPorcentajeCapacitacion() {
	

	$ss = "select id id_usuarios_jerarquia, id_periodo_eval, id_evaluado, (select mini_glosa from periodo_eval where id = id_periodo_eval) ano_en_curso from usuarios_jerarquia
	where 
	id_periodo_eval = (select id from periodo_eval where activo = 't')
	and id_evaluador <> id_evaluado 
	--	and id_estado_eval = 0
	-- and id_evaluado = 3
	
 	";

	$sql_items     = consulta_sql($ss);
	for ($x=0;$x<count($sql_items);$x++) {
		extract($sql_items[$x]);
		$resultado = obtienePorcentajeCapacitacionPorUsuario($ano_en_curso, $id_evaluado);
		$SQL_update = "	
				update usuarios_jerarquia
				set porc_capacitacion = $resultado
				where id = $id_usuarios_jerarquia
				";
 
				/*
				where id in (
				
						select id from usuarios_jerarquia
							where 
							id_periodo_eval = (select id from periodo_eval where activo = 't')
							and id_evaluador <> id_evaluado 
						--	and id_estado_eval = 0
						-- and id_evaluado = 3
						);
				";
				*/
		
	
		if (consulta_dml($SQL_update) > 0) {
		//echo("Grabación exitosa de porcentaje de asistencia!");
		}

	}






}

?>