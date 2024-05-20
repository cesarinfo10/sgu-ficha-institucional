
<script>
function acciongrabar(modo, id_usuarios_jerarquia) {
	console.log("he entrado");	
	var puedeSeguir = true;
	var strSalida = "";

//	var porcAsistencia = $("#id_select_porc_asistencia").val();
//	var porcCapacitacion = $("#id_select_porc_capacitacion").val();
//	var porcProcon = $("#id_select_porc_procon").val();
//	console.log("asistencia = "+porcAsistencia);
//	console.log("capacitacion = "+porcCapacitacion);


	$('#tablaConAlternativas select').each(function() {
		var id_eval_items = $(this).attr('id');
		var registrarValor = $("#"+id_eval_items).val();
		console.log("registrarValor="+registrarValor);	
		if (registrarValor == 0 ) {
			console.log("valor CERO");
			//alert("Debe responder todo el cuestionario");
			puedeSeguir = false;
			return false;
		} else {
			//console.log("bien!");
			//console.log("bien??? "+id_eval_items);	
			strSalida = strSalida + id_eval_items.replace("id_select_","") + "," + registrarValor + ";";
		}
		//console.log($("#"+id_eval_items).val());
		//console.log($("#id_select_21").val());

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
		console.log("resultado = " + strSalida);
		//alert("se procede a grabar ahora!");
		//location.href='?modulo=evdem_evaluador_autoevaluacion&llaves='+strSalida+'&modo='+modo+'&id_usuarios_jerarquia='+id_usuarios_jerarquia+'&asistencia='+porcAsistencia+'&capacitacion='+porcCapacitacion+"&procon="+porcProcon;
/*
		$.post({url: '?modulo=evdem_realizar_autoevaluacion", 
			data: { data1: "HOLA1", data2: "HOLA2" }
			).done(function( data ) { 
				$( "body" ).html(data);
			});
		});
		*/
		enviarValores(strSalida, id_usuarios_jerarquia, modo);
	} else {
		alert("Todos los valores del cuestionario deben ser ingresados.")
	}
	
	//alert('he pasado');
}
function almacenaVariable(nombreVariable, valor) {
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
		//f.appendChild(i);
	return i;
}
function enviarValores(llaves, id_usuarios_jerarquia, modo){
/*	$.post({url: '?modulo=evdem_realizar_autoevaluacion", 
			data: { data1: "HOLA1", data2: "HOLA2" }
			).done(function( data ) { 
				$( "body" ).html(data);
			});
		});
*/
		var f = document.createElement('form');
		f.action='?modulo=evdem_realizar_autoevaluacion';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');


		i = almacenaVariable("llaves", llaves);
		f.appendChild(i);
		i = almacenaVariable("id_usuarios_jerarquia", id_usuarios_jerarquia);
		f.appendChild(i);
		i = almacenaVariable("evdem_modo", modo);
		f.appendChild(i);

		

		document.body.appendChild(f);
		f.submit();
}
</script>
<?php
		setlocale(LC_NUMERIC, "en_US.UTF-8");
		setlocale(LC_MONETARY, "en_US.UTF-8");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
$idEvaluado = $_REQUEST['id_evaluado'];
$nombreEvaluado    = $_REQUEST['nombre_evaluado'];

if (isset($_POST['llaves']) && (isset($_POST['id_usuarios_jerarquia'])) && (isset($_POST['evdem_modo']))) {
	$llaves = $_POST['llaves'];
	$id_usuarios_jerarquia = $_POST['id_usuarios_jerarquia'];
//CAMBIO
	$evdem_modo = $_POST['evdem_modo'];

//	echo("POST Valor llaves = $llaves"."</br>");
//	echo("POST Valor id_usuarios_jerarquia = $id_usuarios_jerarquia"."</br>");
//	echo("POST Valor evdem_modo = $evdem_modo"."</br>");
	$huboError = evdem_grabarEvaluaciones($llaves, $id_usuarios_jerarquia, $evdem_modo);
	if ($huboError == 0) {
		$evdem_modo = "UPDATE";
		//calculo autoevaluacion!
		$ss = "
			select sum(cast(a.evaluacion as int)/7::float * 100) / count(*) as autoevaluacion
			from eval_items_evaluaciones a , eval_items_preguntas b
			where a.id_usuario_jerarquia = $id_usuarios_jerarquia
			and b.id = a.id_eval_items_preguntas
			and b.es_autoevaluacion = 'S'		
			";
		$cc     = consulta_sql($ss);
		extract($cc[0]);
		$myAutoevaluacion = round($autoevaluacion, 2);
		$myAutoevaluacion = str_replace(",", ".", $myAutoevaluacion);
		//fin calculo autoevaluacion!
		$SQL_update = "	
			update usuarios_jerarquia set 
			final_auto_funcionario_directivo_vicerrector = $myAutoevaluacion
			where id = $id_usuarios_jerarquia												
		";

		//realiza calculo!
		$ss = "
		select 
		cumplimiento_poa as resultado_poa, 
		resultado_eval as result_eval,
		porc_asistencia as porcasistencia,
		porc_capacitacion as porccapacitacion,
		(select id_tipo_ponderaciones from usuarios	 where id = id_evaluado) as id_tipo_ponderaciones
		from usuarios_jerarquia
		where 
		id = $id_usuarios_jerarquia";
//		final_auto_funcionario_directivo_vicerrector as autoevaluacion,

		//echo($ss."</br>");
		$sql_ss     = consulta_sql($ss);
		extract($sql_ss[0]);

		evdem_realizaGrabaCalculos(
							$id_tipo_ponderaciones, 
							$id_usuarios_jerarquia, 
							$porcasistencia, 
							$porccapacitacion, 
							$resultado_poa, 
							$result_eval,									 
							"S",
							$myAutoevaluacion); 

//		echo($SQL_update."</br>");
		if (consulta_dml($SQL_update) == 1) {
			echo(msje_js("Datos Almacenados exitosamente"));
			//echo("<div class='texto'>");
			//echo "REGISTROS ALMACENADOS EXITOSAMENTE!";
			//echo("</div><br>");
		} else {
			//echo("<div class='texto'>");
			//echo "Error Inesperado al momento de actualizar valor!";
			//echo("</div><br>");
			echo(msje_js("Error Inesperado al momento de actualizar valor!"));
		}


	} else {
		echo("<div class='texto'>");
		echo "NO SE PUDO COMPLETAR LA OPERACIÓN!";
		echo("</div><br>");
	}
}
//$evdem_modo = $_REQUEST['evdem_modo'];
//$myAsistencia = $_REQUEST['asistencia'];
//$myCapacitacion = $_REQUEST['capacitacion'];
//$myProcon = $_REQUEST['procon'];

//BUSCAMOS EL USUARIO EVALUADOR//-------------------------------------------------------------------------
//$ss = "
//select id_unidad as id_unidad from usuarios where id = $id_usuario
//";
//DATOS EN DURO
$id_usuario = $_SESSION['id_usuario'];
//$id_usuario = 960;

$id_usuarioParam = $_GET['ID_USUARIO_PARAM'];
//echo(msje_js("usuario normal = $id_usuario"));
if ($id_usuarioParam <> "") {
//	//$id_usuario = 1273; //960; //656; //1321; //1211; //1305; //569; //1321; //939; //1180; //315; //722; //1274; //3; //655; //656; //419; //558; //744; //1258; //741; //1207; //1211; //1273;
//	echo(msje_js("usuario = $id_usuarioParam"));
	$id_usuario = $id_usuarioParam;	
	echo(msje_js("usuario cambiado = $id_usuario"));
}

//$id_usuario = 1021; //932; //1021; //$_SESSION['id_usuario'];
//$id_usuario = 1308; //csanhueza, 1020; //932; //1021; //$_SESSION['id_usuario'];
$ss = "	 
	select mini_glosa as mini_glosa, id as id_periodo_eval from periodo_eval where activo=true;
		";
$sqlss     = consulta_sql($ss);
extract($sqlss[0]);

$ss = "
	select id as id_usuario_evaluador from usuarios 
	where id_unidad = (
					select id_unidad 
					from usuarios 
					where id = $id_usuario
			)
	and jefe_unidad;
";
/*
$ss = "
	select id as id_usuario_evaluador from usuarios 
	where id_unidad = (
		select dependencia 
		from gestion.unidades 
		where id = (
					select id_unidad 
					from usuarios 
					where id = $id_usuario
			)
	)
	and jefe_unidad
";
*/
//echo ("***1021 = CARLOS MANFRED ZAMBRANO RODRIGUEZ </br>");
//echo ("*** 932 = Cesar Suay </br>");
//echo("id_usuario=".$id_usuario."</br>");

//echo ("*** $ss</br>");
$cc     = consulta_sql($ss);
$existeCC = count($cc);
extract($cc[0]);
if ($existeCC==0) {  
	//NO EXISTE REGISTRO; SE ASIGNA EL MISMO usuario evaluador como evaluado
	$id_usuario_evaluador = $id_usuario;
}

//echo("* * * id_usuario_evaluador=$id_usuario_evaluador"."</br>");
//FIN BUSCAMOS EL USUARIO EVALUADOR//---------------------------------------------------------------------
//BUSCAMOS EL USUARIO JERARQUIA --------------------------------------------------------------------------
$ss = "
	select id from usuarios_jerarquia where 
	id_periodo_eval = (select id from periodo_eval where activo)
	and id_evaluado = $id_usuario
";
//echo ("*** $ss</br>");
$candidatosAutoEval     = consulta_sql($ss);
$cuentaAuto = count($candidatosAutoEval);
//echo ("*** cuentaAuto = $cuentaAuto</br>");
if ($cuentaAuto==0) {  
//	echo ("*** INSERT...</br>");
	$SQL_insert = "
		insert into usuarios_jerarquia(id_evaluador, 
									id_evaluado, 
									id_estado_eval, 
									id_periodo_eval									   
									) values (
										$id_usuario_evaluador, 
										$id_usuario, 
										0, 
										(select id from periodo_eval where activo)
										)";
	if (consulta_dml($SQL_insert) == 1) {
	} else {
	}
}
if (!isset($_POST['id_usuarios_jerarquia'])) {
	$ss = "
		select 
		id as id_usuarios_jerarquia, 
		COALESCE(final_auto_funcionario_directivo_vicerrector,0) as autoevaluacion , 
		cerrado as autoevaluacion_cerrado
		from usuarios_jerarquia 
		where 
		id_periodo_eval = (select id from periodo_eval where activo)
		and id_evaluado = $id_usuario
	";
	
	//echo ("$ss"."</br>");
	$myAutoEval     = consulta_sql($ss);
	extract($myAutoEval[0]);
	//echo ("OBTIENE INICIAL id_usuarios_jerarquia = $id_usuarios_jerarquia"."</br>");
	if ($autoevaluacion == 0) {
		$evdem_modo = "NEW";
	} else {
		$evdem_modo = "UPDATE";
	}
	//echo("<br>evdem_modo=".$evdem_modo);
} else {

}

//FIN BUSCAMOS EL USUARIO JERARQUIA ------------------------------------------------------------

//$id_usuarios_jerarquia = $_REQUEST['id_usuarios_jerarquia'];
//echo("* * *id_usuarios_jerarquia = ".$id_usuarios_jerarquia."</br>");

//include("validar_modulo.php");



$disabledAtribute = "";
if (($autoevaluacion_cerrado == "t")){
	$disabledAtribute = "disabled";
}

//echo("disabledAtribute = ".$disabledAtribute."</br>");
/* LISTADO PREGUNTAS ALTERNATIVAS*/
$ss = "select
		id 			as id_eval_items,
		glosa_item 	as glosa_item, 
		mostrar	 	as mostrar
		from eval_items
		where id_tipo_evaluaciones = 1
		and mostrar = 1
		and id in (1,2,3)
		order by orden";

$sql_items     = consulta_sql($ss);
$HTML_items .= "  <table cellpadding='2' id='tablaConAlternativas' cellspacing='1' class='tabla' bgcolor='#FFFFFF' width='auto'>";
for ($x=0;$x<count($sql_items);$x++) {
	extract($sql_items[$x]);
	if ($mostrar == 1) {
		$HTML_items .= "  <tr class='filaTituloTabla'>".$LF
		. "    <td class='celdaFiltro'><b>$glosa_item</b></td>".$LF
		. "    <td class='celdaFiltro'><b>Puntaje</b></td>".$LF
		. "  </tr>".$LF;
/*
		$ss = "select 
				id 					as id_eval_items_preguntas,
				glosa_pregunta 		as glosa_pregunta,
				id_tipo_pregunta 	as id_tipo_pregunta
				from eval_items_preguntas
				where id_eval_items = $id_eval_items
				and es_autoevaluacion = 'S'
				order by orden;";
				*/
		$ss = "select 
				id 					as id_eval_items_preguntas,
				(
					case when 
						(select tiene_procon from usuarios where id = $id_usuario) = 't' then 
							glosa_pregunta
						else
							glosa_pregunta_sin_procon
					end
				) glosa_pregunta,
				id_tipo_pregunta 	as id_tipo_pregunta
				from eval_items_preguntas
				where id_eval_items = $id_eval_items
				and es_autoevaluacion = 'S'
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
		//echo("item no mostrado! OJO");
	}


}
$HTML_items .= "  </table>";



?>



<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<div class="texto">	
  Evaluación desempeño : <b><?php echo $mini_glosa ?></b>
</div><br>

<!--<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('anotacion');"> -->
	<?php echo($HTML_items); ?>
	<?php //echo($HTML_sinAlternativas); 
	?>
	</br>
	<?php 
?>
<div class="texto">	
	<?php echo $HTML_tabla2; ?>
</div><br>


<?php
		$ww = "200px";
		$hh = "30px";
	?>
	<input style='width: <?php echo $ww; ?>; height:<?php echo $hh; ?>; text-align: center;' type="button" name="grabar" value="Grabar" onClick="javascript:acciongrabar('<?php echo $evdem_modo;?>', <?php echo $id_usuarios_jerarquia; ?>);" <?php echo $disabledAtribute; ?>>

