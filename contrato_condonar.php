<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_contrato = $_REQUEST['id_contrato'];

if ($_REQUEST["fecha_condonacion"] == "") { $_REQUEST["fecha_condonacion"] = date("d-m-Y"); }

if (!is_numeric($id_contrato)) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

// Glosas que son condonables
//$Ids_glosas = "2,20,21,22,3,31";
$Ids_glosas = "2,20";

$SQL_cobros = "SELECT sum(coalesce(monto-monto_abonado,monto)) AS total_condonable,count(id) AS total_cuotas
               FROM finanzas.cobros 
               WHERE id_contrato=$id_contrato and id_glosa IN ($Ids_glosas) AND (NOT pagado OR abonado)";
//echo($SQL_cobros);
$cobros     = consulta_sql($SQL_cobros);
if ($cobros[0]['total_condonable'] == 0) {
	echo(msje_js("Este contrato está completamente pagado o bien no tiene deuda.\\n"
	            ."No es posible condonar este contrato"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_contrato = "SELECT c.*,coalesce(va.id,vp.id) AS id,coalesce(va.rut,vp.rut) AS rut,coalesce(va.nombre,vp.nombre) AS nombre,                        
                        to_char(c.fecha,'DD-MM-YYYY') AS fecha,monto_condonacion
                 FROM finanzas.contratos AS c
                 LEFT JOIN alumnos       AS al  ON al.id=c.id_alumno
                 LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
                 LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
                 LEFT JOIN pap                  ON pap.id=c.id_pap
                 WHERE c.id=$id_contrato";
$contrato     = consulta_sql($SQL_contrato);
if (count($contrato) == 0) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}


if ($contrato[0]['monto_condonacion'] > 0) {
	echo(msje_js("Este contrato ya se ha condonado anteriormente.\\n"
	            ."No es posible condonar nuevamente"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$fecha_condonacion   = $_REQUEST['fecha_condonacion'];
$monto_condonacion   = str_replace(".","",$_REQUEST['monto_condonacion']);
$motivo_condonacion  = $_REQUEST['motivo_condonacion'];
$id_tipo_condonacion = $_REQUEST['id_tipo_condonacion'];
if ($_REQUEST['guardar'] == "Guardar") {
	$problemas = false;
	if ($monto_condonacion>0 && $monto_condonacion > $cobros[0]['total_condonable']) {
		echo(msje_js("El monto de la condonación es mayor que la deuda total. Esto no es posible."));
		$problemas = true;
	}
	
	if ($fecha_condonacion<>"" && strtotime($fecha_condonacion) < strtotime($contrato[0]['fecha'])) {
		echo(msje_js("La fecha de la condonación es anterior a la fecha del contrato. Esto no es posible."));		
		$problemas = true;
	}

	if ($fecha_condonacion<>"" && strtotime($fecha_condonacion) > time()) {
		echo(msje_js("La fecha de la condonación es posterior a la fecha de hoy. Esto no es posible."));
		$problemas = true;
	}

	if (!$problemas) {
		$SQL_cobros_resp = "INSERT INTO finanzas.cobros_resp 
		                    SELECT * FROM finanzas.cobros 
		                    WHERE id_contrato = $id_contrato AND id_glosa IN ($Ids_glosas) AND (NOT pagado OR abonado)";
		if (consulta_dml($SQL_cobros_resp) == 0) {
			echo(msje_js("No fue posible establecer un punto de retorno para la operación, por lo que esta no se ha realizado.\\n\\n"
			            ."Por favor avise este error al Departamento de Informática"));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
		
		$SQL_cobros_cond = "SELECT id,monto,monto_abonado FROM finanzas.cobros 
		                    WHERE id_contrato = $id_contrato AND id_glosa IN ($Ids_glosas) AND (NOT pagado OR abonado)
		                    ORDER BY fecha_venc DESC";
		$cobros_cond  = consulta_sql($SQL_cobros_cond);
		
		$x = 0;
		$SQL_cond = "";
		while ($monto_condonacion > 0) {
			if ($cobros_cond[$x]['monto_abonado'] > 0 && $monto_condonacion >= $cobros_cond[$x]['monto'] - $cobros_cond[$x]['monto_abonado']) {
				$SQL_cond .= "UPDATE finanzas.cobros 
				              SET monto=monto_abonado,pagado=true,abonado=false,monto_abonado=null
				              WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion -= $cobros_cond[$x]['monto'] - $cobros_cond[$x]['monto_abonado'];
				$x++;
			} 
			elseif ($cobros_cond[$x]['monto_abonado'] > 0 && $monto_condonacion < $cobros_cond[$x]['monto'] - $cobros_cond[$x]['monto_abonado']) {
				$SQL_cond .= "UPDATE finanzas.cobros 
				              SET monto=monto-$monto_condonacion
				              WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion = 0;
				$x++;
			} 
			elseif ($cobros_cond[$x]['monto_abonado'] == 0 && $monto_condonacion >= $cobros_cond[$x]['monto']) {
				$SQL_cond .= "DELETE FROM finanzas.cobros WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion -= $cobros_cond[$x]['monto'];
				$x++;
			}
			elseif ($cobros_cond[$x]['monto_abonado'] == 0 && $monto_condonacion < $cobros_cond[$x]['monto']) {
				$SQL_cond .= "UPDATE finanzas.cobros SET monto=monto-$monto_condonacion WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion = 0;
				$x++;
			}
			
		}
		consulta_dml($SQL_cond);
		$monto_condonacion  = str_replace(".","",$_REQUEST['monto_condonacion']);
		$SQL_contrato_upd = "UPDATE finanzas.contratos 
		                     SET monto_condonacion=$monto_condonacion,
		                         motivo_condonacion='$motivo_condonacion',
		                         id_tipo_condonacion='$id_tipo_condonacion',
		                         fecha_condonacion='$fecha_condonacion'::date
		                     WHERE id=$id_contrato";
		if (consulta_dml($SQL_contrato_upd) > 0) {
			echo(msje_js("Se ha guardado y aplicado exitósamente la condonación."));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
	}		
}

$monto_condonable = number_format($cobros[0]['total_condonable'],0,',','.');

$tipos_condonacion = consulta_sql("SELECT id,nombre FROM finanzas.tipos_condonacion");
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="get" onSubmit="if (enblanco2('monto_condonacion','motivo_condonacion','id_tipo_condonacion','fecha_condonacion')) { if (!confirm('Está seguro de guardar esta condonacion?')) { return false; } } else { return false; }">
<input type="hidden" name="modulo"           value="<?php echo($modulo); ?>">
<input type="hidden" name="id_contrato"      value="<?php echo($id_contrato); ?>">
<input type="hidden" name="total_condonable" value="<?php echo($monto_condonable); ?>">
<input type="hidden" name="fecha_contrato"   value="<?php echo($contrato[0]['fecha']); ?>">
<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Contrato</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo($id_contrato); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['tipo']); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['semestre'].'-'.$contrato[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($contrato[0]['nombre']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Deuda</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr'>$<?php echo($monto_condonable); ?></td>
    <td class='celdaNombreAttr'>Cuotas:</td>
    <td class='celdaValorAttr'><?php echo($cobros[0]['total_cuotas']); ?></td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <div class='celdaNombreAttr' style='text-align: left'><u>Motivo detallado de la Condonación:</u></div>
      <textarea name='motivo_condonacion' style='width: 100%; height: 60px'><?php echo($_REQUEST['motivo_condonacion']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Tipo de Condonación:</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <select name='id_tipo_condonacion' class='filtro'>
        <option value="">-- Seleccione --</option>
        <?php echo(select($tipos_condonacion,$_REQUEST['id_tipo_condonacion'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Monto condonación:</u></td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='monto_condonacion' value="<?php echo($_REQUEST['monto_condonacion']); ?>" 
              onBlur="if (formulario.total_condonable.value.replace('.','').replace('.','')*1<this.value.replace('.','').replace('.','')*1) { alert('El monto de la condonación ($'+this.value+') es mayor a la deuda total ($'+formulario.total_condonable.value+'). Esto no es posible.'); this.focus(); this.select(); }"
              onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name);">
    </td>
    <td class='celdaNombreAttr'><u>Fecha:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size='10' name='fecha_condonacion' value="<?php echo($_REQUEST['fecha_condonacion']); ?>"><br>             
      <sup>DD-MM-AAAA</sup>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
