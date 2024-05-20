<script>

function almacenaVariable(nombreVariable, valor) {
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
	return i;
}
function enviarValoresEvaluar(idEvaluador, nombreEvaluador, modo, id_usuarios_jerarquia, asistencia, capacitacion, procon, id_tipo_ponderaciones_evaluado, id_tipo_ponderaciones_evaluador, emailjefe, nombreevaluado){
		var f = document.createElement('form');
		f.action='?modulo=evdem_realizar_evaluador';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input'); 


		i = almacenaVariable("id_tipo_ponderaciones_evaluado", id_tipo_ponderaciones_evaluado);
		f.appendChild(i);

		i = almacenaVariable("id_tipo_ponderaciones_evaluador", id_tipo_ponderaciones_evaluador);
		f.appendChild(i);

//		i = almacenaVariable("nombre_evaluador", nombreEvaluador);
//		f.appendChild(i);

		i = almacenaVariable("modo", modo);
		f.appendChild(i);

		i = almacenaVariable("id_usuarios_jerarquia", id_usuarios_jerarquia);
		f.appendChild(i);

		i = almacenaVariable("asistencia", asistencia);
		f.appendChild(i);

		i = almacenaVariable("capacitacion", capacitacion);
		f.appendChild(i);

//		i = almacenaVariable("procon", procon);
//		f.appendChild(i);

		i = almacenaVariable("emailjefe", emailjefe);
		f.appendChild(i);

		i = almacenaVariable("nombre_evaluado", nombreevaluado);
		f.appendChild(i);


		document.body.appendChild(f);
		f.submit();
}


function verMiEvaluacion(idEvaluador, nombreEvaluador, modo, id_usuarios_jerarquia, asistencia, capacitacion, procon, id_tipo_ponderaciones_evaluado, id_tipo_ponderaciones_evaluador, emailjefe, nombreevaluado) {
	enviarValoresEvaluar(idEvaluador, nombreEvaluador, modo, id_usuarios_jerarquia, asistencia, capacitacion, procon, id_tipo_ponderaciones_evaluado, id_tipo_ponderaciones_evaluador,emailjefe, nombreevaluado);
}
</script>
<?php
		setlocale(LC_NUMERIC, "en_US.UTF-8");
		setlocale(LC_MONETARY, "en_US.UTF-8");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//DATOS EN DURO
$id_usuario = $_SESSION['id_usuario'];//OJO NO ESTABA
//$id_usuario = 744; //741; //csanhueza, 1020; //932; //1021; //$_SESSION['id_usuario'];
//$id_usuario = 744; 
$anoEnCurso = $ANO; //"2020";

actualizaPorcentajeAsistencia($anoEnCurso);
actualizaPorcentajeCapacitacion();



//$id_periodo_eval = "1";
//FIN DATOS EN DURO

$ss = "	 
	select mini_glosa as mini_glosa, id as id_periodo_eval from periodo_eval where activo=true;
		";
$sqlss     = consulta_sql($ss);
extract($sqlss[0]);


//echo("id_usuario=".$id_usuario."</br>");

/*
$llaves = $_REQUEST['llaves'];
$accion = $_REQUEST['accion'];
$id_usuarios_jerarquia = $_REQUEST['id_usuarios_jerarquia'];
*/

if (isset($_POST['llaves'])) {
	$llaves 			= $_POST['llaves'];
}
if (isset($_POST['accion'])) {
	$accion 			= $_POST['accion'];
}
if (isset($_POST['id_usuarios_jerarquia'])) {
	$id_usuarios_jerarquia 			= $_POST['id_usuarios_jerarquia'];
}
if (isset($_POST['emailjefe'])) {
	$emailjefe 			= $_POST['emailjefe'];
}
if (isset($_POST['nombre_evaluado'])) {
	$nombreevaluado 			= $_POST['nombre_evaluado'];
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////
$evdem_modo = "LECTURA";
/*
if ($id_usuarios_jerarquia != "") {
	$ss = "select id from eval_items_evaluaciones where id_usuario_jerarquia = $id_usuarios_jerarquia;";
	echo($ss."</br>");
	$sqlCuenta     = consulta_sql($ss);
	$cuentaRegistro = count($sqlCuenta);
	//extract($sqlCuenta[0]);
	echo("cuentaRegistro = ".$cuentaRegistro."</br>");
	if ($cuentaRegistro == 0) {
		$evdem_modo = "NEW";
	} else {
		$evdem_modo = "UPDATE";
	}	
	
} else {
	$evdem_modo = $_REQUEST['modo'];
}
*/
//echo("***estoy en modo=".$evdem_modo."</br>");
//////////////////////////////////////////////////////////////////////////////////////////////////////////
$myIdEstadoEval = "";
if ($id_usuarios_jerarquia != "") {
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
	$sql_ss     = consulta_sql($ss);
				extract($sql_ss[0]);

	//$asunto_evaluado = "SGU: Se le informa que $nombre_evaluador le ha hecho una evaluación de desempeño.";
	//$cuerpo_evaluado = "Su evaluación desempeño se encuentra lista para su aprovación o rechazo.";
	//$cabeceras = "From: SGU" . "\r\n"
	//			. "Content-Type: text/plain;charset=utf-8" . "\r\n";
	//$asunto_evaluador = "SGU: Evaluación de desempeño para $nombre_evaluado.";
	//$cuerpo_evaluador = "Ud, hizo una evaluación de desempeño para que sea aprovada o rechazada por $nombre_evaluado.";

	//mail($email_evaluado,$asunto_evaluado,$cuerpo_evaluado,$cabeceras);
	//mail($email_evaluador,$asunto_evaluador,$cuerpo_evaluador,$cabeceras);

	if ($accion == 'ACEPTAR_EVALUACION') { //evaluado acepta evaluación!
		$myIdEstadoEval = "2";
		$asunto = "SGU: Usuario Acepta evaluación desempeño";
		$cuerpo = "El usuario $nombre_evaluado, ha aceptado su evaluación de desempeño.";
		$cabeceras = "From: SGU" . "\r\n"
				. "Content-Type: text/plain;charset=utf-8" . "\r\n";

		//mail($emailjefe,$asunto,$cuerpo,$cabeceras);	
		mail($email_evaluador,$asunto,$cuerpo,$cabeceras);
	}
	if ($accion == 'RECHAZAR_EVALUACION') { //evaluado rechaza evaluación!
		$myIdEstadoEval = "3";
		//ENVIAR MAIL A JEFE
		$asunto = "SGU: Usuario Rechaza evaluación desempeño";
		$cuerpo = "El usuario $nombreevaluado, ha rechazado su evaluación de desempeño.";
		$cabeceras = "From: SGU" . "\r\n"
				. "Content-Type: text/plain;charset=utf-8" . "\r\n";
	//	echo("MAIL TO : $emailjefe"."</br>");
	//	echo("MAIL asunto : $asunto"."</br>");
	//	echo("MAIL cuerpo : $cuerpo"."</br>");
	//	echo("MAIL cabeceras : $cabeceras"."</br>");

		//mail($emailjefe,$asunto,$cuerpo,$cabeceras);
		mail($email_evaluador,$asunto,$cuerpo,$cabeceras);

	}

}
if ($myIdEstadoEval != "") {
	$SQL_update = "update usuarios_jerarquia set id_estado_eval = $myIdEstadoEval where id = $id_usuarios_jerarquia;";
	//echo($SQL_update."</br>");
	if (consulta_dml($SQL_update) == 1) {
		//echo("Grabación exitosa!");
	} else {
		alert("Error inesperado al momento de actualizar dato!");
	}	
}
$id_usuarios_tipo = $_SESSION['tipo'];
//$tipos_usuario = tipos_usuario($_SESSION['tipo']);
//echo("-->".$tipos_usuario['id']."<--");
//$id_tipo_usuario = $tipos_usuario['id'];
//echo("***Tipo Usuario = ".$id_tipo_usuario."***");
$ss = "select count(*) as cuenta from usuarios_tipos_evaluaciones
where id_usuarios_tipo = $id_usuarios_tipo
and id_tipo_evaluaciones = 1;"; //ev.desempeño

//echo($ss);

$sqlCuenta     = consulta_sql($ss);
extract($sqlCuenta[0]);
//echo("cuenta=".$cuenta);
if ($cuenta==1) {
	include("validar_modulo.php");

/*
	$ss = "SELECT u.id as id, 
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
		uj.final_auto_funcionario_directivo_vicerrector as final_auto_funcionario_directivo_vicerrector,
		uj.final_asistencia as final_asistencia,
		uj.final_procon as final_procon,
		uj.final_capacitacion as final_capacitacion,
		uj.final_resultado as final_resultado		,
		(select u.id_tipo_ponderaciones from usuarios u where u.id = uj.id_evaluado) as id_tipo_ponderaciones_evaluado,
		(select id_tipo_ponderaciones from usuarios where id = uj.id_evaluador)	as id_tipo_ponderaciones_evaluador,
		(select u.nombre || ' ' || u.apellido from usuarios u  where u.id = uj.id_evaluado) as nombreevaluado,
		email as emailjefe
	from usuarios u, usuarios_jerarquia uj 
	where 
	uj.id_evaluador = u.id 
	and uj.id_evaluado = $id_usuario 
	and uj.id_periodo_eval = $id_periodo_eval
	order by u.nombre;";
	*/

//LO ULTIMO!!
$ss = "SELECT u.id as id, 
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
) as final_auto_funcionario_directivo_vicerrector 												,
uj.final_asistencia as final_asistencia,
uj.final_procon as final_procon,
uj.final_capacitacion as final_capacitacion,
uj.final_resultado as final_resultado		,
(select u.id_tipo_ponderaciones from usuarios u where u.id = uj.id_evaluado) as id_tipo_ponderaciones_evaluado,
(select id_tipo_ponderaciones from usuarios where id = uj.id_evaluador)	as id_tipo_ponderaciones_evaluador,
(select u.nombre || ' ' || u.apellido from usuarios u  where u.id = uj.id_evaluado) as nombreevaluado,
email as emailjefe
from usuarios u, usuarios_jerarquia uj 
where 
uj.id_evaluador = u.id 
and uj.id_evaluado = $id_usuario 
and uj.id_periodo_eval = $id_periodo_eval

and uj.id_evaluador <> uj.id_evaluado	   

order by u.nombre;";



/*
	$ss = "SELECT u.id as id, 
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
) as final_auto_funcionario_directivo_vicerrector 												,
		uj.final_asistencia as final_asistencia,
		uj.final_procon as final_procon,
		uj.final_capacitacion as final_capacitacion,
		uj.final_resultado as final_resultado		,
		(select u.id_tipo_ponderaciones from usuarios u where u.id = uj.id_evaluado) as id_tipo_ponderaciones_evaluado,
		(select id_tipo_ponderaciones from usuarios where id = uj.id_evaluador)	as id_tipo_ponderaciones_evaluador,
		(select u.nombre || ' ' || u.apellido from usuarios u  where u.id = uj.id_evaluado) as nombreevaluado,
		email as emailjefe
	from usuarios u, usuarios_jerarquia uj 
	where 
	uj.id_evaluador = u.id 
	and uj.id_evaluado = $id_usuario 
	and uj.id_periodo_eval = $id_periodo_eval
	order by u.nombre;";
*/
	//echo($ss);

	$candidatos     = consulta_sql($ss);
	$HTML_encuesta .= "  <tr class='filaTituloTabla'>".$LF
	. "    <td colspan='2' align='center' class='tituloTabla'><b>Evaluador</b></td>".$LF

	. "    <td colspan='2' align='center' class='tituloTabla'><b>Periodo Eval</b></td>".$LF
	. "    <td colspan='2' align='center' class='tituloTabla'><b>POA</b></td>".$LF

	. "    <td colspan='2' align='center' class='tituloTabla'><b>Ev.Jefe</b></td>".$LF
	. "    <td colspan='2' align='center' class='tituloTabla'><b>Autoevaluaci&oacute;n</b></td>".$LF
	. "    <td colspan='2' align='center' class='tituloTabla'><b>Asistencia</b></td>".$LF
	//. "    <td colspan='2' align='center' class='tituloTabla'><b>Procon</b></td>".$LF
	. "    <td colspan='2' align='center' class='tituloTabla'><b>Capacitaci&oacute;n</b></td>".$LF
	. "    <td colspan='2' align='center' class='tituloTabla'><b>Resultado Final</b></td>".$LF


	. "    <td colspan='2' align='center' class='tituloTabla'><b>Acción</b></td>".$LF
	. "  </tr>".$LF;
	
	for ($x=0;$x<count($candidatos);$x++) {
		extract($candidatos[$x]);
		$HTML_encuesta .= "  <tr class='filaTabla'>".$LF
						. "    <td colspan='2' class='textoTabla'><u>$nombre $apellido</u></td>".$LF
						. "    <td colspan='2' class='textoTabla'><u>$mini_glosa</u></td>".$LF;
		if ($poa != "") {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$poa%</u></td>".$LF;
		} else {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
		}
		if ($resulteval != "") {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$resulteval%</u></td>".$LF;
		} else {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
		}
		if ($final_auto_funcionario_directivo_vicerrector != "") {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$final_auto_funcionario_directivo_vicerrector%</u></td>".$LF;
		} else {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
		}
		if ($asistencia != "") {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$asistencia%</u></td>".$LF;
		} else {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
		}
		/*
		if ($final_procon != "") {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$final_procon%</u></td>".$LF;
		} else {
			$HTML_encuesta .= "		<td colspan='2' class='textoTabla'><u></u></td>".$LF;
		}
		*/
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

		
		
		
		$ww = "200px";
		$hh = "30px";

		
		$nombreApellido = $nombre." ".$apellido;

		if ($id_estado_eval == 0) { //EVALUACION AUN NO CREADA
			$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u>pendiente</u></td>".$LF;
		}				
		if ($id_estado_eval == 1) { //EVALUACION YA CREADA POR EVALUADOR
			$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: $ww; height:$hh; text-align: center;' size='10' name='Ver' value='Ver...' onClick='javascript:verMiEvaluacion($id,\"$nombreApellido\",\"PUEDE_ACEPTAR_RECHAZAR\",$id_usuarios_jerarquia, $asistencia, $capacitacion, $porcprocon, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$emailjefe\", \"$nombreevaluado\");'></u></td>".$LF;
		}				
		if ($id_estado_eval == 2) { //ECEPTADA POR EVALUADO
			$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: $ww; height:$hh; text-align: center;' size='10' name='Ver' value='Ver (Aceptada)' onClick='javascript:verMiEvaluacion($id,\"$nombreApellido\",\"EVALUACION_ACEPTADA\",$id_usuarios_jerarquia, $asistencia, $capacitacion, $porcprocon, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$emailjefe\", \"$nombreevaluado\");'></u></td>".$LF;
		}				
		if ($id_estado_eval == 3) { //RECHAZADA POR EVALUADO
			$HTML_encuesta .= "    <td colspan='2' class='textoTabla'><u><input type='button' style='width: $ww; height:$hh; text-align: center;' size='10' name='Ver' value='Ver (Rechazada)' onClick='javascript:verMiEvaluacion($id,\"$nombreApellido\",\"EVALUACION_RECHAZADA\",$id_usuarios_jerarquia, $asistencia, $capacitacion, $porcprocon, $id_tipo_ponderaciones_evaluado, $id_tipo_ponderaciones_evaluador, \"$emailjefe\", \"$nombreevaluado\");'></u></td>".$LF;
		}				
		$HTML_encuesta .= "  </tr>".$LF;
	
	}
	?>
	<div class="tituloModulo">
		<?php echo($nombre_modulo); ?>
	</div><br>
	<!--
	<div class="texto">
	  Modo Evaluado.
	</div>
	--><br>
	<table cellpadding="2" cellspacing="1" class="tabla" bgcolor="#FFFFFF">
	  <?php 
		  echo($HTML_encuesta); 
	  ?>
	</table>
<?php	
} else {
	echo "NO TIENE PERMISOS PARA EVALUAR TEST DESEMPEÑO";
}
?>



