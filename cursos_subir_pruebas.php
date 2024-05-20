<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
if ($id_curso == "") {
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
}

if ($_REQUEST['guardar'] == "Subir") {
	
	$pruebas = count(consulta_sql("SELECT id_curso FROM cursos_pruebas WHERE id_curso=$id_curso"));
	
	$arch_s1_tmp_nombre = $_FILES['arch_sol1']['tmp_name'];
	$arch_s1_tipo_mime  = $_FILES['arch_sol1']['type'];
	$arch_s1_longitud   = $_FILES['arch_sol1']['size'];
	$arch_s2_tmp_nombre = $_FILES['arch_sol2']['tmp_name'];
	$arch_s2_tipo_mime  = $_FILES['arch_sol2']['type'];
	$arch_s2_longitud   = $_FILES['arch_sol2']['size'];
	$arch_rec_tmp_nombre = $_FILES['arch_sol_recup']['tmp_name'];
	$arch_rec_tipo_mime  = $_FILES['arch_sol_recup']['type'];
	$arch_rec_longitud   = $_FILES['arch_sol_recup']['size'];

	if (($arch_s1_longitud > 0 && ($arch_s1_longitud > 2097152 || $arch_s1_tipo_mime <> "application/pdf")) ||
	    ($arch_s2_longitud > 0 && ($arch_s2_longitud > 2097152 || $arch_s2_tipo_mime <> "application/pdf")) ||
	    ($arch_rec_longitud > 0 && ($arch_rec_longitud > 2097152 || $arch_rec_tipo_mime <> "application/pdf"))) {
		echo(msje_js("ATENCIÓN: El o los archivo(s) que intenta subir tiene(n) un tamaño que sobrepasa los 2MB "
		            ."o bien no está(n) en formato PDF .\\n"
		            ."Lo sentimos, pero no están permitidos archivos sobre este tamaño u otro formato. "));
	} else {
		if ($arch_s1_longitud > 0 || $arch_s2_longitud > 0 || $arch_rec_longitud > 0) {
			$arch_s1_data = pg_escape_bytea(file_get_contents($arch_s1_tmp_nombre));
			$arch_s2_data = pg_escape_bytea(file_get_contents($arch_s2_tmp_nombre));
			$arch_rec_data = pg_escape_bytea(file_get_contents($arch_rec_tmp_nombre));
			if ($pruebas > 0) {
				$SQL = "";
				if ($arch_s1_longitud > 0)  { $SQL .= "s1_arch = '{$arch_s1_data}',s1_fec=now(),s1_id_usuario={$_SESSION['id_usuario']},"; }
				if ($arch_s2_longitud > 0)  { $SQL .= "s2_arch = '{$arch_s2_data}',s2_fec=now(),s2_id_usuario={$_SESSION['id_usuario']},"; }
				if ($arch_rec_longitud > 0) { $SQL .= "rec_arch = '{$arch_s2_data}',rec_fec=now(),rec_id_usuario={$_SESSION['id_usuario']}"; }
				if (substr($SQL,-1) == ",") { $SQL = substr($SQL,0,-1); }

				$SQL_subir_pruebas = "UPDATE cursos_pruebas SET $SQL WHERE id_curso=$id_curso";
			} else {
				$SQL_campos = $SQL_valores = "";
				if ($arch_s1_longitud > 0)  { $SQL_campos .= "s1_arch,s1_fec,s1_id_usuario,"; $SQL_valores .= "'{$arch_s1_data}',now(),{$_SESSION['id_usuario']},"; }
				if ($arch_s2_longitud > 0)  { $SQL_campos .= "s2_arch,s2_fec,s2_id_usuario,"; $SQL_valores .= "'{$arch_s2_data}',now(),{$_SESSION['id_usuario']},"; }
				if ($arch_rec_longitud > 0) { $SQL_campos .= "rec_arch,rec_fec,rec_id_usuario"; $SQL_valores .= "'{$arch_rec_data}',now(),{$_SESSION['id_usuario']}"; }
				
				if (substr($SQL_campos,-1) == ",") { $SQL_campos = substr($SQL_campos,0,-1); }
				if (substr($SQL_valores,-1) == ",") { $SQL_valores = substr($SQL_valores,0,-1); }
				
				$SQL_subir_pruebas = "INSERT INTO cursos_pruebas ($SQL_campos,id_curso) VALUES ($SQL_valores,$id_curso)";
			}
			if (consulta_dml($SQL_subir_pruebas) > 0) {
				echo(msje_js("Se ha recibido y guardado satisfactoriamente el o los documentos subidos."));
			}
		}
	}
}
			
$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre,
                     vc.ano,vc.profesor,vc.id_profesor,vc.carrera,cant_alumnos_asist(vc.id),
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,c.cupo,c.cant_notas_parciales,
                     md5(vc.id::text||vc.id_profesor::text) AS cod,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo,
                     coalesce(to_char(c.fec_ini,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_ini,coalesce(to_char(c.fec_fin,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_fin,
                     coalesce(to_char(c.fec_sol1,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol1,coalesce(to_char(c.fec_sol2,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol2,
                     coalesce(to_char(c.fec_sol_recup,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol_recup
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c USING(id)
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {
	extract($curso[0]);
	if ($curso[0]['cant_notas_parciales'] == "") {
		echo(msje_js("Estimado Profesor(a), actualmente no se encuentra definido el número de calificaciones parciales "
		             ."que aplicará para este curso en este semestre. Pinche en el botón Aceptar, para que SGU le permita "
		             ."definir este parámetro."));
		echo(js("location.href='principal.php?modulo=calificaciones_def_cant_notas_parciales&id_curso=$id_curso';"));
		exit;
	}

	$SQL_cursos_fusion = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,c.id_prog_asig 
	                      FROM vista_cursos AS vc
	                      LEFT JOIN cursos AS c USING (id)
	                      WHERE id_fusion = $id_curso";
	$cursos_fusion     = consulta_sql($SQL_cursos_fusion);
	$HTML_fusionadas = "";
	$ids_cursos = $ids_pa = "";
	for ($x=0;$x<count($cursos_fusion);$x++) {
		$HTML_fusionadas .= "<small><br>&nbsp;<big><b>↳</b></big>{$cursos_fusion[$x]['asignatura']}</small>";
		$ids_cursos      .= "{$cursos_fusion[$x]['id']},";
		$ids_pa          .= "{$cursos_fusion[$x]['id_prog_asig']},";
	}
	
	$SQL_pruebas = "SELECT length(s1_arch) AS s1_arch,to_char(s1_fec,'tmDay DD-tmMon-YYYY HH24:MI') AS s1_fec,
	                       length(s2_arch) AS s2_arch,to_char(s2_fec,'tmDay DD-tmMon-YYYY HH24:MI') AS s2_fec,
	                       length(rec_arch) AS rec_arch,to_char(rec_fec,'tmDay DD-tmMon-YYYY HH24:MI') AS rec_fec 
	                FROM cursos_pruebas 
	                WHERE id_curso=$id_curso";
	$pruebas = consulta_sql($SQL_pruebas);
	
	$enl_s1 = $enl_s2 = $enl_rec = "";
	if (count($pruebas) > 0) {
		if ($pruebas[0]['s1_arch'] > 0) { 
			$enl_s1 = "<a class='enlaces' target='_blank' href='ver_prueba_solemne.php?id_curso=$id_curso&prueba=s1&token=$cod'>Ver documento <small>(subido el {$pruebas[0]['s1_fec']})</small></a><br>";
		}
		if ($pruebas[0]['s2_arch'] > 0) { 
			$enl_s2 = "<a class='enlaces' target='_blank' href='ver_prueba_solemne.php?id_curso=$id_curso&prueba=s2&token=$cod'>Ver documento <small>(subido el {$pruebas[0]['s2_fec']})</small></a><br>";
		}
		if ($pruebas[0]['rec_arch'] > 0) { 
			$enl_rec = "<a class='enlaces' target='_blank' href='ver_prueba_solemne.php?id_curso=$id_curso&prueba=rec&token=$cod'>Ver documento <small>(subido el {$pruebas[0]['rec_fec']})</small></a><br>";
		}
	}
}

$fec_ini_fin = " <b>F. Inicio:</b> $fec_ini <b>F. Término:</b> $fec_fin";
	
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo"   value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Subir">
  <input type="button" onClick="parent.jQuery.fancybox.close();" value="Cerrar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($semestre."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura . " " . $prog_asig . $HTML_fusionadas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?> <?php echo($ficha_prof); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Solemne I:</td>
    <td class='celdaValorAttr'><?php echo($fec_sol1); ?></td>
    <td class='celdaNombreAttr'><u>Prueba:</u></td>
    <td class='celdaValorAttr'>
      <?php echo($enl_s1); ?>
      <input type="file" name="arch_sol1" 
         onChange="if (this.files[0].size > 2097152 || this.files[0].type != 'application/pdf') { alert('El documento tiene un tamaño superior a 2MB o bien no está en formato PDF.\n\n No es posible subir este archivo como Prueba Solemne I'); this.value = ''; }">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Solemne II:</td>
    <td class='celdaValorAttr'><?php echo($fec_sol2); ?></td>
    <td class='celdaNombreAttr'><u>Prueba:</u></td>
    <td class='celdaValorAttr'>
      <?php echo($enl_s2); ?>
      <input type="file" name="arch_sol2"
         onChange="if (this.files[0].size > 2097152 || this.files[0].type != 'application/pdf') { alert('El documento tiene un tamaño superior a 2MB o bien no está en formato PDF.\n\n No es posible subir este archivo como Prueba Solemne II'); this.value = ''; }">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Solemne Recuperativa:</td>
    <td class='celdaValorAttr'><?php echo($fec_sol_recup); ?></td>
    <td class='celdaNombreAttr'><u>Prueba:</u></td>
    <td class='celdaValorAttr'>
      <?php echo($enl_rec); ?>
      <input type="file" name="arch_sol_recup"
         onChange="if (this.files[0].size > 2097152 || this.files[0].type != 'application/pdf') { alert('El documento tiene un tamaño superior a 2MB o bien no está en formato PDF.\n\n No es posible subir este archivo como Prueba Solemne Recuperativa'); this.value = ''; }">
    </td>
  </tr>
</table>
</form>
<div class="texto" style='margin-top: 5px'>
  <b>ATENCIÓN:</b> El formato que debe usarse es el PDF, con un límite de 2MB por archivo o prueba. El límite de tamaño 
  equivalente a varias páginas de sólo texto o hasta unas 10 páginas mezclando textos con imágenes o gráficos.<br>
  Si sube nuevamente una prueba, la anterior será eliminada.
</div>
<!-- Fin: <?php echo($modulo); ?> -->
