<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$tipo_docto_b = $_REQUEST['tipo_docto_b'];
$nro_docto_b  = $_REQUEST['nro_docto_b'];
$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$tiempo       = $_REQUEST['tiempo'];
$id_cajero    = $_REQUEST['id_cajero'];
$id_erp       = $_REQUEST['id_erp'];
$regimen      = $_REQUEST['regimen'];
$fec_ini      = $_REQUEST['fec_ini'];
$fec_fin      = $_REQUEST['fec_fin'];
$nulas        = $_REQUEST['nulas'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if (empty($tiempo) && empty($fec_ini) && empty($fec_fin)) { $tiempo = 3; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($fec_ini)) { $fec_ini = date("Y-m-d"); }
if (empty($fec_fin)) { $fec_fin = date("Y-m-d"); }
if (empty($nulas))   { $nulas = "t"; }

$condicion = "WHERE (p.nro_boleta IS NOT NULL OR p.nro_boleta_e IS NOT NULL OR p.nro_factura IS NOT NULL) ";

if ($buscar == "Buscar" && is_numeric($nro_docto_b)) {
	$condicion .= " AND nc.nro_docto = $nro_docto_b ";
} else {

	if ($buscar == 'Buscar' && $texto_buscar <> "") {
		$texto_buscar_regexp = sql_regexp($texto_buscar);
		$textos_buscar = explode(" ",$texto_buscar_regexp);
		$condicion = "WHERE (nro_boleta IS NOT NULL OR nro_boleta_e IS NOT NULL) AND ";
		for ($x=0;$x<count($textos_buscar);$x++) {
			$cadena_buscada = strtolower($textos_buscar[$x]);
	/*
			$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
					   .  " pap.rut ~* '$cadena_buscada' OR "
					   .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR lower(a3.nombres||' '||a3.apellidos) ~* '$cadena_buscada' OR "
					   .  " a.rut ~* '$cadena_buscada' OR a2.rut ~* '$cadena_buscada' OR a3.rut ~* '$cadena_buscada' "
					   .  " OR text(nro_boleta) = '$cadena_buscada' "
					   .  " OR text(nro_boleta_e) = '$cadena_buscada' "
					   .  ") AND ";
	*/
			$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
					   .  " pap.rut ~* '$cadena_buscada' OR "
					   .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR lower(a3.nombres||' '||a3.apellidos) ~* '$cadena_buscada' OR "
					   .  " a.rut ~* '$cadena_buscada' OR a2.rut ~* '$cadena_buscada' OR a3.rut ~* '$cadena_buscada' "
					   .  ") AND ";
		}
		$condicion=substr($condicion,0,strlen($condicion)-4);
		$tiempo = $id_cajero = $rendidas = $regimen = null;	
	} else {	

		switch ($tiempo) {
			case "1":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND nc.fecha BETWEEN now()-'1 days'::interval AND now() ";
				break;
			case "2":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND nc.fecha BETWEEN now()-'7 days'::interval AND now() ";
				break;
			case "3":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND nc.fecha BETWEEN now()-'1 month'::interval AND now() ";
				break;
			case "4":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND nc.fecha BETWEEN now()-'6 month'::interval AND now() ";
				break;
			case "5":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND nc.fecha BETWEEN now()-'1 years'::interval AND now() ";
				break;
			case "6":
				$fec_ini = $fec_fin = "";
				break;
		}
		
	

		if ($fec_ini <> "" && $fec_fin <> "") {
			if (strtotime($fec_ini) == -1 || strtotime($fec_fin) == -1) {
				echo(msje_js("Las fechas de búsqueda están mal ingresadas. Por favor use el formato DD-MM-AAAA"));
			} else {
				$condicion .= "AND nc.fecha BETWEEN '$fec_ini'::date AND '$fec_fin'::date ";
			}
		}
		
		if ($id_cajero > 0) { $condicion .= "AND p.id_cajero=$id_cajero "; }
			
		if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND ('$regimen' IN ( car.regimen,car2.regimen,car3.regimen) ) "; }	

	}
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_nc = "SELECT DISTINCT ON (nc.nro_docto) nc.nro_docto,coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura) AS nro_docto_pago,nc.id_pago,nc.observacion,
                              CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' 
                                   WHEN p.nro_boleta_e IS NOT NULL THEN 'BE' 
                                   WHEN p.nro_factura IS NOT NULL THEN 'F' 
                              END AS tipo_docto_pago,id_contrato,id_convenio_ci,nc.monto,
                              to_char(nc.fecha,'DD-MM-YYYY') AS fecha,to_char(nc.fecha_reg,'DD-MM-YYYY HH24:MI') AS fecha_reg,u.nombre_usuario AS cajero,
                              CASE WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)||' '||coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                                   WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut||' '||a3.apellidos||' '||a3.nombres 
                                   WHEN cob.id_alumno IS NOT NULL      THEN a2.rut||' '||a2.apellidos||' '||a2.nombres
                              END AS alumno,
                              CASE WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)
                                   WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut
                                   WHEN cob.id_alumno IS NOT NULL      THEN a2.rut                               
                              END AS rut_alumno,
							  to_char(p.fecha,'DD-MM-YYYY') AS fecha_pago,u2.nombre_usuario AS cajero_pago,u.nombre AS nombre_cajero,
							  coalesce(efectivo,0)+coalesce(deposito,0)+coalesce(cheque,0)+coalesce(cheque_afecha,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0) AS monto_pago
           FROM finanzas.notas_credito AS nc
		   LEFT JOIN vista_usuarios AS u                   ON u.id=id_cajero
           LEFT JOIN finanzas.notas_credito_detalle AS ncd ON ncd.nro_nc_docto=nc.nro_docto
		   LEFT JOIN finanzas.pagos AS p                   ON p.id=nc.id_pago
		   LEFT JOIN vista_usuarios AS u2                  ON u2.id=p.id_cajero
		   LEFT JOIN finanzas.cobros AS cob                ON cob.id=ncd.id_cobro
		   LEFT JOIN finanzas.contratos AS c               ON c.id=cob.id_contrato 
           LEFT JOIN finanzas.convenios_ci AS cci          ON cci.id=cob.id_convenio_ci 
           LEFT JOIN alumnos AS a                          ON a.id=c.id_alumno
           LEFT JOIN alumnos AS a2                         ON a2.id=cob.id_alumno
           LEFT JOIN alumnos AS a3                         ON a3.id=cci.id_alumno
           LEFT JOIN pap                                   ON pap.id=c.id_pap
           LEFT JOIN carreras AS car                       ON car.id=c.id_carrera
           LEFT JOIN carreras AS car2                      ON car2.id=a2.carrera_actual
           LEFT JOIN carreras AS car3                      ON car3.id=a3.carrera_actual
		   $condicion 
           ORDER BY nc.nro_docto DESC ";
$nc = consulta_sql($SQL_nc);
//echo($SQL_nc);

$SQL_nc = "SELECT * FROM ($SQL_nc) AS foo ORDER BY nro_docto DESC ";
$SQL_tabla_completa = "COPY ($SQL_nc) to stdout WITH CSV HEADER";
$SQL_boletas .= "$limite_reg OFFSET $reg_inicio";
$nc     = consulta_sql($SQL_nc);

$HTML_nc = "";

$enlace_nav = "$enlbase=$modulo"
            . "&fec_ini=$fec_ini"
            . "&fec_fin=$fec_fin"
            . "&rendidas=$rendidas"
            . "&erp=$erp"
            . "&tiempo=$tiempo"
            . "&id_cajero=$id_cajero"
            . "&regimen=$regimen"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";
            
if (count($nc) > 0) {
	$SQL_total_nc =  "SELECT DISTINCT ON (nro_docto) nc.nro_docto
                      FROM finanzas.notas_credito AS nc
		              LEFT JOIN vista_usuarios AS u                   ON u.id=id_cajero
                      LEFT JOIN finanzas.notas_credito_detalle AS ncd ON ncd.nro_nc_docto=nc.nro_docto
		              LEFT JOIN finanzas.pagos AS p                   ON p.id=nc.id_pago
		              LEFT JOIN finanzas.cobros AS cob                ON cob.id=ncd.id_cobro
		              LEFT JOIN finanzas.contratos AS c               ON c.id=cob.id_contrato 
                      LEFT JOIN finanzas.convenios_ci AS cci          ON cci.id=cob.id_convenio_ci 
                      LEFT JOIN alumnos AS a                          ON a.id=c.id_alumno
                      LEFT JOIN alumnos AS a2                         ON a2.id=cob.id_alumno
                      LEFT JOIN alumnos AS a3                         ON a3.id=cci.id_alumno
                      LEFT JOIN pap                                   ON pap.id=c.id_pap
                      LEFT JOIN carreras AS car                       ON car.id=c.id_carrera
                      LEFT JOIN carreras AS car2                      ON car2.id=a2.carrera_actual
                      LEFT JOIN carreras AS car3                      ON car3.id=a3.carrera_actual
		              $condicion ";
	$total_nc = consulta_sql($SQL_total_nc);
	$tot_reg = count($total_nc);
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
	
	for ($x=0;$x<count($nc);$x++) {
		extract($nc[$x]);
		
		$monto = number_format($monto,0,',','.');
		$monto_pago = number_format($monto_pago,0,',','.');

		$enl       = "$enlbase_sm=ver_nc&nro_docto=$nro_docto";
		$nro_docto = number_format($nro_docto,0,',','.');
		//$nro_docto = "<a href='$enl' id='sgu_fancybox' class='enlaces'>NCE/$nro_docto</a>";
		$nro_docto = "NCE/$nro_docto";

		if ($id_contrato > 0) {
			$alumno = "<a href='$enlbase=form_matricula_ver&id_contrato=$id_contrato' title='Pinche para ver Contrato Nº $id_contrato asociado' class='enlaces'>$alumno</a>";
		}

		$enl_pago       = "$enlbase_sm=ver_pago&id_pago=$id_pago";
		$nro_docto_pago = number_format($nro_docto_pago,0,',','.');
		$nro_docto_pago = "<a href='$enl_pago' id='sgu_fancybox' class='enlaces'>$tipo_docto_pago/$nro_docto_pago</a>";

		$HTML_nc .= "  <tr class='filaTabla'>\n"
				 . "    <td class='textoTabla' align='right'><a title='Observación: $observacion'>$nro_docto</a></td>\n"
				 . "    <td class='textoTabla'><a title='Registrada el $fecha_reg'>$fecha</a></td>\n"
				 . "    <td class='textoTabla'><a title='$nombre_cajero'>$cajero</a></td>\n"
				 . "    <td class='textoTabla' align='right'>$monto</td>\n"
				 . "    <td class='textoTabla'>$alumno</td>\n"
				 . "    <td class='textoTabla' align='right'>$nro_docto_pago</td>\n"
				 . "    <td class='textoTabla'>$fecha_pago</td>\n"
				 . "    <td class='textoTabla'>$cajero_pago</td>\n"
				 . "    <td class='textoTabla' align='right'>$monto_pago</td>\n"
				 . "  </tr>\n";
	}
} else {
	$HTML_nc = "  <tr>"
			 . "    <td class='textoTabla' colspan='7' align='center'>"
			 . "      <br>** No hay registros para los criterios de búsqueda/selección **<br><br>"
			 . "    </td>\n"
			 . "  </tr>";
}

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$id_sesion = $_SESSION['usuario']."_".$modulo."_detalle_".session_id();
$boton_tc_detalle_pagos = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>TC Det. Pagos</small></a>";
$nombre_arch            = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tc_detalle_pagos);

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


$TIPOS_DOCTOS = array(array('id' => "nro_boleta_e", 'nombre' => "Bol-E"),
                      array('id' => "nro_factura",  'nombre' => "Fac-E"),
                      array('id' => "nro_boleta",   'nombre' => "Boleta"));
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
          Mostrar del cajero(a):<br>
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
          <input type="date" name="fec_ini" value="<?php echo($fec_ini); ?>" id="fec_ini" class="boton" style='font-size: 8pt'>
          <input type="date" name="fec_fin" value="<?php echo($fec_fin); ?>" id="fec_fin" class="boton" style='font-size: 8pt'>
          <script>document.getElementById("fec_ini").focus();</script>
          <input type='submit' name='buscar' value='Buscar' style='font-size: 10pt'>
          <?php } ?>
        </td>
<!--        <td class="celdaFiltro">
          ERP:<br>
          <select name="id_erp" onChange="submitform();" class="filtro">
			<option value="0">Todas</option>
            <?php echo(select($sino,$id_erp)); ?>
          </select>
        </td> -->
<!--         <td class="celdaFiltro">
          Nulas:<br>
          <select name="nulas" onChange="submitform();" class="filtro">
            <?php echo(select($sino,$nulas)); ?>    
          </select>
        </td> -->
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
        <td class="celdaFiltro">
          Buscar por Nº de docto:<br>
          <input type="text" name="nro_docto_b" value="<?php echo($nro_docto_b); ?>" size="4" id="nro_docto" class="boton">
          <input type='submit' name='buscar' value='Buscar'>
        </td>			
        <td class="celdaFiltro">
          Buscar por RUT o nombre del estudiante:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="25" id="texto_buscar" class="boton">
          <script>document.getElementById("texto_buscar").focus();</script>     
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	}
          ?>
        </td>
<!--        <td class="celdaFiltro">
          Acciones:<br>
          <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=nota_credito_registrar"); ?>' class='boton'>Registrar Nota de Crédito</a>
        </td> -->
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> nota(s) de crédito en total, en página(s) de
      <select name="cant_reg" onChange="submitform();" class="filtro">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas<br>    
    </td>
    <td class="texto" colspan="5">
	  <?php echo($HTML_paginador); ?>
	  <?php echo($boton_tabla_completa); ?>
	</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="4">Nota de Crédito</td>	
    <td class='tituloTabla' rowspan="2">Estudiante</td>
	<td class='tituloTabla' colspan="4">Boleta/Factura</td>	
  </tr>

  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Nº Docto</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Cajer@</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>N° Docto</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Cajer@</td>
    <td class='tituloTabla'>Monto</td>
  </tr>
  <?php echo($HTML_nc); ?>
</table>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
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
