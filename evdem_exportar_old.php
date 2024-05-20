<script>
function cambiaPeriodo() {	
	var periodoSeleccionado = $("#cmbPeriodos").val();
	enviarValores(periodoSeleccionado);
}
function almacenaVariable(nombreVariable, valor) {
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
		//f.appendChild(i);
	return i;
}

function enviarValores(periodoSeleccionado){
		var f = document.createElement('form');
		f.action='?modulo=evdem_exportar';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');
		i = almacenaVariable("periodo_seleccionado", periodoSeleccionado);
		f.appendChild(i);
		document.body.appendChild(f);
		f.submit();
}

</script>

<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
if (isset($_POST['periodo_seleccionado'])) {
	$periodoSeleccionado 			= $_POST['periodo_seleccionado'];
} else {
	$ss = "select id as id_vigente from periodo_eval where activo";
	$sqlperiodo     = consulta_sql($ss);
	extract($sqlperiodo[0]);	
	$periodoSeleccionado = $id_vigente;
}
//echo("periodoSeleccionado = $periodoSeleccionado"."</br>");

$id_usuario = $_SESSION['id_usuario'];
$id_usuarios_tipo = $_SESSION['tipo'];


	$anoEnCurso = $ANO; //"2020";
	//$id_periodo_eval = "1";
	//FIN DATOS EN DURO
	$ss = "	 
	select mini_glosa as mini_glosa, id as id_periodo_eval from periodo_eval where activo=true;
		";
	$sqlss     = consulta_sql($ss);
	extract($sqlss[0]);	
	
		include("validar_modulo.php");
		/*
		$ss = "
		select 
		u.id as id, 
		u.nombre || ' ' || u.apellido as nombre, 
		gu.nombre as unidad,
		uj.resultado_eval as resulteval,
		uj.porc_asistencia as asistencia, 
		uj.porc_capacitacion as capacitacion,
		uj.final_auto_funcionario_directivo_vicerrector as autoevaluacion,
		uj.cumplimiento_poa as poa,
		uj.final_resultado as final_resultado
		from 
		usuarios u 
		left join gestion.unidades as gu on gu.id = u.id_unidad
		left join usuarios_jerarquia as uj on uj.id_evaluado = u.id and uj.id_periodo_eval = $periodoSeleccionado
		where u.tipo <> 3 and u.activo
		and u.id_unidad is not null
		order by gu.nombre, u.nombre_usuario, unidad;
		";
		*/
/*
		$ss = "
		select 
		u.id as id, 
		u.nombre || ' ' || u.apellido as nombre, 
		gu.nombre as unidad,
		uj.resultado_eval as resulteval,
		uj.porc_asistencia as asistencia, 
		uj.porc_capacitacion as capacitacion,
		(select uj2.final_auto_funcionario_directivo_vicerrector 
		from usuarios_jerarquia uj2 where uj2.id_evaluador = u.id and uj2.id_evaluado = u.id and uj2.id_periodo_eval = $periodoSeleccionado) as autoevaluacion,
		uj.cumplimiento_poa as poa,
		uj.final_resultado as final_resultado
		from 
		usuarios u 
		left join gestion.unidades as gu on gu.id = u.id_unidad
		left join usuarios_jerarquia as uj on uj.id_evaluado = u.id 
										and uj.id_evaluador <> u.id 
										and uj.id_periodo_eval = $periodoSeleccionado
		where u.tipo <> 3 and u.activo
		and u.id_unidad is not null
		order by gu.nombre, u.nombre_usuario, unidad;		
		";
*/
/*
		$ss = "
		select 
		u.id as id, 
		u.nombre || ' ' || u.apellido as nombre, 
		gu.nombre as unidad,
		uj.resultado_eval as resulteval,
		uj.porc_asistencia as asistencia, 
		uj.porc_capacitacion as capacitacion,
		(
			case when (
						(
							select uj2.final_auto_funcionario_directivo_vicerrector 
								from usuarios_jerarquia uj2 
									where uj2.id_evaluador = u.id 
									and uj2.id_evaluado = u.id 
									and uj2.id_periodo_eval = $periodoSeleccionado
						) is not null	
					) then 
						(
							select uj2.final_auto_funcionario_directivo_vicerrector 
								from usuarios_jerarquia uj2 
									where uj2.id_evaluador = u.id 
									and uj2.id_evaluado = u.id 
									and uj2.id_periodo_eval = $periodoSeleccionado
						)
					
			else uj.final_auto_funcionario_directivo_vicerrector
			end
			) as autoevaluacion, 			
		uj.cumplimiento_poa as poa,
		uj.final_resultado as final_resultado
		,
		--pregunta 1
		--(select glosa_pregunta from eval_items_preguntas where id = 18) pregunta1,
		--respuesta 1
		(select evaluacion respuesta_01 from eval_items_evaluaciones where id_usuario_jerarquia = uj.id and id_eval_items_preguntas = 18) esfuerzo_de_mejora,
		--pregunta 2
		--(select glosa_pregunta from eval_items_preguntas where id = 19) pregunta2,
		--respuesta 2
		(select evaluacion respuesta_01 from eval_items_evaluaciones where id_usuario_jerarquia = uj.id and id_eval_items_preguntas = 19) necesidad_capacitacion,
		--pregunta 3
		--(select glosa_pregunta from eval_items_preguntas where id = 20) pregunta3,
		--respuesta 2
		(select evaluacion respuesta_01 from eval_items_evaluaciones where id_usuario_jerarquia = uj.id and id_eval_items_preguntas = 20) comentario_desempeño		
		from 
		usuarios u 
		left join gestion.unidades as gu on gu.id = u.id_unidad
		left join usuarios_jerarquia as uj on uj.id_evaluado = u.id 
										and uj.id_evaluador <> u.id 
										and uj.id_periodo_eval = $periodoSeleccionado
		where u.tipo <> 3 and u.activo
		and u.id_unidad is not null
		order by gu.nombre, u.nombre_usuario, unidad	
		";		
*/
//ESTA ES 

$ss = "
select 
	id, 
	nombre, 
	unidad, 
	nombre_usuario,
	sum(resulteval) resulteval, 
	sum(asistencia) asistencia, 
	sum(capacitacion) capacitacion, 
	sum(autoevaluacion) autoevaluacion, 
	sum(poa) poa, 
	sum(final_resultado) final_resultado,
	esfuerzo_de_mejora,
	necesidad_capacitacion,
	comentario_desempeno		
	from (
select 
u.id as id, 
u.nombre || ' ' || u.apellido as nombre, 
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
							and uj2.id_periodo_eval = $periodoSeleccionado
				) is not null	
			) then 
				(
					select uj2.final_auto_funcionario_directivo_vicerrector 
						from usuarios_jerarquia uj2 
							where uj2.id_evaluador = u.id 
							and uj2.id_evaluado = u.id 
							and uj2.id_periodo_eval = $periodoSeleccionado
				)
			
	else uj.final_auto_funcionario_directivo_vicerrector
	end
	,0) as autoevaluacion, 			
coalesce(uj.cumplimiento_poa,0) as poa,
coalesce(uj.final_resultado,0) as final_resultado,
(
	select evaluacion respuesta_01 from eval_items_evaluaciones where id_usuario_jerarquia in (
	select id from usuarios_jerarquia ujj where ujj.id_periodo_eval = 2 and ujj.id_evaluado = u.id) 
	and evaluacion is not null and id_eval_items_preguntas = 18
	
) esfuerzo_de_mejora,
(
	select evaluacion respuesta_01 from eval_items_evaluaciones where id_usuario_jerarquia in (
	select id from usuarios_jerarquia ujj where ujj.id_periodo_eval = 2 and ujj.id_evaluado = u.id) 
	and evaluacion is not null and id_eval_items_preguntas = 19
) necesidad_capacitacion,
(
	select evaluacion respuesta_01 from eval_items_evaluaciones where id_usuario_jerarquia in (
	select id from usuarios_jerarquia ujj where ujj.id_periodo_eval = 2 and ujj.id_evaluado = u.id) 
	and evaluacion is not null and id_eval_items_preguntas = 20
) comentario_desempeno
from 
usuarios u 
left join gestion.unidades as gu on gu.id = u.id_unidad
left join usuarios_jerarquia as uj on uj.id_evaluado = u.id 
								and uj.id_evaluador <> u.id 
								and uj.id_periodo_eval = $periodoSeleccionado
where u.tipo <> 3 and u.activo
and u.id_unidad is not null
--and u.id not in (	744	)
) as tabla
group by 
id, 
nombre, 
unidad,
nombre_usuario, 
esfuerzo_de_mejora,
necesidad_capacitacion,
comentario_desempeno
--order by gu.nombre, u.nombre_usuario, unidad	
order by unidad, nombre_usuario
";


		//echo ($ss);
		$SQL_tabla_completa = "COPY ($ss) to stdout WITH CSV HEADER";
		$candidatos     = consulta_sql($ss);
		$HTML_encuesta .= "  <tr class='filaTituloTabla'>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Id</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Nombre</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Unidad</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>POA</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Ev.Jefe</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Autoevaluaci&oacute;n</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Asistencia</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Capacitaci&oacute;n</b></td>".$LF
		. "    <td colspan='2' align='center' class='tituloTabla'><b>Resultado Final</b></td>".$LF

		. "  </tr>".$LF;
		
//echo("uno");
		$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
//		echo("dos");		
		$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
//		echo("tres");
		$nombre_arch = "sql-fulltables/$id_sesion.sql";
//		echo("cuatro");
		file_put_contents($nombre_arch,$SQL_tabla_completa);
//		echo("cinco");

		$ss = "select id as id, mini_glosa as mini_glosa, activo as activo from periodo_eval";
		$periodos = consulta_sql($ss);
		$HTML_selectAno = "Resultado evaluación desempeño : 
							<select name='cmbPeriodos' id='cmbPeriodos' onChange='cambiaPeriodo()'>";

		for ($x=0;$x<count($periodos);$x++) {
			extract($periodos[$x]);
			$sss = "";
			//echo("id = $id ");
			if ($id == $periodoSeleccionado) {
				$sss = "selected";
			}
			$HTML_selectAno .="<option value='$id' $sss>$mini_glosa</option>";
		}	

		$HTML_selectAno .="</select>";	

		for ($x=0;$x<count($candidatos);$x++) {
			extract($candidatos[$x]);
			
			$disabledAtribute = "";
			if ($autoevaluacion_cerrada == "t") { //true
				$disabledAtribute = "disabled";
			}
			$HTML_encuesta .= "  <tr class='filaTabla'>".$LF
							. "    <td colspan='2' class='textoTabla'><u>$id</u></td>".$LF
							. "    <td colspan='2' class='textoTabla'><u>$nombre</u></td>".$LF
							. "    <td colspan='2' class='textoTabla'><u>$unidad</u></td>".$LF;
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
			if ($autoevaluacion != "") {
				$HTML_encuesta .= "		<td colspan='2' class='textoTabla' style='text-align: right;'><u>$autoevaluacion%</u>";
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
		}
		?>
		<div class="tituloModulo">
			<?php echo($nombre_modulo); ?>
			
		</div><br>
		<div class="texto">
		<?php
			echo($HTML_selectAno);
		?>
		</div>		
		<div class="texto">
		<?php
			echo($boton_tabla_completa);
		?>
		</div>
		
		</br>
		<table cellpadding="2" cellspacing="1" class="tabla" bgcolor="#FFFFFF">
		<?php 
			echo($HTML_encuesta); 			
			
		?>
		</table>
		



