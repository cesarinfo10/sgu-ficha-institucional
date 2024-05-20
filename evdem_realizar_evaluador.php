
<script>
//******************************************************
//ESTE MODULO REALIZA LA EVALUACION (PREGUNTA x PREGUNTA)
//******************************************************

function almacenaVariable(nombreVariable, valor) {
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
	return i;
}
function enviarValoresAccionGrabar(strSalida, modo, id_usuarios_jerarquia, porcAsistencia, porcCapacitacion){
		var f = document.createElement('form');
		f.action='?modulo=evdem_evaluador';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("llaves", strSalida);
		f.appendChild(i);

		i = almacenaVariable("modo", modo);
		f.appendChild(i);

		i = almacenaVariable("id_usuarios_jerarquia", id_usuarios_jerarquia);
		f.appendChild(i);

		i = almacenaVariable("asistencia", porcAsistencia);
		f.appendChild(i);

		i = almacenaVariable("capacitacion", porcCapacitacion);
		f.appendChild(i);

//		i = almacenaVariable("procon", procon);
//		f.appendChild(i);

		document.body.appendChild(f);
		f.submit();
}

function acciongrabar(modo, id_usuarios_jerarquia) {
	console.log("he entrado");	
	var puedeSeguir = true;
	var strSalida = "";

	var porcAsistencia = $("#id_select_porc_asistencia").val();
	var porcCapacitacion = $("#id_select_porc_capacitacion").val();

	$('#tablaConAlternativas select').each(function() {
		var id_eval_items = $(this).attr('id');
		var registrarValor = $("#"+id_eval_items).val();
		console.log("registrarValor="+registrarValor);	
		if (registrarValor == 0 ) {
			console.log("valor CERO");
			//TODO alert("Debe responder todo el cuestionario");
			puedeSeguir = false;
			return false;
		} else {
			strSalida = strSalida + id_eval_items.replace("id_select_","") + "," + registrarValor + ";";
		}

	});	
	if (puedeSeguir) {
		console.log("voy a la segunda verificación!");
		$('#tablaSinAlternativas textarea').each(function() {
			var id_eval_items = $(this).attr('id');
			var registrarValor = $("#"+id_eval_items).val();
			console.log(id_eval_items);	
			console.log("registrarValor="+registrarValor);	
			//console.log($("#id_select_21").val());
			//if (registrarValor != "") {
				strSalida = strSalida + id_eval_items.replace("textarea_","") + "," + registrarValor + ";";
			//}
			
		});	
		enviarValoresAccionGrabar(strSalida, modo, id_usuarios_jerarquia, porcAsistencia, porcCapacitacion);
	} else {
		alert("Todos los valores del cuestionario deben ser ingresados.")
	}
}
function enviarValoresEvaluacion(accion, modo, id_usuarios_jerarquia, emailjefe, nombreevaluado){
		var f = document.createElement('form');
		f.action='?modulo=evdem_evaluado';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("accion", accion);
		f.appendChild(i);

//		i = almacenaVariable("modo", modo);
//		f.appendChild(i);

		i = almacenaVariable("id_usuarios_jerarquia", id_usuarios_jerarquia);
		f.appendChild(i);

		i = almacenaVariable("emailjefe", emailjefe);
		f.appendChild(i);

		i = almacenaVariable("nombre_evaluado", nombreevaluado);
		f.appendChild(i);

		document.body.appendChild(f);
		f.submit();
}
function aceptarEvaluacion(modo, id_usuarios_jerarquia) {
	var r = confirm("esta seguro(a) de aceptar evaluación?");
	if (r == true) {
		enviarValoresEvaluacion("ACEPTAR_EVALUACION", modo, id_usuarios_jerarquia, "", "");
	} else {
		//alert("salida!");
	}
	
}
function rechazarEvaluacion(modo, id_usuarios_jerarquia, emailjefe, nombreevaluado) {
	var r = confirm("esta seguro(a) de rechazar evaluación?");
	if (r == true) {
		enviarValoresEvaluacion("RECHAZAR_EVALUACION", modo, id_usuarios_jerarquia, emailjefe, nombreevaluado);
	} else {
		//alert("salida!");
	}	

}

</script>
<?php
		setlocale(LC_NUMERIC, "en_US.UTF-8");
		setlocale(LC_MONETARY, "en_US.UTF-8");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if (isset($_POST['id_evaluado'])) {
	$idEvaluado 			= $_POST['id_evaluado'];
}
//echo("* * * id_tipo_ponderaciones_evaluado ");
if (isset($_POST['id_tipo_ponderaciones_evaluado'])) {
	$id_tipo_ponderaciones_evaluado 			= $_POST['id_tipo_ponderaciones_evaluado'];
}
//echo("* * * id_tipo_ponderaciones_evaluado = $id_tipo_ponderaciones_evaluado"."</br>");
if (isset($_POST['id_tipo_ponderaciones_evaluador'])) {
	$id_tipo_ponderaciones_evaluador 			= $_POST['id_tipo_ponderaciones_evaluador'];
}

if (isset($_POST['nombre_evaluado'])) {
	$nombreEvaluado    		= $_POST['nombre_evaluado'];
}
if (isset($_POST['modo'])) {
	$evdem_modo 			= $_POST['modo'];
}
if (isset($_POST['asistencia'])) {
	$myAsistencia 			= $_POST['asistencia'];
}
if (isset($_POST['capacitacion'])) {
	$myCapacitacion		 	= $_POST['capacitacion'];
}
if (isset($_POST['procon'])) {
	$myProcon 				= $_POST['procon'];
}
if (isset($_POST['id_usuarios_jerarquia'])) {
	$id_usuarios_jerarquia 	= $_POST['id_usuarios_jerarquia'];	
}
if (isset($_POST['rehacer'])) {
	$rehacer 			= $_POST['rehacer'];
}

if (isset($_POST['emailjefe'])) {
	$emailjefe 	= $_POST['emailjefe'];	
}

//echo("* * * id_tipo_ponderaciones_evaluado = $id_tipo_ponderaciones_evaluado"."</br>");
//echo("* * * nombreEvaluado = $nombreEvaluado"."</br>");
//echo("* * * evdem_modo = $evdem_modo"."</br>");-
//echo("* * * myAsistencia = $myAsistencia"."</br>");
//echo("* * * myCapacitacion = $myCapacitacion"."</br>");
//echo("* * * id_usuarios_jerarquia = $id_usuarios_jerarquia"."</br>");
include("validar_modulo.php");

//REHACER!!
if ($rehacer == 1) {
	$SQL_rehacer = "update usuarios_jerarquia set rehacer = 1, id_estado_eval = 1 where id = $id_usuarios_jerarquia";
	if (consulta_dml($SQL_rehacer) == 1) {
		$evdem_modo = "UPDATE";
	}	
}
//NEW* * * * * * * * * * * * * * * * **  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
//$SQL_QUERY_PROCON = "select final_procon from usuarios_jerarquia where id = $id_usuarios_jerarquia";
////echo("<br>sql_qery_procon=".$SQL_QUERY_PROCON);
//$sql_procon     = consulta_sql($SQL_QUERY_PROCON);
//$final_procon = $sql_procon[0]['final_procon'];
////echo("<br>final_procon=".$final_procon);



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
	extract($sql_denominador[0]);
	$myDenominador = $denominador;
	if ($denominador=="") {
		$myDenominador = "0";
	}

//			echo("4.-numerador = ".$myNumerador."</br>");
//			echo("5.-denominador = ".$myDenominador."</br>");

	$resultadoPOA = round(($myNumerador / $myDenominador)*100,2);
//echo("6.-resultado POA = ".$resultadoPOA);
//echo("7.-id_tipo_ponderaciones_evaluado  = ".$id_tipo_ponderaciones_evaluado);
} else {
	$resultadoPOA = "0";
}





//FIN NEW* * * * * * * *  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

$disabledAtribute = "";
//if (($evdem_modo == "PUEDE_ACEPTAR_RECHAZAR") || ($evdem_modo == "EVALUACION_ACEPTADA") || ($evdem_modo == "EVALUACION_RECHAZADA")){
if (($evdem_modo == "PUEDE_ACEPTAR_RECHAZAR") || ($evdem_modo == "EVALUACION_ACEPTADA") ){	
	$disabledAtribute = "disabled";
}
/* LISTADO PREGUNTAS ALTERNATIVAS*/
$ss = "		select
		a.id 			as id_eval_items,
		a.glosa_item 	as glosa_item, 
		a.mostrar	 	as mostrar
		,cod_interno
		from eval_items a, ponderaciones b
		where a.id_tipo_evaluaciones = 1
		and a.mostrar = 1
		and b.id_tipo_ponderaciones = $id_tipo_ponderaciones_evaluado
		and b.eval_items_cod_interno = a.cod_interno
		order by a.orden; ";

$sql_items     = consulta_sql($ss);
$HTML_items .= "  <table cellpadding='2' id='tablaConAlternativas' cellspacing='1' class='tabla' bgcolor='#FFFFFF' width='auto'>";
for ($x=0;$x<count($sql_items);$x++) {
	extract($sql_items[$x]);
	if ($mostrar == 1) {
		$HTML_items .= "  <tr class='filaTituloTabla'>".$LF
		. "    <td class='celdaFiltro'><b>$glosa_item</b></td>".$LF
		. "    <td class='celdaFiltro'><b>Puntaje</b></td>".$LF
		. "  </tr>".$LF;

		$ss = "select 
				id 					as id_eval_items_preguntas,
				glosa_pregunta 		as glosa_pregunta,
				id_tipo_pregunta 	as id_tipo_pregunta
				from eval_items_preguntas
				where id_eval_items = $id_eval_items
				and es_autoevaluacion = 'N'
				order by orden;";

		
		$sql_sub_items     = consulta_sql($ss);	
		for ($y=0;$y<count($sql_sub_items);$y++) {
			extract($sql_sub_items[$y]);
			$HTML_items .= "  <tr class='filaTabla'>".$LF
			. "    <td class='textoTabla' id='$id_eval_items_preguntas'><u>$glosa_pregunta</u></td>".$LF
			. "    <td class='textoTabla' id='td_select_$id_eval_items_preguntas'>
													<u>";
			if ($id_tipo_pregunta == 1) {

				$HTML_items .= " 	<select id='id_select_$id_eval_items_preguntas' $disabledAtribute>";

				$ss = "select evaluacion as evaluacion from eval_items_evaluaciones where id_usuario_jerarquia = $id_usuarios_jerarquia 
													and id_eval_items_preguntas = $id_eval_items_preguntas";
				$sql_evaluacion     = consulta_sql($ss);	
				$registros = count($sql_evaluacion);
				extract($sql_evaluacion[0]);
				if ($registros = 0) { 
					$HTML_items .= " 		<option value='0'>Seleccione</option>
												<option value='1'>1</option>
												<option value='2'>2</option>
												<option value='3'>3</option>
												<option value='4'>4</option>
												<option value='5'>5</option>
												<option value='6'>6</option>
												<option value='7'>7</option>";

				} else {
					$HTML_items .= " 		<option value='0'>Seleccione</option>";
					for ($z=1;$z<=7;$z++) {
						$seleccionado = "";
						if ($evaluacion==$z) {
							$seleccionado = "selected";
						}
						$HTML_items .= " 		<option value='$z' $seleccionado>$z</option>";
					}
				}
				$HTML_items .= " 						</select>";
			}										
			$HTML_items .= "  										</u></td>".$LF
			. "  </tr>".$LF;

		}
			
	} else {
		echo("item no mostrado! OJO");
	}


}
$HTML_items .= "  </table>";



/* LISTADO PREGUNTAS SIN ALTERNATIVAS*/
$ss = "select
		id 			as id_eval_items,
		glosa_item 	as glosa_item, 
		mostrar	 	as mostrar
		from eval_items
		where id_tipo_evaluaciones = 1
		and mostrar = 0
		order by orden";

$sql_items     = consulta_sql($ss);


for ($x=0;$x<count($sql_items);$x++) {
	extract($sql_items[$x]);

	$HTML_sinAlternativas .= "  <table cellpadding='2' id='tablaSinAlternativas' cellspacing='1' class='tabla' bgcolor='#FFFFFF' width='auto'>";
		$HTML_sinAlternativas .= "  <tr class='filaTituloTabla'>".$LF
		. "    <td class='celdaFiltro'><b>$glosa_item</b></td>".$LF
		. "  </tr>".$LF;

		$ss = "
				select 
				eip.id 					as id_eval_items_preguntas,
				eip.glosa_pregunta 		as glosa_pregunta,
				eip.id_tipo_pregunta 	as id_tipo_pregunta,
				tp.largo_campo         as largo_campo
				from eval_items_preguntas eip, tipo_preguntas tp
				where eip.id_eval_items = $id_eval_items
				and eip.es_autoevaluacion = 'N'
				and tp.id = eip.id_tipo_pregunta
				order by eip.orden;";

		$sql_sub_items     = consulta_sql($ss);	
		for ($y=0;$y<count($sql_sub_items);$y++) {
			extract($sql_sub_items[$y]);

			$ss = "select evaluacion as evaluacion from eval_items_evaluaciones where id_usuario_jerarquia = $id_usuarios_jerarquia 
			and id_eval_items_preguntas = $id_eval_items_preguntas";

$sql_evaluacion     = consulta_sql($ss);	
$registros = count($sql_evaluacion);
extract($sql_evaluacion[0]);
if ($registros = 0) { 
	$evaluacion = "";
}
			$HTML_sinAlternativas .= "  <tr class='filaTabla'>".$LF
									. "    <td class='textoTabla'><u>
													<div class='texto'>
													<b>$glosa_pregunta</b>
													</div><br>
													<textarea id='textarea_$id_eval_items_preguntas' class='grande' maxlength='$largo_campo' name='anotacion' $disabledAtribute>$evaluacion</textarea>
													</u>
											</td>".$LF;
			$HTML_sinAlternativas .= "  </tr>".$LF;

		}
		$HTML_sinAlternativas .= "  </table>";
	}
	
?>

<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<div class="texto">	
  Evaluación Para : <b><?php echo $nombreEvaluado ?></b>
</div><br>

	<?php echo($HTML_items); ?>
	<?php echo($HTML_sinAlternativas); ?>
	</br>
	<?php 
		$HTML_porcAsistencia .= " 	<select id='id_select_porc_asistencia' $disabledAtribute>";

		for ($p=0;$p<=100;$p++) {
			if ($p==$myAsistencia) {
				$HTML_porcAsistencia .= " 					<option value='$p' selected>$p</option>";
			} else {
				$HTML_porcAsistencia .= " 					<option value='$p'>$p%</option>";
			}
			
		}	

		$HTML_porcAsistencia .= "</select>";

		//ULTIMO CAMBIO
		//$HTML_porcCapacitacion .= " 	<select id='id_select_porc_capacitacion' $disabledAtribute>";
		$HTML_porcCapacitacion .= " 	<select id='id_select_porc_capacitacion' disabled>";
		//FIN ULTIMO CAMBIO

		for ($p=0;$p<=100;$p++) {
			if ($p==$myCapacitacion) {
				$HTML_porcCapacitacion .= " 					<option value='$p' selected>$p</option>";
			} else {
				$HTML_porcCapacitacion .= " 					<option value='$p'>$p%</option>";
			}

			
		}	

		$HTML_porcCapacitacion .= "</select>";

		$HTML_tabla2 = "";

		$HTML_tabla2 .= "  <table cellpadding='2' id='tablaSinAlternativas' cellspacing='1' class='tabla' bgcolor='#FFFFFF' width='auto'>";
		$HTML_tabla2 .=   "  <tr class='filaTituloTabla'>".$LF
						. "    <td class='celdaFiltro'><b>Asistencia</b></td>".$LF
						. "    <td class='celdaFiltro'><b>$HTML_porcAsistencia</b></td>".$LF
						. "  </tr>".$LF;
		$HTML_tabla2 .=   "  <tr class='filaTituloTabla'>".$LF
						. "    <td class='celdaFiltro'><b>Capacitaci&oacute;n</b></td>".$LF
						. "    <td class='celdaFiltro'><b>$HTML_porcCapacitacion</b></td>".$LF
						. "  </tr>".$LF;
$HTML_tabla2 .=   "  <tr class='filaTituloTabla'>".$LF
. "    <td class='celdaFiltro'><b>PROCON</b></td>".$LF
. "    <td class='celdaFiltro'><b>$resultadoPOA%</b></td>".$LF
. "  </tr>".$LF;

		$HTML_tabla2 .= "</table>";
		

?>
<div class="texto">	
	<?php echo $HTML_tabla2; ?>
</div><br>


<?php
		$ww = "200px";
		$hh = "30px";

		if ($evdem_modo == "PUEDE_ACEPTAR_RECHAZAR") { ?>
			<input style='width: <?php echo $ww; ?>; height:<?php echo $hh; ?>; text-align: center;' type="button" id="aceptarEvaluacion" name="aceptarEvaluacion" value="Aceptar Evaluación" onClick="javascript:aceptarEvaluacion('<?php echo $evdem_modo; ?>',<?php echo $id_usuarios_jerarquia; ?>);">
			<input style='width: <?php echo $ww; ?>; height:<?php echo $hh; ?>; text-align: center;' type="button" id="rechazarEvaluacion" name="rechazarEvaluacion" value="Rechazar Evaluación" onClick="javascript:rechazarEvaluacion('<?php echo $evdem_modo; ?>',<?php echo $id_usuarios_jerarquia; ?>,'<?php echo $emailjefe; ?>','<?php echo $nombreEvaluado; ?>');">
			
		<?php 
		}
		//echo("MODO = $evdem_modo"."</br>");
		if ($evdem_modo == "EVALUACION_RECHAZADA") {
			//para que pueda grabac cuantas veces quiera!
			$evdem_modo = "UPDATE";
		}
		//echo("MODO = $evdem_modo"."</br>");
		if (($evdem_modo == "NEW") || ($evdem_modo == "UPDATE")) { ?>
			<input style='width: <?php echo $ww; ?>; height:<?php echo $hh; ?>; text-align: center;' type="button" name="grabar" value="Grabar" onClick="javascript:acciongrabar('<?php echo $evdem_modo; ?>',<?php echo $id_usuarios_jerarquia; ?>);">
		<?php 
		} 
	?>
<!-- Fin: <?php echo($modulo); ?> -->


