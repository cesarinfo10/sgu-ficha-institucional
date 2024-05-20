<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,va.carrera||'-'||jornada AS carrera,va.semestre_cohorte||'-'||va.cohorte AS cohorte,
                      va.estado,ca.arancel,financiamiento,a.carrera_actual AS id_carrera,a.jornada AS id_jornada,a.regimen,a.cohorte AS cohorte_alumno
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN finanzas.contratos_al_2017 AS ca ON ca.id_alumno=a.id
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}
extract($alumno[0]);

$ano_contrato = $ANO_MATRICULA;
$SQL_reajuste  = "SELECT coalesce(mul(factor::numeric),1) FROM finanzas.reajuste_aranceles WHERE ano BETWEEN $cohorte_alumno+1 AND $ano_contrato";

if ($alumno[0]['regimen'] == "POST-GD" || $alumno[0]['regimen'] == "POST-TD") { $SQL_reajuste  = "SELECT 1"; }

$ano_arancel = 0;
if ($cohorte_alumno < 2010) { $ano_arancel = 2010; } else { $ano_arancel = $cohorte_alumno; }

$SQL_aranceles = "SELECT carrera,jornada,monto_matricula,round(monto_arancel*($SQL_reajuste)) AS monto_arancel,
						 round(monto_arancel_credito*($SQL_reajuste)) AS monto_arancel_credito,cuotas 
				  FROM vista_aranceles 
				  WHERE id_carrera=$id_carrera AND id_jornada='$id_jornada' 
					AND ano=$ano_arancel;";
//echo($SQL_aranceles);
$aranceles = consulta_sql($SQL_aranceles);
if (count($aranceles) == 0) {
	if ($cohorte_reinc > 0) { 
		$ano_arancel = $cohorte_reinc;
		$SQL_reajuste  = "SELECT coalesce(mul(factor::numeric),1) FROM finanzas.reajuste_aranceles WHERE ano BETWEEN $cohorte_reinc+1 AND $ano_contrato";

		$SQL_aranceles = "SELECT carrera,jornada,monto_matricula,round(monto_arancel*($SQL_reajuste)) AS monto_arancel,
								 round(monto_arancel_credito*($SQL_reajuste)) AS monto_arancel_credito,cuotas 
						  FROM vista_aranceles 
						  WHERE id_carrera=$id_carrera AND id_jornada='$id_jornada' 
							AND ano=$ano_arancel;";
		//echo($SQL_aranceles);
		$aranceles = consulta_sql($SQL_aranceles);
	} else {
		echo(msje_js("ERROR: No ha sido posible definir el arancel a aplicar. Comuníquese con el Departamento de Informática."));
		echo(js("location.href='$enlbase=gestion_alumnos';"));
		exit;
	}
} 


if ($_REQUEST['guardar'] == "Guardar") {
	$_REQUEST['arancel'] = str_replace(".","",$_REQUEST['arancel']);
	$_REQUEST['beca']    = str_replace(".","",$_REQUEST['beca']);
	if (empty($_REQUEST['cred_interno'])) {	
		$_REQUEST['cred_interno'] = 0;
	} else {
		$_REQUEST['cred_interno'] = str_replace(".","",$_REQUEST['cred_interno']);
	}
	if ($arancel <> $_REQUEST['arancel'] || $beca <> $_REQUEST['beca'] || $congelado <> $_REQUEST['congelado'] ||
	    $cred_interno <> $_REQUEST['cred_interno'] || $financiamiento <> $_REQUEST['financiamiento']) {
		$contrato_al_2010 = consulta_sql("SELECT arancel,id_alumno FROM finanzas.contratos_al_2017 WHERE id_alumno=$id_alumno");
		var_dump($contrato_al_2010);
		if (count($contrato_al_2010) == 1) {
			$SQL_cont_al_2010 = "UPDATE finanzas.contratos_al_2017
			                     SET arancel={$_REQUEST['arancel']},financiamiento='{$_REQUEST['financiamiento']}'
			                     WHERE id_alumno=$id_alumno";
		} elseif (count($contrato_al_2010) == 0) {
			$SQL_cont_al_2010 = "INSERT INTO finanzas.contratos_al_2017 (id_alumno,arancel,financiamiento)
			                          VALUES ($id_alumno,{$_REQUEST['arancel']},'{$_REQUEST['financiamiento']}')";
		}
		
		echo($SQL_cont_al_2010);
		
		if (consulta_dml($SQL_cont_al_2010) == 1) {
			echo(msje_js("Se han guardado exitosamente los cambios."));
		} else {
			echo(msje_js("ERROR: No se han guardado los cambios."));
		}
	} else {
		echo(msje_js("No se han realizado cambios, nada que guardar."));
	}
	echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	exit;
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('arancel','beca');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<!--
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td>
  </tr>
</table>
<br> 
-->
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">ID alumno:</td>
    <td class="celdaValorAttr"><?php echo($id); ?></td>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrrera:</td>
    <td class="celdaValorAttr"><?php echo($carrera); ?></td>
    <td class="celdaNombreAttr">Cohorte:</td>
    <td class="celdaValorAttr"><?php echo($cohorte); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Valores Año <?php echo($ANO_MATRICULA); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Arancel Anual Contado:</td>
    <td class="celdaValorAttr" align='right'><?php echo(number_format($aranceles[0]['monto_arancel'],0,',','.')); ?></td>
    <td class="celdaNombreAttr">Arancel Anual Crédito:</td>
    <td class="celdaValorAttr" align='right'><?php echo(number_format($aranceles[0]['monto_arancel_credito'],0,',','.')); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Arancel Semestral Contado:</td>
    <td class="celdaValorAttr" align='right'><?php echo(number_format(round($aranceles[0]['monto_arancel']/2,0),0,',','.')); ?></td>
    <td class="celdaNombreAttr">Arancel Semestral Crédito:</td>
    <td class="celdaValorAttr" align='right'><?php echo(number_format(round($aranceles[0]['monto_arancel_credito']/2,),0,',','.')); ?></td>
  </tr>

<!--
  <tr>
    <td class="celdaNombreAttr"><u>Arancel Colegiatura:</u></td>
    <td class="celdaValorAttr" colspan="3">
      $<input type='text' class='montos' style="text-align: right;" size='10' name='arancel' value="<?php echo($arancel); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)"
              onClick="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <b>Financiamiento:</b>
      <select name='financiamiento'>
        <option>-- Seleccione --</option>
        <?php echo(select($financiamientos,trim($financiamiento))); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Beca de arancel:</u></td>
    <td class="celdaValorAttr" colspan="3">
      $<input type='text' class='montos' style="text-align: right;" size='10' name='beca' value="<?php echo($beca); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)"
              onClick="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Crédito Interno (último):</u></td>
    <td class="celdaValorAttr" colspan="3">
      $<input type='text' class='montos' style="text-align: right;" size='10' name='cred_interno' value="<?php echo($cred_interno); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)"
              onClick="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Arancel congelado?</u></td>
    <td class="celdaValorAttr" colspan="3">
      <select name="congelado">
        <?php echo(select($sino,$congelado)); ?>
      </select>
    </td>
  </tr>

-->

</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

