<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$tiempo       = $_REQUEST['tiempo'];
$id_cajero    = $_REQUEST['id_cajero'];
$rendidas     = $_REQUEST['rendidas'];
$regimen      = $_REQUEST['regimen'];
$fec_ini      = $_REQUEST['fec_ini'];
$fec_fin      = $_REQUEST['fec_fin'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if (empty($tiempo) && empty($fec_ini) && empty($fec_fin)) { $tiempo=1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($fec_ini)) { $fec_ini = date("d-m-Y"); }
if (empty($fec_fin)) { $fec_fin = date("d-m-Y"); }

$condicion = "WHERE nro_boleta IS NOT NULL ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE nro_boleta IS NOT NULL AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
		           .  " pap.rut ~* '$cadena_buscada' OR "
		           .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		           .  " a.rut ~* '$cadena_buscada' OR a2.rut ~* '$cadena_buscada' "
		           .  " OR text(nro_boleta) = '$cadena_buscada' "
		           .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$tiempo = $id_cajero = $rendidas = $regimen = null;	
} else {

	switch ($tiempo) {
		case "1":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND p.fecha BETWEEN now()-'1 days'::interval AND now() ";
			break;
		case "2":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND p.fecha BETWEEN now()-'7 days'::interval AND now() ";
			break;
		case "3":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND p.fecha BETWEEN now()-'1 month'::interval AND now() ";
			break;
		case "4":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND p.fecha BETWEEN now()-'6 month'::interval AND now() ";
			break;
		case "5":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND p.fecha BETWEEN now()-'1 years'::interval AND now() ";
			break;
		case "6":
			$fec_ini = $fec_fin = "";
			break;
	}
	
	if ($rendidas == "t") {
		$condicion .= "AND id_arqueo IS NOT NULL ";
	} elseif ($rendidas == "f") {
		$condicion .= "AND id_arqueo IS NULL ";
	}
	
	if ($fec_ini <> "" && $fec_fin <> "") {
		if (strtotime($fec_ini) == -1 || strtotime($fec_fin) == -1) {
			echo(msje_js("Las fechas de búsqueda están mal ingresadas. Por favor use el formato DD-MM-AAAA"));
		} else {
			$condicion .= "AND p.fecha BETWEEN '$fec_ini'::date AND '$fec_fin'::date ";
		}
	}
	
	if ($id_cajero > 0) {
		$condicion .= "AND p.id_cajero=$id_cajero ";
	}
	
	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen='$regimen' OR a2.regimen='$regimen' OR p.nulo) "; }	
	
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$detalle = ",char_comma_sum(pd.monto_pagado::text) AS montos_pagados,char_comma_sum(g.nombre) AS glosas";

$SQL_anos_contratos = "SELECT char_comma_sum(c2.ano::text) 
                       FROM finanzas.pagos_detalle pd2 
                       LEFT JOIN finanzas.cobros cob2 ON pd2.id_cobro=cob2.id 
                       LEFT JOIN finanzas.glosas g2 ON g2.id=cob2.id_glosa
                       LEFT JOIN finanzas.contratos c2 ON c2.id=cob2.id_contrato
                       WHERE pd2.id_pago=p.id
                       ORDER BY c2.ano";

$SQL_glosas = "SELECT char_comma_sum(g2.nombre::text) 
               FROM finanzas.pagos_detalle pd2 
               LEFT JOIN finanzas.cobros cob2 ON pd2.id_cobro=cob2.id 
               LEFT JOIN finanzas.glosas g2 ON g2.id=cob2.id_glosa
               LEFT JOIN finanzas.contratos c2 ON c2.id=cob2.id_contrato
               WHERE pd2.id_pago=p.id
               ORDER BY c2.ano";

$SQL_montos_pagados = "SELECT char_comma_sum(pd2.monto_pagado::text) 
                       FROM finanzas.pagos_detalle pd2 
                       LEFT JOIN finanzas.cobros cob2 ON pd2.id_cobro=cob2.id 
                       LEFT JOIN finanzas.glosas g2 ON g2.id=cob2.id_glosa
                       LEFT JOIN finanzas.contratos c2 ON c2.id=cob2.id_contrato
                       WHERE pd2.id_pago=p.id
                       ORDER BY c2.ano";
                       
$SQL_boletas = "SELECT DISTINCT ON (nro_boleta) p.id,p.nro_boleta,to_char(p.fecha,'DD-MM-YYYY') AS fecha,u.nombre_usuario AS cajero,
                       coalesce(efectivo,0)+coalesce(cheque,0)+coalesce(cheque_afecha,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0) AS monto_boleta,
                       CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,cob.id_contrato,
                       CASE 
                         WHEN cob.id_contrato IS NOT NULL THEN coalesce(a.rut,pap.rut)||' '||coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                         WHEN cob.id_alumno IS NOT NULL THEN a2.rut||' '||a2.apellidos||' '||a2.nombres
                         WHEN p.nulo THEN '****** NULO ******'
                       END AS alumno,($SQL_anos_contratos) AS anos_contratos,($SQL_glosas) AS glosas,($SQL_montos_pagados) AS montos_pagados
                FROM finanzas.pagos AS p
                LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
                LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
                LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
                LEFT JOIN finanzas.glosas AS g         ON g.id=cob.id_glosa
                LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
                LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
                LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
                LEFT JOIN pap                          ON pap.id=c.id_pap
                LEFT JOIN carreras AS car			   ON car.id=c.id_carrera
                $condicion 
                ORDER BY p.nro_boleta DESC 
                $limite_reg
                OFFSET $reg_inicio";
$boletas     = consulta_sql($SQL_boletas);
//echo($SQL_boletas);
$HTML_boletas = "";
if (count($boletas) > 0) {
	for ($x=0;$x<count($boletas);$x++) {
		extract($boletas[$x]);
		
		$enl = "$enlbase_sm=ver_pago&id_pago=$id";
		$enlace = "a class='enlitem' href='$enl'";
		$monto_boleta = number_format($monto_boleta,0,',','.');
		$nro_boleta   = number_format($nro_boleta,0,',','.');
		if ($id_contrato > 0) {
			$alumno = "<a href='$enlbase=form_matricula_ver&id_contrato=$id_contrato' title='Pinche para ver Contrato Nº $id_contrato asociado' class='enlaces'>$alumno</a>";
		}
		$nro_boleta = "<a href='$enl' id='sgu_fancybox' class='enlaces'>$nro_boleta</a>";
		$HTML_boletas .= "  <tr class='filaTabla'>\n"
					  . "    <td class='textoTabla' align='center' style='color: #7F7F7F'>$id</td>\n"
					  . "    <td class='textoTabla' align='right'>$nro_boleta</td>\n"
					  . "    <td class='textoTabla'>$fecha</td>\n"
					  . "    <td class='textoTabla'>$alumno</td>\n"
					  . "    <td class='textoTabla'>$cajero</td>\n"
					  . "    <td class='textoTabla' align='right'>$monto_boleta</td>\n"
					  . "    <td class='textoTabla' align='center'>$rendida</td>\n"
					  . "  </tr>\n";
	}
} else {
	$HTML_boletas = "  <tr>"
				  . "    <td class='textoTabla' colspan='5'>"
				  . "      No hay registros para los criterios de búsqueda/selección"
				  . "    </td>\n"
				  . "  </tr>";
}

$enlace_nav = "$enlbase=$modulo"
            . "&fec_ini=$fec_ini"
            . "&fec_fin=$fec_fin"
            . "&rendidas=$rendidas"
            . "&tiempo=$tiempo"
            . "&id_cajero=$id_cajero"
            . "&regimen=$regimen"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";

if (count($boletas) > 0) {
	$SQL_total_boletas =  "SELECT DISTINCT ON (p.id) count(p.id) AS total_boletas 
	                       FROM finanzas.pagos AS p 
						   LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
						   LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
						   LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
						   LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
						   LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
						   LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
						   LEFT JOIN pap                          ON pap.id=c.id_pap
						   LEFT JOIN carreras AS car		      ON car.id=c.id_carrera
	                       $condicion
	                       GROUP BY p.id";
	$total_boletas = consulta_sql($SQL_total_boletas);
	//$tot_reg = $total_boletas[0]['total_boletas'];
	$tot_reg = count($total_boletas);
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$SQL_cajeros = "SELECT id,apellido||' '||nombre||' ('||nombre_usuario||')' AS nombre 
                FROM usuarios WHERE id IN (SELECT DISTINCT ON (id_cajero) id_cajero FROM finanzas.pagos) ORDER BY apellido,nombre";
$cajeros     = consulta_sql($SQL_cajeros);                

$tiempos = array(array('id'=>"1",'nombre'=>"último día"),
                 array('id'=>"2",'nombre'=>"última semana"),
                 array('id'=>"3",'nombre'=>"último mes"),
                 array('id'=>"4",'nombre'=>"último semestre"),
                 array('id'=>"5",'nombre'=>"último año"),
                 array('id'=>"6",'nombre'=>"Todas"),
                 array('id'=>"7",'nombre'=>"Otro (especificar)"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");


?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Mostrar boletas del cajero(a):<br>
          <select name="id_cajero" onChange="submitform();" class="filtro">
            <option value="">Todo(a)s</option>
            <?php echo(select($cajeros,$id_cajero)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Temporalidad:<br>
          <select name="tiempo" onChange="submitform();" class="filtro">
            <?php echo(select($tiempos,$tiempo)); ?>    
          </select>
          <?php if ($tiempo == 7) { ?>
          <input type="text" name="fec_ini" value="<?php echo($fec_ini); ?>" size="8" id="fec_ini" class="boton" style='font-size: 8pt'>
          <input type="text" name="fec_fin" value="<?php echo($fec_fin); ?>" size="8" id="fec_fin" class="boton" style='font-size: 8pt'>
          <script>document.getElementById("fec_ini").focus();</script>
          <input type='submit' name='buscar' value='Buscar' style='font-size: 7pt'>
          <?php } ?>
        </td>
        <td class="celdaFiltro">
          Rendidas:<br>
          <select name="rendidas" onChange="submitform();" class="filtro">
			<option value="0">Todas</option>
            <?php echo(select($sino,$rendidas)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Régimen:<br> 
          <select name="regimen" onChange="submitform();" class="filtro">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro" colspan="4">
          Buscar por Nº de Boleta, RUT o nombre del alumno:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="60" id="texto_buscar" class="boton">
          <script>document.getElementById("texto_buscar").focus();</script>     
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	}
          ?>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> boleta(s) en total, en página(s) de
      <select name="cant_reg" onChange="submitform();" class="filtro">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas<br>    
    </td>
    <td class="texto" colspan="2"><?php echo($HTML_paginador); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='color: #7F7F7F'>ID</td>
    <td class='tituloTabla'>Nº Boleta</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Alumno</td>
    <td class='tituloTabla'>Cajero</td>
    <td class='tituloTabla'>Monto Total</td>
    <td class='tituloTabla'>Rendida</td>
  </tr>
  <?php echo($HTML_boletas); ?>
</table>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'height'			: 400,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 500,
		'height'			: 350,
		'maxHeight'			: 350,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
<!-- Fin: <?php echo($modulo); ?> -->
