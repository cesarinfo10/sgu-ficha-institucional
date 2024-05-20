<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_prog_asig = $_REQUEST['id_prog_asig'];
$id_malla     = $_REQUEST['id_malla'];
if (!is_numeric($id_prog_asig)) {
	echo(js("location.href='$enlbase=gestion_asignaturas';"));
	exit;
}

$modulo_ant = (empty($_REQUEST['modulo_ant'])) ? $_SERVER['SCRIPT_NAME'] : $_REQUEST['modulo_ant'];

$aCampos = array("horas_semanal","nro_semanas_semestrales","horas_autonomas_semanales","creditos","obj_generales","obj_especificos",
				 "contenidos","met_instruccion","evaluacion","bib_obligatoria","bib_oblig_otras_asig",
				 "bib_complement","bib_compl_otras_asig","descripcion","aporte_perfil_egreso");

if ($_REQUEST['guardar'] == "💾 Guardar" || $_REQUEST['guardar_cerrar'] == "💾 Guardar y cerrar") {
	$SQLupdate = "UPDATE prog_asig SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_prog_asig;";
	if (consulta_dml($SQLupdate) == 1) {
		echo(msje_js("Se han guardado los cambios exitósamente"));
	}
}

if ($_REQUEST['guardar_cerrar'] == "💾 Guardar y cerrar") {
	echo(js("location.href='$modulo_ant?modulo=ver_prog_asig&id_prog_asig=$id_prog_asig&id_malla=$id_malla';"));
	exit;		
}

$SQL_mallas_prog_asig = "SELECT char_comma_sum(c.alias||'-'||m.ano) AS mallas
                         FROM mallas AS m 
                         LEFT JOIN carreras AS c on c.id=m.id_carrera 
                         WHERE m.id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig=pa.id)";

$SQL_prog_asig = "SELECT pa.*,vpa.asignatura,($SQL_mallas_prog_asig) AS mallas 
                  FROM prog_asig AS pa 
                  LEFT JOIN vista_prog_asig AS vpa USING (id) 
                  WHERE pa.id=$id_prog_asig";
$prog_asig     = consulta_sql($SQL_prog_asig);
if (count($prog_asig) == 0) {
	echo(js("location.href='$modulo_ant?modulo=ver_prog_asig&id_prog_asig=$id_prog_asig';"));
	exit;
}	
extract($prog_asig[0]);


$aRequeridos = array(0,1,2,3,4,5,6,7,8);
$requeridos  = requeridos($aRequeridos,$aCampos);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo"       value="<?php echo($modulo); ?>">
<input type="hidden" name="modulo_ant"   value="<?php echo($modulo_ant); ?>">
<input type="hidden" name="id_malla"     value="<?php echo($id_malla); ?>">
<input type="hidden" name="id_prog_asig" value="<?php echo($id_prog_asig); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<div class="texto" style="margin-top: 5px">
  <input type="submit" name="guardar" value="💾 Guardar" onClick="return confirmar_guardar();">
  &nbsp;&nbsp;
  <input type="submit" name="guardar_cerrar" value="💾 Guardar y cerrar" onClick="return confirmar_guardar();">
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type="button" name="cancelar" value="⛔ Cancelar"  onClick="cancelar_guardar();">
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="85%" style="margin-top: 5px">
  <tr><td class='celdaNombreAttr' style='text-align: center;' colspan="4">Antecedentes del Programa de Estudios</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($ano); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr' ><?php echo($id); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($cod_asignatura." ".$asignatura); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Malla(s):</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($mallas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Créditos:</td>
    <td class='celdaValorAttr' ><input type="number" size="2" min="1" max="30" name="creditos" value="<?php echo($creditos); ?>" onBlur="calc_creditos()" required></td>
    <td class='celdaNombreAttr'>Semanas semestrales:</td>
    <td class='celdaValorAttr' ><input type="number" size="2" min="1" max="18" name="nro_semanas_semestrales" value="<?php echo($nro_semanas_semestrales); ?>" onBlur="calc_creditos()" required> <span id='horas_crono'></span></td>
  </tr>

  <tr><td class='celdaNombreAttr' style='text-align: center;' colspan="4">Horas Semanales</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Lectivas:</td>
    <td class='celdaValorAttr' ><input type="number" size="2" min="1" max="8" name="horas_semanal" value="<?php echo($horas_semanal); ?>" onBlur="calc_creditos()" required></td>
    <td class='celdaNombreAttr'>Autónomas:</td>
    <td class='celdaValorAttr' ><input type="number" size="2" min="0" max="10" name="horas_autonomas_semanales" value="<?php echo($horas_autonomas_semanales); ?>" onBlur="calc_creditos()" required> <span id='horas_crono_calc'></span></td>
  </tr>

  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Descripción:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="descripcion" rows="2" class="general" required><?php echo($descripcion); ?></textarea></td></tr>

  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Aporte al Perfil de Egreso:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="aporte_perfil_egreso" rows="2" class="general" required><?php echo($aporte_perfil_egreso); ?></textarea></td></tr>

  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Objetivo General:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="obj_generales" rows="2" class="general" required><?php echo($obj_generales); ?></textarea></td></tr>

  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Objetivos Específicos:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="obj_especificos" rows="5" class="general" required><?php echo($obj_especificos); ?></textarea></td></tr>

  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left;'>Contenidos:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="contenidos" rows="10" class="general" required><?php echo($contenidos); ?></textarea></td></tr>

  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left;'>Método de instrucción:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="met_instruccion" rows="5" class="general" required><?php echo($met_instruccion); ?></textarea></td></tr>

  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left;'>Método de Evaluación:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="evaluacion" rows="5" class="general" required><?php echo($evaluacion); ?></textarea></td></tr>

  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left;'>Bibliografía Obligatoria:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="3">
      <b>Autor, Título, Editorial y Año</b><br>
      <textarea name="bib_obligatoria" rows="10" class="general" required><?php echo($bib_obligatoria); ?></textarea>
    </td>
    <td class='celdaValorAttr' nowrap>
      <b>Otras asignaturas que requerirán este título*</b><br>
      <textarea name="bib_oblig_otras_asig" rows="10" class="general"><?php echo($bib_oblig_otras_asig); ?></textarea>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="10" style='text-align: left;'>Bibliografía Complementaria:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="3">
      <b>Autor, Título, Editorial y Año</b><br>
      <textarea name="bib_complement" rows="10" class="general"><?php echo($bib_complement); ?></textarea>
    </td>
    <td class='celdaValorAttr' nowrap>
      <b>Otras asignaturas que requerirán este título*</b><br>
      <textarea name="bib_compl_otras_asig" rows="10" class="general"><?php echo($bib_compl_otras_asig); ?></textarea>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<script>

function calc_creditos() {

	var creditos                  = parseInt(formulario.creditos.value),
	    horas_semanal             = parseInt(formulario.horas_semanal.value),
	    horas_autonomas_semanales = parseInt(formulario.horas_autonomas_semanales.value),
	    nro_semanas_semestrales   = parseInt(formulario.nro_semanas_semestrales.value),
		Credito                   = 24,
		Credito_ped	              = Math.round(Credito * 60/40),
		horas_totales_crono       = creditos * Credito,
		horas_totales_ped         = Math.round(horas_totales_crono*60/40),
		porc_horas_lectivas_ped	  = 0.33,
		horas_totales_crono_calc  = 0;

	if (isNaN(creditos)) { creditos = 0; }
	if (isNaN(horas_semanal)) { horas_semanal = 0; }
	if (isNaN(horas_autonomas_semanales)) { horas_autonomas_semanales = 0; }
	if (isNaN(nro_semanas_semestrales)) { nro_semanas_semestrales = 0; }

	if (creditos > 0 && nro_semanas_semestrales > 0) {
		if (horas_semanal == 0) { 
			horas_semanal = Math.round((creditos * Credito_ped)/nro_semanas_semestrales * porc_horas_lectivas_ped); 
			formulario.horas_semanal.value = horas_semanal;
		} 

		horas_autonomas_semanales = Math.round(horas_totales_ped/nro_semanas_semestrales) - horas_semanal;
		formulario.horas_autonomas_semanales.value = horas_autonomas_semanales;

		horas_totales_crono_calc = Math.round(((horas_semanal + horas_autonomas_semanales) * nro_semanas_semestrales) * 40/60);

		document.getElementById("horas_crono").innerHTML = " Se requiren "+horas_totales_crono+" horas semestrales";
		document.getElementById("horas_crono_calc").innerHTML = " Se satisfacen "+horas_totales_crono_calc+" horas semestrales";

		if (horas_totales_crono != horas_totales_crono_calc) {
			alert("ERROR: No coincide las horas semanales en total con los parámetros ingresados.");
			return false;
		}

		if (horas_totales_crono == horas_totales_crono_calc) { 
			document.getElementById("horas_crono_calc").innerHTML += "...Validado.";
			return true; 
		}
	} else {
		alert("ERROR: No se ha ingresado el valor de «Semanas Semestrales» ni «Créditos».\n\n"
		     +"Se asignan valores por defecto, puede modificarlos.");
		formulario.creditos.value = 1;
		formulario.nro_semanas_semestrales.value = 18;
		return false;
	}

}

calc_creditos();
</script>