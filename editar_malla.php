<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_malla = $_REQUEST['id_malla'];
if (!is_numeric($id_malla)) {
	echo(js("location.href='principal.php?modulo=gestion_carreras';"));
	exit;
};

if ($_REQUEST['guardar'] == "Guardar") {

	$tit_grado_tipos = array("tns_actividad","ga_actividad","tp_actividad","otros_actividad");
	foreach ($tit_grado_tipos AS $tipo_tit_grado) {
		foreach($_REQUEST AS $campo => $valor) {
			if (substr($campo,0,strlen($tipo_tit_grado)) == $tipo_tit_grado) {
				$_REQUEST[$campo] = "string_to_array('".implode(",",$valor)."',',')";
				if (substr($campo,-6) == "nombre") { $_REQUEST[$campo] .= "::text[]"; }
				if (substr($campo,-4) == "pond")   { $_REQUEST[$campo] .= "::float[]"; }
			}
		}
	}
	$aCampos = array("niveles","cant_asig_oblig","cant_asig_elect","cant_asig_efp","comentarios",
	                 "requisitos_titulacion","mencion","mencion_alias",
	                 "tns_nombre","tns_sem_req","tns_promgen_pond","tns_actividad_nombre","tns_actividad_pond",
	                 "ga_nombre","ga_sem_req","ga_promgen_pond","ga_actividad_nombre","ga_actividad_pond",
	                 "tp_nombre","tp_sem_req","tp_promgen_pond","tp_actividad_nombre","tp_actividad_pond",
	                 "otros_nombre","otros_sem_req","otros_promgen_pond","otros_actividad_nombre","otros_actividad_pond");
	
	
	$SQLupdate = "UPDATE mallas SET " . str_replace("]'","]",str_replace("'string_","string_",arr2sqlupdate($_REQUEST,$aCampos))) . " WHERE id=$id_malla;";
	if (consulta_dml($SQLupdate) > 0) {
		echo(msje_js("Se han guardado los datos exitosamente"));
	} else {
		echo(msje_js("ERROR: No se han guardado los datos. Revise los cambios que ha intentado realizar."));
	}
}

$SQL_malla = "SELECT vm.*,m.*,c.regimen FROM vista_mallas vm LEFT JOIN mallas m USING(id) LEFT JOIN carreras c ON c.id=m.id_carrera WHERE vm.id=$id_malla;";
$malla = consulta_sql($SQL_malla);

$validar_js = "'niveles','cant_asig_oblig','cant_asig_elect','cant_asig_efp'";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="" method="post" onSubmit="return enblanco2(<?php echo($validar_js); ?>);">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_malla" value="<?php echo($id_malla); ?>">
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>: <?php echo($malla[0]['carrera']); ?> - <?php echo($malla[0]['ano']); ?>
</div>
<table class="tabla" style='margin-top: 5px'>
  <tr>
    <td>
      <input type="submit" name="guardar" value="Guardar" onClick="return confirmar_guardar();">
      <input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();">
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Antecedentes de la Malla</td></tr>
  <tr>
    <td class="celdaNombreAttr">Id:</td>
    <td class="celdaValorAttr"><?php echo($malla[0]['id']); ?></td>
    <td class="celdaNombreAttr">Año:</td>
    <td class="celdaValorAttr"><?php echo($malla[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($malla[0]['carrera']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Mención:</td>
    <td class="celdaValorAttr" colspan='3'><input type="text" name="mencion" value="<?php echo($malla[0]['mencion']); ?>" size="30" class='boton' onChange="document.forms['formulario']['mencion_alias'].required=(this.value!='');"></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Alias mención:</td>
    <td class="celdaValorAttr"><input type="text" name="mencion_alias" value="<?php echo($malla[0]['mencion_alias']); ?>" size="5" maxlength="5" class='boton' onChange="document.forms['formulario']['mencion'].required=(this.value!='');"></td>
    <td class="celdaNombreAttr">Niveles (semestres):</td>
    <td class="celdaValorAttr">
      <input type="text" name="niveles" value="<?php echo($malla[0]['niveles']); ?>" size="2" maxlength="2" class='boton' required>
    </td>
  </tr>
  <?php echo(tabla_editar_malla_otorga_titulos_grados($malla[0])); ?>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Cantidad de Asignaturas</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" style='text-align: center'>
      <b>Obligatorias:</b> <input type="text" name="cant_asig_oblig" value="<?php echo($malla[0]['cant_asig_oblig']); ?>" size="2" maxlength="2" class='boton' required>
      <b><a title='Electivas de Formación General'>E.F.G.</a>:</b> <input type="text" name="cant_asig_elect" value="<?php echo($malla[0]['cant_asig_elect']); ?>" size="2" maxlength="2" class='boton' required>
      <b><a title='Electivas de Formación Profesional'>E.F.P.</a>:</b> <input type="text" name="cant_asig_efp" value="<?php echo($malla[0]['cant_asig_efp']); ?>" size="2" maxlength="2" class='boton' required>
    </td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Comentarios</td></tr>
  <tr><td class="celdaValorAttr" colspan='4'><textarea class='general' rows='5' cols='50' name='comentarios'><?php echo($malla[0]['comentarios']); ?></textarea></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Requsitos de Titulación y/o Graduación</td></tr>
  <tr><td class="celdaValorAttr" colspan='4'><textarea  class='general' rows='5' cols='50' name='requisitos_titulacion' required><?php echo($malla[0]['requisitos_titulacion']); ?></textarea></td></tr>
  
</table>
<br>
<!-- Fin: <?php echo($modulo); ?> -->

<?php

function tabla_editar_malla_otorga_titulos_grados($malla) {
	setlocale(LC_ALL,"C");
	setlocale(LC_NUMERIC,"C");
	extract($malla);
	$NIVELES = array();
	for ($nivel=1;$nivel<=$niveles;$nivel++) { $NIVELES[] = array("id" => $nivel, "nombre" => $nivel); }
	$PORC_POND = array();
	for ($porc_pond=2.5;$porc_pond<=100;$porc_pond+=2.5) { $PORC_POND[] = array("id" => $porc_pond/100, "nombre" => "$porc_pond%"); }
	
	$HTML = "";
	if ($regimen == "PRE") { // Regimen Pregrado solamente presencial o a distancia, otorgan títulos técnicos de nivel superior
		$actividades = array();
		if (!is_null($tns_actividad_nombre) && !is_null($tns_actividad_pond)) {
			$tns_actividad_nombre = explode(",",str_replace("\"","",substr($tns_actividad_nombre,1,-1))); 
			$tns_actividad_pond   = explode(",",substr($tns_actividad_pond,1,-1)); 
			if (count($tns_actividad_nombre) == count($tns_actividad_pond)) {
				for ($x=0;$x<count($tns_actividad_nombre);$x++) { 
					$actividades[$x]['nombre'] = $tns_actividad_nombre[$x];
					$actividades[$x]['pond']   = $tns_actividad_pond[$x];
				}
			}
		}
		
		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_tns AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_tns > 0");
		$HTML_ramos = "";
		for ($x=0;$x<count($ramos_ponderados);$x++) {
			$msje = "ERROR: No es modificable.\\n\\n"
			      . "Para cambiar esta ponderación debe entrar al Plan de Estudios de esta malla y pinchar en la asignatura {$ramos_ponderados[$x]['nombre']}";
			$HTML_ramos .= "      <tr>"
			            .  "        <td class='celdaValorAttr'><small>Asignatura {$ramos_ponderados[$x]['nombre']}:</small></td>"
			            .  "        <td class='celdaValorAttr' style='text-align: center'><select name='tns_ramo$x"."_pond' class='filtro' onClick=\"alert('$msje');this.value={$ramos_ponderados[$x]['pond']};\">".select($PORC_POND,$ramos_ponderados[$x]['pond'])."</select></td>"
			            .  "      </tr>";
		}

		if (is_null($tns_promgen_pond) || $tns_promgen_pond == 0) { $tns_promgen_pond = 1; }
		
		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Técnico de Nivel Superior (TNS):</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>"
		      .  "    <input type='text' name='tns_nombre' value='$tns_nombre' class='boton' size='40' placeholder='Nombre del Título Técnico de Nivel Superior'> "
		      .  "    <b><i>aprobando</i></b> <select class='filtro' name='tns_sem_req'><option value=''>N/A</option>".select($NIVELES,$tns_sem_req)."</select> semestre(s) <br><br>"
		      .  "    <table class='tabla' cellspacing='0' cellpadding='2' align='center'>"
	          .  "      <tr class='filaTituloTabla'><td class='tituloTabla'></td><td class='tituloTabla' style='text-align: center'><small>Ponderaciones</small></td></tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small>Promedio General:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tns_promgen_pond' onChange=\"calc_tot_pond('tns');\">".select($PORC_POND,$tns_promgen_pond)."</select></td>"
	          .  "      </tr>"
	          .  $HTML_ramos
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='tns_actividad_nombre[1]' value='{$tns_actividad_nombre[0]}' size='30' class='boton' placeholder='Nombre de la actividad 1 de Titulación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tns_actividad_pond[1]' onChange=\"calc_tot_pond('tns');document.forms['formulario']['tns_actividad_nombre[1]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$tns_actividad_pond[0])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='tns_actividad_nombre[2]' value='{$tns_actividad_nombre[1]}' size='30' class='boton' placeholder='Nombre de la actividad 2 de Titulación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tns_actividad_pond[2]' onChange=\"calc_tot_pond('tns');document.forms['formulario']['tns_actividad_nombre[2]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$tns_actividad_pond[1])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='tns_actividad_nombre[3]' value='{$tns_actividad_nombre[2]}' size='30' class='boton' placeholder='Nombre de la actividad 3 de Titulación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tns_actividad_pond[3]' onChange=\"calc_tot_pond('tns');document.forms['formulario']['tns_actividad_nombre[3]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$tns_actividad_pond[2])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaNombreAttr'>Total:</td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><input name='pond_tot_tns' size='1'></td>"
	          .  "      </tr>"
	          .  "    </table>"
		      .  "  </td>"
		      .  "</tr>";
	}

	if ($regimen == "PRE" || $regimen == "POST-G" ||  $regimen == "POST-GD") { // Regimenes Pregrado y Postgrado presenciales o a distancia, que otorgan grados académicos
		$actividades = array();
		if (!is_null($ga_actividad_nombre) && !is_null($ga_actividad_pond)) {
			$ga_actividad_nombre = explode(",",str_replace("\"","",substr($ga_actividad_nombre,1,-1))); 
			$ga_actividad_pond   = explode(",",substr($ga_actividad_pond,1,-1)); 
			if (count($ga_actividad_nombre) == count($ga_actividad_pond)) {
				for ($x=0;$x<count($ga_actividad_nombre);$x++) { 
					$actividades[$x]['nombre'] = $ga_actividad_nombre[$x];
					$actividades[$x]['pond']   = $ga_actividad_pond[$x];
				}
			}
		}

		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_ga AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_ga > 0");
		$HTML_ramos = "";
		for ($x=0;$x<count($ramos_ponderados);$x++) {
			$msje = "ERROR: No es modificable.\\n\\n"
			      . "Para cambiar esta ponderación debe entrar al Plan de Estudios de esta malla y pinchar en la asignatura {$ramos_ponderados[$x]['nombre']}";
			$HTML_ramos .= "      <tr>"
			            .  "        <td class='celdaValorAttr'><small>Asignatura {$ramos_ponderados[$x]['nombre']}:</small></td>"
			            .  "        <td class='celdaValorAttr' style='text-align: center'><select name='ga_ramo$x"."_pond' class='filtro' onClick=\"alert('$msje');this.value={$ramos_ponderados[$x]['pond']};\">".select($PORC_POND,$ramos_ponderados[$x]['pond'])."</select></td>"
			            .  "      </tr>";
		}

		if (is_null($ga_promgen_pond) || $ga_promgen_pond == 0) { $ga_promgen_pond = 100; }
		
		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Grado Académico (GA):</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>"
		      .  "    <input type='text' name='ga_nombre' value='$ga_nombre' class='boton' size='40' placeholder='Nombre del Grado Académico'> "
		      .  "    <b><i>aprobando</i></b> <select class='filtro' name='ga_sem_req'><option value=''>N/A</option>".select($NIVELES,$ga_sem_req)."</select> semestre(s) <br><br>"
		      .  "    <table class='tabla' cellspacing='0' cellpadding='2' align='center'>"
	          .  "      <tr class='filaTituloTabla'><td class='tituloTabla'></td><td class='tituloTabla' style='text-align: center'><small>Ponderaciones</small></td></tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small>Promedio General:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='ga_promgen_pond' onChange=\"calc_tot_pond('ga');\">".select($PORC_POND,$ga_promgen_pond)."</select></td>"
	          .  "      </tr>"
	          .  $HTML_ramos
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='ga_actividad_nombre[1]' value='{$ga_actividad_nombre[0]}' size='30' class='boton' placeholder='Nombre de la actividad 1 de Graduación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='ga_actividad_pond[1]' onChange=\"calc_tot_pond('ga');document.forms['formulario']['ga_actividad_nombre[1]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$ga_actividad_pond[0])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='ga_actividad_nombre[2]' value='{$ga_actividad_nombre[1]}' size='30' class='boton' placeholder='Nombre de la actividad 2 de Graduación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='ga_actividad_pond[2]' onChange=\"calc_tot_pond('ga');document.forms['formulario']['ga_actividad_nombre[2]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$ga_actividad_pond[1])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='ga_actividad_nombre[3]' value='{$ga_actividad_nombre[2]}' size='30' class='boton' placeholder='Nombre de la actividad 3 de Graduación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='ga_actividad_pond[3]' onChange=\"calc_tot_pond('ga');document.forms['formulario']['ga_actividad_nombre[3]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$ga_actividad_pond[2])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaNombreAttr'>Total:</td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><input name='pond_tot_ga' size='1'></td>"
	          .  "      </tr>"
	          .  "    </table>"
		      .  "  </td>"
		      .  "</tr>";
	}

	if ($regimen == "PRE" || $regimen == "POST-T" ||  $regimen == "POST-TD") { // Regimenes Pregrado y Postítulos presenciales o a distancia, que otorgan títulos profesionales
		$actividades = array();
		if (!is_null($tp_actividad_nombre) && !is_null($tp_actividad_pond)) {
			$tp_actividad_nombre = explode(",",str_replace("\"","",substr($tp_actividad_nombre,1,-1))); 
			$tp_actividad_pond   = explode(",",substr($tp_actividad_pond,1,-1)); 
			if (count($tp_actividad_nombre) == count($tp_actividad_pond)) {
				for ($x=0;$x<count($tp_actividad_nombre);$x++) {
					$actividades[$x]['nombre'] = $tp_actividad_nombre[$x];
					$actividades[$x]['pond']   = $tp_actividad_pond[$x];
				}
			}
		}		
		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_tp AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_tp > 0");
		$HTML_ramos = "";
		for ($x=0;$x<count($ramos_ponderados);$x++) {
			$msje = "ERROR: No es modificable.\\n\\n"
			      . "Para cambiar esta ponderación debe entrar al Plan de Estudios de esta malla y pinchar en la asignatura {$ramos_ponderados[$x]['nombre']}";
			$HTML_ramos .= "      <tr>"
			            .  "        <td class='celdaValorAttr'><small>Asignatura {$ramos_ponderados[$x]['nombre']}:</small></td>"
			            .  "        <td class='celdaValorAttr' style='text-align: center'><select name='tp_ramo$x"."_pond' class='filtro' onClick=\"alert('$msje');this.value={$ramos_ponderados[$x]['pond']};\">".select($PORC_POND,$ramos_ponderados[$x]['pond'])."</select></td>"
			            .  "      </tr>";
		}
		if (is_null($tp_promgen_pond) || $tp_promgen_pond == 0) { $tp_promgen_pond = 1; }
		
		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Título Profesional (TP):</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>"
		      .  "    <input type='text' name='tp_nombre' value='$tp_nombre' class='boton' size='40' placeholder='Nombre del Título Profesinal'> "
		      .  "    <b><i>aprobando</i></b> <select class='filtro' name='tp_sem_req'><option value=''>N/A</option>".select($NIVELES,$tp_sem_req)."</select> semestre(s) <br><br>"
		      .  "    <table class='tabla' cellspacing='0' cellpadding='2' align='center'>"
	          .  "      <tr class='filaTituloTabla'><td class='tituloTabla'></td><td class='tituloTabla' style='text-align: center'><small>Ponderaciones</small></td></tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small>Promedio General:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tp_promgen_pond' onChange=\"calc_tot_pond('tp');\">".select($PORC_POND,$tp_promgen_pond)."</select></td>"
	          .  "      </tr>"
	          .  $HTML_ramos
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='tp_actividad_nombre[1]' value='{$tp_actividad_nombre[0]}' size='30' class='boton' placeholder='Nombre de la actividad 1 de Titulación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tp_actividad_pond[1]' onChange=\"calc_tot_pond('tp');document.forms['formulario']['tp_actividad_nombre[1]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$tp_actividad_pond[0])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='tp_actividad_nombre[2]' value='{$tp_actividad_nombre[1]}' size='30' class='boton' placeholder='Nombre de la actividad 2 de Titulación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tp_actividad_pond[2]' onChange=\"calc_tot_pond('tp');document.forms['formulario']['tp_actividad_nombre[2]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$tp_actividad_pond[1])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='tp_actividad_nombre[3]' value='{$tp_actividad_nombre[2]}' size='30' class='boton' placeholder='Nombre de la actividad 3 de Titulación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='tp_actividad_pond[3]' onChange=\"calc_tot_pond('tp');document.forms['formulario']['tp_actividad_nombre[3]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$tp_actividad_pond[2])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaNombreAttr'>Total:</td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><input name='pond_tot_tp' size='1'></td>"
	          .  "      </tr>"
	          .  "    </table>"
		      .  "  </td>"
		      .  "</tr>";
	}
	
	if ($regimen <> "") {  // Todos los Regimenes pueden otorgar otras certificaciones
		$actividades = array();
		if (!is_null($otros_actividad_nombre) && !is_null($otros_actividad_pond)) {
			$otros_actividad_nombre = explode(",",str_replace("\"","",substr($otros_actividad_nombre,1,-1))); 
			$otros_actividad_pond   = explode(",",substr($otros_actividad_pond,1,-1)); 
			if (count($otros_actividad_nombre) == count($otros_actividad_pond)) {
				for ($x=0;$x<count($otros_actividad_nombre);$x++) {
					$actividades[$x]['nombre'] = $otros_actividad_nombre[$x];
					$actividades[$x]['pond']   = $otros_actividad_pond[$x]*100;
				}
			}
		}
		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_otros AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_otros > 0");
		$HTML_ramos = "";
		for ($x=0;$x<count($ramos_ponderados);$x++) {
			$msje = "ERROR: No es modificable.\\n\\n"
			      . "Para cambiar esta ponderación debe entrar al Plan de Estudios de esta malla y pinchar en la asignatura {$ramos_ponderados[$x]['nombre']}";
			$HTML_ramos .= "      <tr>"
			            .  "        <td class='celdaValorAttr'><small>Asignatura {$ramos_ponderados[$x]['nombre']}:</small></td>"
			            .  "        <td class='celdaValorAttr' style='text-align: center'><select name='otros_ramo$x"."_pond' class='filtro' onClick=\"alert('$msje');this.value={$ramos_ponderados[$x]['pond']};\">".select($PORC_POND,$ramos_ponderados[$x]['pond'])."</select></td>"
			            .  "      </tr>";
		}

		if (is_null($otros_promgen_pond) || $otros_promgen_pond == 0) { $otros_promgen_pond = 1; }

		
		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Otra Certificación (Otros):</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>"
		      .  "    <input type='text' name='otros_nombre' value='$otros_nombre' class='boton' size='40' placeholder='Nombre de la Certificación'> "
		      .  "    <b><i>aprobando</i></b> <select class='filtro' name='otros_sem_req'><option value=''>N/A</option>".select($NIVELES,$otros_sem_req)."</select> semestre(s) <br><br>"
		      .  "    <table class='tabla' cellspacing='0' cellpadding='2' align='center'>"
	          .  "      <tr class='filaTituloTabla'><td class='tituloTabla'></td><td class='tituloTabla' style='text-align: center'><small>Ponderaciones</small></td></tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small>Promedio General:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='otros_promgen_pond' onChange=\"calc_tot_pond('otros');\">".select($PORC_POND,$otros_promgen_pond)."</select></td>"
	          .  "      </tr>"
	          .  $HTML_ramos
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='otros_actividad_nombre[1]' value='{$otros_actividad_nombre[0]}' size='30' class='boton' placeholder='Nombre de la actividad 1 de Certificación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='otros_actividad_pond[1]' onChange=\"calc_tot_pond('otros');document.forms['formulario']['otros_actividad_nombre[1]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$otros_actividad_pond[0])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='otros_actividad_nombre[2]' value='{$otros_actividad_nombre[1]}' size='30' class='boton' placeholder='Nombre de la actividad 2 de Certificación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='otros_actividad_pond[2]' onChange=\"calc_tot_pond('otros');document.forms['formulario']['otros_actividad_nombre[2]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$otros_actividad_pond[1])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaValorAttr'><small><input name='otros_actividad_nombre[3]' value='{$otros_actividad_nombre[2]}' size='30' class='boton' placeholder='Nombre de la actividad 3 de Certificación'>:</small></td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><select class='filtro' name='otros_actividad_pond[3]' onChange=\"calc_tot_pond('otros');document.forms['formulario']['otros_actividad_nombre[3]'].required=(this.value>0);\"><option value='0'>N/A</option>".select($PORC_POND,$otros_actividad_pond[2])."</select></td>"
	          .  "      </tr>"
	          .  "      <tr>"
	          .  "        <td class='celdaNombreAttr'>Total:</td>"
	          .  "        <td class='celdaValorAttr' style='text-align: center'><input name='pond_tot_otros' size='1'></td>"
	          .  "      </tr>"
	          .  "    </table>"
		      .  "  </td>"
		      .  "</tr>";		      
	}
	if ($HTML <> "") {
		$HTML = "<tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Nombre de Títulos y/o Grados que otorga</td></tr>" . $HTML;
	}
	return $HTML;
}

?>
<script>
	
calc_tot_pond("tns");
calc_tot_pond("ga");
calc_tot_pond("tp");
calc_tot_pond("otros");

function calc_tot_pond(tipo) {

	var form = document.forms["formulario"], i, tot_pond=0;
	
	for (i = 0; i < form.length; i++) {
		if (form.elements[i].name.slice(0,tipo.length)==tipo && (form.elements[i].name.slice(-7,-3)=="pond" || form.elements[i].name.slice(-4)=="pond")) {
			//alert(form.elements[i].name);
			tot_pond += Number(form.elements[i].value);
		}
	}
	
	document.forms["formulario"]["pond_tot_"+tipo].value = Math.round(tot_pond*1000)/10+"%";
	
	if (document.forms["formulario"]["pond_tot_"+tipo].value=='100%') { 
		document.forms["formulario"]["pond_tot_"+tipo].style='background: #90EE90';	
		document.formulario.guardar.disabled=false;
	} else { 
		document.forms["formulario"]["pond_tot_"+tipo].style='background: #FF7474';
		document.formulario.guardar.disabled=true;
		alert("ERROR: La suma de ponderaciones debe ser siempre 100%. No se permitirá guardar los cambios hasta que corrija esto. Se marca en rojo la suma fuera de rango");
	}
}

</script>
