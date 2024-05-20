<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if ($_REQUEST['mod_anterior'] == "") {
	$mod_anterior = array();
	$http_referer = parse_url($_SERVER['HTTP_REFERER']);
	parse_str($http_referer['query'],$mod_anterior);
	$mod_anterior = $mod_anterior['modulo'];
} elseif ($_REQUEST['mod_anterior'] == "prog_cursos_agregar_curso") {
	$mod_anterior = "prog_cursos_ver";
} else {
	$mod_anterior = $_REQUEST['mod_anterior'];
}

if (!is_numeric($_REQUEST['id_pc_det']) || !is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=$mod_anterior';"));
	exit;
} else {
	$id_pc_det     = $_REQUEST['id_pc_det'];
	$id_prog_curso = $_REQUEST['id_prog_curso'];
}

$aCampos = array('id_pa_fusion','seccion','al_proyectado','id_profesor','cupo','tipo','fec_ini','fec_fin',
                 'dia1','horario1','dia2','horario2','dia3','horario3','fec_sol1','fec_sol2','fec_sol_recup',
                 'modalidad','comentarios','horas_semestrales');         
         
if ($_REQUEST['guardar'] == "Guardar") {

	if ($_REQUEST['tipo'] <> "m") {
/*
		if ($_REQUEST['al_proyectado'] <= 6) {
			$_REQUEST['tipo'] = "t";
			$_REQUEST['horas_semestrales'] = $_REQUEST['hrs_semanal'] * 18 * 0.5; 
		} elseif (intval($_REQUEST['al_proyectado']) > 6) {
			$_REQUEST['tipo'] = "r";
			$_REQUEST['horas_semestrales'] = $_REQUEST['hrs_semanal'] * 18; 
		}
*/
    $_REQUEST['horas_semestrales'] = $_REQUEST['hrs_semanal'] * 18; 
		
		if ($_REQUEST['hrs_semanal'] < 4) {
			$_REQUEST['tipo'] = "r";
			$_REQUEST['horas_semestrales'] = $_REQUEST['hrs_semanal'] * 18;
		}
		
	} elseif ($_REQUEST['tipo'] == "m") {
		$_REQUEST['horas_semestrales'] = intval($_REQUEST['hrs_semanal'] * 18 * 0.78);
	}
	
	$SQLupdate_pc_det = "UPDATE prog_cursos_detalle SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_pc_det;";
	//echo($SQLupdate_pc_det);
	if (consulta_dml($SQLupdate_pc_det)>0) {
		echo(msje_js("Se han guardados los datos exitosamente"));
	} else {
		echo(msje_js("ATENCIÓN: Ha ocurrio un error y no se pudo guardar los datos"));
	}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$prog_curso = consulta_sql("SELECT * FROM vista_prog_cursos WHERE id=$id_prog_curso");
if (count($prog_curso) == 0) {
	echo(msje_js("ATENCIÓN: Ha ocurrio un error. No se puede continuar"));
	echo(js("window.location='$enlbase=';"));
	exit;
}
extract($prog_curso[0],EXTR_PREFIX_ALL,"pc");

$SQL_prog_asig = "SELECT dm.id_prog_asig,pa.horas_semanal AS hrs_sem,char_comma_sum(c.alias||m.ano::text) AS mallas
                  FROM detalle_mallas AS dm
                  LEFT JOIN mallas AS m ON m.id=dm.id_malla
                  LEFT JOIN prog_asig AS pa ON pa.id=dm.id_prog_asig
                  LEFT JOIN carreras AS c ON c.id=m.id_carrera
                  WHERE c.id_escuela=$pc_id_escuela
                  GROUP BY id_prog_asig,horas_semanal";
                  
$SQL_pc_det = "SELECT pcd.id,pcd.id_prog_curso,pcd.id_prog_asig,cod_asignatura,asignatura,seccion,al_proyectado,
                      pcd.id_profesor,cupo,tipo,pcd.dia1,pcd.horario1,pcd.dia2,pcd.horario2,pcd.dia3,pcd.horario3,
                      pcd.comentarios,pcd.fec_ini,fec_fin,
                      fec_sol1,fec_sol2,
                      fec_sol_recup,mpa.mallas,pcd.id_pa_fusion,horas_semestrales,modalidad,
                      \"horas semanales\" as hrs_semanal,CASE WHEN vobo_vra THEN 'Sí' ELSE 'No' END AS vobo_vra,
                      CASE WHEN vobo_vraf THEN 'Sí' ELSE 'No' END AS vobo_vraf
               FROM prog_cursos_detalle AS pcd
               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig
               LEFT JOIN vista_profesores AS vp ON vp.id=pcd.id_profesor
               LEFT JOIN ($SQL_prog_asig) AS mpa ON mpa.id_prog_asig=pcd.id_prog_asig
               WHERE pcd.id=$id_pc_det   
               ORDER BY cod_asignatura";
$pc_det = consulta_sql($SQL_pc_det);
if (count($pc_det) == 0) {
	echo(msje_js("ATENCIÓN: Ha ocurrio un error. No se puede continuar"));
	echo(js("window.location='$enlbase=';"));
	exit;
}
extract($pc_det[0]);

if ($seccion==9 && (empty($tipo) || $tipo<>"m")) { $tipo="m"; echo(msje_js("AVISO: La sección del curso es 9 y el tipo no es Modular. Se asigna como Modular al campo tipo. No olvide pinchar en Guardar.")); }

$deshabilitado = "";
if ($tipo == "m") {
	$deshabilitado = "disabled";
} else {
	$rojo = "color: #ff0000";
	$dia1_style = $dia2_style = $dia3_style = $horario1_style  = $horario2_style = $horario3_style = "";
	if ($dia1 == "") { $dia1_style = $rojo; }
	if ($dia2 == "" && $horas_semestrales >= 72) { $dia2_style = $rojo; }
	if ($dia3 == "" && $horas_semestrales > 72) { $dia3_style = $rojo; }
	if ($horario1 == "") { $horario1_style = $rojo; }
	if ($horario2 == "" && $horas_semestrales >= 72) { $horario2_style = $rojo; }
	if ($horario3 == "" && $horas_semestrales > 72) { $horario3_style = $rojo; }
}

//$profesores = consulta_sql("SELECT vp.id,vp.nombre||' - '||categorizacion AS nombre FROM vista_profesores vp LEFT JOIN usuarios u USING (id) ORDER BY vp.nombre");
$profesores = consulta_sql("SELECT id,upper(apellido)||' '||initcap(nombre)||' - '||coalesce(categorizacion,'** Sin categorización **') AS nombre FROM usuarios WHERE tipo=3 ORDER BY nombre");

$TIPOS = array(array("id"=>"m","nombre"=>"Modular"));

$TIPO_CLASES = consulta_sql("SELECT * FROM vista_tipo_clase;");

$horarios = consulta_sql("SELECT id,id||'=> '||intervalo AS nombre FROM vista_horarios ORDER BY id;");

$SQL_prog_asig = "SELECT id,ano||'/'||cod_asignatura||' '||asignatura AS nombre
                  FROM vista_prog_asig
                  WHERE id IN (SELECT id_prog_asig FROM detalle_mallas
                               WHERE id_malla IN (SELECT id FROM mallas 
                                                  WHERE id_carrera IN (SELECT id FROM carreras 
                                                                       WHERE id_escuela = $pc_id_escuela)))
                    AND id<>$id_prog_asig
                  ORDER BY nombre";
$prog_asig     = consulta_sql($SQL_prog_asig);

$tolerancia = 7 * 24 * 60 * 60;

if ($pc_semestre == 1) {
	$f_ini_sem   = date("Y-m-d",$Fec_Ini_Sem1);
	$f_fin_sem   = date("Y-m-d",$Fec_Fin_Sem1);
	$f_ini_sol1  = date("Y-m-d",$f_ini_sol1_sem1 - $tolerancia);
	$f_fin_sol1  = date("Y-m-d",$f_fin_sol1_sem1 + $tolerancia);
	$f_ini_sol2  = date("Y-m-d",$f_ini_sol2_sem1 - $tolerancia);
	$f_fin_sol2  = date("Y-m-d",$f_fin_sol2_sem1 + $tolerancia);
	$f_ini_recup = date("Y-m-d",$f_ini_recup_sem1 - $tolerancia);
	$f_fin_recup = date("Y-m-d",$f_fin_recup_sem1);
}
if ($pc_semestre == 2) {
	$f_ini_sem   = date("Y-m-d",$Fec_Ini_Sem2);
	$f_fin_sem   = date("Y-m-d",$Fec_Fin_Sem2);
	$f_ini_sol1  = date("Y-m-d",$f_ini_sol1_sem2 - $tolerancia);
	$f_fin_sol1  = date("Y-m-d",$f_fin_sol1_sem2 + $tolerancia);
	$f_ini_sol2  = date("Y-m-d",$f_ini_sol2_sem2 - $tolerancia);
	$f_fin_sol2  = date("Y-m-d",$f_fin_sol2_sem2 + $tolerancia);
	$f_ini_recup = date("Y-m-d",$f_ini_recup_sem2 - $tolerancia);
	$f_fin_recup = date("Y-m-d",$f_fin_recup_sem2);
}

if (empty($fec_ini)) { $fec_ini = $f_ini_sem; echo(msje_js("AVISO: Fecha de inicio del curso NO definida.\\n\\n- Se asigna fecha de inicio del semestre $pc_periodo.")); }
if (empty($fec_fin)) { $fec_fin = $f_fin_sem; echo(msje_js("AVISO: Fecha de término del curso NO definida.\\n\\n- Se asigna fecha de término del semestre $pc_periodo.")); }
if (empty($fec_sol1)) { $fec_sol1 = $f_ini_sol1; echo(msje_js("AVISO: Fecha de Prueba Solemne I NO definida.\\n\\n- Se asigna fecha de inicio del periodo de Pruebas Solemnes I del semestre $pc_periodo.")); }
if (empty($fec_sol2)) { $fec_sol2 = $f_ini_sol2; echo(msje_js("AVISO: Fecha de Prueba Solemne II NO definida.\\n\\n- Se asigna fecha de inicio del periodo de Pruebas Solemnes II del semestre $pc_periodo.")); }
if (empty($fec_sol_recup)) { $fec_sol_recup = $f_ini_recup; echo(msje_js("AVISO: Fecha de Prueba Recuperativa NO definida.\\n\\n- Se asigna fecha de inicio del periodo de Pruebas Recuperativas del semestre $pc_periodo.")); }


if ($tipo == "m") {
  $f_fin_sem = date("Y-m-d",strtotime("$f_fin_sem +4 months"));
  $f_ini_sol1 = $f_ini_sol2 = $f_ini_recup = $fec_ini;
  $f_fin_sol1 = $f_fin_sol2 = $f_fin_recup = $fec_fin;
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->

<script>
function tipo_curso(valor) {
	if (valor != 'm') {
		formulario.dia1.disabled = false;
		formulario.dia2.disabled = false;
		formulario.dia3.disabled = false;
		formulario.horario1.disabled = false;
		formulario.horario2.disabled = false;
		formulario.horario3.disabled = false;
	} else {
		formulario.dia1.disabled = true;
		formulario.dia2.disabled = true;
		formulario.dia3.disabled = true;
		formulario.horario1.disabled = true;
		formulario.horario2.disabled = true;
		formulario.horario3.disabled = true;
	}
}
</script>
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo"        value="<?php echo($modulo); ?>">
<input type="hidden" name="id_pc_det"     value="<?php echo($id_pc_det); ?>">
<input type="hidden" name="id_prog_curso" value="<?php echo($id_prog_curso); ?>">
<input type="hidden" name="hrs_semanal"   value="<?php echo($hrs_semanal); ?>">
<input type="hidden" name="mod_anterior"  value="<?php echo($mod_anterior); ?>">
<input type="hidden" name="f_ini_sem"     value="<?php echo($f_ini_sem); ?>">
<input type="hidden" name="f_fin_sem"     value="<?php echo($f_fin_sem); ?>">
<input type="hidden" name="f_ini_sol1"    value="<?php echo($f_ini_sol1); ?>">
<input type="hidden" name="f_fin_sol1"    value="<?php echo($f_fin_sol1); ?>">
<input type="hidden" name="f_ini_sol2"    value="<?php echo($f_ini_sol2); ?>">
<input type="hidden" name="f_fin_sol2"    value="<?php echo($f_fin_sol2); ?>">
<input type="hidden" name="f_ini_recup"   value="<?php echo($f_ini_recup); ?>">
<input type="hidden" name="f_fin_recup"   value="<?php echo($f_fin_recup); ?>">

<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" onClick="history.back();" name="cancelar" value="Cancelar">
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr>
    <td class="celdaNombreAttr" colspan="4" style="text-align:center">Antecendentes de la Programación de Cursos</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr"><?php echo($pc_escuela); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($pc_periodo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($pc_creador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr" colspan="4" style="text-align:center">Antecendentes del Curso propuesto</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Asignatura:</td>
    <td class="celdaValorAttr"><?php echo("$cod_asignatura $asignatura"); ?>
    <td class="celdaNombreAttr">Seccion:</td>
    <td class="celdaValorAttr"><input type="text" class="boton" size="1" name="seccion" value="<?php echo($seccion); ?>" required></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Malla(s):</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($mallas); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Tipo:</td>
    <td class="celdaValorAttr">
      <select name="tipo" onChange="tipo_curso(this.value);">
        <option value="r">Regular</option>
        <?php echo(select($TIPOS,$tipo)); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">Horas:</td>
    <td class="celdaValorAttr"><?php echo($horas_semestrales."/".($hrs_semanal*18)); ?></td>
  </tr>
<!--  <tr>
    <td class="celdaNombreAttr">Fusionar con:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="id_pa_fusion" style="font-weight:normal">
        <option value="">-- Sin curso fusionado --</option>
        <?php //echo(select($prog_asig,$id_pa_fusion)); ?>
      </select>
    </td>
  </tr> -->
  <tr>
    <td class="celdaNombreAttr">Profesor:</td>
    <td class="celdaValorAttr" colspan="3">
      <select id="id_profesor" name="id_profesor" style="font-weight:normal">
        <option value="">-- Sin profesor --</option>
        <?php echo(select($profesores,$id_profesor)); ?>
      </select>
    </td>
  </tr>
<!--  <tr>
    <td class="celdaNombreAttr">Al. Proyectados:</td>
    <td class="celdaValorAttr">
      <input type="text" size="2" name="al_proyectado" value="<?php echo($al_proyectado); ?>">
      <b>Cupo:</b>
      <input type="text" size="2" name="cupo" value="<?php echo($cupo); ?>">
    </td> -->
  </tr>
  <tr>
    <td class="celdaNombreAttr" colspan="4" style="text-align:center">Horarios y Fechas</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">F. Inicio:</td>
    <td class="celdaValorAttr"><input type="date" class="boton" name="fec_ini" min="<?php echo($f_ini_sem); ?>" max="<?php echo($f_fin_sem); ?>" value="<?php echo($fec_ini); ?>" onBlur="calc_fechas();" required></td>
    <td class="celdaNombreAttr">F. Término:</td>
    <td class="celdaValorAttr"><input type="date" class="boton" name="fec_fin" min="<?php echo($f_ini_sem); ?>" max="<?php echo($f_fin_sem); ?>" value="<?php echo($fec_fin); ?>" onBlur="calc_fechas();" required></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Módulo 1:</td>
    <td class="celdaValorAttr">
      <select name="dia1" style="<?php echo($dia1_style); ?>" <?php echo($deshabilitado); ?>>
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia1)); ?>
      </select>
      <select name="horario1" style="<?php echo($horario1_style); ?>" <?php echo($deshabilitado); ?>>
        <option value="">-- Módulo --</option>
        <?php echo(select($horarios,$horario1)); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">F. Prueba Solemne I:</td>
    <td class="celdaValorAttr"><input type="date" class="boton" name="fec_sol1" min="<?php echo($f_ini_sol1); ?>" max="<?php echo($f_fin_sol1); ?>" value="<?php echo($fec_sol1); ?>" required></td>
  </tr>
  <tr>
  <td class="celdaNombreAttr">Módulo 2:</td>
    <td class="celdaValorAttr">
      <select name="dia2" style="<?php echo($dia2_style); ?>" <?php echo($deshabilitado); ?>>
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia2)); ?>
      </select>
      <select name="horario2" style="<?php echo($horario2_style); ?>"<?php echo($deshabilitado); ?>>
        <option value="">-- Módulo --</option>
        <?php echo(select($horarios,$horario2)); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">F. Prueba Solemne II:</td>
    <td class="celdaValorAttr"><input type="date" class="boton" name="fec_sol2" min="<?php echo($f_ini_sol2); ?>" max="<?php echo($f_fin_sol2); ?>" value="<?php echo($fec_sol2); ?>" required></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Módulo 3:</td>
    <td class="celdaValorAttr">
      <select name="dia3" style="<?php echo($dia3_style); ?>" <?php echo($deshabilitado); ?>>
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia3)); ?>
      </select>
      <select name="horario3" style="<?php echo($horario3_style); ?>" <?php echo($deshabilitado); ?>>
        <option value="">-- Módulo --</option>
        <?php echo(select($horarios,$horario3)); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">F. Prueba Recuperativa:</td>
    <td class="celdaValorAttr"><input type="date" class="boton" name="fec_sol_recup" min="<?php echo($f_ini_recup); ?>" max="<?php echo($f_fin_recup); ?>" value="<?php echo($fec_sol_recup); ?>" required></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Visado VRA:</td>
    <td class="celdaValorAttr"><?php echo($vobo_vra); ?></td>
    <td class="celdaNombreAttr">Modalidad:</td>
    <td class="celdaValorAttr">
      <input type="hidden" name="modalidad" value="<?php echo($modalidad); ?>">
      <select name="modalidad" class='filtro' style="max-width: 500px" required>
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPO_CLASES,$modalidad)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Comentarios:</td>
    <td class="celdaValorAttr" colspan="3"><textarea name="comentarios"><?php echo($comentarios); ?></textarea></td>
  </tr>
</table><br>

</form>

<!-- Fin: <?php echo($modulo); ?> -->

<?php
function arr2sqlupd($aTabla) {
	$arr2sqlupd = "";
	$aCampos = array_keys($aTabla);
	for($x=1;$x<count($aCampos);$x++) {
		if ($aTabla[$aCampos[$x]] == "") {
			$aTabla[$aCampos[$x]] = "null";
		} else {
			$aTabla[$aCampos[$x]] = "'{$aTabla[$aCampos[$x]]}'";
		}
		$arr2sqlupd .= "{$aCampos[$x]}={$aTabla[$aCampos[$x]]},";
	}
	return substr($arr2sqlupd,0,-1);
}
?>

<script>
  $(document).ready(function () {
      $('#id_profesor').selectize({
          sortField: 'text'
      });
  });

function calc_fechas() {
  var tipo    = formulario.tipo.value,
      fec_ini = formulario.fec_ini.value,
      fec_fin = formulario.fec_fin.value;
  
  if (tipo == "m") {
    formulario.fec_sol1.min      = fec_ini;
    formulario.fec_sol2.min      = fec_ini;
    formulario.fec_sol_recup.min = fec_ini;
    formulario.fec_sol1.max      = fec_fin;
    formulario.fec_sol2.max      = fec_fin;
    formulario.fec_sol_recup.max = fec_fin;    
  }
}
</script>