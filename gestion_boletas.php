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
$nulas        = $_REQUEST['nulas'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if (empty($tiempo) && empty($fec_ini) && empty($fec_fin)) { $tiempo=1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($fec_ini)) { $fec_ini = date("d-m-Y"); }
if (empty($fec_fin)) { $fec_fin = date("d-m-Y"); }
if (empty($nulas))   { $nulas = "t"; }

$condicion = "WHERE (p.nro_boleta IS NOT NULL OR p.nro_factura IS NOT NULL) ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE nro_boleta IS NOT NULL AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
		           .  " pap.rut ~* '$cadena_buscada' OR "
		           .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR lower(a3.nombres||' '||a3.apellidos) ~* '$cadena_buscada' OR "
		           .  " a.rut ~* '$cadena_buscada' OR a2.rut ~* '$cadena_buscada' OR a3.rut ~* '$cadena_buscada' "
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
		
	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND ('$regimen' IN (car.regimen,car2.regimen,car3.regimen) OR p.nulo) "; }	

	if ($nulas == "f") { $condicion .= " AND NOT p.nulo "; }
	
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_boletas = "SELECT DISTINCT ON (coalesce(p.nro_boleta,p.nro_factura)) p.id,coalesce(p.nro_boleta,p.nro_factura) AS nro_docto,
                       CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' WHEN p.nro_factura IS NOT NULL THEN 'F' END AS tipo_doc,
                       to_char(p.fecha,'DD-MM-YYYY') AS fecha,u.nombre_usuario AS cajero,
                       coalesce(efectivo,0)+coalesce(cheque,0)+coalesce(cheque_afecha,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0) AS monto_boleta,
                       CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,cob.id_contrato,cob.id_convenio_ci,
                       CASE 
                         WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)||' '||coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                         WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut||' '||a3.apellidos||' '||a3.nombres 
                         WHEN cob.id_alumno IS NOT NULL      THEN a2.rut||' '||a2.apellidos||' '||a2.nombres
                         WHEN p.nulo THEN '****** NULO ******'
                       END AS alumno,
                       CASE 
                         WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)
                         WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut
                         WHEN cob.id_alumno IS NOT NULL      THEN a2.rut
                         WHEN p.nulo THEN '****** NULO ******'
                       END AS rut_alumno,p.fecha_reg,coalesce(car.nombre,car2.nombre,car3.nombre) AS carrera_alumno,
                       efectivo,cheque,cheque_afecha,transferencia,tarj_credito,tarj_debito
                FROM finanzas.pagos AS p
                LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
                LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
                LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
                LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
                LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci 
                LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
                LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
                LEFT JOIN alumnos AS a3                ON a3.id=cci.id_alumno
                LEFT JOIN pap                          ON pap.id=c.id_pap
                LEFT JOIN carreras AS car              ON car.id=c.id_carrera
                LEFT JOIN carreras AS car2             ON car2.id=a2.carrera_actual
                LEFT JOIN carreras AS car3             ON car3.id=a3.carrera_actual
                $condicion 
                ORDER BY coalesce(p.nro_boleta,p.nro_factura) DESC ";
$SQL_tabla_completa = "COPY ($SQL_boletas) to stdout WITH CSV HEADER";
$SQL_boletas .= "$limite_reg OFFSET $reg_inicio";
$boletas     = consulta_sql($SQL_boletas);
//echo($SQL_boletas);
$HTML_boletas = "";
if (count($boletas) > 0) {
	for ($x=0;$x<count($boletas);$x++) {
		extract($boletas[$x]);
		
		$enl = "$enlbase_sm=ver_pago&id_pago=$id";
		$enlace = "a class='enlitem' href='$enl'";
		$monto_boleta = number_format($monto_boleta,0,',','.');
		$nro_docto    = number_format($nro_docto,0,',','.');
		if ($id_contrato > 0) {
			$alumno = "<a href='$enlbase=form_matricula_ver&id_contrato=$id_contrato' title='Pinche para ver Contrato Nº $id_contrato asociado' class='enlaces'>$alumno</a>";
		}
		$nro_docto = "<a href='$enl' id='sgu_fancybox' class='enlaces'>$tipo_doc/$nro_docto</a>";
		$HTML_boletas .= "  <tr class='filaTabla'>\n"
					  . "    <td class='textoTabla' align='center' style='color: #7F7F7F'>$id</td>\n"
					  . "    <td class='textoTabla' align='right'>$nro_docto</td>\n"
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
	                       LEFT JOIN finanzas.cobros AS cob       ON cob.id=pd.id_cobro 
	                       LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
	                       LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci 
	                       LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
	                       LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
	                       LEFT JOIN alumnos AS a3                ON a3.id=cob.id_convenio_ci
	                       LEFT JOIN pap                          ON pap.id=c.id_pap
	                       LEFT JOIN carreras AS car              ON car.id=c.id_carrera
	                       LEFT JOIN carreras AS car2             ON car2.id=a2.carrera_actual
	                       LEFT JOIN carreras AS car3             ON car3.id=a3.carrera_actual
	                       $condicion
	                       GROUP BY p.id";
	$total_boletas = consulta_sql($SQL_total_boletas);
	//$tot_reg = $total_boletas[0]['total_boletas'];
	$tot_reg = count($total_boletas);
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

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
          Nulas:<br>
          <select name="nulas" onChange="submitform();" class="filtro">
            <?php echo(select($sino,$nulas)); ?>    
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
        <td class="celdaFiltro">
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
        <td class="celdaFiltro">Acciones:<br><a id="sgu_fancybox" href='<?php echo("$enlbase_sm=anular_pago"); ?>' class='boton'>Anular Pago</a></td>
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
    <td class="texto" colspan="2"><?php echo($HTML_paginador); ?> <?php echo($boton_tabla_completa); ?></td>
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
