<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_pago = $_REQUEST['id_pago'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

$SQL_pago = "SELECT p.id,p.nro_boleta,to_char(p.fecha,'DD-tmMon-YYYY') AS fecha,u.nombre_usuario AS cajero,
                    efectivo,cheque,transferencia,tarj_credito,tarj_debito,
                    CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,p.nulo,
                    CASE
                      WHEN cob.id_contrato IS NOT NULL THEN coalesce(a.rut,pap.rut)
                      WHEN cob.id_alumno   IS NOT NULL THEN a2.rut
                    END AS rut_alumno,
                    CASE
                      WHEN cob.id_contrato IS NOT NULL THEN coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                      WHEN cob.id_alumno   IS NOT NULL THEN a2.apellidos||' '||a2.nombres  
                    END AS nombre_alumno,
                    CASE
                      WHEN cob.id_contrato IS NOT NULL THEN car.nombre 
                      WHEN cob.id_alumno   IS NOT NULL THEN car2.nombre  
                    END AS carrera_alumno,
                    CASE
                      WHEN cob.id_contrato IS NOT NULL THEN c.jornada 
                      WHEN cob.id_alumno   IS NOT NULL THEN a2.jornada  
                    END AS jornada_alumno                    
             FROM finanzas.pagos AS p
             LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
             LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
             LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
             LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
             LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
             LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
             LEFT JOIN pap                          ON pap.id=c.id_pap
             LEFT JOIN carreras AS car			    ON car.id=c.id_carrera
             LEFT JOIN carreras AS car2			    ON car2.id=a2.carrera_actual
             WHERE p.id=$id_pago";
$pago     = consulta_sql($SQL_pago);

if (count($pago) > 0) {
	$efectivo      = number_format($pago[0]['efectivo'],0,",",".");
	$cheque        = number_format($pago[0]['cheque'],0,",",".");
	$transferencia = number_format($pago[0]['transferencia'],0,",",".");
	$tarj_credito  = number_format($pago[0]['tarj_credito'],0,",",".");
	$tarj_debito   = number_format($pago[0]['tarj_debito'],0,",",".");
	
	$SQL_pago_detalle = "SELECT c.id AS id_cobro,g.nombre AS glosa,pd.monto_pagado,c.monto,
	                            to_char(c.fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,nro_cuota,id_contrato,con.ano
	                     FROM finanzas.pagos_detalle AS pd
	                     LEFT JOIN finanzas.cobros    AS c   ON c.id=pd.id_cobro
	                     LEFT JOIN finanzas.glosas    AS g   ON g.id=c.id_glosa
	                     LEFT JOIN finanzas.contratos AS con ON con.id=c.id_contrato
	                     WHERE pd.id_pago=$id_pago
	                     ORDER BY c.fecha_venc";
	$pago_detalle     = consulta_sql($SQL_pago_detalle);
	$monto_total = 0;
	$HTML = "";
	for ($x=0;$x<count($pago_detalle);$x++) {
		extract($pago_detalle[$x]);
		$accion = "Pagó";
		if ($monto_pagado < $monto) { $accion = "Abonó"; } 
		$monto_total += $monto_pagado;
		$monto_pagado = number_format($monto_pagado,0,",",".");
		$id_contrato =  number_format($id_contrato,0,",",".");
		$HTML .= "<tr>"
		      .  "  <td colspan='2' style='vertical-align: middle'>"
		      .       $glosa . "<br>"
		      .  "    Venc: $fecha_venc &nbsp;&nbsp; N° Cuota:$nro_cuota <br>"
		      .  "    N° Contrato: $id_contrato &nbsp;&nbsp; Año Contrato: $ano <br>"
		      .  "    <div style='text-align: right'>$accion $$monto_pagado</div> <hr>"
		      .  "  </td>"
		      .  "</tr>";
	}
	$monto_total = number_format($monto_total,0,",",".");
} else {
	exit;
}

$jornada_alumno = "";
if ($pago[0]['jornada_alumno'] == "D") { $jornada_alumno = "Diurna"; } else { $jornada_alumno = "Vespertina"; }
comp_pago_email($id_pago);
?>

<!-- Inicio: <?php echo($modulo); ?> -->

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td colspan='4' align='center'><br><b>C O M P R O B A N T E&nbsp;&nbsp;D E&nbsp;&nbsp;P A G O</b><br><br></td></tr>
  <tr>
    <td style='color: #7F7F7F; text-align: right'>ID:</td>
    <td style='color: #7F7F7F;'><?php echo($pago[0]['id']); ?></td>
    <td style='text-align: right'>Nº Boleta:</td>
    <td><b><?php echo(number_format($pago[0]['nro_boleta'],0,",",".")); ?></b></td>
  </tr>
  <tr>
    <td style='text-align: right'>Cajero:</td>
    <td><?php echo($pago[0]['cajero']); ?></td>
    <td style='text-align: right'>Fecha:</td>
    <td><?php echo($pago[0]['fecha']); ?></td>
  </tr>
  <tr><td colspan="4" style="text-align: center; "><br><b>Antecedentes del Alumno</b></td></tr>
  <tr>
    <td>Nombre:</td>
    <td colspan='3'><?php echo($pago[0]['nombre_alumno']); ?></td>
  </tr>
  <tr>
    <td>RUT:</td>
    <td colspan='3'><?php echo($pago[0]['rut_alumno']); ?></td>
  </tr>
  <tr>
    <td>Carrera:</td>
    <td colspan='3'><?php echo($pago[0]['carrera_alumno']); ?></td>
  </tr>
  <tr>
    <td>Jornada:</td>
    <td colspan='3'><?php echo($jornada_alumno); ?></td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td colspan="2" align='center'>DETALLE</td></tr>
  <?php echo($HTML); ?>
  <tr><td>&nbsp;</td></tr>
  <tr>
    <td style="text-align: right; ">Monto Boleta</td>
    <td style='text-align: right;'><b>$<?php echo($monto_total); ?></b></td>
  </tr>
  <tr>
    <td colspan="2" style="text-align: center;">&nbsp;</td>
  </tr>
  <tr>
    <td style='text-align: right;'>Efectivo</td>
    <td style='text-align: right;'>$<?php echo($efectivo); ?></td>
  </tr>
  <tr>
    <td style='text-align: right;'>Cheque(s)</td>
    <td style='text-align: right;'>$<?php echo($cheque); ?></td>
  </tr>
  <tr>
    <td style='text-align: right;'>Transferencia</td>
    <td style='text-align: right;'>$<?php echo($transferencia); ?></td>
  </tr>
  <tr>
    <td style='text-align: right;'>Tarjeta de Crédito</td>
    <td style='text-align: right;'>$<?php echo($tarj_credito); ?></td>
  </tr>
  <tr>
    <td style='text-align: right;'>Tarjeta de Débito</td>
    <td style='text-align: right;'>$<?php echo($tarj_debito); ?></td>
  </tr>  
</table>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
.

<!-- Fin: <?php echo($modulo); ?> -->
