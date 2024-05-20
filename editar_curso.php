<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso     = $_REQUEST['id_curso'];
$dia1         = $_REQUEST['dia1'];
$horario1     = $_REQUEST['horario1'];
$dia2         = $_REQUEST['dia2'];
$horario2     = $_REQUEST['horario2'];
$dia3         = $_REQUEST['dia3'];
$horario3     = $_REQUEST['horario3'];
$sala1        = $_REQUEST['sala1'];
$sala2        = $_REQUEST['sala2'];
$sala3        = $_REQUEST['sala3'];

$deshabilitado = "disabled";
if ($_SESSION['tipo'] == 0) { $deshabilitado = ""; }

if ($_REQUEST['guardar'] <> "") {
	if ( !empty($dia1) || !empty($horario1) ) { $sesion1 = $dia1 . $horario1; } else { $sesion1 = NULL; }
	if ( !empty($dia2) || !empty($horario2) ) { $sesion2 = $dia2 . $horario2; } else { $sesion2 = NULL; }
	if ( !empty($dia3) || !empty($horario3) ) { $sesion3 = $dia3 . $horario3; } else { $sesion3 = NULL; }
	if ( ($sesion1 == $sesion2 && (!is_null($sesion1) && !is_null($sesion2))) ||	
	     ($sesion1 == $sesion3 && (!is_null($sesion1) && !is_null($sesion3))) ||
	     ($sesion2 == $sesion3 && (!is_null($sesion2) && !is_null($sesion3)))
	   ) {
		$mensaje_error = "Se han definido 2 o 3 horarios en el mismo día o módulo.\\n"
		               . "Por favor corrija el(los) horarios repetidos.";
		echo(msje_js($mensaje_error));		
	} else {
		$cond_salas = "";
		if (!empty($dia1) && !empty($horario1) && !empty($sala1)) { $cond_salas .= "AND dia1=$dia1 AND horario1='$horario1' AND sala1='$sala1' "; }
		if (!empty($dia2) && !empty($horario2) && !empty($sala2)) { $cond_salas .= "AND dia2=$dia2 AND horario2='$horario2' AND sala2='$sala2' "; }
		if (!empty($dia3) && !empty($horario3) && !empty($sala1)) { $cond_salas .= "AND dia3=$dia3 AND horario3='$horario3' AND sala3='$sala3' "; }
		if (empty($cond_salas)) { $cond_salas = "AND false"; }
		$comp_sala = consulta_sql("SELECT cod_asignatura||'-'||seccion||' '||asignatura AS asignatura FROM vista_cursos WHERE id<>$id_curso AND semestre=$SEMESTRE AND ano=$ANO $cond_salas");
		if (count($comp_sala) > 0) {
			echo(msje_js("ATENCIÓN: Existe un conflicto en la asignación de sala. Actualmente la sala está ocupada porel curso {$comp_sala[0]['asignatura']}.\\n"
			            ."Debe corregir este problema a la brevedad para no generar confusión en alumnos y profesores. "
			            ."El SGU le permitirá seguir y guardará la información tal cual la ha ingresado."));
		}
		$aCampos = array("cupo","cerrado","id_profesor","ayudantia","tipo_clase","dia1","horario1","dia2","horario2","dia3","horario3","sala1","sala2","sala3",
		                 "fec_ini","fec_fin",
                     "fec_sol1","sol1_horario1","sol1_horario2","sol1_sala1","sol1_sala2",
                     "fec_sol2","sol2_horario1","sol2_horario2","sol2_sala1","sol2_sala2",
                     "fec_sol_recup","recup_horario1","recup_horario2","recup_sala1","recup_sala2");
		$SQLupd_curso = "UPDATE cursos SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_curso OR id_fusion=$id_curso";
		if (consulta_dml($SQLupd_curso) > 0) {
			echo(msje_js("Se han guardado los datos"));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
	}
}

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre as sem_num,
                     CASE vc.semestre
                          WHEN 0 THEN 'Verano'
                          WHEN 1 THEN 'Primero'
                          WHEN 2 THEN 'Segundo'
                     END AS sem,vc.semestre,vc.ano,vc.profesor,vc.carrera,vc.id_profesor,
                     ayudantia,
                     c.dia1,c.horario1,c.dia2,c.horario2,c.dia3,c.horario3,vc.sala1,vc.sala2,vc.sala3,                     
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,cant_alumnos_asist(vc.id) AS cant_alumnos_asist,
                     pa.ano AS ano_prog_asig,m.ano AS ano_malla,c.cerrado,to_char(fecha_acta,'DD-MM-YYYY') AS fecha_acta,
                     to_char(fecha_acta_comp,'DD-MM-YYYY') AS fecha_acta_comp,vu.nombre AS usuario_emisor,recep_acta,recep_acta_comp,c.cerrado,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo,
                     c.fec_ini,c.fec_fin,
                     c.fec_sol1,c.sol1_horario1,c.sol1_horario2,c.sol1_sala1::text,c.sol1_sala2::text,
                     c.fec_sol2,c.sol2_horario1,c.sol2_horario2,c.sol2_sala1::text,c.sol2_sala2::text,
                     c.fec_sol_recup,c.recup_horario1,c.recup_horario2,c.recup_sala1::text,c.recup_sala2::text,
                     c.tipo_clase
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c USING (id)
              LEFT JOIN prog_asig AS pa ON pa.id=vc.id_prog_asig
              LEFT JOIN detalle_mallas AS dm ON dm.id_prog_asig=vc.id_prog_asig
              LEFT JOIN mallas AS m ON m.id=dm.id_malla
              LEFT JOIN vista_usuarios AS vu ON vu.id=id_usuario_emisor_acta
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);
extract($curso[0]);

if (count($curso) > 0) {
	$SQL_cursos_fusion = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,c.id_prog_asig 
	                      FROM vista_cursos AS vc
	                      LEFT JOIN cursos AS c USING (id)
	                      WHERE id_fusion = $id_curso";
	$cursos_fusion     = consulta_sql($SQL_cursos_fusion);
	$HTML_fusionadas = "";
	$ids_cursos = $ids_pa = "";
	for ($x=0;$x<count($cursos_fusion);$x++) {
		$HTML_fusionadas .= "<br>&nbsp;&nbsp;Fusionada con: {$cursos_fusion[$x]['asignatura']}";
		$ids_cursos      .= "{$cursos_fusion[$x]['id']},";
		$ids_pa          .= "{$cursos_fusion[$x]['id_prog_asig']},";
	}
	
	$ids_cursos .= $id_curso;
	$ids_pa     .= $id_prog_asig;
	
	$SQL_mallas = "SELECT char_comma_sum(alias_carrera||ano::text) AS anos FROM vista_mallas WHERE id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig IN ($ids_pa))";
	$mallas = consulta_sql($SQL_mallas);
	$mallas = $mallas[0]['anos'];
}

$profesores = consulta_sql("SELECT id,upper(apellido)||' '||initcap(nombre) AS nombre FROM usuarios WHERE tipo=3 AND activo ORDER BY nombre");

$horarios = consulta_sql("SELECT id,id||'=> '||intervalo AS nombre FROM vista_horarios ORDER BY id;");

$TIPO_CLASES = consulta_sql("SELECT * FROM vista_tipo_clase;");

$salas = consulta_sql("SELECT trim(codigo) AS id,nombre||' ('||coalesce(capacidad,0)||' sillas)' AS nombre,'Piso '||piso||'°' AS grupo FROM salas WHERE activa ORDER BY piso,nombre;");

$est_cursos = array(array('id'=>"f",'nombre'=>"Abierto"),
                    array('id'=>"t",'nombre'=>"Cerrado"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<script src="js/Kalendae/kalendae.standalone.js" type="text/javascript" charset="utf-8"></script>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" name="cancelar" value="Cancelar" onClick="window.location='<?php echo("$enlbase=ver_curso&id_curso=$id_curso"); ?>';">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class="celdaNombreAttr" colspan="4" style="text-align: center;">Antecedentes del Curso</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($sem_num."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura . " " . $prog_asig . $HTML_fusionadas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Mallas:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($mallas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Profesor:</td>
    <td class="celdaValorAttr" colspan="3">
      <input type="hidden" name="id_profesor" value="<?php echo($id_profesor); ?>">
      <select name="id_profesor" class='filtro' style="max-width: 500px" <?php echo($deshabilitado); ?>>
        <option value="">-- Seleccione --</option>
        <?php echo(select($profesores,$id_profesor)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> estudiante(s)</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> estudiante(s)</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cupo:</td>
    <td class='celdaValorAttr'><input type="text" size="1" class='boton' name="cupo" value="<?php echo($cupo); ?>"> estudiante(s)</td>	
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'>
      <input type="hidden" name="cerrado" value="<?php echo($cerrado); ?>">
      <select name="cerrado" class='filtro' <?php echo($deshabilitado); ?>>
        <?php echo(select($est_cursos,$cerrado)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Ayudantía:</td>
    <td class="celdaValorAttr">
      <input type="hidden" name="ayudantia" value="<?php echo($ayudantia); ?>">
      <select name="ayudantia" class='filtro' style="max-width: 500px" <?php echo($deshabilitado); ?>>
        <option value="">-- Seleccione --</option>
        <?php echo(select($sino,$ayudantia)); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">Modalidad:</td>
    <td class="celdaValorAttr">
      <input type="hidden" name="tipo_clase" value="<?php echo($tipo_clase); ?>">
      <select name="tipo_clase" class='filtro' style="max-width: 500px" <?php echo($deshabilitado); ?>>
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPO_CLASES,$tipo_clase)); ?>
      </select>
    </td>
  </tr>

  <tr><td class="celdaNombreAttr" colspan="4" style="text-align: center;">Programación Horaria Semanal del curso</td></tr>
    
  <tr>
    <td class='celdaNombreAttr'>F. Inicio:</td>
    <td class='celdaValorAttr'><input type="date" class='boton' size="10" name="fec_ini" id="fec_ini" value="<?php echo($fec_ini); ?>"></td>
    <td class='celdaNombreAttr'>F. Término:</td>
    <td class='celdaValorAttr'><input type="date" class='boton' size="10" name="fec_fin" id="fec_fin" value="<?php echo($fec_fin); ?>"></td>
  </tr>

  <tr>
    <td class="celdaNombreAttr">1ª Sesión:</td>
    <td class="celdaValorAttr" colspan="3">
      <input type="hidden" name="dia1"     value="<?php echo($dia1); ?>">
      <input type="hidden" name="horario1" value="<?php echo($horario1); ?>">
      <input type="hidden" name="sala1"    value="<?php echo($sala1); ?>">
      <select name="dia1" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia1)); ?>
      </select>
      <select name="horario1" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$horario1)); ?>
      </select>
      <select name="sala1" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Sala --</option>
        <?php echo(select($salas,$sala1)); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">2ª Sesión:</td>
    <td class="celdaValorAttr" colspan="3">
      <input type="hidden" name="dia2"     value="<?php echo($dia2); ?>">
      <input type="hidden" name="horario2" value="<?php echo($horario2); ?>">
      <input type="hidden" name="sala2"    value="<?php echo($sala2); ?>">
      <select name="dia2" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia2)); ?>
      </select>
      <select name="horario2" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$horario2)); ?>
      </select>
      <select name="sala2" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Sala --</option>
        <?php echo(select($salas,$sala2)); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">3ª Sesión:</td>
    <td class="celdaValorAttr" colspan="3">
      <input type="hidden" name="dia3"     value="<?php echo($dia3); ?>">
      <input type="hidden" name="horario3" value="<?php echo($horario3); ?>">
      <input type="hidden" name="sala3"    value="<?php echo($sala3); ?>">

      <select name="dia3" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia3)); ?>
      </select>
      <select name="horario3" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$horario3)); ?>
      </select>
      <select name="sala3" class='filtro' <?php echo($deshabilitado); ?>>
        <option value="">-- Sala --</option>
        <?php echo(select($salas,$sala3)); ?>        
      </select>
    </td>
  </tr> 
  <tr><td class="celdaNombreAttr" colspan="4" style="text-align: center;">Pruebas Solemnes</td></tr>
  <tr>
    <td class="celdaNombreAttr">&nbsp;</td>
    <td class="celdaNombreAttr" style="text-align: center;">Fechas</td>
    <td class="celdaNombreAttr" style="text-align: center;">Horarios</td>
    <td class="celdaNombreAttr" style="text-align: center;">Salas</td>
  </tr>

  <tr>
    <td class='celdaNombreAttr'>Primera:</td>
    <td class='celdaValorAttr'><input type='date' name='fec_sol1' value='<?php echo($fec_sol1); ?>' class='boton'><br><br></td>        
    <td class='celdaValorAttr'>
      <select name="sol1_horario1" class='filtro'>
        <option value="">-- Horario 1 --</option>
        <?php echo(select($horarios,$sol1_horario1)); ?>        
      </select><br>
      <select name="sol1_horario2" class='filtro' style='margin-top: 4px; margin-buttom: 4px'>
        <option value="">-- Horario 2 --</option>
        <?php echo(select($horarios,$sol1_horario2)); ?>        
      </select>
    </td>       
    <td class='celdaValorAttr'>
      <select name="sol1_sala1" class='filtro' onChange="formulario.sol1_sala2.value=this.value">
        <option value="">-- Sala 1 --</option>
        <?php echo(select_group($salas,$sol1_sala1)); ?>        
      </select><br>
      <select name="sol1_sala2" class='filtro' style='margin-top: 4px; margin-buttom: 4px'>
        <option value="">-- Sala 2 --</option>
        <?php echo(select_group($salas,$sol1_sala2)); ?>        
      </select>
    </td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Segunda:</td>
    <td class='celdaValorAttr'><input type='date' name='fec_sol2' value='<?php echo($fec_sol2); ?>' class='boton'><br><br></td>        
    <td class='celdaValorAttr'>
      <select name="sol2_horario1" class='filtro'>
        <option value="">-- Horario 1 --</option>
        <?php echo(select($horarios,$sol2_horario1)); ?>        
      </select><br>
      <select name="sol2_horario2" class='filtro' style='margin-top: 4px; margin-buttom: 4px'>
        <option value="">-- Horario 2 --</option>
        <?php echo(select($horarios,$sol2_horario2)); ?>        
      </select>       
    </td>
    <td class='celdaValorAttr'>
      <select name="sol2_sala1" class='filtro' onChange="formulario.sol2_sala2.value=this.value">
        <option value="">-- Sala 1 --</option>
        <?php echo(select_group($salas,$sol2_sala1)); ?>        
      </select><br>
      <select name="sol2_sala2" class='filtro' style='margin-top: 4px; margin-buttom: 4px'>
        <option value="">-- Sala 2 --</option>
        <?php echo(select_group($salas,$sol2_sala2)); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Recuperativa:</td>
    <td class='celdaValorAttr'><input type='date' name='fec_sol_recup' value='<?php echo($fec_sol_recup); ?>' class='boton'><br><br></td>        
    <td class='celdaValorAttr'>
      <select name="recup_horario1" class='filtro'>
        <option value="">-- Horario 1 --</option>
        <?php echo(select($horarios,$recup_horario1)); ?>        
      </select><br>
      <select name="recup_horario2" class='filtro' style='margin-top: 4px; margin-buttom: 4px'>
        <option value="">-- Horario 2 --</option>
        <?php echo(select($horarios,$recup_horario2)); ?>        
      </select>       
    </td>    
    <td class='celdaValorAttr'>
      <select name="recup_sala1" class='filtro' onChange="formulario.recup_sala2.value=this.value">
        <option value="">-- Sala 1 --</option>
        <?php echo(select_group($salas,$recup_sala1)); ?>        
      </select><br>
      <select name="recup_sala2" class='filtro' style='margin-top: 4px; margin-buttom: 4px'>
        <option value="">-- Sala 2 --</option>
        <?php echo(select_group($salas,$recup_sala2)); ?>        
      </select>
    </td>
  </tr>



<!-- 
  <tr>
    <td class="celdaNombreAttr" colspan="4" style="text-align: center;">
      Fechas de Pruebas
    </td>
  </tr> 
  <tr>
    <td class='celdaNombreAttr'>Solemne I:</td>
    <td class='celdaValorAttr'>
      <input type="text" size="10" class='boton' name="fec_sol1" id="fec_sol1" value="<?php echo($fec_sol1); ?>">
      <script type="text/javascript" charset="utf-8">
        var k3 = new Kalendae.Input('fec_sol1', { format: 'DD-MM-YYYY', weekStart: 1 } );
      </script>
    </td>
    <td class='celdaNombreAttr'>Solemne II:</td>
    <td class='celdaValorAttr'>
      <input type="text" size="10" class='boton' name="fec_sol2" id="fec_sol2" value="<?php echo($fec_sol2); ?>">
      <script type="text/javascript" charset="utf-8">
        var k4 = new Kalendae.Input('fec_sol2', { format: 'DD-MM-YYYY', weekStart: 1 } );
      </script>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Recuperativa:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="text" class='boton' size="10" name="fec_sol_recup" id="fec_sol_recup" value="<?php echo($fec_sol_recup); ?>">
      <script type="text/javascript" charset="utf-8">
        var k5 = new Kalendae.Input('fec_sol_recup', { format: 'DD-MM-YYYY', weekStart: 1 } );
      </script>
    </td>
  </tr>

  -->

</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

