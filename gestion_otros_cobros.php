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
$id_emisor    = $_REQUEST['id_emisor'];
$pagados      = $_REQUEST['pagados'];
$regimen      = $_REQUEST['regimen'];
$fec_ini      = $_REQUEST['fec_ini'];
$fec_fin      = $_REQUEST['fec_fin'];
$id_glosa     = $_REQUEST['id_glosa'];
$id_agrupador = $_REQUEST['id_agrupador'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if (empty($tiempo) && empty($fec_ini) && empty($fec_fin)) { $tiempo=1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($fec_ini)) { $fec_ini = date("d-m-Y"); }
if (empty($fec_fin)) { $fec_fin = date("d-m-Y"); }

$condicion = "WHERE g.tipo ~* 'Otros Ingresos' ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		           .  " a.rut ~* '$cadena_buscada'"
		           .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$id_emisor = $pagados = $id_glosa = $regimen = null;
	$tiempo = 6;	
} else {

	switch ($tiempo) {
		case "1":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND c.fecha_venc BETWEEN now()-'1 days'::interval AND now() ";
			break;
		case "2":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND c.fecha_venc BETWEEN now()-'7 days'::interval AND now() ";
			break;
		case "3":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND c.fecha_venc BETWEEN now()-'1 month'::interval AND now() ";
			break;
		case "4":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND c.fecha_venc BETWEEN now()-'6 month'::interval AND now() ";
			break;
		case "5":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND c.fecha_venc BETWEEN now()-'1 years'::interval AND now() ";
			break;
		case "6":
			$fec_ini = $fec_fin = "";
			break;
	}
	
	if ($pagados == "t") {
		$condicion .= "AND c.pagado ";
	} elseif ($pagados == "f") {
		$condicion .= "AND NOT c.pagado ";
	}
	
	if ($fec_ini <> "" && $fec_fin <> "") {
		if (strtotime($fec_ini) == -1 || strtotime($fec_fin) == -1) {
			echo(msje_js("Las fechas de búsqueda están mal ingresadas. Por favor use el formato DD-MM-AAAA"));
		} else {
			$condicion .= "AND c.fecha_venc BETWEEN '$fec_ini'::date AND '$fec_fin'::date ";
		}
	}
	
	if ($id_emisor > 0) {
		$condicion .= "AND c.id_usuario=$id_emisor ";
	}
	
	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen='$regimen') "; }
	
	if ($id_glosa > 0) { $condicion .= "AND c.id_glosa=$id_glosa"; }
	
	if ($id_agrupador <> "") { $condicion .= "AND g.agrupador='$id_agrupador'"; }
	
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_cobros = "SELECT c.id,va.rut,va.nombre AS nombre_alumno,carrera,g.nombre as glosa,monto,to_char(fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,
                      u.nombre_usuario AS emisor,g.agrupador,
                      CASE WHEN c.pagado THEN 'Si' ELSE 'No' END pagado,
                      CASE WHEN c.abonado THEN 'Si' ELSE 'No' END abonado,
                      CASE WHEN c.abonado THEN c.monto-c.monto_abonado ELSE 0 END saldo,
                      (SELECT char_comma_sum(coalesce(nro_boleta::text,'')) FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id)) AS nro_boleta,
                      (SELECT char_comma_sum(id_pago::text) FROM finanzas.pagos_detalle WHERE id_cobro=c.id) AS id_pago,
                      (SELECT char_comma_sum(to_char(fecha,'DD-tmMon-YYYY')) FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id)) AS fecha_pago
               FROM finanzas.cobros      AS c 
               LEFT JOIN finanzas.glosas AS g   ON g.id=id_glosa 
               LEFT JOIN vista_alumnos   AS va  ON va.id=id_alumno
               LEFT JOIN alumnos         AS a   ON a.id=id_alumno
               LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
               LEFT JOIN usuarios        AS u   ON u.id=c.id_usuario
               $condicion 
               ORDER BY c.fecha_venc DESC
               $limite_reg
               OFFSET $reg_inicio";
$cobros = consulta_sql($SQL_cobros);

$HTML_cobros = "";
if (count($cobros) > 0) {
	for ($x=0;$x<count($cobros);$x++) {
		extract($cobros[$x]);
		
		$enl = "$enlbase_sm=ver_pago&id_pago=$id";
		$enlace = "a class='enlitem' href='$enl'";
		$monto = number_format($monto,0,',','.');
		$saldo = number_format($saldo,0,',','.');
		
		$fecha_pago = str_replace(",","<br>",$fecha_pago);
	
		$nro_boleta = explode(",",$nro_boleta);
		$id_pago    = explode(",",$id_pago);
		
		$nro_bol = "";
		for($i=0;$i<count($nro_boleta);$i++) {
			$nro_bol .= "<a href='$enlbase_sm=ver_pago&id_pago={$id_pago[$i]}' id='sgu_fancybox_medium' class='enlaces'>{$nro_boleta[$i]}</a><br>";
		}
		$nro_boleta = $nro_bol;
		$id_pago    = str_replace(",","<br>",implode(",",$id_pago));
		
		if ($pagado=="No" && $abonado=="Si") { $pagado = "No ($$saldo)"; }
		
		$HTML_cobros .= "  <tr class='filaTabla'>\n"
					 . "    <td class='textoTabla' align='right' style='color: #7F7F7F'>$id</td>\n"
					 . "    <td class='textoTabla'>$agrupador:<br>$glosa</td>\n"
					 . "    <td class='textoTabla' align='right'>$fecha_venc</td>\n"
					 . "    <td class='textoTabla'><div>$rut<span style='color: white'>;</span></div><div>$nombre_alumno</div></td>\n"
					 . "    <td class='textoTabla'>$carrera</td>\n"
					 . "    <td class='textoTabla'>$emisor</td>\n"
					 . "    <td class='textoTabla' align='right'>$monto</td>\n"
					 . "    <td class='textoTabla' align='center'><span class='$pagado'>$pagado</span></td>\n"
		             . "    <td class='textoTabla' style='vertical-align: top' align='right'>$nro_boleta</td>\n"
		             . "    <td class='textoTabla' style='vertical-align: top; text-align: right; color: #7F7F7F'>$id_pago</td>\n"
		             . "    <td class='textoTabla' style='vertical-align: top' align='right'>$fecha_pago</td>\n"
		             . "  </tr>\n";
	}
} else {
	$HTML_cobros = "  <tr>"
				 . "    <td class='textoTabla' colspan='10'>"
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

if (count($cobros) > 0) {
	$SQL_total_cobros =  "SELECT count(c.id) AS total_cobros
	                      FROM finanzas.cobros      AS c 
	                      LEFT JOIN finanzas.glosas AS g   ON g.id=id_glosa 
	                      LEFT JOIN vista_alumnos   AS va  ON va.id=id_alumno
	                      LEFT JOIN alumnos         AS a   ON a.id=id_alumno
	                      LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
	                      $condicion ";
	$total_cobros = consulta_sql($SQL_total_cobros);
	$tot_reg = $total_cobros[0]['total_cobros'];
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$SQL_cajeros = "SELECT id,apellido||' '||nombre||' ('||nombre_usuario||')' AS nombre 
                FROM usuarios WHERE id IN (SELECT DISTINCT ON (id_usuario) id_usuario FROM finanzas.cobros) ORDER BY apellido,nombre";
$EMISORES     = consulta_sql($SQL_cajeros);                

$tiempos = array(array('id'=>"1",'nombre'=>"último día"),
                 array('id'=>"2",'nombre'=>"última semana"),
                 array('id'=>"3",'nombre'=>"último mes"),
                 array('id'=>"4",'nombre'=>"último semestre"),
                 array('id'=>"5",'nombre'=>"último año"),
                 array('id'=>"6",'nombre'=>"Todas"),
                 array('id'=>"7",'nombre'=>"Otro (especificar)"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$AGRUPADOR = consulta_sql("SELECT DISTINCT ON (agrupador) agrupador AS id,agrupador AS nombre FROM finanzas.glosas WHERE tipo ~* 'Otros Ingresos' AND agrupador IS NOT NULL ORDER BY agrupador");

$cond_glosas = "WHERE tipo ~* 'Otros Ingresos' ";
if ($id_agrupador <> "") { $cond_glosas .= " AND agrupador='$id_agrupador'"; }
$GLOSAS = consulta_sql("SELECT id,nombre FROM finanzas.glosas $cond_glosas ORDER BY nombre");

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
          Mostrar cobros emitidos por:<br>
          <select name="id_emisor" onChange="submitform();" class="filtro">
            <option value="">Todo(a)s</option>
            <?php echo(select($EMISORES,$id_emisor)); ?>    
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
          Agrupador:<br>
          <select name="id_agrupador" onChange="submitform();" class="filtro">
			<option value="">Todas</option>
            <?php echo(select($AGRUPADOR,$id_agrupador)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Glosa:<br>
          <select name="id_glosa" onChange="submitform();" class="filtro">
			<option value="0">Todas</option>
            <?php echo(select($GLOSAS,$id_glosa)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Pagados:<br>
          <select name="pagados" onChange="submitform();" class="filtro">
			<option value="0">Todos</option>
            <?php echo(select($sino,$pagados)); ?>    
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
          Buscar por RUT o nombre del alumno:<br>
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
      Mostrando <b><?php echo($tot_reg); ?></b> cobros(s) en total, en página(s) de
      <select name="cant_reg" onChange="submitform();" class="filtro">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas<br>    
    </td>
    <td class="texto" colspan="5"><?php echo($HTML_paginador); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='color: #7F7F7F'>ID</td>
    <td class='tituloTabla'>Glosa</td>
    <td class='tituloTabla'>Fecha<br>Vencimiento</td>
    <td class='tituloTabla'>Alumno</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Emisor</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>Pagado?</td>
    <td class='tituloTabla'>Nº<br>Boleta</td>
    <td class='tituloTabla' style='color: #7F7F7F'>ID<br>Pago</td>
    <td class='tituloTabla'>Fecha<br>Pago</td>
  </tr>
  <?php echo($HTML_cobros); ?>
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
		'afterClose'		: function () { location.reload(false); },
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
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 700,
		'height'			: 600,
		'maxHeight'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});
</script>
<!-- Fin: <?php echo($modulo); ?> -->
