<?php

$id_contrato = $_REQUEST['id_contrato'];

if (empty($forma)) { $forma = $_REQUEST['forma']; }

if (empty($id_alumno)) { $id_alumno = $_REQUEST['id_alumno']; }

if (is_numeric($id_alumno)) {
	$SQL_alumno = "SELECT a.id AS id_alumno,rut,nombres,apellidos,c.nombre AS carrera,
	                      CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
	                      semestre_cohorte||'-'||cohorte AS cohorte,a.id_pap
	               FROM alumnos AS a
	               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
	               WHERE a.id=$id_alumno";
	$alumno = consulta_sql($SQL_alumno);
	$_REQUEST = array_merge($_REQUEST,$alumno[0]);



  $id_pap        = $alumno[0]['id_pap'];
  $SQL_contratos = "SELECT id,'N°: '||id||' Periodo: '||coalesce(semestre||'-','')||ano||' Saldo Total: '||monto_saldot::money||' Monto Moroso: '||monto_moroso::money AS nombre 
                    FROM vista_contratos 
                    WHERE (id_alumno=$id_alumno OR id_pap=$id_pap) AND estado IS NOT NULL AND monto_saldot > 0
                    ORDER BY ano,semestre";
  $CONTRATOS     = consulta_sql($SQL_contratos);

  if ($id_contrato>0) {
    $SQL_contrato = "SELECT id,coalesce(semestre||'-','')||ano AS periodo,monto_saldot,monto_moroso 
                     FROM vista_contratos 
                     WHERE id = $id_contrato";
    $contrato = consulta_sql($SQL_contrato);
    $_REQUEST['monto_pie'] = round($contrato[0]['monto_moroso']*.5);
    $_REQUEST['nro_cuotas'] = 5;
  }
}

$NRO_CUOTAS = array();
for ($x=1;$x<=12;$x++) { $NRO_CUOTAS = array_merge($NRO_CUOTAS,array(array("id" => $x,"nombre" => $x))); }

$venc_min = date('Y-m');
$venc_max = date('Y-m', strtotime('+60 days'));
?>

<div style='margin-top: 5px'>
<?php

if ($forma == 'editar') {
	echo("  <input type='submit' name='editar' value='Guardar y Presentar Solicitud' tabindex='99'>\n");
} else {
	echo("  <input type='submit' name='crear' value='Guardar y Presentar Solicitud' tabindex='99'>\n");
}

?>  
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Tipo de Solicitud:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($nombre_tipo_solic); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales y Curriculares</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['id_alumno']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['apellidos']); ?></td>
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['nombres']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($_REQUEST['carrera']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['jornada']); ?></td>
    <td class='celdaNombreAttr'>Cohorte:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['cohorte']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto</td></tr>

  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>e-mail:</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <input type='email' size="40" name='email' value="<?php echo($_REQUEST['email']); ?>" onKeyUp="var valor=this.value;this.value=valor.toLowerCase();" class='boton' required>
      <small><br>En este correo se informará el resultado de esta solicitud</small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Fijo:</u></td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' maxlength='9' name="telefono" min="100000001" max="999999999" pattern="[0-9]*" title="Ingrese sólo números" name='telefono' value="<?php echo($_REQUEST['telefono']); ?>" class="boton" required>
      <small><br>Si no posee un número de red fija, ingrese<br>acá también su número de teléfono móvil</small>
    </td>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Móvil:</u></td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' maxlength='9' name="tel_movil" min="100000001" max="999999999" pattern="[0-9]*" title="Ingrese sólo números" value="<?php echo($_REQUEST['tel_movil']); ?>" class="boton" required>
      <small><br>Si no posee un número de teléfono móvil,<br>ingrese acá también su número de teléfono fijo</small>
    </td>
  </tr>

  <tr><td class='celdaValorAttr' colspan="4"><small>&nbsp;</small></td></tr>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Solicitud</td></tr>

  <?php if (empty($id_contrato)) { ?>

  <tr>
    <td class='celdaNombreAttr'><u>Contrato:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_contrato' class='filtro' onChange="submitform();" required>
        <option value="">-- Seleccione --</option>
        <?php echo(select($CONTRATOS,$_REQUEST['id_contrato'])); ?>
      </select>
    </td>
  </tr>

<?php } else { ?>

  <tr>
    <td class='celdaNombreAttr'>N° Contrato:</td>
    <td class='celdaValorAttr'><?php echo($id_contrato); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['periodo']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Saldo Total:</td>
    <td class='celdaValorAttr'>
      <input type='hidden' name="monto_saldot" value="<?php echo($contrato[0]['monto_saldot']); ?>">
      <?php echo(number_format($contrato[0]['monto_saldot'],0,',','.')); ?><br>
      <small>
        Este monto comprende cuotas o saldos de estas que<br>
        se encuentren vencidas y por vencer no pagadas a la fecha.
      </small>
    </td>
    <td class='celdaNombreAttr'>Monto Moroso:</td>
    <td class='celdaValorAttr'>
      <input type='hidden' name="monto_moroso" value="<?php echo($contrato[0]['monto_moroso']); ?>">
      <?php echo(number_format($contrato[0]['monto_moroso'],0,',','.')); ?><br>
      <small>
        Este monto corresponde sólo a las cuotas vencidas y no pagadas.
      </small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Monto del Pie:</u></td>
    <td class='celdaValorAttr'>
      $<input type='text' size="9" name='monto_pie' value="<?php echo($_REQUEST['monto_pie']); ?>" class='montos' onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name);" onBlur="calc_cuotas();" required>
      <small>
       Este monto deberá<br>pagarlo inmediatamente, ya que
       es requisito<br>para la aprobación de esta solicitud.
      </small>
    </td>
    <td class='celdaNombreAttr'><u>N° de Cuotas:</u></td>
    <td class='celdaValorAttr'><select name='nro_cuotas' class='filtro' onChange="calc_cuotas();" required><?php echo(select($NRO_CUOTAS,$_REQUEST['nro_cuotas'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' nowrap><u>Vencimiento 1ra cuota:</u></td>
    <td class='celdaValorAttr'>
      <select name='diap_ini' class='filtro' required>
        <option value=''>Día</option>
        <option value='5'>5</option>
        <option value='15'>15</option>
        <option value='30'>30</option>
      </select> de 
      <input type='month' name='mes_ano_pago' class='boton' min="<?php echo($venc_min); ?>" max="<?php echo($venc_max); ?>" required>
    </td>
    <td class='celdaNombreAttr' nowrap><u>Monto de las Cuotas:</u></td>
    <td class='celdaValorAttr'>
      $<input type='text' size="9" name='monto_cuota' class='montos' readonly>
    </td>
  </tr>
  
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <blockquote><blockquote>
		Considera lo siguiente en tu solicitud:<br>
		<br>
	    - El Monto del Pie debe ser a lo menos un 50% del Monto Moroso o bien un 30% del Saldo Total<br>
	    - Mientras más grande sea el Monto del Pie, el N° de cuotas puede ser mayor.<br>
	    - El Vencimiento de la 1ra cuota no puede ser superior a 30 días.<br>
      - Recuerde que esta repactación no considera el pago de otros contratos que puediran estar con saldo vigente o por firmar.<br>
      - Si tiene más contratos con deuda vencida, debe hacer una solicitud por cada uno de ellos.<br>
	    <br>
	    NOTA: En caso de no proponer un plan de pago según las consideraciones antes descritas, 
            la solicitud podrá ser Rechazada o bien derivada a la Vicerrectoría de Administración y Finanzas.
	  </blockquote></blockquote>
    </td>
  </tr>
<?php } ?>
</table>
<script language="Javascript">

puntitos(document.formulario.monto_pie,document.formulario.monto_pie.value.charAt(document.formulario.monto_pie.value.length-1),document.formulario.monto_pie.name);
calc_cuotas();

function calc_cuotas() {
  var monto_pie=parseInt(formulario.monto_pie.value.replace(".","").replace(".","").replace(".",""));
  var nro_cuotas=formulario.nro_cuotas.value;
  var monto_saldot=formulario.monto_saldot.value;
  var monto_moroso=formulario.monto_moroso.value;

  if (monto_pie<=0 || monto_pie>monto_saldot) {
    alert("ERROR: El Monto del Pie no puede ser cero o inferior.\n\n"
         +"De igual forma, no puede ser superior al Saldo Total. ");
    formulario.monto_pie.value = Math.round(monto_moroso*.5,0);
    puntitos(document.formulario.monto_pie,document.formulario.monto_pie.value.charAt(document.formulario.monto_pie.value.length-1),document.formulario.monto_pie.name);
  } else {
    formulario.monto_cuota.value = Math.round((monto_saldot-monto_pie)/nro_cuotas,0);
    puntitos(document.formulario.monto_cuota,document.formulario.monto_cuota.value.charAt(document.formulario.monto_cuota.value.length-1),document.formulario.monto_cuota.name);
  }
}
</script>
