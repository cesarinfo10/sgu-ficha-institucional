<script>

function almacenaVariable(nombreVariable, valor) {
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
	return i;
}
function enviarValoresEvaluar(idEvaluado, id_tipo_ponderaciones_evaluado, id_tipo_ponderaciones_evaluador, nombreEvaluado, modo, id_usuarios_jerarquia, asistencia, capacitacion, procon, rehacer){
		var f = document.createElement('form');
		f.action='?modulo=evdem_realizar_evaluador';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("rehacer", rehacer);
		f.appendChild(i);

		i = almacenaVariable("id_evaluado", idEvaluado);
		f.appendChild(i);

		i = almacenaVariable("id_tipo_ponderaciones_evaluado", id_tipo_ponderaciones_evaluado);
		f.appendChild(i);

		i = almacenaVariable("id_tipo_ponderaciones_evaluador", id_tipo_ponderaciones_evaluador);
		f.appendChild(i);

		i = almacenaVariable("nombre_evaluado", nombreEvaluado);
		f.appendChild(i);

		i = almacenaVariable("modo", modo);
		f.appendChild(i);

		i = almacenaVariable("id_usuarios_jerarquia", id_usuarios_jerarquia);
		f.appendChild(i);

		i = almacenaVariable("asistencia", asistencia);
		f.appendChild(i);

		i = almacenaVariable("capacitacion", capacitacion);
		f.appendChild(i);

		i = almacenaVariable("procon", procon);
		f.appendChild(i);

		

		document.body.appendChild(f);
		f.submit();
}

function evaluar(idEvaluado, id_tipo_ponderaciones_evaluado, id_tipo_ponderaciones_evaluador, nombreEvaluado, modo, id_usuarios_jerarquia, asistencia, capacitacion, procon, rehacer) {	
	if (rehacer == 1) {
		var r = confirm("esta seguro(a) de rehacer evaluación?");
		if (r == true) {
			enviarValoresEvaluar(idEvaluado, id_tipo_ponderaciones_evaluado, id_tipo_ponderaciones_evaluador, nombreEvaluado, modo, id_usuarios_jerarquia, asistencia, capacitacion, procon, rehacer);
		} else {
//			rehacer = 0; //para que haga nada!!
		}

	} else {
		enviarValoresEvaluar(idEvaluado, id_tipo_ponderaciones_evaluado, id_tipo_ponderaciones_evaluador, nombreEvaluado, modo, id_usuarios_jerarquia, asistencia, capacitacion, procon, rehacer);
	}
	
}

function enviarValoresCerrarCalcular(cerrar, id_usuarios_jerarquia){
		var f = document.createElement('form');
		f.action='?modulo=evdem_evaluador';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');


		i = almacenaVariable("modo_cerrar", cerrar);
		f.appendChild(i);

		i = almacenaVariable("id_usuarios_jerarquia", id_usuarios_jerarquia);
		f.appendChild(i);


		document.body.appendChild(f);
		f.submit();
}


function accionCerrarCalcular(id_usuarios_jerarquia) {
	//alert("que sucede");
	
	console.log("he entrado");	
	var puedeSeguir = true;
	if (puedeSeguir) {
		//location.href='?modulo=evdem_evaluador&modo_cerrar=SI&id_usuarios_jerarquia='+id_usuarios_jerarquia;
		enviarValoresCerrarCalcular("SI", id_usuarios_jerarquia);
//		enviarValores(strSalida, id_usuarios_jerarquia, modo);
	} else {
		alert("Todos los valores del cuestionario deben ser ingresados.")
	}
	
	//alert('he pasado');
	
}
</script>
<?php
		setlocale(LC_NUMERIC, "en_US.UTF-8");
		setlocale(LC_MONETARY, "en_US.UTF-8");


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
$id_fi_EVAL_JEFE = "1"; //evaluacion jefe
$id_fi_AUTOEVAL_F_D_V = "2"; //autoevaluacipn funcionario/directivo/vicerrector
$id_fi_ASISTENCIA = "3"; //asistencia
$id_fi_PROCON = "4"; //PROCON
$id_fi_CAPATICATION = "5"; //capacitación!

$id_curso    = $_REQUEST['id_curso']; 

$id_usuario = $_SESSION['id_usuario'];
//$id_usuario = 744;



$id_usuarioParam = $_GET['ID_USUARIO_PARAM'];
$nombre_usuario_parametro = $_GET['nombre_usuario_parametro'];
//echo(msje_js("usuario normal = $id_usuario"));
if ($id_usuarioParam <> "") {
//	//$id_usuario = 1273; //960; //656; //1321; //1211; //1305; //569; //1321; //939; //1180; //315; //722; //1274; //3; //655; //656; //419; //558; //744; //1258; //741; //1207; //1211; //1273;
//	echo(msje_js("usuario = $id_usuarioParam"));
	$id_usuario = $id_usuarioParam;	
	//echo(msje_js("usuario cambiado = $id_usuario"));
}



$id_usuarios_tipo = $_SESSION['tipo'];

if (isset($_POST['modo_cerrar'])) {
	$modo_cerrar 				= $_POST['modo_cerrar'];
} else {
}

if (isset($_POST['llaves'])) {
	$llaves 				= $_POST['llaves'];
}
if (isset($_POST['modo'])) {
	$evdem_modo    			= $_POST['modo'];
}
if (isset($_POST['id_usuarios_jerarquia'])) {
	$id_usuarios_jerarquia 	= $_POST['id_usuarios_jerarquia'];
}
if (isset($_POST['asistencia'])) {
	$porcAsistencia 		= $_POST['asistencia'];
}
if (isset($_POST['capacitacion'])) {
	$porcCapacitacion	 	= $_POST['capacitacion'];
}
	
	
	$ss = "	 
		 select u.id_tipo_ponderaciones as id_tipo_ponderaciones,
		 u.id_unidad as id_unidad,
		 u.jefe_unidad as jefe_unidad
		 from usuarios u where u.id = $id_usuario;";
	$sqlss     = consulta_sql($ss);
	extract($sqlss[0]);


//	echo("1.-asistencia = ".$porcAsistencia.", capacitacion=".$porcCapacitacion."</br>");
//	echo("1.-evdem_modo = $evdem_modo"."</br>");
//	echo("1.-id_usuarios_jerarquia = $id_usuarios_jerarquia"."</br>");
//	echo("1.-id_tipo_ponderaciones = $id_tipo_ponderaciones"."</br>");
	//DATOS EN DURO
	$anoEnCurso = $ANO; //"2020";
	//$id_periodo_eval = "1";
	//FIN DATOS EN DURO

	

	actualizaPorcentajeAsistencia($anoEnCurso);
	actualizaPorcentajeCapacitacion();

	$ss = "	 
		select mini_glosa as mini_glosa, id as id_periodo_eval from periodo_eval where activo=true;
			";
	$sqlss     = consulta_sql($ss);
	extract($sqlss[0]);
	
	if (!$jefe_unidad) {
		
		echo("<div class='tituloModulo'>");
		echo("Ud no tiene permisos para ejecutar este módulo!");
		echo("</div><br>");
		echo("<div class='texto'>");
		echo("Ud no posee rol : Jefe Unidad.");
		echo("</div>");
	
	} else {
	
	
		if ($id_tipo_ponderaciones != "") {
			
		} else {
			//echo("* * *  se ha asociado usuario con TIPO_PONDERACIONES </br>");
		}
	
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		if ($id_usuarios_jerarquia != "") {
			$existeEvaluacion = evdem_existeEvaluacion($id_usuarios_jerarquia);
			if ($existeEvaluacion) {
				$evdem_modo = "UPDATE";
			} else {
				$evdem_modo = "NEW";
			}
		}
	
		if ($llaves!="") {
			$huboError = evdem_grabarEvaluaciones($llaves, $id_usuarios_jerarquia, $evdem_modo);
			//echo("huboError=".$huboError);	
			if ($huboError == 0) {
	//			$es_autoevaluacion = "N";
				evdem_realizaGraba($anoEnCurso, $id_usuarios_jerarquia, $porcAsistencia, $porcCapacitacion,"N");

/*				
				$ss = "
				select 
id_evaluador, 
id_evaluado,
				cumplimiento_poa as resultado_poa, 
				resultado_eval as result_eval,
				final_auto_funcionario_directivo_vicerrector as autoevaluacion
				 from usuarios_jerarquia 
				where 
				 id = $id_usuarios_jerarquia";
*/
				 $ss = "select 
				 id_evaluador,
				 (
					 select
					 u.nombre || ' ' || u.apellido 
					 from usuarios u 
					 where u.id = id_evaluador
				 ) nombre_evaluador, 
				 (
					 select u.email
					 from usuarios u 
					 where u.id = id_evaluador
				 ) email_evaluador, 
				 id_evaluado,
				 (
					 select
					 u.nombre || ' ' || u.apellido 
					 from usuarios u 
					 where u.id = id_evaluado
				 ) nombre_evaluado, 
				 (
					 select u.email
					 from usuarios u 
					 where u.id = id_evaluado
				 ) email_evaluado, 
				 cumplimiento_poa as resultado_poa, 
				 resultado_eval as result_eval,
				 final_auto_funcionario_directivo_vicerrector as autoevaluacion
				 from usuarios_jerarquia 
				 where 
				 id = $id_usuarios_jerarquia";



				//echo($ss."</br>");
				$sql_ss     = consulta_sql($ss);
				extract($sql_ss[0]);
        
        
        
//PASAMOS PARAMETRO USUARIO JERARQUIA DEL EVALUADO EMEDINA - CADABRA.CL -- 20230930
      
   $SQL = " select  id as id_usuarios_jerarquia from usuarios_jerarquia
			where id_periodo_eval = (select id from periodo_eval where activo)
			and id_evaluador =   $id_evaluado  and id_evaluado =  $id_evaluado";
	$fPending = consulta_sql($SQL);
	extract($fPending[0]);
	$id_usuarios_jerarquia = $id_usuarios_jerarquia;
 //echo($SQL."</br>");
//FIN PASA PARAMETROS


//INICIO CUIDADO		
				evdem_realizaGrabaCalculos(//$anoEnCurso, 
									$id_tipo_ponderaciones, 
									$id_usuarios_jerarquia, 
									$porcAsistencia, 
									$porcCapacitacion, 
									$resultado_poa, 
									$result_eval,									 
									"N",
									$autoevaluacion );
//FIN CUIDADO									
/*AQUI DEBE ENVIAR CORREO*/
$asunto_evaluado = "SGU: Se le informa que $nombre_evaluador le ha hecho una evaluación de desempeño.";
$cuerpo_evaluado = "Su evaluación desempeño se encuentra lista para su aprovación o rechazo.";
$cabeceras = "From: SGU" . "\r\n"
			. "Content-Type: text/plain;charset=utf-8" . "\r\n";
//	echo("MAIL TO : $emailjefe"."</br>");
//	echo("MAIL asunto : $asunto"."</br>");
//	echo("MAIL cuerpo : $cuerpo"."</br>");
//	echo("MAIL cabeceras : $cabeceras"."</br>");
$asunto_evaluador = "SGU: Evaluación de desempeño para $nombre_evaluado.";
$cuerpo_evaluador = "Ud, hizo una evaluación de desempeño para que sea aprobada o rechazada por $nombre_evaluado.";

mail($email_evaluado,$asunto_evaluado,$cuerpo_evaluado,$cabeceras);
mail($email_evaluador,$asunto_evaluador,$cuerpo_evaluador,$cabeceras);

//INICIO CUIDADO
/*
echo(msje_js("DOS MAILS."));
echo(msje_js("1.- email_evaluado : $asunto_evaluado"));
echo(msje_js("1.- asunto evaluado : $asunto_evaluado"));
echo(msje_js("1.- cuerpo evaluado : $cuerpo_evaluado"));

echo(msje_js("2.- email_evaluador : $asunto_evaluador"));
echo(msje_js("2.- asunto evaluador : $asunto_evaluador"));
echo(msje_js("2.- cuerpo evaluador : $cuerpo_evaluador"));
*/
//FIN CUIDADO

/*FIN CORREO*/

			} else {
	
			}
		} else {
			//echo("AUN NO HA GRABADO!!");
		}
		
//}
//echo("UNO");
		//UNIVERSO
		//--UNIVERSO EVALUADOS QUE FALTAN INSERTAR
		//LOS QUE SON RECTOR y VICERRECTOR
//		if ($rector...vicerrector) {
		//LO QUE NO SON VICERRECTORES
			$ss = "select 
			id as id_evaluar
			from (
				select 
					id
					from usuarios 
				where 
				id_unidad = $id_unidad
				and activo
				and COALESCE(jefe_unidad,false) = false
				union
				select 
					id
				from usuarios 
				where 
					activo
				and COALESCE(jefe_unidad,false) = true
				and id_unidad in (select id from gestion.unidades where dependencia = $id_unidad)
				)
			as a
			EXCEPT
			select id_evaluado id from usuarios_jerarquia
			where id_periodo_eval = (select id from periodo_eval where activo)
			and id_evaluador = $id_usuario
			";
/*
		} else {
			//LO QUE NO SON VICERRECTORES
			$ss = "select 
			id as id_evaluar
			from (
				select 
					id
					from usuarios 
				where 
				id_unidad = $id_unidad
				and activo
				and COALESCE(jefe_unidad,false) = false
				union
				select 
					id
				from usuarios 
				where 
					activo
				and COALESCE(jefe_unidad,false) = false
				and id_unidad in (select id from gestion.unidades where dependencia = $id_unidad)
				)
			as a
			EXCEPT
			select id_evaluado id from usuarios_jerarquia
			where id_periodo_eval = (select id from periodo_eval where activo)
			and id_evaluador = $id_usuario
			";
			*/
//		}
	
	
		$candidatos     = consulta_sql($ss);
		for ($x=0;$x<count($candidatos);$x++) {
			extract($candidatos[$x]);
			//echo("id_evaluar = $id_evaluar"."</br>");
			$SQL_insert = "
				insert into usuarios_jerarquia(id_evaluador, 
											id_evaluado, 
											id_estado_eval, 
											id_periodo_eval									   
											) values (
												$id_usuario, 
												$id_evaluar, 
												0, 
												(select id from periodo_eval where activo)
												)";
			if (consulta_dml($SQL_insert) == 1) {
			} else {
			}
		}
	

		//echo("DOS");

	//FIN UNIVERSO

	if ($modo_cerrar=='SI') {
		//echo("estoy en modo CERRAR = SI");
		$SQL_update = "update usuarios_jerarquia		
			set cerrado = true
			where 
			id = $id_usuarios_jerarquia
		";
//		id_periodo_eval = (select id from periodo_eval where activo)
//		and id_evaluador = $id_usuario

		//echo($SQL_update."</br>");
		if (consulta_dml($SQL_update) == 1) {
		//echo("Se han guardado exitosamente los cambios."	);
			echo("<div class='texto'>");
			echo "AUTOEVALUACIONES CERRADAS";
			echo("</div><br>");
		} else {
			//echo(msje_js("ERROR: No se han guardado los cambios."));
			$huboError = 1;
		}

	} else {
//		echo("YAPPP!");
	}

	
	$ss = "select count(*) as cuenta from usuarios_tipos_evaluaciones
	where id_usuarios_tipo = $id_usuarios_tipo
	and id_tipo_evaluaciones = 1;"; //ev.desempeño

	//echo("<br>NO ENTEN................".$ss);

	$sqlCuenta     = consulta_sql($ss);
	extract($sqlCuenta[0]);
	//echo("cuenta=".$cuenta);
	if ($cuenta==1) {
		include("validar_modulo.php");
		//$nombreUsuario = $nombre_real_usuario; 
		
		$ss = "SELECT u.id as id, 
			u.id_tipo_ponderaciones as id_tipo_ponderaciones_evaluado,
			(select id_tipo_ponderaciones from usuarios where id = $id_usuario)	as id_tipo_ponderaciones_evaluador,
			u.nombre as nombre,
			u.apellido as apellido,
			uj.id_estado_eval as id_estado_eval,
			uj.id as id_usuarios_jerarquia,
			to_char(uj.fecha_evaluador,'DD/MM/YYYY HH24:MI:SS') AS fechaeval,
			uj.resultado_eval as resulteval,
			uj.porc_asistencia as asistencia, 
			uj.porc_capacitacion as capacitacion,
			uj.porc_procon as porcprocon,
			uj.cumplimiento_poa as poa,
			uj.final_ev_jefe as final_ev_jefe, 
--			(select uj2.final_auto_funcionario_directivo_vicerrector 
--			from usuarios_jerarquia uj2 where uj2.id_evaluador = uj.id_evaluado 
--									and uj2.id_evaluado = uj.id_evaluado 
--									and uj2.id_periodo_eval = $id_periodo_eval) as final_auto_funcionario_directivo_vicerrector,
			(
			case when (
						(
							select uj2.final_auto_funcionario_directivo_vicerrector 
								from usuarios_jerarquia uj2 
									where uj2.id_evaluador = uj.id_evaluado 
									and uj2.id_evaluado = uj.id_evaluado 
									and uj2.id_periodo_eval = $id_periodo_eval
						) is not null	
					) then 
						(
							select uj2.final_auto_funcionario_directivo_vicerrector 
								from usuarios_jerarquia uj2 
									where uj2.id_evaluador = uj.id_evaluado 
									and uj2.id_evaluado = uj.id_evaluado 
									and uj2.id_periodo_eval = $id_periodo_eval
						)
					
			else uj.final_auto_funcionario_directivo_vicerrector
			end
			) as final_auto_funcionario_directivo_vicerrector, 												
			uj.final_asistencia as final_asistencia,
			uj.final_procon as final_procon,
			uj.final_capacitacion as final_capacitacion,
			
      
      	(
			case when (
						(
							select uj2.final_resultado 
								from usuarios_jerarquia uj2 
									where uj2.id_evaluador = uj.id_evaluado 
									and uj2.id_evaluado = uj.id_evaluado 
									and uj2.id_periodo_eval = $id_periodo_eval
						) is not null	
					) then 
						(
							select uj2.final_resultado 
								from usuarios_jerarquia uj2 
									where uj2.id_evaluador = uj.id_evaluado 
									and uj2.id_evaluado = uj.id_evaluado 
									and uj2.id_periodo_eval = $id_periodo_eval
						)
					
			else uj.final_resultado
			end
			) as final_resultado, 
      
       
			    
      uj.cerrado as autoevaluacion_cerrada,
			COALESCE(uj.rehacer,0) as cuenta_rehacer
			from usuarios u, usuarios_jerarquia uj 
			where 
			uj.id_evaluado = u.id 
and u.activo = 't' --NEW 
			and uj.id_evaluador = $id_usuario
			and uj.id_periodo_eval = $id_periodo_eval
			and uj.id_evaluado <> $id_usuario
			order by u.nombre;";
		//echo($ss);
		$candidatos     = consulta_sql($ss);
		$HTML_encuesta .= "  <tr class='filaTituloTabla'>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Realizar evaluacion</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Periodo Eval</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>POA</b></td>".$LF

		. "    <td colspan='2' align='center' class='tituloTabla'><b>Ev.Jefe</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Autoevaluaci&oacute;n</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Asistencia</b></td>".$LF
//		. "    <td colspan='2' align='center' class='tituloTabla'><b>Procon</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Capacitaci&oacute;n</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Resultado Final</b></td>".$LF

		. "    <td colspan='2' align='center' class='tituloTabla'><b>Acción</b></td>".$LF
		. "  </tr>".$LF;
		
		for ($x=0;$x<count($candidatos);$x++) {
			extract($candidatos[$x]);
			
			$disabledAtribute = "";
			if ($autoevaluacion_cerrada == "t") { //true
				$disabledAtribute = "disabled";
			}
			if ($cuenta_rehacer == 1) {
				$disabledAtributeRehacer = "disabled";
			}
			//echo("$x * * * * * * * * autoevaluacion_cerrada = $autoevaluacion_cerrada  disabledAtribute = $disabledAtribute"."</br>");
			$HTML_encuesta .= "  <tr class='filaTabla'>".$LF
							. "    <td colspan='2' class='textoTabla'><u>$nombre $apellido</u></td>".$LF
							. "    <td colspan='2' class='textoTabla'><u>$mini_glosa</u></td>".$LF;
			if ($poa != "") {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla'  style='text-align: right;' ><u>$poa%</u></td>".$LF;
			} else {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
			}
			if ($resulteval != "") {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$resulteval%</u></td>".$LF;
			} else {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
			}
			if ($final_auto_funcionario_directivo_vicerrector != "") {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$final_auto_funcionario_directivo_vicerrector%</u>";
				$HTML_encuesta .= "<input style='text-align: right;' type='button' name='grabar' value='C' onClick='javascript:accionCerrarCalcular($id_usuarios_jerarquia);' $disabledAtribute>";
				$HTML_encuesta .= "</td>.$LF";
				
			} else {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
			}
			if ($asistencia != "") {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$asistencia%</u></td>".$LF;
			} else {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
			}
			if ($capacitacion != "") {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$capacitacion%</u></td>".$LF;
			} else {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
			}
			if ($final_resultado != "") {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$final_resultado%</u></td>".$LF;					
			} else {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
			}

			if ($asistencia == "") {
				$asistencia = "0";
			}
			if ($capacitacion == "") {
				$capacitacion = "0";
			}
			if ($porcprocon == "") {
				$porcprocon = "0";
			}

			$ww = "200px";
			$hh = "30px";

			$nombreCompleto = $nombre." ".$apellido;
			if ($id_estado_eval == 0) { //EVALUACION AUN NO CREADA
				$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: $ww; height:$hh; text-align: center;' size='10' name='arancel' value='Evaluar' onClick='javascript:evaluar($id, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$nombreCompleto\",\"NEW\",$id_usuarios_jerarquia,$asistencia, $capacitacion, $porcprocon, 0);'></u></td>".$LF;
			}				
			if ($id_estado_eval == 1) { //EVALUACION YA CREADA POR EVALUADOR
				$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: $ww; height:$hh; text-align: center;' size='10' name='arancel' value='Volver Evaluar' onClick='javascript:evaluar($id, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$nombreCompleto\",\"UPDATE\",$id_usuarios_jerarquia,$asistencia, $capacitacion, $porcprocon, 0);'></u></td>".$LF;
			}				
			if ($id_estado_eval == 2) { //ECEPTADA POR EVALUADO
				$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: $ww; height:$hh; text-align: center;' size='10' name='arancel' value='Aceptada x Evaluado'  onClick='javascript:evaluar($id, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$nombreCompleto\",\"EVALUACION_ACEPTADA\",$id_usuarios_jerarquia,$asistencia, $capacitacion, $porcprocon, 0);'></u></td>".$LF;
				//if ($cuenta_rehacer == 0) {
					$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: 100; height:$hh; text-align: center;' size='10' name='arancel' value='Rehacer'  onClick='javascript:evaluar($id, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$nombreCompleto\",\"EVALUACION_ACEPTADA\",$id_usuarios_jerarquia,$asistencia, $capacitacion, $porcprocon, 1);' ".$disabledAtributeRehacer."></u></td>".$LF;
				//} 
				
			}				
			if ($id_estado_eval == 3) { //RECHAZADA POR EVALUADO
				$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: $ww; height:$hh; text-align: center;' size='10' name='arancel' value='Rechazada x Evaluado'  onClick='javascript:evaluar($id, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$nombreCompleto\",\"EVALUACION_RECHAZADA\",$id_usuarios_jerarquia,$asistencia, $capacitacion, $porcprocon, 0);'></u></td>".$LF;
				//if ($cuenta_rehacer == 0) {
					$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: 100; height:$hh; text-align: center;' size='10' name='arancel' value='Rehacer'  onClick='javascript:evaluar($id, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$nombreCompleto\",\"EVALUACION_ACEPTADA\",$id_usuarios_jerarquia,$asistencia, $capacitacion, $porcprocon, 1);' ".$disabledAtributeRehacer."'></u></td>".$LF;
				//}
			}				
			
			$HTML_encuesta .= "  </tr>".$LF;
		
		}
		?>
		<div class="tituloModulo">
			<?php echo($nombre_modulo); ?>
		</div><br>
		<div class="tituloModulo">
			Simulado para : <?php echo($id_usuarioParam); ?>, <?php echo($nombre_usuario_parametro) ?>
		</div><br>

	<!--	<div class="texto">
		Modo Evaluador.
		</div>
		-->
		</br>
		<table cellpadding="2" cellspacing="1" class="tabla" bgcolor="#FFFFFF">
		<?php 
			echo($HTML_encuesta); 			
		?>
		</table>
		<!--
		<div class="texto">
			<input style='width: <?php echo $ww; ?>; height:<?php echo $hh; ?>; text-align: center;' type="button" name="grabar" value="Cerrar Autoevaluaciones" onClick="javascript:accionCerrarCalcular(<?php echo $id_usuarios_jerarquia; ?>);">
		</div><br>
		-->
	<?php	
	} else {
		echo("<div class='tituloModulo'>");
		echo "NO TIENE PERMISOS PARA EVALUAR TEST DESEMPEÑO";
		echo("</div><br>");
	
	}

}
?>



