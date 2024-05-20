<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if ($_REQUEST['id_alumno'] == "") {
	header("Location: principal.php?modulo=gestion_alumnos");
	exit;
}

$id_alumno = $_REQUEST['id_alumno'];
$id_curso  = $_REQUEST['id_curso'];

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.genero,va.fec_nac,
                      va.nacionalidad,coalesce(va.pasaporte,'**No corresponde**') AS pasaporte,va.direccion,va.comuna,va.region,
                      va.telefono,coalesce(va.tel_movil,'** No se registra **') AS tel_movil,va.email,
                      coalesce(va.semestre_cohorte,0) AS semestre_cohorte,va.cohorte,pap.email AS email_externo,
                      trim(va.carrera) AS carrera_alias,va.malla_actual,va.estado,va.id_malla_actual,c.nombre AS carrera,
                      ae.nombre AS estado_tramite,CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      CASE a.admision WHEN 1 THEN 'Regular' WHEN 2 THEN 'Extraordinaria' WHEN 10 THEN 'Modular' WHEN 20 THEN 'Modular (Extr.)' END AS admision
               FROM vista_alumnos AS va
               LEFT JOIN carreras AS c ON c.id=id_carrera
               LEFT JOIN alumnos AS a ON a.id=va.id
               LEFT JOIN al_estados AS ae ON ae.id=a.estado_tramite
               LEFT JOIN pap ON pap.id=a.id_pap
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

extract($alumno[0]);

if (empty($id_curso)) {
	$SQL_cursos = "SELECT vac.id_curso AS id,vac.semestre||'-'||vac.ano||' '||vac.asignatura AS nombre,
	               vac.semestre||'-'||vac.ano AS periodo
				   FROM vista_alumnos_cursos AS vac
				   WHERE vac.id_alumno=$id_alumno AND vac.id_estado IN (1,2)
				   ORDER BY vac.ano,vac.semestre,vac.asignatura";
} else {
	$SQL_cursos = "SELECT vac.asignatura,vac.semestre||'-'||vac.ano AS periodo,
						  vac.s1, vac.nc, vac.s2, vac.recuperativa AS rec,vac.nf,vac.situacion,vac.ano
				   FROM vista_alumnos_cursos AS vac
				   WHERE vac.id_alumno=$id_alumno AND vac.id_curso=$id_curso";
}
$cursos = consulta_sql($SQL_cursos);
//var_dump($cursos);
if ($_REQUEST['guardar'] == "Guardar") {
	$aCampos = array("solemne1","nota_catedra","solemne2","recuperativa");
	foreach ($aCampos AS $campo) { if ($_REQUEST[$campo] == "NSP") { $_REQUEST[$campo] = -1; } }
	
	if     ($cursos[0]['ano'] <=2004) { $func_calc_nf = "calc_nota_final"; } 
	elseif ($cursos[0]['ano'] <=2010) { $func_calc_nf = "calc_nf"; } 
	else                              { $func_calc_nf = "calc_nf_2011"; } 
		
	$SQLupdate = "UPDATE cargas_academicas SET fecha_mod_notas = now()," . arr2sqlupdate($_REQUEST,$aCampos)
	           . " WHERE id_curso=$id_curso AND id_alumno=$id_alumno;";
	$SQLupdate .= "UPDATE cargas_academicas
	               SET nota_final = $func_calc_nf(solemne1,nota_catedra,solemne2,recuperativa),
	                   id_estado  = (CASE WHEN $func_calc_nf(solemne1,nota_catedra,solemne2,recuperativa) >= 4 THEN 1 ELSE 2 END)
	               WHERE id_curso=$id_curso AND id_alumno=$id_alumno;";
	//echo($SQLupdate);
	if (consulta_dml($SQLupdate) > 0) {		
		$url_si = "$enlbase=ver_alumno&id_alumno=$id_alumno";
		$url_no = "$enlbase=ver_curso&id_curso=$id_curso";
		$msje   = "Se han guardado los cambios exitosamente.\\n\\n"
		        . "Si desea volver a la ficha del alumno, pinche en [Aceptar]\\n"
		        . "Si desea ver la ficha del curso pinche en [Cancelar]";		        
		echo(confirma_js($msje,$url_si,$url_no));
		exit;
	} else {
		echo(msje_js("ERROR: No se han guardado los cambios. Verifique que se hayan ingresado todas las calificaciones que corresponda"));
	}
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" method="post" action="principal.php" onSubmit="return validar_nota('solemne1','nota_catedra','solemne2','recuperativa')">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<input type="submit" name="guardar" value="Guardar">
<br>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['id']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Curriculares</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Admisión:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['admision']); ?></td>
    <td class='celdaNombreAttr'>Cohorte:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['semestre_cohorte'].'-'.$alumno[0]['cohorte']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera Actual:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['carrera'].' ('.$alumno[0]['carrera_alias'].')'); ?></td>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['jornada']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año Malla Actual:</td>
    <td class='celdaValorAttr'>
      <a class='enlaces' href="<?php echo($enlbase.'=ver_malla&id_malla='.$alumno[0]['id_malla_actual']); ?>">
        <?php echo($alumno[0]['malla_actual']); ?>
      </a>
    </td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['estado'].$estado_tramite); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan='3'>
<?php if (empty($id_curso)) { ?>
      <select name='id_curso' onChange="submitform()">
        <option value="">-- Seleccione --</option>
        <?php echo(select($cursos,$id_curso)); ?>
      </select>
<?php } else { ?>
      <?php
        extract($cursos[0]);
        $_azul = "color: #000099";
	    $_rojo = "color: #ff0000";
	    
	    if ($nf >= 4) {$estilo = $_azul;} else {$estilo = $_rojo;}
	    $nf        = "<span style='$estilo'>$nf</span>";
	    $situacion = "<span style='$estilo'>$situacion</span>";
      ?>
      <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
        <tr class='filaTituloTabla'>
          <td class='tituloTabla'>&nbsp;</td>
          <td class='tituloTabla'>&nbsp;</td>
          <td class='tituloTabla'>S1</td>
          <td class='tituloTabla'>NC</td>
          <td class='tituloTabla'>S2</td>
          <td class='tituloTabla'>Rec</td>
          <td class='tituloTabla'>NF</td>
          <td class='tituloTabla'>Situación</td>
        <tr class='filaTabla'>
          <td class='textoTabla'><?php echo($periodo); ?></td>
          <td class='textoTabla'><?php echo($asignatura); ?></td>
          <td class='textoTabla'>
            <input type="text" size="3" maxlenght="3" name="solemne1" value="<?php echo($s1); ?>" onChange='this.value = this.value.toUpperCase();' onBlur="if (this.value=='') { this.value='NSP'; }"></td>
          <td class='textoTabla'><input type="text" size="3" maxlenght="3" name="nota_catedra" value="<?php echo($nc); ?>" onChange='this.value = this.value.toUpperCase();' onBlur="if (this.value=='') { this.value='NCR'; }"></td>
          <td class='textoTabla'><input type="text" size="3" maxlenght="3" name="solemne2" value="<?php echo($s2); ?>" onChange='this.value = this.value.toUpperCase();' onBlur="if (this.value=='') { this.value='NSP'; }"></td>
          <td class='textoTabla'><input type="text" size="3" maxlenght="3" name="recuperativa" value="<?php echo($rec); ?>" onChange='this.value = this.value.toUpperCase();' onBlur="if (this.value=='') { this.value='NSP'; }"></td>
          <td class='textoTabla'><?php echo($nf); ?></td>
          <td class='textoTabla'><?php echo($situacion); ?></td>
        </tr>
      </table>
<?php } ?>
    </td>
  </tr>
</table>
</div>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
