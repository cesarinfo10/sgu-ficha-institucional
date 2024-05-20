<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$mod_ant   = $_REQUEST['mod_ant'];
$id_alumno = $_REQUEST['id_alumno'];

if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.genero,va.fec_nac,
                      va.nacionalidad,coalesce(va.pasaporte,'**No corresponde**') AS pasaporte,va.direccion,a.comuna,a.region,
                      va.telefono,coalesce(va.tel_movil,'** No se registra **') AS tel_movil,va.email as email_inst,
                      coalesce(va.semestre_cohorte,0) AS semestre_cohorte,va.cohorte,a.email,
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
	//echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
} else {
	$aDatos_contacto = array("direccion","comuna","region","telefono","tel_movil","email");
	foreach($aDatos_contacto AS $campo) {
		$SQL_dat_con = "SELECT adc.$campo,to_char($campo"."_fecha,'DD-tmMon-YYYY') AS $campo"."_fecha,u1.nombre_usuario AS $campo"."_usuario
		                FROM alumnos_datos_contacto AS adc
		                LEFT JOIN usuarios AS u1 ON u1.id=adc.$campo"."_usuario
		                WHERE adc.id_alumno=$id_alumno AND adc.$campo IS NOT NULL
		                ORDER BY adc.$campo"."_fecha DESC LIMIT 1";
		$dat_con = consulta_sql($SQL_dat_con);
		if (count($dat_con) > 0) {
			$alumno[0][$campo."_vig"] .= "<br><small><i>desde el {$dat_con[0][$campo.'_fecha']}, por {$dat_con[0][$campo.'_usuario']}</i></small>";
		} else {
			$alumno[0][$campo."_vig"] .= "<br><small><i>desde la Matrícula, por Admisión</i></small>";
		}
	}
}

$aCampos = array("direccion","comuna","region","telefono","tel_movil","email");
if ($_REQUEST['guardar'] == "Guardar") {
	$SQLins_al_dat_con = "";
	foreach($aCampos AS $campo) {
		if ($_REQUEST[$campo] <> $alumno[0][$campo]) {
			$SQLins_al_dat_con .= "INSERT INTO alumnos_datos_contacto (id_alumno,$campo,$campo"."_fecha,$campo"."_usuario) 
			                            VALUES ($id_alumno,'{$_REQUEST[$campo]}',now(),{$_SESSION['id_usuario']});";
		}
	}
	consulta_dml($SQLins_al_dat_con);
	$SQLupdate = "UPDATE alumnos SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_alumno;";
	if (consulta_dml($SQLupdate) > 0) {
		echo(msje_js("Se han guardado existosamente los cambios"));
		if ($mod_ant == "alumno_emitir_certif") {
			echo(js("window.location='$enlbase_sm=$mod_ant&id_alumno=$id_alumno&val_datos_contacto=t';"));
		} else {
			echo(js("parent.jQuery.fancybox.close();"));
		}
		exit;
	}
}

if ($estado_tramite <> "") {
	echo(msje_js("Actualmente este alumno está trámite de obtener el estado de $estado_tramite"));
	$estado_tramite = "<sub><br>En trámite: <b>$estado_tramite</b></sub>";
}

$comunas        = consulta_sql("SELECT id,nombre FROM comunas;");
$regiones       = consulta_sql("SELECT id,nombre||' ('||romano||')' AS nombre FROM regiones;");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form action="principal_sm.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="mod_ant" value="<?php echo($mod_ant); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar" tabindex="99">
  <input type="button" name="cancelar" value="Cancelar" onclick="parent.jQuery.fancybox.close();">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td></tr>
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
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['genero']); ?></td>
    <td class='celdaNombreAttr' nowrap>F. Nacimiento:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['fec_nac']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['nacionalidad']); ?></td>
    <td class='celdaNombreAttr'>Pasaporte:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['pasaporte']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="text" size="40" name="direccion" value="<?php echo($alumno[0]['direccion']); ?>" class='boton' required>
      <br><?php echo($alumno[0]['direccion_vig']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'>
      <select name='comuna' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($comunas,$alumno[0]['comuna'])); ?>        
      </select>
      <br><?php echo($alumno[0]['comuna_vig']); ?>
    </td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr' nowrap>
      <select name='region' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($regiones,$alumno[0]['region'])); ?>        
      </select>
      <br><?php echo($alumno[0]['region_vig']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tel. fijo:</td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' maxlength='9' name="telefono" min="100000001" pattern="[0-9]*" title="Ingrese sólo números" value="<?php echo($alumno[0]['telefono']); ?>" class="boton" required>
      <br><?php echo($alumno[0]['telefono_vig']); ?>
    </td>
    <td class='celdaNombreAttr'>Tel. móvil:</td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' maxlength='9' name="tel_movil" min="100000001" pattern="[0-9]*" title="Ingrese sólo números" value="<?php echo($alumno[0]['tel_movil']); ?>" class="boton" required>
      <br><?php echo($alumno[0]['tel_movil_vig']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-mail UMC:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['email_inst']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-mail externo:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="email" size="40" name="email" value="<?php echo($alumno[0]['email']); ?>" class='boton' required>
      <br><?php echo($alumno[0]['email_vig']); ?>
    </td>
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
</table>
</form>
